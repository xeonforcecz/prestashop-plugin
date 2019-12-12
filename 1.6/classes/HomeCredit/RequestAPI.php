<?php
/**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
**/

namespace MyLoan\HomeCredit;

use HomeCredit\OneClickApi\Entity\CreateApplicationRequest;

/**
 * Class RequestAPI
 * @package MyLoan\HomeCredit
 */
class RequestAPI extends AuthAPI
{
    /**
     *
     */
    const END_POINT_CZ = "https://api.homecredit.cz/";
    /**
     *
     */
    const END_POINT_SK = "https://api.homecredit.sk/";

    /**
     *
     */
    const END_POINT_CZ_TEST = "https://apicz-test.homecredit.net/verdun-train/";
    /**
     *
     */
    const END_POINT_SK_TEST = "https://apisk-test.homecredit.net/verdun-train/";

    /**
     *
     */
    const END_POINT_CALCULATOR_CZ = "https://api.homecredit.cz/public/v1/calculator/";
    /**
     *
     */
    const END_POINT_CALCULATOR_SK = "https://api.homecredit.sk/public/v1/calculator/";

    /**
     *
     */
    const END_POINT_CALCULATOR_PUBLIC_CZ = "https://kalkulacka.homecredit.cz/";
    /**
     *
     */
    const END_POINT_CALCULATOR_PUBLIC_TEST_CZ = "https://kalkulacka.train.hciapp.net/";
    /**
     *
     */
    const END_POINT_CALCULATOR_PUBLIC_SK = "https://kalkulacka.homecredit.sk/";
    /**
     *
     */
    const END_POINT_CALCULATOR_PUBLIC_TEST_SK = "https://kalkulacka-sk.train.hciapp.net/";

    /**
     *
     */
    const END_POINT_CALCULATOR_CZ_TEST = "https://apicz-test.homecredit.net/verdun-train/public/v1/calculator/";
    /**
     *
     */
    const END_POINT_CALCULATOR_SK_TEST = "https://apisk-test.homecredit.net/verdun-train/public/v1/calculator/";


    private $client;

    /**
     * RequestAPI constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setClient($this->authorize());
    }

    /**
     * aktualizace objednávky v Myloan
     * @param $orderNumberPS
     * @return mixed
     * @throws \PrestaShopModuleException
     */
    public function updateLoan($orderNumberPS)
    {
        $loan = new \Loan($orderNumberPS);
        $response = $this->getLoanDetail($loan->getApplicationId());

        $loan->setStateReason($response->getStateReason());
        $loan->setApplicationUrl($response->getGatewayRedirectUrl());
        $loan->setDownPayment(\MyLoan\Tools::convertNumberFromMinorUnits(
            $response->getSettingsInstallment()->getPreferredDownPayment()->getAmount(),
            $response->getSettingsInstallment()->getPreferredDownPayment()->getCurrency()
        ));
        $loan->setCurrency($response->getSettingsInstallment()->getPreferredDownPayment()->getCurrency());

        $loan->update();

        ResponseAPI::changeOrderState($loan->getStateReason(), $orderNumberPS);

        return $response;
    }

    /**
     * Vytvoření objednávky v Myloan
     * @param $orderNumberPS
     * @return mixed
     * @throws \Nette\Utils\JsonException
     */
    public function createLoan($orderNumberPS)
    {
        $context = \Context::getContext();

        $order = new \Order($orderNumberPS);
        $currency = $context->currency;
        $link = $context->link;
        $customer = $order->getCustomer();
        $deliveryAddress = new \Address($order->id_address_invoice);
        $invoiceAddress = new \Address($order->id_address_invoice);

        $response = $this->getClient()->create(
            new CreateApplicationRequest(
                $this->createCustomer($customer, $invoiceAddress),
                $this->createOrder($order, $currency, $deliveryAddress, $invoiceAddress),
                CreateApplicationRequest::TYPE_INSTALLMENT,
                $this->createMerchantUrl($link),
                $this->createSettingsInstallment($currency)
            )
        );

        $loan = new \Loan();
        $loan->setIdOrder($orderNumberPS);
        $loan->setStateReason($response->getStateReason());
        $loan->setApplicationId($response->getId());
        $loan->setApplicationUrl($response->getGatewayRedirectUrl());
        $loan->setCurrency($currency->suffix);
        $loan->add();

        return $response;
    }

