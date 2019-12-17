<?php
/**
 * @category    Safe2Pay
 * @package     Safe2Pay_S2P
 * @copyright   Copyright (c) 2014 Safe2Pay. (https://safe2pay.com.br)
 */
class Safe2Pay_S2P_Model_Cc extends Mage_Payment_Model_Method_Abstract
{
    const MIN_INSTALLMENT_VALUE = 5;

    protected $_code = 's2p_cc';

    protected $_formBlockType = 's2p/form_cc';
    protected $_infoBlockType = 's2p/info_cc';

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


            ->setS2pKdtVisitor($data->getS2pKdtVisitor())
            


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

        $paymentMethod = (object) ['Code' => '2'];

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

         if ($order->getBaseShippingAmount() > 0) 
         {
             $shipping = (object) ['Code' => '1',
                                 'Description' => $order->getShippingDescription(),
                                 'Quantity' => 1,
                                 'UnitPrice' => $order->getBaseShippingAmount()];
 
             $products[] = $shipping;
         }

        $interestRate = $this->getInterestRate($payment->getInstallments());
        $totalWithInterest = $this->calcTotalWithInterest($amount, $interestRate);

        if ($totalWithInterest - $amount > 0) 
        {
            $interest = (object) ['Code' => '1',
                                  'Description' => Mage::helper('s2p')->__('Interest'),
                                  'Quantity' => 1,
                                  'UnitPrice' => $totalWithInterest - $amount];

            $products[] = $interest;
        }

        $one_click_checkout = false;

        if ($payment->getS2pCustomerPaymentMethodId() > 0)
        {
            $one_click_checkout = true;

            $customerId = Mage::helper('s2p')->getCustomerId();

        
            $token = Mage::helper('s2p')->getCardToken($payment->getS2pCustomerPaymentMethodId(), $customerId);

            $credit_card =  (object) ['Token' => $token,
                                      'SecurityCode' => $payment->getS2pCardVerification(),
                                      'InstallmentQuantity' => $payment->getInstallments()];
        }
        else
        {
            $one_click_checkout = false;

            $credit_card =  (object) ['Holder' => $payment->getS2pCardHolder(),
                                      'CardNumber' => $payment->getS2pCardNumber(),
                                      'ExpirationDate' => str_pad($payment->getS2pCardExpirationMonth(), 2, "0", STR_PAD_LEFT) . '/' . $payment->getS2pCardExpirationYear(),
                                      'SecurityCode' => $payment->getS2pCardVerification(),
                                      'InstallmentQuantity' => $payment->getInstallments()];
        }


        $transaction = (object) ['Application' => 'Magento ' . Mage::getVersion(),
                                 'Reference' => $order->getIncrementId(),
                                 'IsSandbox' => Mage::helper('s2p')->getMode() == "test" ? true : false,
                                 'CallbackUrl' => Mage::app()->getStore(0)->getBaseUrl().'s2p/notification/notify/',
                                 'Customer' => $customer,
                                 'PaymentMethod' => $paymentMethod,
                                 'Products' => $products,
                                 'Amount' => $amount,
                                 'VisitorID' => $payment->getS2pKdtVisitor(),
                                 'PaymentObject' => $credit_card];

        $result = Mage::getSingleton('s2p/api')->checkout($transaction);

        if ($result->HasError)
        {
            Mage::throwException($result->Error);
        }

        
        if ($result->ResponseDetail->Status == 3)
        {
            if ($payment->getS2pSave() && !$one_click_checkout)
            {
                $customer_card_id = Mage::helper('s2p')->tokenize($credit_card, false);

                $payment->setS2pCustomerPaymentMethodId($customer_card_id);    
            }
    
            // Set s2p info
            $payment->setS2pTransactionId($result->ResponseDetail->IdTransaction)
                ->setS2pPaymentMethod(2)
                ->setS2pTotalWithInterest($totalWithInterest)
                ->setTransactionId($result->ResponseDetail->IdTransaction)
                ->setIsTransactionClosed(0)
                ->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,array('message' => $result->ResponseDetail->Description));
        }
        else
        {
            $payment->setS2pTransactionId($result->ResponseDetail->IdTransaction)
                ->setS2pPaymentMethod(2)
                ->setTransactionId($result->ResponseDetail->IdTransaction);

            Mage::throwException($result->ResponseDetail->Description);
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







    /**
     * @param float $amount
     * @return array
     */
    public function getInstallmentOptions($amount = null)
    {
        $quote = $this->getInfoInstance()->getQuote();
        if (is_null($amount)) {
            $amount = $quote->getGrandTotal();
        }

        $maxInstallments = (int)$this->getConfigData('max_installments');
        $minInstallmentValue = (float)$this->getConfigData('min_installment_value');

        if ($minInstallmentValue < self::MIN_INSTALLMENT_VALUE) {
            $minInstallmentValue = self::MIN_INSTALLMENT_VALUE;
        }

        $installments = floor($amount / $minInstallmentValue);
        if ($installments > $maxInstallments) {
            $installments = $maxInstallments;
        } elseif ($installments < 1) {
            $installments = 1;
        }

        $options = array();
        for ($i=1; $i <= $installments; $i++) {
            if ($i == 1) {
                $label = Mage::helper('s2p')->__('Pagamento à vista - %s', $quote->getStore()->formatPrice($amount, false));
            } else {
                $interestRate = $this->getInterestRate($i);
                $installmentAmount = $this->calcInstallmentAmount($amount, $i, $interestRate);
                if ($interestRate > 0) {
                    $label = Mage::helper('s2p')->__('%sx - %s com juros', $i, $quote->getStore()->formatPrice($installmentAmount, false));
                } else {
                    $label = Mage::helper('s2p')->__('%sx - %s sem juros', $i, $quote->getStore()->formatPrice($installmentAmount, false));
                }
            }
            $options[$i] = $label;
        }
        return $options;
    }

    /**
     * @param int $installments
     * @return float
     */
    public function getInterestRate($installments)
    {
        if ($installments < 2) {
            return 0;
        }
        
        $interestMap = unserialize($this->getConfigData('interest_rate'));
        usort($interestMap, array($this, '_sortInterestRateByInstallments'));
        $interestMap = array_reverse($interestMap, true);
        $interestRate = 0;
        foreach ($interestMap as $item) {
            if ($installments <= $item['installments']) {
                $interestRate = $item['interest'];
            }
        }
        return (float)$interestRate/100;
    }

    public function calcInstallmentAmount($amount, $installments, $rate = 0.0)
    {
        if ($rate > 0){
            $result = $this->calcTotalWithInterest($amount, $rate) / $installments;
        } else {
            $result = $amount / $installments;
        }
        return round($result, 2);
    }

    public function calcTotalWithInterest($amount, $rate = 0.0)
    {
        return $amount + ($amount * $rate);
    }

    protected function _sortInterestRateByInstallments($a, $b)
    {
        if ($a['installments'] == $b['installments']) {
            return 0;
        }
        return ($a['installments'] < $b['installments']) ? -1 : 1;
    }
}
