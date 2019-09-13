<?php
/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// Quote Payment
$entity = 'quote_payment';
$attributes = array(
    's2p_token'                        => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    's2p_customer_payment_method_id'   => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    's2p_save'                         => array('type' => Varien_Db_Ddl_Table::TYPE_SMALLINT),
    's2p_customer_identity'            => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    'installments'                     => array('type' => Varien_Db_Ddl_Table::TYPE_SMALLINT),
    'installment_description'          => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
);

foreach ($attributes as $attribute => $options) {
    $installer->addAttribute($entity, $attribute, $options);
}

// Order Payment
$entity = 'order_payment';
$attributes = array(
    's2p_transaction_id'           => array('type' => Varien_Db_Ddl_Table::TYPE_BIGINT),
    's2p_payment_method'           => array('type' => Varien_Db_Ddl_Table::TYPE_SMALLINT),
    'installments'                 => array('type' => Varien_Db_Ddl_Table::TYPE_SMALLINT),
    's2p_url'                      => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    'installments'                 => array('type' => Varien_Db_Ddl_Table::TYPE_SMALLINT),
    'installment_description'      => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    's2p_cripto_symbol'            => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    's2p_cripto_amount'            => array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL),
    's2p_cripto_wallet_address'    => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    's2p_total_with_interest'      => array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL),
    's2p_customer_identity'        => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
);

foreach ($attributes as $attribute => $options) {
    $installer->addAttribute($entity, $attribute, $options);
}

$installer->run("CREATE TABLE customer_card (
	  `customer_card_id` int(11) unsigned NOT NULL auto_increment,
	  `customer_id` int(11) unsigned NOT NULL,
      `card_token` varchar(255) default '',
      `card_masked_number` varchar(255) default '',
	  PRIMARY KEY (`customer_card_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
