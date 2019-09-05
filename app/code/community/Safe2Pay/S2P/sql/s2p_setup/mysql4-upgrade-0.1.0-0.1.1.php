<?php
/**
 * @category    Safe2Pay
 * @package     Safe2Pay_S2P
 * @copyright   Copyright (c) 2014 Safe2Pay. (https://safe2pay.com.br)
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$this->startSetup();

$installer->addAttribute('customer', 's2p_customer_id', array(
    'type'     => 'varchar',
    'input'    => 'hidden',
    'visible'  => false,
    'required' => false
));

$this->endSetup();
