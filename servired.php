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
if (!defined('_CAN_LOAD_FILES_'))
    exit;

class servired extends PaymentModule
{

    private $_html = '';
    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'servired';
        $this->tab = 'payments_gateways';
        $this->version = '1.5.4';

        //configuration data array
        $config = Configuration::getMultiple(array('SERVIRED_TPV_URL', 'SERVIRED_MERCHANT_KEY', 'SERVIRED_MERCHANT_NAME', 'SERVIRED_MERCHANT_CODE', 'SERVIRED_TERMINAL', 'SERVIRED_SIGNATURE', 'SERVIRED_EXTRA_FEE', 'SERVIRED_CURRENCY', 'SERVIRED_TRANSACTION_TYPE', 'SERVIRED_NOTIFICATION', 'SERVIRED_SSL', 'SERVIRED_PAYMENT_ERROR', 'SERVIRED_LANGUAGES'));
        //set configuration values
        $this->environment = $config['SERVIRED_TPV_URL'];
        switch ($this->environment) {
            case 1:
                $this->tpv_url = "https://sis-t.redsys.es:25443/sis/realizarPago";
                break;
            case 2:
                $this->tpv_url = "https://sis-i.redsys.es:25443/sis/realizarPago";
                break;
            default:
                $this->tpv_url = "https://sis.sermepa.es/sis/realizarPago";
        }
        $this->merchant_key = $config['SERVIRED_MERCHANT_KEY'];
        if (isset($config['SERVIRED_MERCHANT_NAME']))
            $this->merchant_name = $config['SERVIRED_MERCHANT_NAME'];
        if (isset($config['SERVIRED_MERCHANT_CODE']))
            $this->merchant_code = $config['SERVIRED_MERCHANT_CODE'];
        if (isset($config['SERVIRED_TERMINAL']))
            $this->terminal = $config['SERVIRED_TERMINAL'];
        if (isset($config['SERVIRED_SIGNATURE']))
            $this->signature = $config['SERVIRED_SIGNATURE'];
        if (isset($config['SERVIRED_CURRENCY']))
            $this->currency_code = $config['SERVIRED_CURRENCY'];
        if (isset($config['SERVIRED_TRANSACTION_TYPE']))
            $this->transaction_type = $config['SERVIRED_TRANSACTION_TYPE'];
        if (isset($config['SERVIRED_NOTIFICATION']))
            $this->notification = $config['SERVIRED_NOTIFICATION'];
        if (isset($config['SERVIRED_SSL']))
            $this->ssl = $config['SERVIRED_SSL'];
        if (isset($config['SERVIRED_PAYMENT_ERROR']))
            $this->payment_error = $config['SERVIRED_PAYMENT_ERROR'];
        if (isset($config['SERVIRED_LANGUAGES']))
            $this->lang_activation = $config['SERVIRED_LANGUAGES'];


        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Servired Payment Gateway');
        $this->description = $this->l('Payment using debit or credit card trough Servired Payment Gateway');

