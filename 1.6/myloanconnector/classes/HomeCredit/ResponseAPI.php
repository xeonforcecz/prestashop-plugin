<?php
/**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
**/

namespace MyLoan\HomeCredit;

use Loan;
use MlcConfig;
use MyLoan\Tools;
use Order;
use PrestaShopModuleException;

class ResponseAPI
{
    /**
     *
     * @throws PrestaShopModuleException
     */
    public static function processLoanCreateResponse()
    {
        $orderReference = \Tools::getValue("orderNumber");
        $check_sum = \Tools::getValue("checkSum");
        $state_reason = \Tools::getValue("stateReason");
        if (!$orderReference || !$check_sum || !$state_reason) {
            throw new PrestaShopModuleException("HomeCredit API - empty required parameter.");
        }
        $secretKey = MlcConfig::get(MlcConfig::API_SECRETCODE);
        $data = $orderReference.":".$state_reason;

        if (\Tools::strtoupper(hash_hmac('sha512', $data, $secretKey)) != $check_sum) {
            throw new PrestaShopModuleException("Wrong checkSum.");
        }

        $orderCollection = Order::getByReference($orderReference);
        $order_id = $orderCollection->getFirst()->id;

        Loan::updateLoan($order_id);
        $loan = new Loan($order_id);

        if (!self::authLoanCreateResponse($loan)) {
            throw new PrestaShopModuleException("HomeCredit API - unauthorized request");
        }

        self::changeOrderState($state_reason, $order_id);
    }

    /**
     *
     * @param Loan $loan
     * @return bool
     */
    public static function authLoanCreateResponse(Loan $loan)
    {
        return $loan->getApplicationId() === (string)\Tools::getValue("applicationId");
    }

    /**
     * Nastaví stav objednávky podle stavu z Myloan
     * @param $stateReason
     * @param $order_id
     * @throws PrestaShopModuleException
     */
    public static function changeOrderState($stateReason, $order_id)
    {
        $order = new Order($order_id);
        $newState = $order->current_state;
        switch ($stateReason) {
            case Loan::PROCESSING_PREAPPROVED:
            case Loan::PROCESSING_REDIRECT_NEEDED:
            case Loan::PROCESSING_REVIEW:
            case Loan::PROCESSING_SIGNED:
            case Loan::PROCESSING_APPROVED:
            case Loan::PROCESSING_ALT_OFFER:
                $newState = MlcConfig::get("HC_PROCESSING");
                break;
            case Loan::CANCELLED_NOT_PAID:
            case Loan::CANCELLED_RETURNED:
            case Loan::REJECTED:
                $newState = MlcConfig::get("HC_REJECTED");
                break;
            case Loan::READY_TO_SHIP:
                $newState = MlcConfig::get("HC_READY");
                break;
            case Loan::READY_SHIPPED:
                $newState = MlcConfig::get("HC_READY_SHIPPED");
                break;
            case Loan::READY_DELIVERING:
                $newState = MlcConfig::get("HC_READY_DELIVERING");
                break;
            case Loan::READY_DELIVERED:
                $newState = MlcConfig::get("HC_READY_DELIVERED");
                break;
            case Loan::READY_PAID:
                $newState = MlcConfig::get("HC_READY_PAID");
                break;
            default:
                throw new PrestaShopModuleException("HomeCredit API - unknown response stateReason");
        }
        if ($order->current_state != $newState) {
            $order->setCurrentState($newState);
            $order->update();
        }
    }
}
