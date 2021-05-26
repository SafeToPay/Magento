<?php

class CallbackController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $secret_key = $this->getRequest()->getParam('SecretKey');
        $store_secret_key = Mage::helper('s2p')->getApiSecretKey();

        // Verifica se a secret key da requisição bate com a da loja.
        if ($secret_key == $store_secret_key) {
            // Busca o orderId do magento pelo transacionId do Safe2Pay
            $orderId = $this->_getOrderIdByTransactionId($this->getRequest()->getParam('IdTransaction'));

            if (!$orderId) {
                // Caso não encontre um pedido no magento, retorna o httpstatus 404
                $this->_forward('404');
            }

            $transaction_status = $this->getRequest()->getParam('TransactionStatus');

            // Verifica se a transação está paga
            if ($transaction_status == "3") {
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
            } else {
                $this->getResponse()->setBody('ok');
            }
        } else {
            // Caso a secret informada não esteja correta, responde o httpstatus 401
            $this->_forward('401');
        }
    }

    protected function _getOrderIdByTransactionId($transactionId)
    {
        $resource = Mage::getSingleton("core/resource");
        $connection = $resource->getConnection("core_write");
        $select = $connection->select()
            ->from(array('p' => $resource->getTableName('sales/order_payment')), array('parent_id'))
            ->where('safe2pay_transaction_id = ?', $transactionId)
            ->limit(1);
        $orderId = $connection->fetchOne($select);
        return $orderId;
    }
}
