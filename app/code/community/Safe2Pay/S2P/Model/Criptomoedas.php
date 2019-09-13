<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */



class Safe2Pay_S2P_Model_Criptomoedas extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 's2p_criptomoedas';

    protected $_formBlockType = 's2p/form_criptomoedas';
    protected $_infoBlockType = 's2p/info_criptomoedas';

    protected $_isGateway                   = true;
    protected $_canUseForMultishipping      = false;
    protected $_isInitializeNeeded          = true;
    protected $_canUseInternal              = false;

    public function assignData($data)
    {
        $info = $this->getInfoInstance();
        $info->setInstallments(null)
            ->setInstallmentDescription(null)
            ->setS2pCustomerIdentity($data->getS2pCustomerIdentity())
            ->setS2pCriptoWalletAddress($data->getS2pCriptoWalletAddress())
            ->setS2pCriptoSymbol($data->getS2pCriptoSymbol())
            ->setS2pCriptoAmount($data->getS2pCriptoAmount());
        return $this;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();


        $order = $payment->getOrder();
        $this->_place($payment, $order->getBaseTotalDue());
        return $this;
    }

    public function _place(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        $order = $payment->getOrder();
        
        $paymentMethod = (object) ['Code' => '3'];

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

        $crypto =  (object) ['Symbol' => $payment->getS2pCriptoSymbol()];

        $transaction = (object) ['Application' => 'Magento ' . Mage::getVersion(),
                                 'Reference' => $order->getIncrementId(),
                                 'IsSandbox' => Mage::helper('s2p')->getMode() == "test" ? true : false,
                                 'CallbackUrl' => Mage::app()->getStore(0)->getBaseUrl().'s2p/notification/notify/',
                                 'Customer' => $customer,
                                 'PaymentMethod' => $paymentMethod,
                                 'Products' => $products,
                                 'PaymentObject' => $crypto];

        $result = Mage::getSingleton('s2p/api')->checkout($transaction);

        if ($result->HasError)
        {
            Mage::throwException($result->Error);
        }

        $AmountCripto = 0;

        switch ($result->ResponseDetail->Symbol)
        {
            case "BTC":
                $AmountCripto = $result->ResponseDetail->AmountBTC;
                break;
            case "LTC":
                $AmountCripto = $result->ResponseDetail->AmountLTC;
                break;
            case "BCH":
                $AmountCripto = $result->ResponseDetail->AmountBCH;
                break;
        }

        $payment->setS2pTransactionId($result->ResponseDetail->IdTransaction)
            ->setS2pPaymentMethod(3)
            ->setS2pUrl($result->ResponseDetail->QrCode)
            ->setS2pCriptoWalletAddress($result->ResponseDetail->WalletAddress)
            ->setS2pCriptoSymbol($result->ResponseDetail->Symbol)
            ->setS2pCriptoAmount($AmountCripto);

        return $this;
    }
}
