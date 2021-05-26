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

    const XML_PATH_BANKSLIP_INSTRUCTIONS       = 'payment/s2p_boleto/instructions';
    const XML_PATH_BANKSLIP_DUE_DAYS           = 'payment/s2p_boleto/due_days';
    const XML_PATH_BANKSLIP_PAY_AFTER_DUE      = 'payment/s2p_boleto/pay_after_due';

    const XML_PATH_EXPIRATION                  = 'payment/s2p_pix/expiration';


    public function getInstructions()
    {
        $instructions = Mage::getStoreConfig(self::XML_PATH_BANKSLIP_INSTRUCTIONS);

        if ($instructions)
        {
            $instructions = explode("\r\n", $instructions);
        }

        return $instructions;
    }

    public function getDueDate()
    {
        $duedays = Mage::getStoreConfig(self::XML_PATH_BANKSLIP_DUE_DAYS);

        if ($duedays > 0)
        {
            return date('d/m/Y', strtotime(' + ' . $duedays . ' days'));
        }
        else
        {
            return date("d/m/Y");
        }

    }

    public function getCancelAfterDue()
    {
        $canPayAfterDue = Mage::getStoreConfig(self::XML_PATH_BANKSLIP_PAY_AFTER_DUE);
        return !$canPayAfterDue;
    }

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

    public function getExpirationPix()
    {
        return Mage::getStoreConfig(self::XML_PATH_EXPIRATION);
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

    public function getCustomerId()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return $customer->getEntityId();
    }

    public function getListCardToken($customerId)
    {
        $resource = Mage::getSingleton("core/resource");
        $connection = $resource->getConnection("core_write");

        $sql = "SELECT customer_card_id, card_token, card_masked_number FROM customer_card WHERE customer_id = $customerId";         
        $results = $connection->fetchAll($sql);

        return $results;
    }

    public function getCardToken($customer_card_id, $customer_id)
    {
        $resource = Mage::getSingleton("core/resource");
        $connection = $resource->getConnection("core_write");

        $sql = "SELECT card_token FROM customer_card WHERE customer_card_id = $customer_card_id AND customer_id = $customer_id";         
        $result = $connection->fetchOne($sql);

        return $result;
    }

    public function insertToken($tokenize)
    {
        $resource = Mage::getSingleton("core/resource");
        $connection = $resource->getConnection("core_write");

        $sql = "INSERT INTO customer_card (customer_id, card_token, card_masked_number) VALUES ($tokenize->CustomerId, '$tokenize->CardToken', '$tokenize->CardMaskedNumber')";         
        $connection->query($sql);  

        $sql = "SELECT customer_card_id FROM customer_card WHERE card_token = '$tokenize->CardToken'";
        $result = $connection->fetchOne($sql);

        return $result;
    }

    public function deleteToken($customerCardId, $customerId)
    {
        $resource = Mage::getSingleton("core/resource");
        $connection = $resource->getConnection("core_write");

        $sql = "DELETE FROM customer_card WHERE customer_card_id=$customerCardId AND customer_id=$customerId";         
        $connection->query($sql);
    }

    public function Log($filename, $texto)
    {
         $fp = fopen('c:\\out\\' . $filename . '.txt', 'w');
         fwrite($fp, Mage::helper('core')->jsonEncode($texto));
         fclose($fp);
    }

    public function tokenize($credit_card, $feedbacks)
    {
        $result = Mage::getSingleton('s2p/api')->tokenize($credit_card);

        if ($result->HasError)
        {
            if ($feedbacks)
            {
                Mage::getSingleton('customer/session')->addError($this->__($result->Error));
            }
        }
        else
        {
            $card_first6 = substr($credit_card->CardNumber, 0, 6);
            $card_last4 = substr($credit_card->CardNumber, -4);

            $tokenize =  (object) ['CustomerId' => Mage::helper('s2p')->getCustomerId(),
                                   'CardToken' => $result->ResponseDetail->Token,
                                   'CardMaskedNumber' => $card_first6 . '••••••' . $card_last4];
            
            $customer_card_id = $this->insertToken($tokenize);
         
            if ($feedbacks)
            {
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('s2p')->__('O seu cartão foi salvo com sucesso.'));
            }

            return $customer_card_id;
        }
    }
}
