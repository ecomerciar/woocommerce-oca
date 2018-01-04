<?php

/*
Plugin Name: Woocommerce OCA
Plugin URI: http://ecomerciar.com
Description: Integración de oca para realizar envíos a través de la plataforma WooCommerce.
Version: 1.7.1
Author: Ecomerciar
Author URI: http://ecomerciar.com
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'oca-class.php';
require_once 'oca-shipping.php';
require_once 'oca-utilities.php';

// Creamos paginas necesarias al instalar el plugin				
function woo_oca_install(){
	$contenido = '<h2>Sucursales de OCA</h2>
	<form method="post">
	<input type="text" placeholder="Codigo Postal" name="cp"style="width:40%"><br>
	<br />
	<input name="submit_button" type="submit"  value="Buscar" id="update_button"  class="update_button"/>
	</form>
	[sucursales_oca]';
	if(! post_exists('OCA Sucursales', $contenido)){
		wp_insert_post( array(
			'post_title'     => 'OCA Sucursales',
			'post_name'      => 'oca-sucursales',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_content'   => $contenido,
			'comment_status' => 'closed',
			'ping_status'    => 'closed'
		) );
	}

	$contenido = '<h2>Número de envío</h2>
	<form method="post">
	<input type="text" name="id"style="width:40%"><br>
	<br />
	<input name="submit_button" type="submit"  value="Consultar"  id="update_button"  class="update_button"/>
	</form>
	[rastreo_oca]';
	if(! post_exists('OCA Rastreo', $contenido)){
		wp_insert_post( array(
			'post_title'     => 'OCA Rastreo',
			'post_name'      => 'oca_rastreo',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_content'   => $contenido,
			'comment_status' => 'closed',
			'ping_status'    => 'closed'
		) );
	}
}
register_activation_hook(__FILE__,'woo_oca_install');