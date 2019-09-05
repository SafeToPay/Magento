<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Model_Source_Status
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Safe2Pay_S2P_Model_Api::INVOICE_STATUS_DRAFT,
                'label' => Mage::helper('s2p')->__('Draft')
            ),
            array(
                'value' => Safe2Pay_S2P_Model_Api::INVOICE_STATUS_PENDING,
                'label' => Mage::helper('s2p')->__('Pending')
            ),
            array(
                'value' => Safe2Pay_S2P_Model_Api::INVOICE_STATUS_PARTIALLY_PAID,
                'label' => Mage::helper('s2p')->__('Partially Paid')
            ),
            array(
                'value' => Safe2Pay_S2P_Model_Api::INVOICE_STATUS_PAID,
                'label' => Mage::helper('s2p')->__('Paid')
            ),
            array(
                'value' => Safe2Pay_S2P_Model_Api::INVOICE_STATUS_CANCELED,
                'label' => Mage::helper('s2p')->__('Canceled')
            ),
            array(
                'value' => Safe2Pay_S2P_Model_Api::INVOICE_STATUS_REFUNDED,
                'label' => Mage::helper('s2p')->__('Refunded')
            ),
            array(
                'value' => Safe2Pay_S2P_Model_Api::INVOICE_STATUS_EXPIRED,
                'label' => Mage::helper('s2p')->__('Expired')
            ),
        );
    }

    public function getOptionLabel($value)
    {
        foreach ($this->toOptionArray() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
