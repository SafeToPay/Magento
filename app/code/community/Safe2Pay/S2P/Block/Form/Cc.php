<?php
/**
 * @category    Safe2Pay
 * @package     Safe2Pay_S2P
 * @copyright   Copyright (c) 2014 Safe2Pay. (https://safe2pay.com.br)
 */
class Safe2Pay_S2P_Block_Form_Cc extends Mage_Payment_Block_Form_Cc
{

    protected $_creditCards;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('s2p/form/cc.phtml');
    }

    /**
     * Retrieve saved credit cards
     *
     * @return array
     */
    public function getCreditCards()
    {
        if (is_null($this->_creditCards)) 
        {
            $this->_creditCards = array();

            $customerId = Mage::helper('s2p')->getCustomerId();
        
            if ($customerId) 
            {
                $this->_creditCards = Mage::helper('s2p')->getListCardToken($customerId);
            }
        }
        return $this->_creditCards;
    }

    /**
     * Retrieve availables installments
     *
     * @return array
     */
    public function getInstallmentsAvailables(){

        return $this->getMethod()->getInstallmentOptions();
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
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
}
