<?php

class Safe2Pay_S2p_NotificationController extends Mage_Core_Controller_Front_Action
{
    public function notifyAction()
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
        // get posted data
        $payload = json_decode(file_get_contents("php://input"));

        $store_secret_key = Mage::helper('s2p')->getApiSecretKey();

        // Verifica se a secret key da requisição bate com a da loja.
        if ($payload->SecretKey == $store_secret_key)
        {
            // Busca o orderId do magento pelo transacionId do Safe2Pay
            $orderId = $this->_getOrderIdByTransactionId($payload->IdTransaction);

            if (!$orderId)
            {
                // Caso não encontre um pedido no magento, retorna o httpstatus 404
                //$this->_forward('404');
                $this->_forward('404');
            }

            // Verifica se a transação está paga
            if ($payload->TransactionStatus == "3")
            {
                $order = Mage::getModel('sales/order')->load($orderId);

                if (!$order->canInvoice()) {
                    Mage::throwException($this->__('The order does not allow creating an invoice.'));
                }

                $invoice = Mage::getModel('sales/service_order', $order)
                    ->prepareInvoice()
                    ->register()
                    ->pay();

                $invoice->setEmailSent(true);
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();

                $invoice->sendEmail();

                $this->getResponse()->setBody('ok');
                return;
            }
            else
            {
                $this->getResponse()->setBody('ok');
            }
        }
        else
        {
            $this->_forward('401');
        }
    }

    protected function _getOrderIdByTransactionId($transactionId)
    {
        $resource = Mage::getSingleton("core/resource");
        $connection = $resource->getConnection("core_write");
        $select = $connection->select()
            ->from(array('p' => $resource->getTableName('sales/order_payment')), array('parent_id'))
            ->where('s2p_transaction_id = ?', $transactionId)
            ->limit(1);
        $orderId = $connection->fetchOne($select);
        return $orderId;
    }
}
