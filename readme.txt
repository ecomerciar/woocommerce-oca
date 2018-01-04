=== Woocommerce OCA Envios ===
Contributors: ecomerciar
Donate link: http://ecomerciar.com/
Tags: oca, shipping, woocommerce, argentina, envios, oca e-pak, spam
Requires at least: 4.6
Tested up to: 4.9
Requires PHP: 5.6
Stable tag: 1.7.1
Language: Spanish
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Suma envios a traves de OCA a tu pagina de WooCommerce a traves de la API OCA E-Pak

== Instalation ==

1. Subi los archivos del plugin a la carpeta `/wp-content/plugins/woo-oca`, o instala el plugin a traves de WordPress plugins directamente.
2. Active el plugin a traves de la pantalla de 'Plugins' en WordPress
3. Usa el plugin en la configuracion de Envios y Zonas de Woocommerce
3. Rellena tu informacion relevante

== Frequently Asked Questions ==

= Como ingreso mi CUIT? =

El CUIT se ingresa en formato XX-XXXXXXXX-X

= Como encuentro mi sucursal de origen? =

En la configuracion del plugin hay un link donde podes hacer click y buscar a traves de tu codigo postal tu sucursal de origen mas cercana.

== Upgrade Notice ==

= 1.0 =
Primera salida.

= 1.1 =
Reiniciar Cache de Envios

= 1.2 =
Reiniciar Cache de Envios

== Screenshots == 

1. Configuracion para Argentina
2. Configuracion de OCA
3. Configuracion de OCA 2
4. Configuracion de OCA 3
5. Sucursal de Origen OCA
6. Lista de Sucursales de OCA

== Changelog ==

= 1.7.1 =
* Agregados campos extra de contacto en la configuración del plugin

= 1.7 =
* Corregido bug en envíos Puerta a Puerta y Puerta a Sucursal
* Agregadas lineas al debug log

= 1.6.3 =
* Arreglado bug en el precio y cantidad a la hora de generar un envío

= 1.6.2 =
* Agregados filtros adicionales para remover caracteres especiales antes de ingresar un envio

= 1.6.1 =
* Expansión de medida minima permitida para los envios

= 1.6 =
* Corregido bug que permitia finalizar compra sin seleccionar una sucursal
* Optimización de código en el SDK
* Corregido bug al ver las etiquetas
* Corregido bug cuando OCA devolvía un error al generar un envío

= 1.5 =
* Corregido bug en el form checkout
* Corregido bug para las operativas con contrareembolso
* Ahora el método de envio desaparece cuando las dimensiones de algún producto son muy pequeñas

= 1.4 =
* Agregada pagina de tracking

= 1.3 =
* Ya no es necesario reiniciar la cache de envio
* Arreglado bug importante sobre el funcionamiento del plugin

= 1.2 =
* Codigo optimizado
* Agregada sanitizacion de informacion del usuario antes de realizar un envio

= 1.1 =
* Agregada verificacion de dimensiones y pesos cero
* Agregada descripcion sobre el CUIT en la configuracion del plugin
* Agregada la opcion para enviar con contrareembolso

= 1.0 =
* Primera subida

Si necesitas ayuda podes contactarnos a traves de nuestra web de [Ecomerciar](http://ecomerciar.com/ "Ecomerciar").