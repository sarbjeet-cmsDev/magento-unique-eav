<?php
require_once('app/Mage.php');
$app = Mage::app('default');

$config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");

$prefix  = (string)Mage::getConfig()->getTablePrefix();


$mysqli = new mysqli($config->host,$config->username,$config->password,$config->dbname);

$multiple_value_q = "SELECT value,count(*) as count ,min(option_id) as min_id,max(option_id) as max_id FROM `".$prefix."eav_attribute_option_value` GROUP BY `value`  having count(*) > 1 ORDER BY count(*) DESC";

$multiple_values = [];

if($result = $mysqli->query($multiple_value_q))
{
	while($row = $result->fetch_object())
	{
		$multiple_values[] = $row;
	}
}



if(count($multiple_values))
{
	foreach ($multiple_values as $option) {
		
		//set one value to products
		$q1 = "UPDATE `".$prefix."catalog_product_entity_int` SET `value` = ".$option->min_id." WHERE `value` IN (SELECT option_id FROM `".$prefix."eav_attribute_option_value` WHERE `value` = '".$option->value."')";
		if($mysqli->query($q1))
		{
			$q2 = "DELETE  FROM `".$prefix."eav_attribute_option` WHERE `option_id` IN (SELECT `option_id` FROM `".$prefix."eav_attribute_option_value` WHERE `value` = '".$option->value."' AND `option_id` != ".$option->min_id.")";
			$mysqli->query($q2);
		}
	}
	echo "Attribute Values are fixed";
}
else
{
	echo "No Duplicate Attribute value Found";
}


?>