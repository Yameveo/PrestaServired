<?php
/*-----------------------------------------------------------------------------
Autor: Javier Barredo
Autor E-Mail: naveto@gmail.com
Fecha: Mayo 2011
Version : 0.7v4
Agradecimientos: Yago Ferrer por su módulo de pago  que se utilizó como base de este módulo.
Alberto Fernández por su ayuda con los testeos y las imágenes.
Version: 1.50 (solo probada en PS1.4)
Adaptación a PS 1.4: David Vidal (chienandalu@gmail.com)
Hibridación del módulo con versiones anteriores: Francisco J. Matas (fjmatad@hotmail.com)

Notas para la versión de Servired 1.50 (28-5-2011)
--------------------------------

[-] Adaptación del módulo a la versión 1.4 de Prestashop:
  - El pago válido retorna a OrderConfirmation, de modo que sigue los cauces de los demás módulos de pago de Prestashop.
  - De este modo ahora el módulo Google Analytics puede ofrecer estadísticas de estos pagos. Antes no se registraban dichas conversiones.
  - Adaptada plantilla pago-correcto.tpl
  - Corregido bug en plantilla pago-error.tpl
  - Corregido fallos en instalación y desinstalación en versión 1.4
  - Corregido fallo de secure_key en PS 1.4
  - Corregida ruta de icono "personalización"
  - pago_correcto.php deja de ser necesario
  - Algunas modificaciones de gráficos
[*] Hibridación del módulo adaptado por David Vidal para aumentar la compatibilidad con las plataformas Sermepa.
  * Se redimensionan imagenes que quedaban cortadas en los resultados de la plataforma.
  * Se corrige error con pagos inferiores a 1 euros.
  * Se añade selector para configurar el entorno.
  * Se añade selector para configurar el tipo de firma.
  * Se añade posibilidad para cobrar un recargo en tantos %.
  * Se añade Notificación HTTP para entorno de pruebas.
  * Se aumenta el número de versión para no confundirlo con las anteriores, ya que existe una versión 1.0 muy similar, pero con menos características.

Released under the GNU General Public License
-----------------------------------------------------------------------------*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/servired.php');

if (!empty($_POST)){

	// Recoger datos de respuesta
	$total     = $_POST["Ds_Amount"];
	$pedido    = $_POST["Ds_Order"];
	$codigo    = $_POST["Ds_MerchantCode"];
	$moneda    = $_POST["Ds_Currency"];
	$respuesta = $_POST["Ds_Response"];
	$firma_remota = $_POST["Ds_Signature"];

	// Creamos objeto
	$servired = new servired();
	//Verificamos opciones
	$error_pago = Configuration::get('SERVIRED_ERROR_PAGO');
	// Contraseña Secreta
	$clave = Configuration::get('SERVIRED_CLAVE');

	// Cálculo del SHA1
	$mensaje = $total . $pedido . $codigo . $moneda . $respuesta . $clave;
	$firma_local = strtoupper(sha1($mensaje));

	if ($firma_local == $firma_remota){
		// Formatear variables
		// NINO - eliminar el punto de los miles para evitar error en pago
		// ORIGINAL - $total  = number_format($total / 100,4);
		$total  = number_format($total / 100,4,'.', '');
		$pedido = substr($pedido,0,8);
		$pedido = intval($pedido);
		$respuesta = intval($respuesta);
		$moneda_tienda = 1; // Euros
		if ($respuesta < 101){
			// Compra válida
			$mailvars=array();
			$cart = new Cart($pedido);
			$servired->validateOrder($pedido, _PS_OS_PAYMENT_, $total, $servired->displayName, NULL, $mailvars, NULL, false, $cart->secure_key);
		}
		else {
			// Compra no válida
			if ($error_pago=="no"){
				//se anota el pedido como no pagado
				$servired->validateOrder($pedido, _PS_OS_ERROR_, 0, $servired->displayName, 'errores:'.$respuesta);
				}
			elseif ($error_pago=="si"){
				//Se permite al cliente intentar otra vez el pago
			}
		}
	}
}
?>
