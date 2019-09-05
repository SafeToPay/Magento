<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Block_Payment_Method_List extends Mage_Core_Block_Template
{
    protected $_items;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('s2p/payment_method/list.phtml');
    }

    public function getAddPaymentMethodUrl()
    {
        return $this->getUrl('s2p/payment_method/new');
    }

    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
    }

    public function getItems()
    {
        if (is_null($this->_items)) {
            $customer = $this->_getSession()->getCustomer();
            if ($customer->getS2pCustomerId()) {
                $result = Mage::getSingleton('s2p/api')->getPaymentMethodList($customer->getS2pCustomerId());
                if ($result->getItems()) {
                    $this->_items = $result->getItems();
                }
            }
            if (!$this->_items) {
                $this->_items = array();
            }
        }
        return $this->_items;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}
