<?php

/**
 *
 * 
 * @author Yago Ferrer
 * @author Javier Barredo <naveto@gmail.com>
 * @author David Vidal <chienandalu@gmail.com>
 * @author Francisco J. Matas <fjmatad@hotmail.com>
 * @author Andrea De Pirro <andrea.depirro@yameveo.com>
 * @author Enrico Aillaud <enrico.aillaud@yameveo.com>
 */
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../header.php');

$smarty->assign(array('this_path' => __PS_BASE_URI__));
$smarty->display(_PS_MODULE_DIR_ . 'servired/views/templates/hook/payment_error.tpl');
include(dirname(__FILE__) . '/../../footer.php');
