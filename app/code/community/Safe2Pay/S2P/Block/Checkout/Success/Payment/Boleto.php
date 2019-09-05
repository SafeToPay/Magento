<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Block_Checkout_Success_Payment_Boleto extends Safe2Pay_S2P_Block_Checkout_Success_Payment_Default
{
    public function getInvoiceUrl()
    {
        return $this->getPayment()->getS2pPdf();
    }
}
