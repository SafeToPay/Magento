<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Model_Api_Abstract
{
    const VERSION   = '2';
    const PAYMENT_ENDPOINT  = 'https://payment.safe2pay.com.br';
    const API_ENDPOINT  = 'https://api.safe2pay.com.br';

    protected $_apiToken;
    protected $_mode;

    public function __construct()
    {
        $this->_apiToken = Mage::helper('s2p')->getApiToken();
        $this->_mode = Mage::helper('s2p')->getMode();
    }

    public function getApiToken()
    {
        if (!$this->_apiToken) {
            Mage::throwException(Mage::helper('s2p')->__('Você precisa configurar o token da API do Safe2Pay antes realizar uma venda.'));
        }
        return $this->_apiToken;
    }

    public function getMode()
    {
        if (!$this->_mode) {
            Mage::throwException(Mage::helper('s2p')->__('Você precisa configurar o ambiente da API do Safe2Pay antes realizar uma venda.'));
        }
        return $this->_mode;
    }

    public function request($url, $data=null, $method='GET')
    {
        $config = array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'timeout' => 120
        );
        
        $client = new Zend_Http_Client($url, $config); 
        $client->setMethod($method);
        $client->setHeaders('Content-type','application/json');
        $client->setHeaders('X-API-KEY', $this->getApiToken());

        if (!$data) {
            $data = new Varien_Object();
        }


        if (in_array($method, array(Zend_Http_Client::POST, Zend_Http_Client::PUT, Zend_Http_Client::DELETE))) 
        {
            $json = Mage::helper('core')->jsonEncode($data);
            $client->setRawData($json, 'application/json');
        } 
        else 
        {
            $client->setParameterGet($this->_parseArray($data));
        }

        $response = $client->request();
        $responseBody = $response->getBody();
        $body = json_decode($responseBody);

        return $body;
    }

     public function getPaymentBaseUrl()
    {
        $url = self::PAYMENT_ENDPOINT . '/v' . self::VERSION;
        return $url;
    }

    public function getApiBaseUrl()
    {
        $url = self::API_ENDPOINT . '/v' . self::VERSION;
        return $url;
    }

    protected function _parseObject(array $data)
    {
        $object = new Varien_Object();
        if ($this->_isAssoc($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if ($this->_isAssoc($value)) {
                        $object->setData($key, $this->_parseObject($value));
                    } else {
                        $items = array();
                        foreach ($value as $itemKey => $itemValue) {
                            $items[$itemKey] = $this->_parseObject($itemValue);
                        }
                        $object->setData($key, $items);
                    }
                } else {
                    $object->setData($key, $value);
                }
            }
        } else {
            $items = array();
            foreach ($data as $itemKey => $itemValue) {
                $items[$itemKey] = $this->_parseObject($itemValue);
            }
            $object->setData('items', $items);
        }
        return $object;
    }

    protected function _parseArray(Varien_Object $object)
    {
        $array = array();
        foreach ($object->getData() as $key => $value) {
            if ($value instanceof Varien_Object) {
                $array[$key] = $this->_parseArray($value);
            } elseif (is_array($value)) {
                $items = array();
                foreach ($value as $itemKey => $itemValue) {
                    if ($itemValue instanceof Varien_Object) {
                        $items[$itemKey] = $this->_parseArray($itemValue);
                    } else {
                        $items[$itemKey] = $itemValue;
                    }
                }
                $array[$key] = $items;
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    protected function _isAssoc($array) {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
}
