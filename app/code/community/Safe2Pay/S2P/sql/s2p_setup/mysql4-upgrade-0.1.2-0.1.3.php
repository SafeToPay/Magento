<?php
/**
 * @category    Safe2Pay
 * @package     Safe2Pay_S2P
 * @copyright   Copyright (c) 2014 Safe2Pay. (https://safe2pay.com.br)
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->addAttribute('quote_payment', 's2p_boleto_name', array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR));
$installer->addAttribute('quote_payment', 's2p_boleto_cpf_cnpj', array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR));

$installer->addAttribute('order_payment', 's2p_boleto_name', array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR));
$installer->addAttribute('order_payment', 's2p_boleto_cpf_cnpj', array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR));
$installer->addAttribute('order_payment', 's2p_boleto_url', array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR));

$this->endSetup();
