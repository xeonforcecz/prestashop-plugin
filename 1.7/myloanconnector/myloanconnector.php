<?php
/**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
**/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php');

class MyLoanConnector extends PaymentModule
{
    public function __construct()
    {
        $this->name = "myloanconnector";
        $this->tab = 'payments_gateways';
        $this->version = '1.0.1';
        $this->author = 'HN Consulting Brno, s.r.o.';
        $this->controllers = array('downPayment', 'changePayment', 'loanNotification', 'loanUpdate');
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6.1.1', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();
        $this->displayName = $this->l('Home Credit MyLoan');
        $this->description = $this->l('Home Credit MyLoan integration');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (isset($this->context->employee) && $this->context->employee->isLoggedBack()) {
            if (!MlcConfig::isModuleConfigured()) {
                $this->warning = $this->l('Module must be configured first!');
            }
        }

        $installedVersion = $this->getInstalledVersion();
        if ($this->version !== $installedVersion) {
            $this->makeMigrationToNewVersion($installedVersion);
        }
        MlcConfig::updateValue(MlcConfig::MODULE_VERSION, $this->version);

        //aby to prestashop přidal do lokalizací
        $this->l('Refresh');
        $this->l('State');
        $this->l('Id');

        if($this->context->cookie->myErrors && is_array($this->context->controller->errors)) {

            // Vytáhnutí custom errorů v případě, kdy standardní metoda selhává
            $this->context->controller->errors = array_merge(
              $this->context->controller->errors,
              (array)json_decode($this->context->cookie->myErrors)
            );

            unset($this->context->cookie->myErrors);
            $this->context->cookie->write();

        }

    }