    /**
     * Vezme nastavení z již nastavených session a nastaví to do Myloan systému
     * @return array|false
     * @throws \Nette\Utils\JsonException
     */
    private function createFingerprintComponents()
    {
        $loanCookie = null;
        if (isset(\Context::getContext()->cookie->hc_calculator)) {
            $loanCookie = \Nette\Utils\Json::decode(
                \Context::getContext()->cookie->hc_calculator,
                \Nette\Utils\Json::FORCE_ARRAY
            );
            unset(\Context::getContext()->cookie->hc_calculator);
            \Context::getContext()->cookie->write();
        }

        if (is_array($loanCookie) &&
          array_key_exists('preferredMonths', $loanCookie) &&
          array_key_exists('preferredInstallment', $loanCookie) &&
          array_key_exists('preferredDownPayment', $loanCookie) &&
          array_key_exists('productCode', $loanCookie)
        ) {
            return $loanCookie;
        } else {
            return false;
        }
    }

    /**
     * Vytvoří třídu customer pro další práci z OneClickApi
     * @param \Customer $customer
     * @param \Address $invoiceAddress
     * @return CreateApplicationRequest\Customer
     */
    private function createCustomer(\Customer $customer, \Address $invoiceAddress)
    {
        $address = new CreateApplicationRequest\Customer\Addresses(
            \Country::getIsoById($invoiceAddress->id_country),
            $invoiceAddress->city,
            $invoiceAddress->address1,
            \MyLoan\Tools::parseStreetNumber($invoiceAddress->address1, $invoiceAddress->address2),
            $invoiceAddress->postcode,
            CreateApplicationRequest\Customer\Addresses::ADDRESSTYPE_CONTACT,
            $invoiceAddress->firstname . " " . $invoiceAddress->lastname
        );

        return new CreateApplicationRequest\Customer(
            $customer->email,
            $invoiceAddress->phone,
            [$address],
            $customer->firstname,
            $customer->lastname,
            null,
            $invoiceAddress->vat_number,
            \Tools::getRemoteAddr()
        );
    }

    /**
     * Vytvoří jednotlivé položky pro další práci z OneClickApi
     * @param \Order $order
     * @param \Currency $currency
     * @return array
     */
    private function createItems(\Order $order, \Currency $currency)
    {
        $items = [];
        foreach ($order->getProducts() as $orderItem) {
            $itemTotalPriceWithVat = $orderItem['total_price_tax_incl'];
            $itemTotalPriceWithoutVat = $orderItem['total_price_tax_excl'];
            $itemTotalVat = $itemTotalPriceWithVat - $itemTotalPriceWithoutVat;
            $itemVatRate = $orderItem['tax_rate'];
            $itemQuantity = $orderItem['product_quantity'];

            $totalPrice = new CreateApplicationRequest\Order\Items\TotalPrice(
                \MyLoan\Tools::convertNumberToMinorUnits($itemTotalPriceWithVat, $currency->iso_code),
                $currency->iso_code
            );

            $totalVat = new CreateApplicationRequest\Order\Items\TotalVat(
                \MyLoan\Tools::convertNumberToMinorUnits($itemTotalVat, $currency->iso_code),
                $currency->iso_code,
                $itemVatRate
            );

            $unitPrice = new CreateApplicationRequest\Order\Items\UnitPrice(
                \MyLoan\Tools::convertNumberToMinorUnits($itemTotalPriceWithVat / $itemQuantity, $currency->iso_code),
                $currency->iso_code
            );

            $unitVat = new CreateApplicationRequest\Order\Items\UnitVat(
                \MyLoan\Tools::convertNumberToMinorUnits($itemTotalVat / $itemQuantity, $currency->iso_code),
                $currency->iso_code,
                $itemVatRate
            );

            $manufacturer = new \Manufacturer($orderItem['id_manufacturer']);

            $items[] = new CreateApplicationRequest\Order\Items(
                $orderItem['id_product'],
                $orderItem['product_name'],
                $totalPrice,
                $totalVat,
                $orderItem['ean13'],
                $itemQuantity,
                null,
                $manufacturer->name,
                $unitPrice,
                $unitVat,
                null
            );
        }

        return $items;
    }

