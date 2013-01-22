<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../header.php');

$smarty->assign(array('this_path' => __PS_BASE_URI__));
$smarty->display(_PS_MODULE_DIR_ . 'servired/pago_error.tpl');

include(dirname(__FILE__) . '/../../footer.php');
?>
