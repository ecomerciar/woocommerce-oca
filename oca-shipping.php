<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


// =========================================================================
/**
 * Function generar_envio_oca
 *
 */
add_action( 'woocommerce_order_status_completed', 'woo_oca_generar_envio_oca');
function woo_oca_generar_envio_oca( $order_id ){
	$order = wc_get_order( $order_id );	
	$envio_seleccionado = reset( $order->get_items( 'shipping' ) )->get_method_id();
	$envio = explode(" ", $envio_seleccionado);
	if($envio[0] === 'oca'){
		$datos = get_option($envio[2]);	
		$xml = woo_oca_crear_datos_oca($datos, $order, $envio);
		require_once trailingslashit( ABSPATH ) . 'wp-content/plugins/woocommerce-oca/oca/autoload.php';
		$oca = new Oca($datos['cuit'], $datos[$envio[3]]);
		if($datos['debug'] === 'yes'){
			$log = new WC_Logger();		
			$log->add( 'oca', $xml);						
		}
		$data = $oca->ingresoORMultiplesRetiros($datos['username'], $datos['password'], $xml, true);
		$numeroenvio = $data['detalleIngresos'][0]['NumeroEnvio'];
		$ordenretiro = $data['detalleIngresos'][0]['OrdenRetiro'];
		$order->update_meta_data('numeroenvio_oca', $numeroenvio );
		$order->update_meta_data('ordenretiro_oca', $ordenretiro );
		$order->save();
		if($datos['debug'] === 'yes'){
			$log = new WC_Logger();							
			$log->add( 'oca', print_r($tracking,true));
		}
	}
}



// =========================================================================
/**
 * Function crear_datos_oca
 *
 */
function woo_oca_crear_datos_oca($datos = array(), $order = '', $envio = ''){
	$xml = '<?xml version="1.0" encoding="iso-8859-1" standalone="yes"?>
	<ROWS>   
		<cabecera ver="2.0" nrocuenta="'.$datos['nrocuenta'].'" />   
		<origenes>     
			<origen calle="'.$datos['calle'].'" nro="'.$datos['nro'].'" piso="'.$datos['piso'].'" depto="'.$datos['depto'].'" cp="'.$datos['cp'].'" localidad="'.$datos['localidad'].'" provincia="'.$datos['provincia'].'" contacto="" email="'.$datos['email'].'" solicitante="" observaciones="" centrocosto="1" idfranjahoraria="'.$datos['idfranjahoraria'].'" idcentroimposicionorigen="'.$datos['idcentroimposicionorigen'].'" fecha="'.current_time('Ymd').'">       
				<envios>         
					<envio idoperativa="'.$datos[$envio[3]].'" nroremito="'.$order->get_order_number().'">';
					$countries_obj = new WC_Countries();
					$country_states_array = $countries_obj->get_states();
					if($order->get_shipping_first_name()){
						$provincia = $country_states_array['AR'][$order->get_shipping_state()];
						$xml .= '<destinatario apellido="'.$order->get_shipping_last_name().'" nombre="'.$order->get_shipping_first_name().'" calle="'.$order->get_shipping_address_1().'" nro="0" piso="" depto="" localidad="'.$order->get_shipping_city().'" provincia="'.$provincia.'" cp="'.$order->get_shipping_postcode().'" telefono="'.$order->get_billing_phone().'" email="'.$order->get_billing_email().'" idci="'.$order->get_meta('sucursal_oca_destino').'" celular="'.$order->get_billing_phone().'" observaciones="'.$order->get_shipping_address_2().'" />';
					}else{
						$provincia = $country_states_array['AR'][$order->get_billing_state()];
						$xml .= '<destinatario apellido="'.$order->get_billing_last_name().'" nombre="'.$order->get_billing_first_name().'" calle="'.$order->get_billing_address_1().'" nro="0" piso="" depto="" localidad="'.$order->get_billing_city().'" provincia="'.$provincia.'" cp="'.$order->get_billing_postcode().'" telefono="'.$order->get_billing_phone().'" email="'.$order->get_billing_email().'" idci="'.$order->get_meta('sucursal_oca_destino').'" celular="'.$order->get_billing_phone().'" observaciones="'.$order->get_billing_address_2().'" />';						
					}           
					$xml .= '<paquetes>';
					$items = $order->get_items();
					foreach ( $items as $item ) {
						$product_name = $item['name'];
						$product_id = $item['product_id'];
						$product_variation_id = $item['variation_id'];
						$product =  wc_get_product( $product_id );
						$product_variado =  wc_get_product( $product_variation_id );
						//Se obtienen los datos del producto
						if($product->get_weight() !== ''){
							$peso = $product->get_weight();
							$xml .= '<paquete alto="'.wc_get_dimension( $product->get_height(), 'm').'" ancho="'.wc_get_dimension( $product->get_width(), 'm').'" largo="'.wc_get_dimension( $product->get_length(), 'm').'" peso="'.wc_get_weight( $peso , 'kg' ).'" valor="0" cant="1" />';           
						}else{
							$peso = $product_variado->get_weight();
							$xml .= '<paquete alto="'.wc_get_dimension( $product_variado->get_height(), 'm').'" ancho="'.wc_get_dimension( $product_variado->get_width(), 'm').'" largo="'.wc_get_dimension( $product_variado->get_length(), 'm').'" peso="'.wc_get_weight( $peso , 'kg' ).'" valor="0" cant="1" />';           
						}
					}
					$xml .= '</paquetes>         
					</envio>       
				</envios>     
			</origen>   
		</origenes> 
	</ROWS> ';
	return $xml;
}



// =========================================================================
/**
 * Function agregar_envio_oca
 *
 */
//Agrega envios OCA como metodo de envio internamente
add_filter( 'woocommerce_shipping_methods', 'woo_oca_agregar_envio_oca' );
function woo_oca_agregar_envio_oca( $methods ) {
	$methods['oca'] = 'WC_OCA';
	return $methods;
}