<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Model_Api extends Safe2Pay_S2P_Model_Api_Abstract
{
    //const PAYMENT_METHOD_BOLETO = 'bank_slip';
    //const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';




    const INVOICE_STATUS_DRAFT              = 'draft';
    const INVOICE_STATUS_PENDING            = 'pending';
    const INVOICE_STATUS_PARTIALLY_PAID     = 'partially_paid';
    const INVOICE_STATUS_PAID               = 'paid';
    const INVOICE_STATUS_CANCELED           = 'canceled';
    const INVOICE_STATUS_REFUNDED           = 'refunded';
    const INVOICE_STATUS_EXPIRED            = 'expired';

    public function checkout(Varien_Object $data)
    {
        $response = $this->request($this->getPaymentUrl(), $data, Zend_Http_Client::POST);
        return $response;
    }

    public function tokenize(Varien_Object $data)
    {
        $response = $this->request($this->getTokenizeUrl(), $data, Zend_Http_Client::POST);
        return $response;
    }

    public function refund($id)
    {
        $response = $this->request($this->getRefundUrl($id), null, Zend_Http_Client::POST);
        return $response;
    }

    public function getTokenizeUrl()
    {
        $url = $this->getPaymentBaseUrl() . '/token';
        return $url;
    }

    public function getPaymentUrl()
    {
        $url = $this->getPaymentBaseUrl() . '/payment';
        return $url;
    }

    public function getRefundUrl($id)
    {
        $url = $this->getApiBaseUrl() . '/CreditCard/Cancel/' . $id;
        return $url;
    }
}
