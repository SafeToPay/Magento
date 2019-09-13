<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */

class Safe2Pay_S2P_Payment_MethodController extends Mage_Core_Controller_Front_Action
{
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
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(Mage::helper('s2p')->__('Meus Cartões de Crédito'));
        }
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
        $request = new Varien_Object($this->getRequest()->getParam('payment', array()));

        $credit_card =  (object) ['Holder' => $request->s2p_card_holder,
                                  'CardNumber' => $request->s2p_card_number,
                                  'ExpirationDate' => str_pad($request->s2p_card_expiration_month, 2, "0", STR_PAD_LEFT) . '/' . $request->s2p_card_expiration_year,
                                  'SecurityCode' => $request->s2p_card_verification];

        Mage::helper('s2p')->tokenize($credit_card, true);

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($paymentMethodId = $this->getRequest()->getParam('id')) 
        {
            $customerId = Mage::helper('s2p')->getCustomerId();

            Mage::helper('s2p')->deleteToken($paymentMethodId, $customerId);


            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('s2p')->__('O cartão foi excluído com sucesso!'));
        }
        else
        {
            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('s2p')->__('Não foi possível encontrar o cartão a ser deletado.'));
        }

        $this->_redirect('*/*/');
    }


    
}
