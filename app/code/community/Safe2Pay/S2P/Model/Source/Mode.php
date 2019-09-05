<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */
class Safe2Pay_S2P_Model_Source_Mode
{
    const MODE_TEST = 'test';
    const MODE_LIVE = 'live';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::MODE_TEST,
                'label' => Mage::helper('s2p')->__('Test')
            ),
            array(
                'value' => self::MODE_LIVE,
                'label' => Mage::helper('s2p')->__('Live')
            ),
        );
    }
}