        //show an alert is some configuration data are missing
        if (!isset($this->tpv_url)
                OR !isset($this->merchant_key)
                OR !isset($this->merchant_name)
                OR !isset($this->merchant_code)
                OR !isset($this->terminal)
                OR !isset($this->signature)
                OR !isset($this->currency_code)
                OR !isset($this->transaction_type)
                OR !isset($this->notification)
                OR !isset($this->ssl)
                OR !isset($this->payment_error)
                OR !isset($this->lang_activation))
            $this->warning = $this->l('Some data is missing. You need to configure it before using Servired\'s module.');
    }

    public function install()
    {
        //module default values
        if (!parent::install()
                //OR !$this->createServiredPaymentTable() //calls function to create payment card table
                OR !Configuration::updateValue('SERVIRED_TPV_URL', '0')
                OR !Configuration::updateValue('SERVIRED_MERCHANT_NAME', $this->l('Your shop\'s name'))
                OR !Configuration::updateValue('SERVIRED_TERMINAL', 1)
                OR !Configuration::updateValue('SERVIRED_SIGNATURE', 0)
                OR !Configuration::updateValue('SERVIRED_CURRENCY', '978')
                OR !Configuration::updateValue('SERVIRED_TRANSACTION_TYPE', 0)
                OR !Configuration::updateValue('SERVIRED_NOTIFICATION', 0)
                OR !Configuration::updateValue('SERVIRED_SSL', 0)
                OR !Configuration::updateValue('SERVIRED_PAYMENT_ERROR', 0)
                OR !Configuration::updateValue('SERVIRED_LANGUAGES', 0)
                OR !$this->registerHook('payment')
                OR !$this->registerHook('paymentReturn'))
            return false;
        return true;
    }

    function createServiredPaymentTable()
    {
        /*         * Function called by install - 
         * creates the "order_paymentcard" table required for storing payment card details
         * Column Descriptions: id_payment the primary key. 
         * id order: Stores the order number associated with this payment card
         * cardholder_name: Stores the card holder name
         * cardnumber: Stores the card number
         * expiry date: Stores date the card expires
         */

        $db = Db::getInstance();
        $query = "CREATE TABLE `" . _DB_PREFIX_ . "servired_order` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `order_id` varchar(128) DEFAULT NULL,
                `merchant_id` varchar(128) DEFAULT NULL,
                `amount` varchar(128) DEFAULT NULL,
                `currency` varchar(128) DEFAULT NULL,
                `response` varchar(255) DEFAULT NULL,
                `signature` varchar(255) DEFAULT NULL,
                `message` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`) 
                ) ENGINE = MYISAM ";

        $db->Execute($query);

        return true;
    }

    public function uninstall()
    {
        //remove module saved values on install
        if (!Configuration::deleteByName('SERVIRED_TPV_URL')
                OR !Configuration::deleteByName('SERVIRED_MERCHANT_KEY')
                OR !Configuration::deleteByName('SERVIRED_MERCHANT_NAME')
                OR !Configuration::deleteByName('SERVIRED_MERCHANT_CODE')
                OR !Configuration::deleteByName('SERVIRED_TERMINAL')
                OR !Configuration::deleteByName('SERVIRED_SIGNATURE')
                OR !Configuration::deleteByName('SERVIRED_CURRENCY')
                OR !Configuration::deleteByName('SERVIRED_TRANSACTION_TYPE')
                OR !Configuration::deleteByName('SERVIRED_NOTIFICATION')
                OR !Configuration::deleteByName('SERVIRED_SSL')
                OR !Configuration::deleteByName('SERVIRED_PAYMENT_ERROR')
                OR !Configuration::deleteByName('SERVIRED_LANGUAGES')
                OR !parent::uninstall())
            return false;
        return true;
    }

    private function _postValidation()
    {
        //showing message on form post if errors
        if (isset($_POST['btnSubmit'])) {
            if (empty($_POST['merchant_key']))
                $this->_postErrors[] = $this->l('Private key is required.');
            if (empty($_POST['merchant_name']))
                $this->_postErrors[] = $this->l('Merchant\'s name is mandatory.');
            if (empty($_POST['merchant_code']))
                $this->_postErrors[] = $this->l('Merchant\'s code is mandatory (FUC).');
            if (empty($_POST['terminal']))
                $this->_postErrors[] = $this->l('Merchant\'s code is required (FUC).');
            if (empty($_POST['currency_code']))
                $this->_postErrors[] = $this->l('Currency code is required.');
        }
    }

    private function _postProcess()
    {
        //updating settings onto db
        if (isset($_POST['btnSubmit'])) {
            Configuration::updateValue('SERVIRED_TPV_URL', $_POST['tpv_url']);
            Configuration::updateValue('SERVIRED_MERCHANT_KEY', $_POST['merchant_key']);
            Configuration::updateValue('SERVIRED_MERCHANT_NAME', $_POST['merchant_name']);
            Configuration::updateValue('SERVIRED_MERCHANT_CODE', $_POST['merchant_code']);
            Configuration::updateValue('SERVIRED_TERMINAL', $_POST['terminal']);
            Configuration::updateValue('SERVIRED_SIGNATURE', $_POST['signature']);
            Configuration::updateValue('SERVIRED_CURRENCY', $_POST['currency_code']);
            Configuration::updateValue('SERVIRED_TRANSACTION_TYPE', $_POST['transaction_type']);
            Configuration::updateValue('SERVIRED_NOTIFICATION', $_POST['notification']);
            Configuration::updateValue('SERVIRED_SSL', $_POST['ssl']);
            Configuration::updateValue('SERVIRED_PAYMENT_ERROR', $_POST['payment_error']);
            Configuration::updateValue('SERVIRED_LANGUAGES', $_POST['lang_activation']);
        }

        $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('ok') . '" /> ' . $this->l('Configuration is updated') . '</div>';
    }

    private function _displayservired()
    {
        //module list text
        $this->_html .= '<img src="../modules/servired/img/servired.png" style="float:left; margin-right:15px;"><b>' . $this->l('This module allows payment using credit / debit cards.') . '</b><br /><br />
		' . $this->l('If customers choose this payment gateway, the payment can be done automatically.') . '<br /><br /><br />';
    }

    private function _displayForm()
    {

        //currency codes select field.
        $currency_code = Tools::getValue('currency_code', $this->currency_code);
        $iseuro = ($currency_code == '978') ? ' selected="selected" ' : '';
        $isdollar = ($currency_code == '840') ? ' selected="selected" ' : '';

        //enable /  disable SSL
        $ssl = Tools::getValue('ssl', $this->ssl);
        $ssl_active = ($ssl == 1) ? ' checked="checked" ' : '';
        $ssl_deactive = ($ssl == 0) ? ' checked="checked" ' : '';

        // What to do in case of error
        $payment_error = Tools::getValue('payment_error', $this->payment_error);
        $payment_error_active = ($payment_error == 1) ? ' checked="checked" ' : '';
        $payment_error_deactive = ($payment_error == 0) ? ' checked="checked" ' : '';

        //language options
        $lang_activation = Tools::getValue('lang_activation', $this->lang_activation);
        $lang_status_active = ($lang_activation == 1) ? ' checked="checked" ' : '';
        $lang_status_deactive = ($lang_activation == 0) ? ' checked="checked" ' : '';

        //environment options (we can choose among production enviroment and test)
        if (!isset($_POST['tpv_url']))
            $environment = Tools::getValue('environment', $this->environment);
        else
            $environment = $_POST['tpv_url'];
        $production_environment = ($environment == 0) ? ' selected="selected" ' : '';
        $environment_i = ($environment == 2) ? ' selected="selected" ' : '';
        $environment_t = ($environment == 1) ? ' selected="selected" ' : '';

        //signature options
        $signature = Tools::getValue('signature', $this->signature);
        $signature_extended = ($signature == 0) ? ' checked="checked" ' : '';
        $signature_complete = ($signature == 1) ? ' checked="checked" ' : '';

        //notifications (notify prestashop about payment result in order to process order or emtpy shopping cart)
        $notification = Tools::getValue('notification', $this->notification);
        $notification_active = ($notification == 1) ? ' checked="checked" ' : '';
        $notification_deactive = ($notification == 0) ? ' checked="checked" ' : '';

        //configuration form
        $this->_html .=
                '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />' . $this->l('TPV Settings') . '</legend>
				<table border="0" width="680" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">' . $this->l('Please, fill in all the fields with data provided from your Bank Services') . '.<br /><br /></td></tr>
					<tr><td width="215" style="height: 35px;">' . $this->l('Servired Environment') . '</td><td><select name="tpv_url"><option value="0"' . $production_environment . '>' . $this->l('Real') . '</option><option value="1"' . $environment_t . '>' . $this->l('Testing in sis-t') . '</option><option value="2"' . $environment_i . '>' . $this->l('Testing in sis-i') . '</option></select></td></tr>
					<tr><td width="215" style="height: 35px;">' . $this->l('Merchant\'s Name') . '</td><td><input type="text" name="merchant_name" value="' . htmlentities(Tools::getValue('merchant_name', $this->merchant_name), ENT_COMPAT, 'UTF-8') . '" style="width: 200px;" /></td></tr>
					<tr><td width="215" style="height: 35px;">' . $this->l('Merchant\'s Code (FUC)') . '</td><td><input type="text" name="merchant_code" value="' . Tools::getValue('merchant_code', $this->merchant_code) . '" style="width: 200px;" /></td></tr>
					<tr><td width="215" style="height: 35px;">' . $this->l('Merchant\'s Encryption Private Key') . '</td><td><input type="text" name="merchant_key" value="' . Tools::getValue('merchant_key', $this->merchant_key) . '" style="width: 200px;" /></td></tr>
					<tr><td width="215" style="height: 35px;">' . $this->l('Terminal Number') . '</td><td><input type="text" name="terminal" value="' . Tools::getValue('terminal', $this->terminal) . '" style="width: 80px;" /></td></tr>
					<tr><td width="215" style="height: 35px;">' . $this->l('Signature') . '</td><td><input type="radio" name="signature" id="signature_complete" value="1"' . $signature_complete . '/>' . $this->l('Complete') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="signature" id="signature_extended" value="0"' . $signature_extended . '/>' . $this->l('Extended') . '</td></tr>
					<tr><td width="215" style="height: 35px;">' . $this->l('Currency') . '</td><td><select name="currency_code" style="width: 80px;"><option value=""></option><option value="978"' . $iseuro . '>EURO</option><option value="840"' . $isdollar . '>DOLLAR</option></select></td></tr>
					<tr><td width="215" style="height: 35px;">' . $this->l('Transaction Type') . '</td><td><input type="text" name="transaction_type" value="' . Tools::getValue('transaction_type', $this->transaction_type) . '" style="width: 80px;" /></td></tr>
				</table>
			</fieldset>
			<br>
			<fieldset>
			<legend><img src="../img/admin/cog.gif" />' . $this->l('Personalized Configuration') . '</legend>
			<table border="0" width="680" cellpadding="0" cellspacing="0" id="form">
		<tr>
		<td colspan="2">' . $this->l('Please, select your options') . '.<br /><br /></td>
		</tr>
		<tr>
		<td width="450" style="height: 35px;">' . $this->l('HTTP Notification (When disable neither processes order or empties shopping cart)') . '</td>
			<td>
			<input type="radio" name="notification" id="notification_1" value="1"' . $notification_active . '/>
			<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />
			<input type="radio" name="notification" id="notification_0" value="0"' . $notification_deactive . '/>
			<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />
			</td>
		</tr>
		<tr>
		<td width="340" style="height: 35px;">' . $this->l('SSL for validation URL') . '</td>
			<td>
			<input type="radio" name="ssl" id="ssl_1" value="1" ' . $ssl_active . '/>
			<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />
			<input type="radio" name="ssl" id="ssl_0" value="0" ' . $ssl_deactive . '/>
			<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />
			</td>
		</tr>
		<tr>
		<td width="340" style="height: 35px;">' . $this->l('In case of error, let the customers choose another payment method') . '</td>
			<td>
			<input type="radio" name="payment_error" id="payment_error_1" value="1" ' . $payment_error_active . '/>
			<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />
			<input type="radio" name="payment_error" id="payment_error_0" value="0" ' . $payment_error_deactive . '/>
			<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />
			</td>
		</tr>
		<tr>
		<td width="340" style="height: 35px;">' . $this->l('Enable all languages into TPV') . '</td>
			<td>
			<input type="radio" name="lang_activation" id="lang_status_active" value="1" ' . $lang_status_active . '/>
			<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />
			<input type="radio" name="lang_activation" id="lang_status_deactive" value="0" ' . $lang_status_deactive . '/>
			<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />
			</td>
		</tr>
		</table>
			</fieldset>
			<br>
		<input class="button" name="btnSubmit" value="' . $this->l('Save Settings') . '" type="submit" />
		</form>';
    }

    public function getContent()
    {
        //get data
        $this->_html = '<h2>' . $this->displayName . '</h2>';
        if (!empty($_POST)) {
            $this->_postValidation();
            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors AS $err)
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
        }
        else
            $this->_html .= '<br />';
        $this->_displayservired();
        $this->_displayForm();
        return $this->_html;
    }

    public function hookPayment($params)
    {
        global $smarty, $cookie, $cart;

        //applying extra fee
        /*
          $extra_fee_percentage = Tools::getValue('extra_fee', $this->extra_fee);
          $extra_fee_percentage = str_replace (',','.',$extra_fee_percentage);
          $total_purchase = floatval($cart->getOrderTotal(true, 3));
          $extra_fee = ($extra_fee_percentage / 100) * $total_purchase;
         */

        //total amount of purchase
        $id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
        $currency = new Currency(intval($id_currency));
        //Post 164, pagina 9 de este hilo: http://www.prestashop.com/forums/topic/110666-modulo-servired-08f-perfectamente-funcional-en-ps14/page__st__160
        $total_amount = number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), Currency::getCurrent(), false), 2, '.', '');
        $total_amount = str_replace('.', '', $total_amount);
        $total_amount = floatval($total_amount);

        //order number is last 8 digit from cart id + time (MMSS format)
        $order = str_pad($params['cart']->id, 8, "0", STR_PAD_LEFT) . date('is');

        $merchant_code = Tools::getValue('merchant_code', $this->merchant_code);
        $currency_code = Tools::getValue('currency_code', $this->currency_code);
        $transaction_type = Tools::getValue('transaction_type', $this->transaction_type);

        $ssl = Tools::getValue('ssl', $this->ssl);
        if ($ssl == '0')
            $merchant_url = 'http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/servired/tpv_response.php';
        elseif ($ssl == '1')
            $merchant_url = 'https://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/servired/tpv_response.php';
        else
            $merchant_url = 'nothing';

        $merchant_key = Tools::getValue('merchant_key', $this->merchant_key);

        //calculating SHA1
        if (Tools::getValue('signature', $this->signature))
            $message = $total_amount . $order . $merchant_code . $currency_code . $merchant_key;
        else
            $message = $total_amount . $order . $merchant_code . $currency_code . $transaction_type . $merchant_url . $merchant_key;

        $signature = strtoupper(sha1($message));

        $products = $params['cart']->getProducts();
        $products_description = '';
        $id_cart = intval($params['cart']->id);

        //TPV languages activation
        $lang_activation = Tools::getValue('lang_activation', $this->lang_activation);
        if ($lang_activation == 1) {
            $ps_language = new Language(intval($cookie->id_lang));
            $web_site_lang_code = $ps_language->iso_code;
            switch ($web_site_lang_code) {
                case 'es':
                    $tpv_lang_code = '001';
                    break;
                case 'en':
                    $tpv_lang_code = '002';
                    break;
                case 'ca':
                    $tpv_lang_code = '003';
                    break;
                case 'fr':
                    $tpv_lang_code = '004';
                    break;
                case 'de':
                    $tpv_lang_code = '005';
                    break;
                case 'nl':
                    $tpv_lang_code = '006';
                    break;
                case 'it':
                    $tpv_lang_code = '007';
                    break;
                case 'sv':
                    $tpv_lang_code = '008';
                    break;
                case 'pt':
                    $tpv_lang_code = '009';
                    break;
                case 'pl':
                    $tpv_lang_code = '011';
                    break;
                case 'gl':
                    $tpv_lang_code = '012';
                    break;
                case 'eu':
                    $tpv_lang_code = '013';
                    break;
                default:
                    $tpv_lang_code = '002';
            }
        } else {
            $tpv_lang_code = '0';
        }

        foreach ($products as $product) {
            $products_description .= '(' . $product['quantity'] . ') ' . $product['name'] . ";";
        }
        $customer = new Customer((int) ($cart->id_customer));

        $smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            'tpv_url' => Tools::getValue('tpv_url', $this->tpv_url),
            'total_amount' => $total_amount,
            'currency_code' => $currency_code,
            'order' => $order,
            'merchant_code' => $merchant_code,
            'terminal' => Tools::getValue('terminal', $this->terminal),
            'transaction_type' => $transaction_type,
            'cardholder' => ($cookie->logged ? $cookie->customer_firstname . ' ' . $cookie->customer_lastname : false),
            'merchant_name' => Tools::getValue('merchant_name', $this->merchant_name),
            'merchant_url' => $merchant_url,
            'notification' => Tools::getValue('notification', $this->notification),
            'product_list' => $products_description,
//            'url_ok' => 'http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . $id_cart . '&id_module=' . (int) ($this->id) . '&key=' . $customer->secure_key . '&id_order=' . (int) ($order),
            'url_ok' => $merchant_url . '?id_cart=' . $id_cart . '&id_module=' . (int) ($this->id) . '&key=' . $customer->secure_key . '&id_order=' . (int) ($order),
            'url_ko' => 'http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/servired/payment_error.php',
            'signature' => $signature,
            'tpv_lang_code' => $tpv_lang_code,
                //'extra_fee' => number_format($extra_fee, 2, '.', '')                        
        ));
        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return;
        global $smarty;
        $smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));
        return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
    }

    /*
      public function hookOrderConfirmation($params)
      {
      if (!$this->active)
      return;

      $servired = new servired();
      $this->assign(array(
      'this_path' => $this->_path
      //,'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
      ));

      return $this->display(__FILE__, 'payment_ok.tpl');
      }
      public function fetchTemplate($path, $name, $extension = false)
      {
      return $this->context->smarty->fetch(_PS_MODULE_DIR_.'/servired/'.$path.$name.'.'.($extension ? $extension : 'tpl'));
      }
     * 
     */
}
