<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Helper_Data extends Mage_Core_Helper_Abstract
{
    // Ambiente
    const XML_PATH_SETTINGS_MODE               = 'payment/s2p_settings/mode';
    
    // Produção
    const XML_PATH_SETTINGS_TOKEN              = 'payment/s2p_settings/token_live';
    const XML_PATH_SETTINGS_SECRET             = 'payment/s2p_settings/secret_key_live';

    // Sandbox
    const XML_PATH_SETTINGS_TOKEN_SANDBOX      = 'payment/s2p_settings/token_test';
    const XML_PATH_SETTINGS_SECRET_SANDBOX     = 'payment/s2p_settings/secret_key_test';

    public function getMode()
    {
        $mode = Mage::getStoreConfig(self::XML_PATH_SETTINGS_MODE);
        return $mode;
    }

    public function getApiToken()
    {
        $mode = Mage::getStoreConfig(self::XML_PATH_SETTINGS_MODE);

        if ($mode == "live")
        {
            $apiToken = Mage::getStoreConfig(self::XML_PATH_SETTINGS_TOKEN);    
        }
        else
        {
            $apiToken = Mage::getStoreConfig(self::XML_PATH_SETTINGS_TOKEN_SANDBOX);
        }

        return $apiToken;
    }

    public function getApiSecretKey()
    {
        $mode = Mage::getStoreConfig(self::XML_PATH_SETTINGS_MODE);

        if ($mode == "live")
        {
            $apiSecretKey = Mage::getStoreConfig(self::XML_PATH_SETTINGS_SECRET);    
        }
        else
        {
            $apiSecretKey = Mage::getStoreConfig(self::XML_PATH_SETTINGS_SECRET_SANDBOX);
        }

        return $apiSecretKey;
    }








    public function getPhonePrefix($telephone)
    {
        $telephone = Zend_Filter::filterStatic($telephone, 'Digits');
        $prefix = substr($telephone, 0, 2);
        return $prefix;
    }

    public function getPhone($telephone)
    {
        $telephone = Zend_Filter::filterStatic($telephone, 'Digits');
        $phone = substr($telephone, 2);
        return $phone;
    }

    public function formatAmount($amount)
    {
        return number_format($amount, 2, '', '');
    }

    public function getItemsFromOrder($order)
    {
        $items = array();
        foreach ($order->getAllVisibleItems() as $data) {
            $item = new Varien_Object();
            $item->setDescription($data->getName());
            $item->setQuantity($data->getQtyOrdered());
            $item->setPriceCents($this->formatAmount($data->getBasePrice()));
            $items[] = $item;
        }

        // Shipping
        if ($order->getBaseShippingAmount() > 0) {
            $item = new Varien_Object();
            $item->setDescription($this->__('Shipping & Handling') . ' (' . $order->getShippingDescription() . ')');
            $item->setQuantity(1);
            $item->setPriceCents($this->formatAmount($order->getBaseShippingAmount()));
            $items[] = $item;
        }

        return $items;
    }

    public function getPayerInfoFromOrder($order)
    {
        $billingAddress = $order->getBillingAddress();

        $address = new Varien_Object();
        $address->setStreet($billingAddress->getStreet(1));
        $address->setNumber($billingAddress->getStreet(2));
        $address->setCity($billingAddress->getCity());
        $address->setState($billingAddress->getRegionCode());
        $address->setCountry('Brasil');
        $address->setZipCode(Zend_Filter::filterStatic($billingAddress->getPostcode(), 'Digits'));

        $payer = new Varien_Object();
        $payer->setPhonePrefix($this->getPhonePrefix($billingAddress->getTelephone()));
        $payer->setPhone($this->getPhone($billingAddress->getTelephone()));
        $payer->setEmail($order->getCustomerEmail());
        $payer->setAddress($address);
        Mage::dispatchEvent('s2p_get_payer_info_from_order_after', array('order' => $order, 'payer_info' => $payer));

        return $payer;
    }

    public function getCustomerId($createIfNotExists = true)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getS2pCustomerId()) {
            // Verify if customer really exists and try create again
            $result = Mage::getSingleton('s2p/api')->getCustomer($customer->getS2pCustomerId());
            if (!$result->getId()) {
                $customer->setS2pCustomerId('');
                $customer->save();
                return $this->getCustomerId();
            }
        } elseif ($createIfNotExists) {
            $customerData = new Varien_Object();
            $customerData->setEmail($customer->getEmail());
            $customerData->setName($customer->getName());
            $customerData->setNotes(Mage::app()->getWebsite()->getName());
            try {
                $result = Mage::getSingleton('s2p/api')->saveCustomer($customerData);
                $customer->setS2pCustomerId($result->getId());
                $customer->save();
            } catch(Exception $e) {
                Mage::throwException($e->getMessage());
            }
        }
        return $customer->getS2pCustomerId();
    }
}