    /**
     * Vytvoří objednávku pro další práci z OneClickApi
     * @param \Order $order
     * @param \Currency $currency
     * @param \Address $addressDelivery
     * @param \Address $addressInvoice
     */
    private function createOrder(
        \Order $order,
        \Currency $currency,
        \Address $addressDelivery,
        \Address $addressInvoice
    ) {
        $addressBilling = new CreateApplicationRequest\Customer\Addresses(
            \Country::getIsoById($addressInvoice->id_country),
            $addressInvoice->city,
            $addressInvoice->address1,
            \MyLoan\Tools::parseStreetNumber($addressInvoice->address1, $addressInvoice->address2),
            $addressInvoice->postcode,
            CreateApplicationRequest\Customer\Addresses::ADDRESSTYPE_BILLING,
            $addressInvoice->firstname . " " . $addressInvoice->lastname
        );

        $addressDelivery = new CreateApplicationRequest\Customer\Addresses(
            \Country::getIsoById($addressDelivery->id_country),
            $addressDelivery->city,
            $addressDelivery->address1,
            \MyLoan\Tools::parseStreetNumber($addressDelivery->address1, $addressDelivery->address2),
            $addressDelivery->postcode,
            CreateApplicationRequest\Customer\Addresses::ADDRESSTYPE_DELIVERY,
            $addressDelivery->firstname . " " . $addressInvoice->lastname
        );

        $cartTotalPriceWithVat = $order->getTotalProductsWithTaxes();
        $cartTotalPriceWithoutVat = $order->getTotalProductsWithoutTaxes();
        $cartTotalVat = $cartTotalPriceWithVat - $cartTotalPriceWithoutVat;

        $orderTotalWithVat = \MyLoan\Tools::convertNumberToMinorUnits(
            $cartTotalPriceWithVat,
            $currency->iso_code
        );

        $orderTotalVat = \MyLoan\Tools::convertNumberToMinorUnits(
            $cartTotalVat,
            $currency->iso_code
        );

        $orderTotalVatRate = \MyLoan\Tools::calcVatRate($cartTotalPriceWithVat, $cartTotalVat);

        $totalPrice = new CreateApplicationRequest\Order\TotalPrice(
            $orderTotalWithVat,
            $currency->iso_code
        );

        $totalVat = new CreateApplicationRequest\Order\TotalVat(
            $orderTotalVat,
            $currency->iso_code,
            $orderTotalVatRate
        );

        return new CreateApplicationRequest\Order(
            $order->reference,
            $totalPrice,
            [$totalVat],
            [$addressBilling, $addressDelivery],
            $this->createItems($order, $currency)
        );
    }

    /**
     * Notifikační url
     * @param \Link $link
     * @return CreateApplicationRequest\MerchantUrls
     */
    private function createMerchantUrl(\Link $link)
    {
        $loanNotificationURL = $link->getModuleLink(\MlcConfig::MODULE_NAME, 'loanNotification', [], true);

        return new CreateApplicationRequest\MerchantUrls(
            $loanNotificationURL,
            $loanNotificationURL,
            $loanNotificationURL
        );
    }

    /**
     * Vytvoří defaultní nastavení pro uživatele, pokud je možné vezme z cookies pokud není je toto zvoleno až v Myloan
     * @param \Currency $currency
     * @return CreateApplicationRequest\SettingsInstallment
     * @throws \Nette\Utils\JsonException
     */
    private function createSettingsInstallment(\Currency $currency)
    {
        $fingerprintComponents = $this->createFingerprintComponents();

        if ($fingerprintComponents) {
            return new CreateApplicationRequest\SettingsInstallment(
                $fingerprintComponents['preferredMonths'],
                new CreateApplicationRequest\SettingsInstallment\PreferredInstallment(
                    $fingerprintComponents['preferredInstallment'],
                    $currency->iso_code
                ),
                new CreateApplicationRequest\SettingsInstallment\PreferredDownPayment(
                    $fingerprintComponents['preferredDownPayment'],
                    $currency->iso_code
                ),
                $fingerprintComponents["productCode"],
                \MlcConfig::get(\MlcConfig::API_PRODUCT_CODE)
            );
        } else {
            return new CreateApplicationRequest\SettingsInstallment(
                null,
                null,
                null,
                "",
                \MlcConfig::get(\MlcConfig::API_PRODUCT_CODE)
            );
        }
    }

    /**
     * Označí objednávku jako doručenou v Myloan
     * @param string $applicationId
     * @return mixed
     */
    public function markOrderAsDelivered($applicationId)
    {
        return $this->getClient()->markOrderAsDelivered($applicationId);
    }

    /**
     * Označí objednávku jako odeslanou v Myloan
     * @param string $applicationId
     * @return mixed
     */
    public function markOrderAsSent($applicationId)
    {
        return $this->getClient()->markOrderAsSent($applicationId);
    }

    /**
     * @param $loanID
     * @return mixed
     */
    public function getLoanDetail($loanID)
    {
        return $this->getClient()->getDetail($loanID);
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}
