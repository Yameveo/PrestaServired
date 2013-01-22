<?php
/*-----------------------------------------------------------------------------
Autor: Javier Barredo
Autor E-Mail: naveto@gmail.com
Fecha: Mayo 2011
Version : 0.7v4
Agradecimientos: Yago Ferrer por su m�dulo de pago  que se utiliz� como base de este m�dulo.
Alberto Fern�ndez por su ayuda con los testeos y las im�genes.
Version: 1.50 (solo probada en PS1.4)
Adaptaci�n a PS 1.4: David Vidal (chienandalu@gmail.com)
Hibridaci�n del m�dulo con versiones anteriores: Francisco J. Matas (fjmatad@hotmail.com)

Notas para la versi�n de Servired 1.50 (28-5-2011)
--------------------------------

[-] Adaptaci�n del m�dulo a la versi�n 1.4 de Prestashop:
  - El pago v�lido retorna a OrderConfirmation, de modo que sigue los cauces de los dem�s m�dulos de pago de Prestashop.
  - De este modo ahora el m�dulo Google Analytics puede ofrecer estad�sticas de estos pagos. Antes no se registraban dichas conversiones.
  - Adaptada plantilla pago-correcto.tpl
  - Corregido bug en plantilla pago-error.tpl
  - Corregido fallos en instalaci�n y desinstalaci�n en versi�n 1.4
  - Corregido fallo de secure_key en PS 1.4
  - Corregida ruta de icono "personalizaci�n"
  - pago_correcto.php deja de ser necesario
  - Algunas modificaciones de gr�ficos
[*] Hibridaci�n del m�dulo adaptado por David Vidal para aumentar la compatibilidad con las plataformas Sermepa.
  * Se redimensionan imagenes que quedaban cortadas en los resultados de la plataforma.
  * Se corrige error con pagos inferiores a 1 euros.
  * Se a�ade selector para configurar el entorno.
  * Se a�ade selector para configurar el tipo de firma.
  * Se a�ade posibilidad para cobrar un recargo en tantos %.
  * Se a�ade Notificaci�n HTTP para entorno de pruebas.
  * Se aumenta el n�mero de versi�n para no confundirlo con las anteriores, ya que existe una versi�n 1.0 muy similar, pero con menos caracter�sticas.

Released under the GNU General Public License
-----------------------------------------------------------------------------*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class servired extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();

	public function __construct(){
		$this->name = 'servired';
		$this->tab = 'payments_gateways';
		$this->version = '1.50';

		// Array config con los datos de configuraci�n
		$config = Configuration::getMultiple(array('SERVIRED_URLTPV', 'SERVIRED_CLAVE', 'SERVIRED_NOMBRE', 'SERVIRED_CODIGO', 'SERVIRED_TERMINAL', 'SERVIRED_TIPOFIRMA', 'SERVIRED_RECARGO', 'SERVIRED_MONEDA', 'SERVIRED_TRANS', 'SERVIRED_NOTIFICACION', 'SERVIRED_SSL', 'SERVIRED_ERROR_PAGO', 'SERVIRED_IDIOMAS_ESTADO'));
		// Establecer propiedades seg�n los datos de configuraci�n
		$this->env = $config['SERVIRED_URLTPV'];
		switch($this->env){
			case 1:
//				$this->urltpv = "https://sis-t.sermepa.es:25443/sis/realizarPago";
				$this->urltpv = "https://sis-t.redsys.es:25443/sis/realizarPago";
				$this->clave = $config['SERVIRED_CLAVE_PRUEBAS'];
				break;
			case 2:
				$this->urltpv = "https://sis-i.sermepa.es:25443/sis/realizarPago";
				$this->clave = $config['SERVIRED_CLAVE_PRUEBAS'];
				break;
			default:
				$this->urltpv = "https://sis.sermepa.es/sis/realizarPago";
	}
			$this->clave = $config['SERVIRED_CLAVE'];
		if (isset($config['SERVIRED_NOMBRE']))
			$this->nombre = $config['SERVIRED_NOMBRE'];
		if (isset($config['SERVIRED_CODIGO']))
			$this->codigo = $config['SERVIRED_CODIGO'];
		if (isset($config['SERVIRED_TERMINAL']))
			$this->terminal = $config['SERVIRED_TERMINAL'];
		if (isset($config['SERVIRED_TIPOFIRMA']))
			$this->tipofirma = $config['SERVIRED_TIPOFIRMA'];
		if (isset($config['SERVIRED_RECARGO']))
			$this->recargo = $config['SERVIRED_RECARGO'];
		if (isset($config['SERVIRED_MONEDA']))
			$this->moneda = $config['SERVIRED_MONEDA'];
		if (isset($config['SERVIRED_TRANS']))
			$this->trans = $config['SERVIRED_TRANS'];
		if (isset($config['SERVIRED_NOTIFICACION']))
			$this->notificacion = $config['SERVIRED_NOTIFICACION'];
		if (isset($config['SERVIRED_SSL']))
			$this->ssl = $config['SERVIRED_SSL'];
		if (isset($config['SERVIRED_ERROR_PAGO']))
			$this->error_pago = $config['SERVIRED_ERROR_PAGO'];
		if (isset($config['SERVIRED_IDIOMAS_ESTADO']))
			$this->idiomas_estado = $config['SERVIRED_IDIOMAS_ESTADO'];


		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Servired');
		$this->description = $this->l('Aceptar pagos con tarjeta v&iacute;a Servired');

		// Mostrar aviso en la p�gina principal de m�dulos si faltan datos de configuraci�n.
		if (!isset($this->urltpv)
		OR !isset($this->clave)
		OR !isset($this->nombre)
		OR !isset($this->codigo)
		OR !isset($this->terminal)
		OR !isset($this->tipofirma)
		OR !isset($this->recargo)
		OR !isset($this->moneda)
		OR !isset($this->trans)
		OR !isset($this->notificacion)
		OR !isset($this->ssl)
		OR !isset($this->error_pago)
		OR !isset($this->idiomas_estado))


		$this->warning = $this->l('Faltan datos por configurar del m&oacute;dulo Servired.');
	}

	public function install()
	{
		// Valores por defecto al instalar el m�dulo
		if (!parent::install()
			OR !Configuration::updateValue('SERVIRED_URLTPV', '0')
			OR !Configuration::updateValue('SERVIRED_NOMBRE', $this->l('Escriba el nombre de su tienda'))
			OR !Configuration::updateValue('SERVIRED_TERMINAL', 1)
			OR !Configuration::updateValue('SERVIRED_TIPOFIRMA', 0)
			OR !Configuration::updateValue('SERVIRED_RECARGO', '00')
			OR !Configuration::updateValue('SERVIRED_MONEDA', '978')
			OR !Configuration::updateValue('SERVIRED_TRANS', 0)
			OR !Configuration::updateValue('SERVIRED_NOTIFICACION', 0)
			OR !Configuration::updateValue('SERVIRED_SSL', 'no')
			OR !Configuration::updateValue('SERVIRED_ERROR_PAGO', 'no')
			OR !Configuration::updateValue('SERVIRED_IDIOMAS_ESTADO', 'no')
			OR !$this->registerHook('payment')
			OR !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}

	public function uninstall()
	{
	   // Valores a quitar si desinstalamos el m�dulo
		if (!Configuration::deleteByName('SERVIRED_URLTPV')
			OR !Configuration::deleteByName('SERVIRED_CLAVE')
			OR !Configuration::deleteByName('SERVIRED_NOMBRE')
			OR !Configuration::deleteByName('SERVIRED_CODIGO')
			OR !Configuration::deleteByName('SERVIRED_TERMINAL')
			OR !Configuration::deleteByName('SERVIRED_TIPOFIRMA')
			OR !Configuration::deleteByName('SERVIRED_RECARGO')
			OR !Configuration::deleteByName('SERVIRED_MONEDA')
			OR !Configuration::deleteByName('SERVIRED_TRANS')
			OR !Configuration::deleteByName('SERVIRED_NOTIFICACION')
			OR !Configuration::deleteByName('SERVIRED_SSL')
			OR !Configuration::deleteByName('SERVIRED_ERROR_PAGO')
			OR !Configuration::deleteByName('SERVIRED_IDIOMAS_ESTADO')
			OR !parent::uninstall())
			return false;
		return true;
	}

	private function _postValidation(){
	    // Si al enviar los datos del formulario de configuraci�n hay campos vacios, mostrar errores.
		if (isset($_POST['btnSubmit'])){
			if (empty($_POST['clave']))
				$this->_postErrors[] = $this->l('Se requiere la Clave secreta de encriptaci&oacute;n.');
			if (empty($_POST['nombre']))
				$this->_postErrors[] = $this->l('Se requiere el Nombre del comercio.');
			if (empty($_POST['codigo']))
				$this->_postErrors[] = $this->l('Se requiere el N&uacute;mero de comercio (FUC).');
			if (empty($_POST['terminal']))
				$this->_postErrors[] = $this->l('Se requiere el N&uacute;mero de comercio (FUC).');
			if (empty($_POST['recargo']))
				$this->_postErrors[] = $this->l('Si no desea aplicar recargo, ponga 00.');
			if (empty($_POST['moneda']))
				$this->_postErrors[] = $this->l('Se requiere el Tipo de moneda.');

		}
	}

	private function _postProcess(){
	    // Actualizar la configuraci�n en la BBDD
			if (isset($_POST['btnSubmit'])){
			Configuration::updateValue('SERVIRED_URLTPV', $_POST['urltpv']);
			Configuration::updateValue('SERVIRED_CLAVE', $_POST['clave']);
			Configuration::updateValue('SERVIRED_NOMBRE', $_POST['nombre']);
			Configuration::updateValue('SERVIRED_CODIGO', $_POST['codigo']);
			Configuration::updateValue('SERVIRED_TERMINAL', $_POST['terminal']);
			Configuration::updateValue('SERVIRED_TIPOFIRMA', $_POST['tipofirma']);
			Configuration::updateValue('SERVIRED_RECARGO', $_POST['recargo']);
			Configuration::updateValue('SERVIRED_MONEDA', $_POST['moneda']);
			Configuration::updateValue('SERVIRED_TRANS', $_POST['trans']);
			Configuration::updateValue('SERVIRED_NOTIFICACION', $_POST['notificacion']);
			Configuration::updateValue('SERVIRED_SSL', $_POST['ssl']);
			Configuration::updateValue('SERVIRED_ERROR_PAGO', $_POST['error_pago']);
			Configuration::updateValue('SERVIRED_IDIOMAS_ESTADO', $_POST['idiomas_estado']);
		}

		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Configuraci&oacute;n actualizada').'</div>';
	}

	private function _displayservired()
	{
	    // Aparici�n el la lista de m�dulos
		$this->_html .= '<img src="../modules/servired/servired.png" style="float:left; margin-right:15px;"><b>'.$this->l('Este m&oacute;dulo te permite aceptar pagos con tarjeta.').'</b><br /><br />
		'.$this->l('Si el cliente elije este modo de pago, podr&aacute; pagar de forma autom&aacute;tica.').'<br /><br /><br />';
	}

	private function _displayForm(){

		// Opciones para el select de monedas.
		$moneda = Tools::getValue('moneda', $this->moneda);
		$iseuro =  ($moneda == '978') ? ' selected="selected" ' : '';
		$isdollar = ($moneda == '840') ? ' selected="selected" ' : '';

		// Opciones para activar/desactivar SSL
		$ssl = Tools::getValue('ssl', $this->ssl);
		$ssl_si = ($ssl == 'si') ? ' checked="checked" ' : '';
		$ssl_no = ($ssl == 'no') ? ' checked="checked" ' : '';

		// Opciones para el comportamiento en error en el pago
		$error_pago = Tools::getValue('error_pago', $this->error_pago);
		$error_pago_si = ($error_pago == 'si') ? ' checked="checked" ' : '';
		$error_pago_no = ($error_pago == 'no') ? ' checked="checked" ' : '';

		// Opciones para activar los idiomas
		$idiomas_estado = Tools::getValue('idiomas_estado', $this->idiomas_estado);
		$idiomas_estado_si = ($idiomas_estado == 'si') ? ' checked="checked" ' : '';
		$idiomas_estado_no = ($idiomas_estado == 'no') ? ' checked="checked" ' : '';

		// Opciones entorno
		if (!isset($_POST['urltpv']))
			$entorno = Tools::getValue('env', $this->env);
				else
					$entorno = $_POST['urltpv'];
		$entorno_real =  ($entorno==0) ? ' selected="selected" ' : '';
		$entorno_i =  ($entorno==2) ? ' selected="selected" ' : '';
		$entorno_t =  ($entorno==1) ? ' selected="selected" ' : '';

		// Opciones tipofirma
		$tipofirma = Tools::getValue('tipofirma', $this->tipofirma);
	  	$tipofirma_a =  ($tipofirma==0) ? ' checked="checked" ' : '';
	  	$tipofirma_c =  ($tipofirma==1) ? ' checked="checked" '  : '';

	    // Opciones notificacion
	    $notificacion = Tools::getValue('notificacion', $this->notificacion);
		$notificacion_s =  ($notificacion==1) ? ' checked="checked" '  : '';
		$notificacion_n =  ($notificacion==0) ? ' checked="checked" '  : '';

		// Mostar formulario
		$this->_html .=
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Configuraci&oacute;n del TPV').'</legend>
				<table border="0" width="680" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Por favor completa la informaci&oacute;n requerida que te proporcionar&aacute; tu banco Servired.').'.<br /><br /></td></tr>
					<tr><td width="215" style="height: 35px;">'.$this->l('Entorno de Servired').'</td><td><select name="urltpv"><option value="0"'.$entorno_real.'>'.$this->l('Real').'</option><option value="1"'.$entorno_t.'>'.$this->l('Pruebas en sis-t').'</option><option value="2"'.$entorno_i.'>'.$this->l('Pruebas en sis-i').'</option></select></td></tr>
					<tr><td width="215" style="height: 35px;">'.$this->l('Nombre del comercio').'</td><td><input type="text" name="nombre" value="'.htmlentities(Tools::getValue('nombre', $this->nombre), ENT_COMPAT, 'UTF-8').'" style="width: 200px;" /></td></tr>
					<tr><td width="215" style="height: 35px;">'.$this->l('N&uacute;mero de comercio (FUC)').'</td><td><input type="text" name="codigo" value="'.Tools::getValue('codigo', $this->codigo).'" style="width: 200px;" /></td></tr>
					<tr><td width="215" style="height: 35px;">'.$this->l('Clave secreta de encriptaci&oacute;n').'</td><td><input type="text" name="clave" value="'.Tools::getValue('clave', $this->clave).'" style="width: 200px;" /></td></tr>
					<tr><td width="215" style="height: 35px;">'.$this->l('N&uacute;mero de terminal').'</td><td><input type="text" name="terminal" value="'.Tools::getValue('terminal', $this->terminal).'" style="width: 80px;" /></td></tr>
					<tr><td width="215" style="height: 35px;">'.$this->l('Tipo de firma').'</td><td><input type="radio" name="tipofirma" id="tipofirma_c" value="1"'.$tipofirma_c.'/>'.$this->l('Completa').'<input type="radio" name="tipofirma" id="tipofirma_a" value="0"'.$tipofirma_a.'/>'.$this->l('Ampliada').'</td></tr>
					<tr><td width="215" style="height: 35px;">'.$this->l('Tipo de moneda').'</td><td><select name="moneda" style="width: 80px;"><option value=""></option><option value="978"'.$iseuro.'>EURO</option><option value="840"'.$isdollar.'>DOLLAR</option></select></td></tr>
					<tr><td width="215" style="height: 35px;">'.$this->l('Tipo de transacci&oacute;n').'</td><td><input type="text" name="trans" value="'.Tools::getValue('trans', $this->trans).'" style="width: 80px;" /></td></tr>
					<tr><td width="215" style="height: 35px;">'.$this->l('Recargo (% de recargo en el precio)').'</td><td><input type="text" name="recargo" value="'.Tools::getValue('recargo', $this->recargo).'" style="width: 80px;" /></td></tr>
		</td></tr>
				</table>
			</fieldset>
			<br>
			<fieldset>
			<legend><img src="../img/admin/cog.gif" />'.$this->l('Personalizaci&oacute;n').'</legend>
			<table border="0" width="680" cellpadding="0" cellspacing="0" id="form">
		<tr>
		<td colspan="2">'.$this->l('Por favor completa los datos adicionales.').'.<br /><br /></td>
		</tr>
		<tr>
		<td width="340" style="height: 35px;">'.$this->l('Notificaci&oacute;n HTTP (Inactivo no procesa pedido ni vacia el carrito)').'</td>
			<td>
			<input type="radio" name="notificacion" id="notificacion_1" value="1"'.$notificacion_s.'/>
			<img src="../img/admin/enabled.gif" alt="'.$this->l('Activado').'" title="'.$this->l('Activado').'" />
			<input type="radio" name="notificacion" id="notificacion_0" value="0"'.$notificacion_n.'/>
			<img src="../img/admin/disabled.gif" alt="'.$this->l('Desactivado').'" title="'.$this->l('Desactivado').'" />
			</td>
		</tr>
		<tr>
		<td width="340" style="height: 35px;">'.$this->l('SSL en URL de validaci&oacute;n').'</td>
			<td>
			<input type="radio" name="ssl" id="ssl_1" value="si" '.$ssl_si.'/>
			<img src="../img/admin/enabled.gif" alt="'.$this->l('Activado').'" title="'.$this->l('Activado').'" />
			<input type="radio" name="ssl" id="ssl_0" value="no" '.$ssl_no.'/>
			<img src="../img/admin/disabled.gif" alt="'.$this->l('Desactivado').'" title="'.$this->l('Desactivado').'" />
			</td>
		</tr>
		<tr>
		<td width="340" style="height: 35px;">'.$this->l('En caso de error, permitir elegir otro medio de pago').'</td>
			<td>
			<input type="radio" name="error_pago" id="error_pago_1" value="si" '.$error_pago_si.'/>
			<img src="../img/admin/enabled.gif" alt="'.$this->l('Activado').'" title="'.$this->l('Activado').'" />
			<input type="radio" name="error_pago" id="error_pago_0" value="no" '.$error_pago_no.'/>
			<img src="../img/admin/disabled.gif" alt="'.$this->l('Desactivado').'" title="'.$this->l('Desactivado').'" />
			</td>
		</tr>
		<tr>
		<td width="340" style="height: 35px;">'.$this->l('Activar los idiomas en el TPV').'</td>
			<td>
			<input type="radio" name="idiomas_estado" id="idiomas_estado_si" value="si" '.$idiomas_estado_si.'/>
			<img src="../img/admin/enabled.gif" alt="'.$this->l('Activado').'" title="'.$this->l('Activado').'" />
			<input type="radio" name="idiomas_estado" id="idiomas_estado_no" value="no" '.$idiomas_estado_no.'/>
			<img src="../img/admin/disabled.gif" alt="'.$this->l('Desactivado').'" title="'.$this->l('Desactivado').'" />
			</td>
		</tr>
		</table>
			</fieldset>
			<br>
		<input class="button" name="btnSubmit" value="'.$this->l('Guardar configuraci&oacute;n').'" type="submit" />
		</form>';
	}

	public function getContent()
	{
	    // Recoger datos
		$this->_html = '<h2>'.$this->displayName.'</h2>';
		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
		}
		else
			$this->_html .= '<br />';
		$this->_displayservired();
		$this->_displayForm();
		return $this->_html;
	}

	public function hookPayment($params)
	{
		// Variables necesarias de fuera
		global $smarty, $cookie, $cart;

		// Aplicar Recargo
		$porcientorecargo = Tools::getValue('recargo', $this->recargo);
		$porcientorecargo = str_replace (',','.',$porcientorecargo);
		$totalcompra = floatval($cart->getOrderTotal(true, 3));
		$fee = ($porcientorecargo / 100) * $totalcompra;


		// Valor de compra
		$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
		$currency = new Currency(intval($id_currency));
		$cantidad = number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3) + $fee, $currency), 2, '.', '');
		$cantidad = str_replace('.','',$cantidad);
		$cantidad = floatval($cantidad);

		// El n�mero de pedido es  los 8 ultimos digitos del ID del carrito + el tiempo MMSS.
		$numpedido = str_pad($params['cart']->id, 8, "0", STR_PAD_LEFT) . date(is);

		$codigo = Tools::getValue('codigo', $this->codigo);
		$moneda = Tools::getValue('moneda', $this->moneda);
		$trans = Tools::getValue('trans', $this->trans);

		$ssl = Tools::getValue('ssl', $this->ssl);
		if ($ssl=='no')
		$urltienda = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/servired/respuesta_tpv.php';
		elseif($ssl=='si')
		$urltienda = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/servired/respuesta_tpv.php';
		else
		$urltienda = 'ninguna';

		$clave = Tools::getValue('clave', $this->clave);

		// C�lculo del SHA1 $trans . $urltienda
		if(Tools::getValue('tipofirma', $this->tipofirma))
			$mensaje = $cantidad . $numpedido . $codigo . $moneda . $clave;
		else
			$mensaje = $cantidad . $numpedido . $codigo . $moneda . $trans . $urltienda . $clave;
		$firma = strtoupper(sha1($mensaje));

		$products = $params['cart']->getProducts();
		$productos = '';
		$id_cart = intval($params['cart']->id);

		//Activaci�n de los idiomas del TPV
		$idiomas_estado = Tools::getValue('idiomas_estado', $this->idiomas_estado);
		if ($idiomas_estado==si){
			$ps_language = new Language(intval($cookie->id_lang));
			$idioma_web = $ps_language->iso_code;
			switch ($idioma_web) {
				case 'es':
				$idioma_tpv='001';
				break;
				case 'en':
				$idioma_tpv='002';
				break;
				case 'ca':
				$idioma_tpv='003';
				break;
				case 'fr':
				$idioma_tpv='004';
				break;
				case 'de':
				$idioma_tpv='005';
				break;
				case 'nl':
				$idioma_tpv='006';
				break;
				case 'it':
				$idioma_tpv='007';
				break;
				case 'sv':
				$idioma_tpv='008';
				break;
				case 'pt':
				$idioma_tpv='009';
				break;
				case 'pl':
				$idioma_tpv='011';
				break;
				case 'gl':
				$idioma_tpv='012';
				break;
				case 'eu':
				$idioma_tpv='013';
				break;
				default:
				$idioma_tpv='002';
			}
		}
		else {
			$idioma_tpv = '0';
		}

		foreach ($products as $product) {
			$productos .= $product['quantity'].' '.$product['name']."<br>";
		}
		$customer = new Customer((int)($cart->id_customer));
		$smarty->assign(array(
			'urltpv' => Tools::getValue('urltpv', $this->urltpv),
			'cantidad' => $cantidad,
			'moneda' => $moneda,
			'pedido' => $numpedido,
			'codigo' => $codigo,
			'terminal' => Tools::getValue('terminal', $this->terminal),
			'trans' => $trans,
			'titular' => ($cookie->logged ? $cookie->customer_firstname.' '.$cookie->customer_lastname : false),
            'nombre' => Tools::getValue('nombre', $this->nombre),
			'urltienda' => $urltienda,
			'notificacion' => Tools::getValue('notificacion', $this->notificacion),
			'productos' => $productos,
			'UrlOk' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='. $id_cart .'&id_module='.(int)($this->id).'&id_order='.(int)($numpedido),
			'UrlKO' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/servired/pago_error.php',
			'firma' => $firma,
			'idioma_tpv' => $idioma_tpv,
			'this_path' => $this->_path,
			'fee' => number_format($fee, 2, '.', '')
		));
		return $this->display(__FILE__, 'servired.tpl');
    }
    public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return ;
		global $smarty;
		return $this->display(__FILE__, 'pago_correcto.tpl');
	}
}
?>