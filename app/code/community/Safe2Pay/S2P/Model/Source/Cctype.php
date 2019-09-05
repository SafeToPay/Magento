<?php
/**
 * @category    Safe2Pay
 * @package     Safe2Pay_S2P
 * @copyright   Copyright (c) 2014 Safe2Pay. (https://safe2pay.com.br)
 */
class Safe2Pay_S2P_Model_Source_Cctype extends Mage_Payment_Model_Source_Cctype
{
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'DC');
    }

    public function getTypeByBrand($brand)
    {
        $brand = strtolower($brand);
        $data = array(
            'visa'          => 'VI',
            'mastercard'    => 'MC',
            'amex'          => 'AE',
            'diners'        => 'DC',
        );

        $type = isset($data[$brand]) ? $data[$brand] : null;
        return $type;
    }
}
