# Módulo Servired por Prestashop

http://www.prestashop.com/forums/topic/110666-modulo-servired-08f-perfectamente-funcional-en-ps14/

## Notas para la versión de Servired 1.5.2

* Fixed problems with templates
* Now the module works with Synchronized payment

## Notas para la versión de Servired 1.5.2

* Code cleanup
* Added GPL licence file 
* Added version, changelog and authors files
* Optimized images
* Changed to Redsys test server

## Notas para la versión de Servired 1.5.1

* Modificación de imagenes para pago correcto y erroneo.
* Eliminación de archivos innecesarios (error.jpg y error_log)
* Eliminación del sistema de recargo de la parte administrativa y frontal.

## Notas para la versión de Servired 1.5.0 (28-5-2011)

### Adaptación del módulo a la versión 1.4 de Prestashop:

* El pago válido retorna a OrderConfirmation, de modo que sigue los cauces de los demós módulos de pago de Prestashop.
* De este modo ahora el módulo Google Analytics puede ofrecer estadásticas de estos pagos. Antes no se registraban dichas conversiones.
* Adaptada plantilla pago-correcto.tpl
* Corregido bug en plantilla pago-error.tpl
* Corregido fallos en instalación y desinstalación en versión 1.4
* Corregido fallo de secure_key en PS 1.4
* Corregida ruta de icono "personalización"
* pago_correcto.php deja de ser necesario
* Algunas modificaciones de gráficos

### Hibridación del módulo adaptado por David Vidal para aumentar la compatibilidad con las plataformas Sermepa:
* Se redimensionan imagenes que quedaban cortadas en los resultados de la plataforma.
* Se corrige error con pagos inferiores a 1 euros.
* Se añade selector para configurar el entorno.
* Se añade selector para configurar el tipo de firma.
* Se añade posibilidad para cobrar un recargo en tantos %.
* Se añade Notificación HTTP para entorno de pruebas.
* Se aumenta el número de versión para no confundirlo con las anteriores, ya que existe una versión 1.0 muy similar, pero con menos características.

Released under the GNU General Public License