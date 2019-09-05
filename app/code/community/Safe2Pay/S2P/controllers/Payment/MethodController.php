<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */

class Safe2Pay_S2P_Payment_MethodController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        if ($block = $this->getLayout()->getBlock('s2p_payment_method_list')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        //if ($headBlock = $this->getLayout()->getBlock('head')) {
        //    $headBlock->setTitle(Mage::helper('s2p')->__('Meus Cartões de Crédito'));
        //}
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('s2p/payment_method');
        }
        $this->renderLayout();
    }

    public function saveAction()
    {
        $data = new Varien_Object($this->getRequest()->getParam('payment', array()));
        $data->setCustomerId(Mage::helper('s2p')->getCustomerId());
        $data->setDescription(Mage::getModel('core/date')->timestamp(time()));
     
        

        

        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('s2p')->__('O seu cartão foi salvo com sucesso.'));




        //$result = Mage::getSingleton('s2p/api')->savePaymentMethod($data);
        
        //if ($result->getErrors()) {
        //    Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving the credit card.'));
        //} else {
         //   Mage::getSingleton('customer/session')->addSuccess(Mage::helper('s2p')->__('Credit card has been saved.'));
        //}

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($paymentMethodId = $this->getRequest()->getParam('id')) {
            $customerId = Mage::helper('s2p')->getCustomerId();
            $result = Mage::getSingleton('s2p/api')->deletePaymentMethod($customerId, $paymentMethodId);
            if ($result->getErrors()) {
                Mage::getSingleton('customer/session')->addError($this->__('An error occurred while deleting the credit card.'));
            } else {
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('s2p')->__('Credit card has been deleted.'));
            }
        } else {
            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('s2p')->__('Unable to find a credit card to delete.'));
        }
        $this->_redirect('*/*/');
    }
}
