<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Block_Payment_Method_Form extends Mage_Core_Block_Template
{
    protected $_method;
    protected $_paymentMethod;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('s2p/payment_method/form.phtml');
    }

    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getPaymentMethod()->getId()) {
            $title = Mage::helper('s2p')->__('Editar Cartão de Crédito');
        }
        else {
            $title = Mage::helper('s2p')->__('Incluir Cartão de Crédito');
        }
        return $title;
    }

    public function getPaymentMethod()
    {
        if (is_null($this->_paymentMethod)) {
            if ($paymentMethodId = $this->getRequest()->getParam('id')) {
                $customerId = Mage::helper('s2p')->getCustomerId();
                $this->_paymentMethod = Mage::getSingleton('s2p/api')->getPaymentMethod($customerId, $paymentMethodId);
            } else {
                $this->_paymentMethod = new Varien_Object();
            }
        }
        return $this->_paymentMethod;
    }

    public function getCcAvailableTypes()
    {
        $types = Mage::getSingleton('payment/config')->getCcTypes();
        $availableTypes = Mage::getStoreConfig('payment/s2p_cc/cctypes');
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
            foreach ($types as $code=>$name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }
        return $types;
    }

    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Mês');
            for ($i=1; $i <= 12; $i++) {
                $months[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = Mage::getSingleton('payment/config')->getYears();
            $years = array(0=>$this->__('Ano'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    public function getSavePaymentMethodUrl()
    {
        return $this->getUrl('s2p/payment_method/save');
    }

    public function getBackUrl()
    {
        return $this->getUrl('s2p/payment_method');
    }
}
