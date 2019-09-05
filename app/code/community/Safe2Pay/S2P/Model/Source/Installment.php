<?php
/**
 * @category    Safe2Pay
 * @package     Safe2Pay_S2P
 * @copyright   Copyright (c) 2014 Safe2Pay. (https://safe2pay.com.br)
 */
class Safe2Pay_S2P_Model_Source_Installment
{
    public function toOptionArray()
    {
        $options = array();
        for ($i=2; $i <= 12; $i++) {
            $options[] = array('value' => $i, 'label' => $i . 'x');
        }
        return $options;
    }
}
