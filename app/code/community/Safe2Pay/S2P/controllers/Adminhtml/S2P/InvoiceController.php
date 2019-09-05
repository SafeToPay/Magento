<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */

class Safe2Pay_S2P_Adminhtml_S2P_InvoiceController extends Mage_Adminhtml_Controller_Action
{
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $result = array();
        $result['success'] = false;
        try {
            $invoice = Mage::getSingleton('s2p/api')->fetch($id);
            if ($invoice->getId()) {
                $result['content_html'] = $this->_getInvoiceHtml($invoice);
                $result['success'] = true;
            } else {
                Mage::throwException($invoice->getErrors());
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error_message'] = $e->getMessage();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _getInvoiceHtml($invoice)
    {
        $this->loadLayout();
        
        
        $blockType = $invoice->getBankSlip() ? 'boleto' : 'cc';
        
        $block = $this->getLayout()->createBlock('s2p/adminhtml_invoice_view_'. $blockType);
        
        $block->setInvoice($invoice);
        return $block->toHtml();
    }
}
