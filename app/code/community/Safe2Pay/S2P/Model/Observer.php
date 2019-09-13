<?php
/**
 * @category    Safe2Pay
 * @package     Safe2Pay_S2P
 * @copyright   Copyright (c) 2014 Safe2Pay. (https://safe2pay.com.br)
 */

class Safe2Pay_S2P_Model_Observer
{
    public function addJs(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        $blockType = $block->getType();
        $targetBlocks = array(
            'checkout/onepage_payment',
            'aw_onestepcheckout/onestep_form_paymentmethod',
        );
        if (in_array($blockType, $targetBlocks) && Mage::getStoreConfig('payment/s2p_cc/active')) {
            $transport = $observer->getEvent()->getTransport();
            $html = $transport->getHtml();
            $preHtml = $block->getLayout()
                ->createBlock('core/template')
                ->setTemplate('s2p/checkout/payment/js.phtml')
                ->toHtml();
            $transport->setHtml($preHtml . $html);
        }
    }

    public function addTotal(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Checkout_Block_Onepage_Review_Info) {
            $transport = $observer->getEvent()->getTransport();
            $reviewHtml = $transport->getHtml();
            $totalHtml = $block->getLayout()
                ->createBlock('s2p/checkout_cart_total')
                ->toHtml();

            $html = str_replace('</tfoot>', $totalHtml . '</tfoot>', $reviewHtml);
            $transport->setHtml($html);
        }
    }
}
