<?php
/**
 * @category    Safe2Pay
 * @package     Safe2Pay_S2P
 * @copyright   Copyright (c) 2014 Safe2Pay. (https://safe2pay.com.br)
 */
class Safe2Pay_S2P_Model_Debito extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 's2p_debito';

    protected $_formBlockType = 's2p/form_debito';
    protected $_infoBlockType = 's2p/info_debito';

    protected $_isGateway                   = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canRefund                   = true;
    protected $_canUseForMultishipping      = false;
    protected $_canUseInternal              = false;

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();

        $info->setInstallments($data->getInstallments())
            ->setInstallmentDescription($data->getInstallmentDescription())
            ->setS2pToken($data->getS2pToken())
            ->setS2pCustomerPaymentMethodId($data->getS2pCustomerPaymentMethodId())
            
            ->setS2pSave($data->getS2pSave())
            ->setS2pCustomerIdentity($data->getS2pCustomerIdentity())

            ->setS2pCardHolder($data->getS2pCardHolder())
            ->setS2pCardNumber($data->getS2pCardNumber())
            ->setS2pCardExpirationMonth($data->getS2pCardExpirationMonth())
            ->setS2pCardExpirationYear($data->getS2pCardExpirationYear())
            ->setS2pCardVerification($data->getS2pCardVerification());

        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        $this->_place($payment, $amount);
        return $this;
    }


    protected function _place($payment, $amount)
    {
        $order = $payment->getOrder();

        $paymentMethod = (object) ['Code' => '4'];

        $address = (object) ['Street' => $order->getBillingAddress()->getStreet(1),
                             'Number' => 'S/N',
                             'District' => 'S/B',
                             'ZipCode' => Zend_Filter::filterStatic($order->getBillingAddress()->getPostcode(), 'Digits'),
                             'CityName' => $order->getBillingAddress()->getCity(),
                             'StateInitials' => $order->getBillingAddress()->getRegionCode(),
                             'CountryName' => 'Brasil'];

        $customer_session = Mage::getSingleton('customer/session')->getCustomer();
        $customer_registry = Mage::getModel('customer/customer')->load($customer_session->getId());
        $identity = $customer_registry->getData('taxvat');

        $customer = (object) ['Name' => $order->getCustomerName(),
                              'Identity' => Zend_Filter::filterStatic($identity, 'Digits'),
                              'Email' => $order->getCustomerEmail(),
                              'Phone' => Zend_Filter::filterStatic($order->getBillingAddress()->getTelephone(), 'Digits'),
                              'Address' => $address];

        $products =  array(); 

        foreach ($order->getAllVisibleItems() as $data) 
        {
           $prod = (object) ['Code' => $data->getProductId(),
                              'Description' => $data->getName(),
                              'Quantity' => $data->getQtyOrdered(),
                              'UnitPrice' => $data->getBasePrice()];


            $products[] = $prod;
        }

         // Shipping
         if ($order->getBaseShippingAmount() > 0) 
         {
             $shipping = (object) ['Code' => '1',
                                 'Description' => $order->getShippingDescription(),
                                 'Quantity' => 1,
                                 'UnitPrice' => $order->getBaseShippingAmount()];
 
             $products[] = $shipping;
         }

       
        $debit_card =  (object) ['Holder' => $payment->getS2pCardHolder(),
                                  'CardNumber' => $payment->getS2pCardNumber(),
                                  'ExpirationDate' => str_pad($payment->getS2pCardExpirationMonth(), 2, "0", STR_PAD_LEFT) . '/' . $payment->getS2pCardExpirationYear(),
                                  'SecurityCode' => $payment->getS2pCardVerification(),
                                  'InstallmentQuantity' => $payment->getInstallments(),
                                  'Authenticate' => true];

        $transaction = (object) ['Application' => 'Magento ' . Mage::getVersion(),
                                 'Reference' => $order->getIncrementId(),
                                 'IsSandbox' => Mage::helper('s2p')->getMode() == "test" ? true : false,
                                 'CallbackUrl' => Mage::app()->getStore(0)->getBaseUrl().'s2p/notification/notify/',
                                 'Customer' => $customer,
                                 'PaymentMethod' => $paymentMethod,
                                 'Products' => $products,
                                 'PaymentObject' => $debit_card];

        $result = Mage::getSingleton('s2p/api')->checkout($transaction);

        if ($result->HasError)
        {
            Mage::throwException($result->Error);
        }

        switch ($result->ResponseDetail->Status)
        {
            case 1:
                $payment->setS2pTransactionId($result->ResponseDetail->IdTransaction)
                ->setS2pPaymentMethod(4)
                ->setTransactionId($result->ResponseDetail->IdTransaction)
                ->setS2pUrl($result->ResponseDetail->AuthenticationUrl);
                break;
            case 3:
                $payment->setS2pTransactionId($result->ResponseDetail->IdTransaction)
                ->setS2pPaymentMethod(4)
                ->setIsTransactionClosed(0)
                ->setTransactionId($result->ResponseDetail->IdTransaction)
                ->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,array('message' => $result->ResponseDetail->Description));
                break;
            default:
                $payment->setS2pTransactionId($result->ResponseDetail->IdTransaction)
                ->setS2pPaymentMethod(4)
                ->setTransactionId($result->ResponseDetail->IdTransaction);

                Mage::throwException($result->ResponseDetail->Message . " - " . $result->ResponseDetail->Description);
                break;
        }

        return $this;
    }






    public function refund(Varien_Object $payment, $amount)
    {
        $transactionID = $payment->getS2pInvoiceId();

        $result = Mage::getSingleton('s2p/api')->refund($transactionID);

        $payment->setTransactionId($transactionID . '-' . Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
            ->setParentTransactionId($transactionID)
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction(1)
            ->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,array('status' => $result->getStatus()))
        ;

        return $this;
    }
}
