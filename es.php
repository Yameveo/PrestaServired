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

global $_MODULE;
$_MODULE = array();
$_MODULE['<{servired}prestashop>pago_correcto_68d6335b8e1869ca7e53900e473b0764'] = '¡El pago se ha realizado con éxito!';
$_MODULE['<{servired}prestashop>pago_correcto_73bc3b5a9a2873d81d33899f1fd68439'] = '¡Genial!';
$_MODULE['<{servired}prestashop>pago_correcto_6d267c996648a8c8ff4f20557de7f6df'] = 'Pedidos';
$_MODULE['<{servired}prestashop>pago_correcto_7442e29d7d53e549b78d93c46b8cdcfc'] = 'Pedidos';
$_MODULE['<{servired}prestashop>pago_correcto_a439710aa70bff88b6707b19bf1e83df'] = 'Pinche aquí para comprobar los detalles de su compra';
$_MODULE['<{servired}prestashop>pago_correcto_d01bb9245def713aab752fd0a7034325'] = 'Recuerde que puede contactar con nosotros en cualquier momento si tienes cualquier duda relacionada con su pedido.';
$_MODULE['<{servired}prestashop>pago_error_46e8a5ded15f175d1b06a5b27c54c5a1'] = 'Pago ERROR';
$_MODULE['<{servired}prestashop>pago_error_e5fc76e68b289d2ab27e0ae75840de49'] = 'El pago con su tarjeta no se ha podido completar';
$_MODULE['<{servired}prestashop>pago_error_4d4e3e4f7aad906b7245200cd269780d'] = 'Lo sentimos, pero su pago no se ha podido llevar a cabo. Puede intentarlo de nuevo o escoger otro método de pago. Recuerde que solo puede pagar mediante tarjetas de crédito VISA o MAESTRO o MASTERCARD; MAESTRO (solo España).';
$_MODULE['<{servired}prestashop>pago_error_601622ecac7eefc30244ca0ae3fac060'] = 'Este error puede haber sucedido por varias razones:';
$_MODULE['<{servired}prestashop>pago_error_b433bd7bc2afa451fc90e0ef8b7877e3'] = 'Se ha confundido al introducir alguno de los números de su tarjeta. En ese caso, inténtelo de nuevo.';
$_MODULE['<{servired}prestashop>pago_error_414f05e01df34a473053ac35b3a8e9c4'] = 'Asegúrese de que su tarjeta no ha caducado y es válida (VISA, MASTERCARD y MAESTRO españolas).';
$_MODULE['<{servired}prestashop>pago_error_ce2a95e684b7fc9215855c623f5b38c4'] = 'También es posible que haya habido algún problema con el proveedor de la pasarela de pago (SERVIRED).';
$_MODULE['<{servired}prestashop>pago_error_a4440290220ddba402fdcba3bf80cc2f'] = 'En cualquier caso, no dude en contactar por teléfono o correo con nosotros. Intentaremos guiarte en la resolución del problema.';
$_MODULE['<{servired}prestashop>pago_error_53839f29217a1785262546e949aacf6a'] = 'Pagos';
$_MODULE['<{servired}prestashop>pago_error_f915a95e609bbd517a8a1e7bdcceef37'] = 'Intentar de nuevo';
$_MODULE['<{servired}prestashop>servired_622a52fd009a91e6bf290bae7f40a4d0'] = 'Plataforma de Pago Servired';
$_MODULE['<{servired}prestashop>servired_8c62895d1775f18824313228a22fecd5'] = 'Aceptar pagos con tarjeta vía Servired';
$_MODULE['<{servired}prestashop>servired_e833f2fcda497c04a6c576f88d6566e0'] = 'Faltan datos por configurar del módulo Servired.';
$_MODULE['<{servired}prestashop>servired_2bf321137f479e3e6ccefd9b728ecfe1'] = 'Escriba el nombre de su tienda';
$_MODULE['<{servired}prestashop>servired_ce9980445c1cda814d9395c95232ad03'] = 'Escriba la Clave secreta de encriptación.';
$_MODULE['<{servired}prestashop>servired_8988d19f4ebb466c170445e53adddf8f'] = 'Escriba el Nombre del Comercio.';
$_MODULE['<{servired}prestashop>servired_4032b2f71b52f9298f57a347765a3135'] = 'Escriba el Número de Comercio (FUC).';
$_MODULE['<{servired}prestashop>servired_3d355e91165c3dcef83f3e427bed73d0'] = 'Si no desea aplicar recargo, escriba 00.';
$_MODULE['<{servired}prestashop>servired_a6d2f99c01c6e824b7b976aa6cf59c49'] = 'Defina el tipo de Moneda.';
$_MODULE['<{servired}prestashop>servired_444bcb3a3fcf8389296c49467f27e1d6'] = 'OK';
$_MODULE['<{servired}prestashop>servired_9c52fba1c41e2698f1fabd77a4d688e6'] = 'Configuración actualizada';
$_MODULE['<{servired}prestashop>servired_3598ea8382763f88c8d254fc450aa470'] = 'Este módulo le permite aceptar pagos con tarjeta.';
$_MODULE['<{servired}prestashop>servired_ac8f8b8e89a84b33c49e744010bd681a'] = 'Si el cliente elije este modo de pago, podrá pagar de forma automática.';
$_MODULE['<{servired}prestashop>servired_e2971baa04c3d91d8abf3b1981898e15'] = 'Configuración del TPV';
$_MODULE['<{servired}prestashop>servired_5211a184125c31be8533aeb13b7272e6'] = 'Complete la información requerida que con la que le proporcionará su banco Servired.';
$_MODULE['<{servired}prestashop>servired_8a3321e3c1f53d0248f70e3cafbe77bb'] = 'Entorno de Servired';
$_MODULE['<{servired}prestashop>servired_7f80fcc452c2f1ed2bb51b39d0864df1'] = 'Real';
$_MODULE['<{servired}prestashop>servired_4289c911d22931c42f7afaf585e0bb03'] = 'Pruebas en sis-t';
$_MODULE['<{servired}prestashop>servired_bf518baad865923b98c69d973547f50a'] = 'Pruebas en sis-i';
$_MODULE['<{servired}prestashop>servired_b736c8d938e78fd92849473c71c7fdae'] = 'Nombre del comercio';
$_MODULE['<{servired}prestashop>servired_1c41e1a11be1ff5ca55fa3ee66f37d61'] = 'Número de comercio (FUC)';
$_MODULE['<{servired}prestashop>servired_a5a3f0272f9199e9a48dca53be9bbbe2'] = 'Clave secreta de encriptación';
$_MODULE['<{servired}prestashop>servired_895dd275d059aaa70a3e10c4341079e7'] = 'Número de terminal';
$_MODULE['<{servired}prestashop>servired_409c83100a634cdacdc23e3e2f868bfc'] = 'Tipo de firma';
$_MODULE['<{servired}prestashop>servired_f9dc2d29f6e3a84b1959cc2565806d1e'] = 'Completa';
$_MODULE['<{servired}prestashop>servired_51b4daa83970d8f58724c460f29f37ae'] = 'Ampliada';
$_MODULE['<{servired}prestashop>servired_a027f648d6511fd1d56eb9077ebdb563'] = 'Tipo de moneda';
$_MODULE['<{servired}prestashop>servired_442c1cef232a1744ca11669f70142d87'] = 'Tipo de transacción';
$_MODULE['<{servired}prestashop>servired_27be675704e8deeb9db7e6c05aa2300f'] = 'Recargo (% de recargo en el precio)';
$_MODULE['<{servired}prestashop>servired_324a5cd77d051e3b6440a23e0d817bf0'] = 'Personalización';
$_MODULE['<{servired}prestashop>servired_06d1c9c9ded2ce38bb1af5e2cb9e779b'] = 'Por favor complete los datos adicionales.';
$_MODULE['<{servired}prestashop>servired_92deae108ca264f8330dad8d9c9f08a2'] = 'Notificación HTTP (Inactivo no procesa pedido ni vacia el carrito)';
$_MODULE['<{servired}prestashop>servired_9f06f65f2960c8989eac8417765c45c6'] = 'Activado';
$_MODULE['<{servired}prestashop>servired_ff378e36c0cefc532464ee669023a91b'] = 'Desactivado';
$_MODULE['<{servired}prestashop>servired_add3813072d8e0250cb427e598748610'] = 'Usar SSL en validación';
$_MODULE['<{servired}prestashop>servired_3795c0a746056989b33e39f04a19dbea'] = 'En caso de error, permitir elegir otro medio de pago';
$_MODULE['<{servired}prestashop>servired_a4ff7bc59e3851a1f633278cfe0c9423'] = 'Activar los idiomas del TPV';
$_MODULE['<{servired}prestashop>servired_0f70d2f49b9e8a6d1dad06041ee07892'] = 'Guardar configuración';
$_MODULE['<{servired}prestashop>servired_a637aa46b1238dbb6a0432f257300310'] = 'Pago con tarjeta de crédito vía Servired.';
$_MODULE['<{servired}prestashop>servired_d7d0015d35ad2c792f9e399e98bd40e1'] = 'Pague con su tarjeta de crédito VISA, MAESTRO o MASTERCARD. Pasará a una pasarela de pago Servired. En ningún momento tendremos acceso a los datos de su tarjeta. No obstante, comuniquese con nosotros si tiene alguna duda o surge algún problema.';
$_MODULE['<{servired}prestashop>servired_81681c87ac76c67311ee40e1f129d88f'] = 'Este sistema de pago lleva asociado un recargo de';
$_MODULE['<{servired}prestashop>servired_3262ec41fee934bef307924b43651f77'] = 'El recargo se sumará a los gastos de envío';
