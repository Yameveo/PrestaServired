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

global $_MODULE;
$_MODULE = array();
$_MODULE['<{servired}prestashop>servired_d7d0015d35ad2c792f9e399e98bd40e1'] = 'Credit card payment. Safe connection via Servired.';
