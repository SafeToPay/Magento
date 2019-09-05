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
    'installments'                      => array('type' => Varien_Db_Ddl_Table::TYPE_SMALLINT),
    'installment_description'           => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
);

foreach ($attributes as $attribute => $options) {
    $installer->addAttribute($entity, $attribute, $options);
}

// Order Payment
$entity = 'order_payment';
$attributes = array(
    'safe2pay_transaction_id'   => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    's2p_url'                  => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    's2p_pdf'                  => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
    'installments'              => array('type' => Varien_Db_Ddl_Table::TYPE_SMALLINT),
    'installment_description'   => array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR),
);

foreach ($attributes as $attribute => $options) {
    $installer->addAttribute($entity, $attribute, $options);
}

$installer->endSetup();
