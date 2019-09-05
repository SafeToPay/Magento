<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Block_Info_Criptomoedas extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('s2p/info/criptomoedas.phtml');
    }

    /**
     * @return string
     */
    public function getInvoiceId()
    {
        return $this->getInfo()->getSafe2payTransactionId();
    }

   /**
     * @return string
     */
    public function getInvoiceUrl()
    {
        return $this->getInfo()->getS2pQrcode();
    }
}
