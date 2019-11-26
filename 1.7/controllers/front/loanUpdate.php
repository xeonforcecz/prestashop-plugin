<?php
/**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
**/

use MyLoan\HomeCredit\ResponseAPI;

class MyLoanConnectorLoanUpdateModuleFrontController extends ModuleFrontController
{
    public function setMedia()
    {
        parent::setMedia();
    }

    public function initContent()
    {

        if (Tools::getIsset("action") && Tools::getIsset("orderReference")) {
            // Update action
            if (Tools::getValue("action") == "update") {
                $orderReference = Tools::getValue("orderReference");

                $order_collection = Order::getByReference($orderReference);

                if ($order_collection->count() != 1) {
                    $this->showMessage("Order reference not found.", "danger");

                    return;
                }

                $order = new Order($order_collection->getFirst()->id);

                if ((!Tools::getIsset("secure_key") or $order->secure_key != Tools::getValue("secure_key"))) {
                    $this->showMessage("Secure key is not defined or does not match.", "danger");

                    return;
                }

                // Update loan data - triggered by constructor
                Loan::updateLoan($order_collection->getFirst()->id);
                new Loan($order_collection->getFirst()->id, null, null);
                Tools::redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    protected function showMessage($text, $type)
    {
        $this->context->smarty->assign(
            array(
            "show_message" => $this->module->l($text),
            "type" => $type
            )
        );

        $this->setTemplate('module:myloanconnector/views/templates/front/message.tpl');
    }
}
