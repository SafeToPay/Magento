<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Block_Checkout_Success_Payment_Criptomoedas extends Safe2Pay_S2P_Block_Checkout_Success_Payment_Default
{
    public function getInvoiceUrl()
    {
        return $this->getPayment()->getS2pUrl();
    }

    public function getCriptoAmount()
    {
        return $this->getPayment()->getS2pCriptoAmount();
    }

    public function getCriptoSymbol()
    {
        return $this->getPayment()->getS2pCriptoSymbol();
    }

    public function getCriptoWalletAddress()
    {
        return $this->getPayment()->getS2pCriptoWalletAddress();
    }



    
}
