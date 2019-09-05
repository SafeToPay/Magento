<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */



class Safe2Pay_S2P_Model_Boleto extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 's2p_boleto';

    protected $_formBlockType = 's2p/form_boleto';
    protected $_infoBlockType = 's2p/info_boleto';

    protected $_isGateway                   = true;
    protected $_canUseForMultishipping      = false;
    protected $_isInitializeNeeded          = true;
    protected $_canUseInternal              = false;

    public function assignData($data)
    {
        $info = $this->getInfoInstance();
        $info->setInstallments(null)
            ->setInstallmentDescription(null)
            ->setS2pBoletoCpfCnpj($data->getS2pBoletoCpfCnpj())
        ;
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
        
        $paymentMethod = (object) ['Code' => '1'];

        $address = (object) ['Street' => $order->getBillingAddress()->getStreet(1),
                             'Number' => 'S/N',
                             'District' => 'S/B',
                             'ZipCode' => Zend_Filter::filterStatic($order->getBillingAddress()->getPostcode(), 'Digits'),
                             'CityName' => $order->getBillingAddress()->getCity(),
                             'StateInitials' => $order->getBillingAddress()->getRegionCode(),
                             'CountryName' => 'Brasil'];
                

        $customer = (object) ['Name' => $order->getCustomerName(),
                              'Identity' => Zend_Filter::filterStatic($payment->getS2pBoletoCpfCnpj(), 'Digits'),
                              'Email' => $order->getCustomerEmail(),
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
        
        $bankslip =  (object) ['DueDate' => date("d/m/Y")];

        $transaction = (object) ['Application' => 'Magento ' . Mage::getVersion(),
                                 'Reference' => $order->getId(),
                                 'IsSandbox' => Mage::helper('s2p')->getMode() == "test" ? true : false,
                                 'CallbackUrl' => Mage::getUrl('s2p/callback'),
                                 'Customer' => $customer,
                                 'PaymentMethod' => $paymentMethod,
                                 'Products' => $products,
                                 'PaymentObject' => $bankslip];

        $result = Mage::getSingleton('s2p/api')->checkout($transaction);

        if ($result->HasError)
        {
            Mage::throwException($result->Error);
        }

        $payment->setSafe2payTransactionId($result->ResponseDetail->IdTransaction)
            ->setS2pUrl($result->ResponseDetail->BankSlipUrl)
            ->setS2pPdf($result->ResponseDetail->BankSlipUrl);

        return $this;
    }
}
