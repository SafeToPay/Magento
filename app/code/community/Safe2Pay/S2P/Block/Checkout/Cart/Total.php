<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Block_Checkout_Cart_Total extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('s2p/checkout/cart/total.phtml');
    }

    public function getAmount()
    {
        $amount = $this->_getQuote()->getBaseGrandTotal();
        $payment = $this->_getPayment();
        if ($payment->getMethod() == 's2p_cc') {
            $installments = $payment->getInstallments();
            $interestRate = $payment->getMethodInstance()->getInterestRate($installments);
            $installmentAmount = $payment->getMethodInstance()->calcInstallmentAmount($amount, $installments, $interestRate);
            $amount = $installmentAmount * $installments;
        }

        return $amount;
    }

    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    protected function _getPayment()
    {
        return $this->_getQuote()->getPayment();
    }

    protected function _toHtml()
    {
        if ($this->getAmount() == $this->_getQuote()->getBaseGrandTotal()) {
            return '';
        }
        return parent::_toHtml();
    }
}
