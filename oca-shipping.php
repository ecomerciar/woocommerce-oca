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
		require_once plugin_dir_path( __FILE__ ) . 'oca/autoload.php';
		$oca = new Oca($datos['cuit'], $datos[$envio[3]]);
		if($datos['debug'] === 'yes'){
			$log = new WC_Logger();		
			$log->add( 'oca', '=== Ingresando el envío en el sistema de OCA ===');						
			$log->add( 'oca', $xml);						
		}
		$data = $oca->ingresoORMultiplesRetiros($datos['username'], $datos['password'], $xml, true);
		if(! isset($data[0]['error'])){
			$numeroenvio = $data[0]['NumeroEnvio'];
			$ordenretiro = $data[0]['OrdenRetiro'];
			$order->update_meta_data('numeroenvio_oca', $numeroenvio );
			$order->update_meta_data('ordenretiro_oca', $ordenretiro );
			$order->save();
			if($datos['debug'] === 'yes'){
				$log = new WC_Logger();							
				$log->add( 'oca', 'Envío Realizado con exito: ');
				$log->add( 'oca', 'Nro. Envio: '.$numeroenvio.' | Orden retiro: '.$ordenretiro);
			}
		}else{
			if($datos['debug'] === 'yes'){
				$log = new WC_Logger();							
				$log->add( 'oca', 'Error al realizar envío: '.$data[0]['error']);
			}
		}
	}
}

// =========================================================================
/**
 * Function crear_datos_oca
 *
 */
function woo_oca_crear_datos_oca($datos = array(), $order = '', $envio = ''){

	$countries_obj = new WC_Countries();
	$country_states_array = $countries_obj->get_states();
	if($order->get_shipping_first_name()){
		$provincia = $country_states_array['AR'][$order->get_shipping_state()];
		$datos['nombre_cliente'] = $order->get_shipping_first_name();
		$datos['apellido_cliente'] = $order->get_shipping_last_name();
		$datos['direccion_cliente'] = $order->get_shipping_address_1();
		$datos['ciudad_cliente'] = $order->get_shipping_city();
		$datos['cp_cliente'] = $order->get_shipping_postcode();
		$datos['observaciones_cliente'] = $order->get_shipping_address_2();
	}else{
		$provincia = $country_states_array['AR'][$order->get_billing_state()];
		$datos['nombre_cliente'] = $order->get_billing_first_name();
		$datos['apellido_cliente'] = $order->get_billing_last_name();
		$datos['direccion_cliente'] = $order->get_billing_address_1();
		$datos['ciudad_cliente'] = $order->get_billing_city();
		$datos['cp_cliente'] = $order->get_billing_postcode();
		$datos['observaciones_cliente'] = $order->get_billing_address_2();
	} 
	$datos['provincia_cliente'] = $provincia;	
	$datos['telefono_cliente'] = $order->get_billing_phone();
	$datos['celular_cliente'] = $order->get_billing_phone();
	$datos['email_cliente'] = $order->get_billing_email();
	$datos['sucursal_oca_destino'] = $order->get_meta('sucursal_oca_destino');
	if($datos['sucursal_oca_destino'] == ''){
		$datos['sucursal_oca_destino'] = 0;
	}
	$datos['valor_declarado'] = $order->get_meta('precio_envio_oca_'.$envio[1].'_'.$envio[3].'_contrareembolso');
	$datos['valor_declarado'] = str_replace(',', "", $datos['valor_declarado']);

	if(html_entity_decode($datos['provincia_cliente']) == 'Tucumán'){
		$datos['provincia_cliente'] = 'Tucumán';
	}

	// Se filtran los caracteres
	$datos = array_map(function($value){
		$value = str_replace('"', "", $value);
		$value = str_replace("'", "", $value);
		$value = str_replace(";", "", $value);
		$value = str_replace("&", "", $value);
		$value = str_replace("<", "", $value);
		$value = str_replace(">", "", $value);
		$value = str_replace("º", "", $value);
		$value = str_replace("ª", "", $value);
		return $value;
	}, $datos);

	if($envio[1] == 'pap' || $envio[1] == 'pas'){
		$centro_imposicion = '';
	}else{
		$centro_imposicion = 'idcentroimposicionorigen="'.$datos['idcentroimposicionorigen'].'" ';
	}

	$xml = '<?xml version="1.0" encoding="iso-8859-1" standalone="yes"?>
	<ROWS>   
		<cabecera ver="2.0" nrocuenta="'.$datos['nrocuenta'].'" />   
		<origenes>     
			<origen calle="'.$datos['calle'].'" nro="'.$datos['nro'].'" piso="'.$datos['piso'].'" depto="'.$datos['depto'].'" cp="'.$datos['cp'].'" localidad="'.$datos['localidad'].'" provincia="'.$datos['provincia'].'" contacto="'.$datos['nombre'].'" email="'.$datos['email'].'" solicitante="'.$datos['nombre_empresa'].'" observaciones="" centrocosto="1" idfranjahoraria="'.$datos['idfranjahoraria'].'" '.$centro_imposicion.'fecha="'.current_time('Ymd').'">       
				<envios>         
					<envio idoperativa="'.$datos[$envio[3]].'" nroremito="'.$order->get_order_number().'">';
					$xml .= '<destinatario apellido="'.$datos['apellido_cliente'].'" nombre="'.$datos['nombre_cliente'].'" calle="'.$datos['direccion_cliente'].'" nro="0" piso="" depto="" localidad="'.$datos['ciudad_cliente'].'" provincia="'.$datos['provincia_cliente'].'" cp="'.$datos['cp_cliente'].'" telefono="'.$datos['telefono_cliente'].'" email="'.$datos['email_cliente'].'" idci="'.$datos['sucursal_oca_destino'].'" celular="'.$datos['celular_cliente'].'" observaciones="'.$datos['observaciones_cliente'].'" />';
					$xml .= '<paquetes>';
					$items = $order->get_items();
					foreach ( $items as $item) {
						$product_name = $item['name'];
						$product_id = $item['product_id'];
						$product_variation_id = $item['variation_id'];
						$product =  wc_get_product( $product_id );
						$product_variado =  wc_get_product( $product_variation_id );
						//Se obtienen los datos del producto
						if($product->get_weight() !== ''){
							$peso = $product->get_weight();
							$xml .= '<paquete alto="'.wc_get_dimension( $product->get_height(), 'm').'" ancho="'.wc_get_dimension( $product->get_width(), 'm').'" largo="'.wc_get_dimension( $product->get_length(), 'm').'" peso="'.wc_get_weight( $peso , 'kg' ).'" valor="'.$item->get_total().'" cant="'.$item->get_quantity().'" />';           
						}else{
							$peso = $product_variado->get_weight();
							$xml .= '<paquete alto="'.wc_get_dimension( $product_variado->get_height(), 'm').'" ancho="'.wc_get_dimension( $product_variado->get_width(), 'm').'" largo="'.wc_get_dimension( $product_variado->get_length(), 'm').'" peso="'.wc_get_weight( $peso , 'kg' ).'" valor="'.$item->get_total().'" cant="'.$item->get_quantity().'" />';           
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