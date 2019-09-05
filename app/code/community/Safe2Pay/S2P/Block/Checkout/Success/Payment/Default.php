<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Block_Checkout_Success_Payment_Default extends Mage_Core_Block_Template
{
    public function setPayment(Varien_Object $payment)
    {
        $this->setData('payment', $payment);
        return $this;
    }

    public function getPayment()
    {
        return $this->_getData('payment');
    }

    public function getOrder()
    {
        return $this->getPayment()->getOrder();
    }
}
