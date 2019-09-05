<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Block_Info_Cc extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('s2p/info/cc.phtml');
    }

    /**
     * @return string
     */
    public function getInvoiceId()
    {
        return $this->getInfo()->getS2pInvoiceId();
    }

    /**
     * @return string
     */
    public function getInstallments()
    {
        return $this->getInfo()->getInstallments();
    }

    /**
     * @return string
     */
    public function getInstallmentDescription()
    {
        return $this->getInfo()->getInstallmentDescription();
    }

    /**
     * @return float
     */
    public function getAmountOrdered()
    {
        return $this->getInfo()->getBaseAmountOrdered();
    }

    /**
     * @return float
     */
    public function getTotalWithInterest()
    {
        return $this->getInfo()->getS2pTotalWithInterest();
    }
}
