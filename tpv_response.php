<?php

/**
 * @author Yago Ferrer
 * @author Javier Barredo <naveto@gmail.com>
 * @author David Vidal <chienandalu@gmail.com>
 * @author Francisco J. Matas <fjmatad@hotmail.com>
 * @author Andrea De Pirro <andrea.depirro@yameveo.com>
 * @author Enrico Aillaud <enrico.aillaud@yameveo.com>
 */
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../header.php');
include(dirname(__FILE__) . '/servired.php');
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET': $request = &$_GET;
        break;
    case 'POST': $request = &$_POST;
        break;
    default:
}
if (!empty($request)) {

    //getting response answer
    $total_amount = $request["Ds_Amount"];
    $order_id = $request["Ds_Order"];
    $cart_id = $request["id_cart"];
    $key = $request["key"];
    $merchant_code = $request["Ds_MerchantCode"];
    $currency_code = $request["Ds_Currency"];
    $remote_response = $request["Ds_Response"];
    $remote_signature = $request["Ds_Signature"];

    $servired = new servired();

    //Getting settings
    $payment_error = Configuration::get('SERVIRED_PAYMENT_ERROR');
    //private key (needed to create hash)
    $merchant_key = Configuration::get('SERVIRED_MERCHANT_KEY');

    //SHA1
    $message = $total_amount . $order_id . $merchant_code . $currency_code . $remote_response . $merchant_key;
    $local_signature = strtoupper(sha1($message));
    /*
      $db = Db::getInstance();
      Db::getInstance()->insert('servired_order', array(
      'order_id' => $order_id,
      'merchant_id'=>  $merchant_code,
      'amount' => $total_amount,
      'currency'=> $currency_code,
      'response' => $remote_response,
      'signature' => $remote_signature . "-" . $local_signature,
      'message' => $message
      ));
     */
    if ($local_signature == $remote_signature) {
        $total_amount = number_format($total_amount / 100, 4, '.', '');
        $order_id = substr($order_id, 0, 8);
        $order_id = intval($order_id);
        $remote_response = intval($remote_response);
        $shop_currency = 1; // Euros
        if ($remote_response < 101) {
            $mailvars = array();
            $cart = new Cart($order_id);
            $servired->validateOrder($order_id, _PS_OS_PAYMENT_, $total_amount, $servired->displayName, NULL, $mailvars, NULL, false, $cart->secure_key);
            $url = 'index.php?controller=order-confirmation&';
            if (_PS_VERSION_ < '1.5')
                $url = 'order-confirmation.php?';

            Tools::redirect($url . 'id_module=' . (int) $servired->id . '&id_cart=' . $cart_id . '&key=' . $key . '&id_order=' . $servired->currentOrder);
        } else {
            //invalid purchase
            if ($payment_error == 0) {
                //no payment has been done
                $servired->validateOrder($order_id, _PS_OS_ERROR_, 0, $servired->displayName, 'errors:' . $remote_response);
            } elseif ($payment_error == 1) {
                //let the customers retry the payment
            }
            $checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';
            $url = _PS_VERSION_ >= '1.5' ? 'index.php?controller=' . $checkout_type . '&' : $checkout_type . '.php?';
            $url .= 'step=3&cgv=1&message=' . $remote_response;

            if (!isset($_SERVER['HTTP_REFERER']) || strstr($_SERVER['HTTP_REFERER'], 'order'))
                Tools::redirect($url);
            elseif (strstr($_SERVER['HTTP_REFERER'], '?'))
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&gestpayerror=1&message=' . $remote_response, '');
            else
                Tools::redirect($_SERVER['HTTP_REFERER'] . '?gestpayerror=1&message=' . $remote_response, '');
        }
    }
    else {
       $url = _PS_VERSION_ >= '1.5' ? 'index.php?controller=history' : 'history.php';
       Tools::redirect($url);
    }
}
