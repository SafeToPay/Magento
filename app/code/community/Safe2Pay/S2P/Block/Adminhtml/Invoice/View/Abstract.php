<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */

abstract class Safe2Pay_S2P_Block_Adminhtml_Invoice_View_Abstract extends Mage_Adminhtml_Block_Template
{
    protected $_viewBlockType;
    protected $_invoice;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('s2p/invoice/view/' . $this->_viewBlockType . '.phtml');
    }

    public function getInvoice()
    {
        return $this->_invoice;
    }

    public function setInvoice($invoice)
    {
        $this->_invoice = $invoice;
    }

    public function getDueDate()
    {
        return date('d/m/Y', strtotime($this->getInvoice()->getDueDate()));
    }

    public function getStatusLabel()
    {
        return Mage::getModel('s2p/source_status')->getOptionLabel($this->getInvoice()->getStatus());
    }

    public function getTotal()
    {
        return $this->getInvoice()->getTotal();
    }
}
