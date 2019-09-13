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
        return $this->getInfo()->getS2pTransactionId();
    }

   /**
     * @return string
     */
    public function getInvoiceUrl()
    {
        return $this->getInfo()->getS2pUrl();
    }

    public function getCriptoAmount()
    {
        return $this->getInfo()->getS2pCriptoAmount();
    }

    public function getCriptoSymbol()
    {
        return $this->getInfo()->getS2pCriptoSymbol();
    }

    public function getCriptoWalletAddress()
    {
        return $this->getInfo()->getS2pCriptoWalletAddress();
    }
}