    /**
     * Provede instalaci potřebných součástí
     * @return bool|void
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install() && MlcConfig::install();
    }

    /**
     * Odinstaluje modul
     * @return bool|void
     */
    public function uninstall()
    {
        if (!parent::uninstall() || !MlcConfig::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * @return string|void
     */
    public function getContent()
    {

        if (Tools::isSubmit('submit' . $this->name)) {
            return MlcConfig::saveConfig() . MlcConfig::renderForm();
        }

        return MlcConfig::renderForm();
    }

    /**
     * @param $hookParams
     * @return bool
     */
    public function hookHeader($hookParams)
    {
        if (!\MyLoan\Tools::shouldHookModule()) {
            return false;
        }

        $this->context->controller->addCSS($this->_path .  "/views/css/myloan.css");
        $this->context->controller->addJS($this->_path . "/vendor/components/jquery-cookie/jquery.cookie.js");

        switch (MlcConfig::get(MlcConfig::API_COUNTRY)) {
            case MlcConfig::CZ_VERSION:
            case MlcConfig::CZ_TEST_VERSION:
                $this->context->controller->addCSS(
                    $this->_path . "/vendor/homecreditcz/widget-calculator/releases/CZ/dist/hc-calc/style/style.css"
                );
                $this->context->controller->addJS(
                    $this->_path . "/vendor/homecreditcz/widget-calculator/releases/CZ/dist/hc-calc/js/resize.js"
                );
                $this->context->controller->addJS(dirname(__FILE__) . "/views/js/appLoader-cz.js");
                break;
            case MlcConfig::SK_VERSION:
            case MlcConfig::SK_TEST_VERSION:
                $this->context->controller->addCSS(
                    $this->_path . "/vendor/homecreditcz/widget-calculator/releases/SK/dist/hc-calc/style/style.css"
                );
                $this->context->controller->addJS(
                    $this->_path . "/vendor/homecreditcz/widget-calculator/releases/SK/dist/hc-calc/js/resize.js"
                );
                $this->context->controller->addJS($this->_path . "/views/js/appLoader-sk.js");
        }
    }

    /**
     * @param $hookParams
     * @return bool|void
     */
    public function hookDisplayProductButtons($hookParams)
    {
        $context = \Context::getContext();
        $productPrice = \MyLoan\Tools::getProductPriceInMinorUnits($hookParams["product"]["id"]);

        if (!\MyLoan\Tools::shouldHookModule( \Myloan\Tools::convertNumberFromMinorUnits($productPrice, $context->currency->iso_code))) {
            return false;
        }

        $this->smarty->assign(array(
          "isCertified" => MlcConfig::get(MlcConfig::API_CERTIFIED),
          "hcLogo" => \MyLoan\Tools::getImagePath("hc-logo.svg"),
          "calcButton" => \MyLoan\Tools::getImagePath("hc-calculator.svg"),
          "productId" => $hookParams["product"]["id"],
          "productPrice" => $productPrice,
          "calcUrl" => MyLoan\Tools::genCalculatorUrl($productPrice),
          "calcPostUrl" => Context::getContext()->link->getModuleLink(
              MlcConfig::MODULE_NAME,
              'payment'
          ),
          "productSetCode" => \MlcConfig::get(\MlcConfig::API_PRODUCT_CODE),
          "apiKey" => \MlcConfig::get(\MlcConfig::API_CALC_KEY),
        ));

        return $this->display(__FILE__, "calculator.tpl");
    }

    /**
     * @return bool|void
     */
    public function hookPaymentOptions()
    {
        $cartOrderTotal = $this->context->cart->getOrderTotal();
        if (!\MyLoan\Tools::shouldHookModule($cartOrderTotal) || !(\MlcConfig::testHCApiConnection() === true)) {
            return false;
        }

        $cartOrderTotal = \MyLoan\Tools::convertNumberToMinorUnits(
            $cartOrderTotal,
            $this->context->currency->iso_code
        );

        $this->context->controller->addCSS(dirname(__FILE__) . "/views/css/myloan.css");

        $this->context->smarty->assign(
            [
            "isCertified" => MlcConfig::get(MlcConfig::API_CERTIFIED),
            "loanOverview" => \MyLoan\Tools::getLoanOverview($cartOrderTotal),
            "calcUrl" => \MyLoan\Tools::genCalculatorUrl($cartOrderTotal),
            "calcPostUrl" => Context::getContext()->link->getModuleLink(
                MlcConfig::MODULE_NAME,
                'payment'
            ),
            "productSetCode" => \MlcConfig::get(\MlcConfig::API_PRODUCT_CODE),
            "apiKey" => \MlcConfig::get(\MlcConfig::API_CALC_KEY),
            'hcLogo' => \MyLoan\Tools::getImagePath("hc-logo.svg"),
            'cartOrderTotal' => $cartOrderTotal
            ]
        );

        $newOption = new PaymentOption();

        try {

            $newOption->setModuleName($this->name)
                ->setCallToActionText('HomeCredit')
                //->setLogo(\MyLoan\Tools::getImagePath("hc-logo.svg"))
                ->setAction($this->context->link->getModuleLink(
                    MlcConfig::MODULE_NAME,
                    'payment',
                    ['cartID' => $this->context->cart->id],
                    true
                ))
                ->setAdditionalInformation($this->context->smarty->fetch('module:myloanconnector/views/templates/hook/payment.tpl'));

        } catch(Exception $e){

            $this->_errors[] = "Payment option error: $e->getMessage()";
            die($e);

        }

        $payment_options = [
          $newOption,
        ];

        return $payment_options;
    }

    /**
     * Vygeneruje link na změnu platební metody
     * Returns link for changing payment method
     * @param $orderId
     * @return string
     */
    public function getOrderChangePaymentLink($orderId)
    {
        $order = new Order($orderId);

        $link = $this->context->link->getModuleLink(
            MlcConfig::MODULE_NAME,
            'changePayment',
            [
            'orderReference' => $order->reference,
            'secure_key' => $order->secure_key,
            ],
            true
        );

        return $link;
    }

    /**
     *
     * @param $params
     */
    public function hookActionOrderStatusUpdate($params)
    {
        $loan = new Loan($params["id_order"]);
        $response = null;

        if ($loan->getApplicationId()) {
            if ($params["newOrderStatus"]->id == MlcConfig::get(MlcConfig::getIdOfOrderStateMapping(MyLoan\HomeCredit\OrderStates\ReadyToShippedState::ID))) {
                try {
                    $api = new \MyLoan\HomeCredit\RequestAPI();
                    $response = (array)$api->markOrderAsSent($loan->getApplicationId());
                } catch (Exception $e) {
                    \MyLoan\Tools::addMyError($this->l('State change error! Please contact Home Credit'));
                }
            } else {
                if ($params["newOrderStatus"]->id == MlcConfig::get(MlcConfig::getIdOfOrderStateMapping(MyLoan\HomeCredit\OrderStates\ReadyToDeliveredState::ID))) {
                    try {
                        $api = new \MyLoan\HomeCredit\RequestAPI();
                        $response = (array)$api->markOrderAsDelivered($loan->getApplicationId());
                    } catch (Exception $e) {
                        \MyLoan\Tools::addMyError($this->l('State change error! Please contact Home Credit'));
                    }
                }
            }

            if (is_null($response) || array_key_exists("errors", $response)) {
                \MyLoan\Tools::addMyError(
                  $this->l("HomeCredit change order stare error") . ":" .
                  $response["errors"][0]["message"]
                );
            }
        }
    }

    /**
     * @param $params
     * @return string
     */
    public function hookDisplayAdminOrder($params)
    {
        $loan = new Loan($params["id_order"]);
        $order = new Order($params["id_order"]);

        if ($loan->getApplicationId() === null) {
            return "";
        }

        $link = $this->context->link->getModuleLink(
          MlcConfig::MODULE_NAME,
          "loanUpdate",
          [
            'orderReference' => $order->reference,
            'secure_key' => $order->secure_key,
            'action' => "update"
          ],
          true
        );

        $cancelLink = $this->context->link->getModuleLink(
          MlcConfig::MODULE_NAME,
          "loanUpdate",
          [
            'orderReference' => $order->reference,
            'secure_key' => $order->secure_key,
            'action' => "cancel"
          ],
          true
        );


        $this->context->smarty->assign([
          "loanDetail" => $loan,
          "amount" => $loan->getDownPayment(),
          "currency" => $loan->getCurrency(),
          "refreshLink" => $link,
          "cancelLink" => $cancelLink,
          "text" => [
            "id" => $this->l("Id"). ": ",
            "state" => $this->l("State"). ": ",
            "downpayment" => $this->l("Downpayment"). ": ",
            "button" => $this->l("Refresh")
          ]
        ]);

        return $this->display(__FILE__, 'adminOrder.tpl');
    }

    /**
     * @param $params
     */
    public function hookActionAdminOrdersListingResultsModifier($params)
    {
        foreach ($params['list'] as &$order) {
            $loan = new Loan($order["id_order"]);
            if ($loan->getApplicationId() !== null) {
                $order['downpayment'] = round($loan->getDownPayment(), -2) . " " . $loan->getCurrency();
            } else {
                $order['downpayment'] = "---";
            }
        }
    }

    /**
     * @param array $params
     */
    public function hookActionAdminOrdersListingFieldsModifier(array $params)
    {
        $params['fields'] += [
          'downpayment' => [
            'title' => $this->l('Downpayment'),
            'search' => false,
          ],
        ];
    }

    private function getInstalledVersion()
    {
        if (MlcConfig::hasKey(MlcConfig::MODULE_VERSION)) {
            return MlcConfig::get(MlcConfig::MODULE_VERSION);
        } else if (MlcConfig::hasKey(Tools::strtoupper($this->name . "_ORDER_STATES_INSTALLED"))) {
            return "1.0.0";
        } else {
            return $this->version;
        }
    }

    private function makeMigrationToNewVersion($installedVersion)
    {
        switch ($installedVersion) {
            case "1.0.0":
                $this->migrationTo_1_0_1($installedVersion);
            case "1.0.1":
            default:
                return true;
        }
    }

    private function migrationTo_1_0_1($installedVersion = "1.0.0")
    {
        if (version_compare($installedVersion, "1.0.1") >= 0)
            return; // Pokud nainstalovaná verze je 1.0.1 nebo novější tak migraci neprováděj
        $orderState = new MyLoan\HomeCredit\OrderStateManager();
        foreach ($orderState->getIdStates(false) as $idState) {
            MlcConfig::updateValue(MlcConfig::getIdOfOrderStateMapping($idState), MlcConfig::get($idState));
            MlcConfig::updateValue(MlcConfig::getIdOfOrderStateGenerated($idState), 1);
        }
        // Vytvořeni stavu "Nezařazeno"
        $id = MlcConfig::generateNewOrderState($orderState->getState(MyLoan\HomeCredit\OrderStates\UnclassifiedState::ID));
        MlcConfig::updateValue(MlcConfig::getIdOfOrderStateMapping(MyLoan\HomeCredit\OrderStates\UnclassifiedState::ID), $id);
    }
}
