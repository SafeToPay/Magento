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

    public function getDeletePaymentMethodUrl()
    {
        return $this->getUrl('s2p/payment_method/delete');
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
        if (is_null($this->_items)) 
        {
            $customerId = Mage::helper('s2p')->getCustomerId();

            $lista = Mage::helper('s2p')->getListCardToken($customerId);

            if ($lista) {
                $this->_items = $lista;
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
