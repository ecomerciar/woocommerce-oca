<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


// =========================================================================
/**
 * Shortcode [rastreo_oca]
 *
 */
add_shortcode ( 'rastreo_oca' , 'woo_oca_rastreo_oca_func' );
function woo_oca_rastreo_oca_func( $atts, $content= NULL) {
	if($_POST['id']){
		$oca_id = filter_var ($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
		$_query_string = array(	'Pieza'					=> $oca_id,
		'NroDocumentoCliente'	=> '',
		'Cuit'					=> '',
			);
		$ch = curl_init();
		$curl_opt_arr[CURLOPT_POST] 		= true;
		$curl_opt_arr[CURLOPT_POSTFIELDS] 	= http_build_query($_query_string);
		$curl_opt_arr[CURLOPT_RETURNTRANSFER] 	= true;
		$curl_opt_arr[CURLOPT_HEADER] 	= false;
		$curl_opt_arr[CURLOPT_URL] 			= "webservice.oca.com.ar/epak_tracking/Oep_TrackEPak.asmx/Tracking_Pieza";
		curl_setopt_array($ch, $curl_opt_arr);
		$res = curl_exec($ch);
		$dom = new DOMDocument();
		@$dom->loadXML($res);
		$xpath = new DOMXpath($dom);	
		$envio = array();
		foreach (@$xpath->query("//NewDataSet/Table") as $tp)
		{

			$envio[] = array("NumeroEnvio"=>$tp->getElementsByTagName('NumeroEnvio')->item(0)->nodeValue,
				"Descripcion_Motivo"=>$tp->getElementsByTagName('Descripcion_Motivo')->item(0)->nodeValue,
				"Desdcripcion_Estado"=>$tp->getElementsByTagName('Desdcripcion_Estado')->item(0)->nodeValue,
				"SUC"=>$tp->getElementsByTagName('SUC')->item(0)->nodeValue,
				"fecha"=>$tp->getElementsByTagName('fecha')->item(0)->nodeValue,
			);
		}

		ob_start();
		if(isset($envio[0]['SUC'])){
			echo '<h3>Envío Nro: '.$envio[0]['NumeroEnvio'].'</h3>';
			echo "<table>";
			echo "<tr>";
				echo "<td>Estado</td>";
				echo "<td>".$envio[0]['Desdcripcion_Estado']."</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>Sucursal</td>";
				echo "<td>".$envio[0]['SUC']."</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>Fecha</td>";
				echo "<td>".$envio[0]['fecha']."</td>";
			echo "</tr>";
			echo "</table>";
		}else{
			wc_print_notice( __( 'Hubo un error, por favor intenta nuevamente', 'woocommerce' ), 'error' );
		}
		return ob_get_clean();
		
	}
}




// =========================================================================
/**
 * Shortcode [sucursales_oca]
 *
 */
add_shortcode ( 'sucursales_oca' , 'woo_oca_sucursales_oca_func' );
function woo_oca_sucursales_oca_func( $atts, $content= NULL) {
	if($_POST['cp']){
		$post_data = [ "CodigoPostal" => intval($_POST['cp']) ];
		$url = 'http://webservice.oca.com.ar/epak_tracking/Oep_TrackEPak.asmx/GetCentrosImposicionConServiciosByCP';
		$response = wp_remote_get( $url, array(
				'method' => 'GET',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $post_data,
				'cookies' => array()
			)
		);
		$response = $response['http_response']->get_response_object()->body;
		$dom = new DOMDocument();
		@$dom->loadXML($response);
		$xpath = new DOMXpath($dom);
	
		$c_imp = array();
		foreach (@$xpath->query("//CentrosDeImposicion/Centro") as $ci)
		{
			$c_imp[] = array(	'idCentroImposicion'	=> $ci->getElementsByTagName('IdCentroImposicion')->item(0)->nodeValue,
								'Sucursal'				=> $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
								'Sigla'					=> $ci->getElementsByTagName('Sigla')->item(0)->nodeValue,
								'Sucursal'				=> $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
								'Calle'					=> $ci->getElementsByTagName('Calle')->item(0)->nodeValue,
								'Numero'				=> $ci->getElementsByTagName('Numero')->item(0)->nodeValue,
								'Localidad'				=> $ci->getElementsByTagName('Localidad')->item(0)->nodeValue,
								'Provincia'				=> $ci->getElementsByTagName('Provincia')->item(0)->nodeValue,
								'Telefono'				=> $ci->getElementsByTagName('Telefono')->item(0)->nodeValue,
								'CodigoPostal'			=> $ci->getElementsByTagName('CodigoPostal')->item(0)->nodeValue
							);
		}
		ob_start();
		echo '<table>';
		echo '<tr>
			<th>ID</th>
			<th>Nombre</th>
			<th>Calle</th>
			<th>Número</th>
			<th>Localidad</th>
			<th>Teléfono</th>
			<th>CP</th>
		  </tr>';
		foreach($c_imp as $centro){
			echo '<tr>
					<td>'.$centro['idCentroImposicion'].'</td>
					<td>'.$centro['Sucursal'].'</td>
					<td>'.$centro['Calle'].'</td>
					<td>'.$centro['Numero'].'</td>
					<td>'.$centro['Localidad'].'</td>
					<td>'.$centro['Telefono'].'</td>
					<td>'.$centro['CodigoPostal'].'</td>
	  			</tr>';
		}					
		echo '</table>';
		return ob_get_clean();
	}
}


// =========================================================================
/**
 * Function oca_leer_sucursales
 *
 */
add_action( 'wp_ajax_oca_leer_sucursales', 'woo_oca_leer_sucursales' );
add_action( 'wp_ajax_nopriv_oca_leer_sucursales', 'woo_oca_leer_sucursales' );
function woo_oca_leer_sucursales(){
	
	$session = WC()->session;
	if ( ! isset( $session ) ) {
		wp_die();
	}

	$temp = $_REQUEST['data'];

	$cp = explode("|",$temp)[1];

	$session->set('cp_sucursal_oca', $cp);
	$session->set('id_destino_sucursal_oca', $temp);
}


// =========================================================================
/**
 * Function enqueue_oca_scripts
 *
 */
add_action( 'wp_enqueue_scripts', 'woo_oca_enqueue_oca_scripts' );
function woo_oca_enqueue_oca_scripts() {
if ( function_exists( 'is_woocommerce' ) ) {
	if ( ! is_checkout() ) {
		wp_dequeue_script( 'oca-script' );
	} else {
		wp_enqueue_script( 'oca-script', plugin_dir_url( __FILE__ ) . '/js/oca.js', array('jquery') );
		wp_localize_script( 'oca-script', 'objeto_url_ajax_lf',
				array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
}
}


// =========================================================================
/**
 * Function check_if_oca_selected
 *
 */
add_action( 'woocommerce_review_order_before_submit', 'woo_oca_check_if_oca_selected' );
function woo_oca_check_if_oca_selected( $chosen_method ) {
	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
	$chosen_shipping = $chosen_methods[0]; 
	$chosen_shipping = explode(" ",$chosen_shipping);
	$session = WC()->session;
	?><script>
		jQuery("#sucursal_oca_destino_field").hide();
		function cambiar_suc(val){
			if(val !== '-1'){
					var id = val.split("|")[0];
					var cp = val.split("|")[1];
					jQuery("#sucursal_oca_destino").val(id);
					jQuery.post(

					objeto_url_ajax_lf.ajax_url, 
					{
						'action': 'oca_leer_sucursales',
						'data': val
					}, 
					function(response){
						console.log(response);
					}
					).done(function(responseString) {
						var response = JSON.parse(responseString);
						if(response.error){
							console.log(response.msg);
						}else{
							console.log(response.error);
							jQuery(document.body).trigger("update_checkout");
					}
				});
			}else{
				jQuery("#sucursal_oca_destino").val(val);			
			}
		}
	</script>
<?php
	if ($chosen_shipping[0] === 'oca' && ($chosen_shipping[1] === 'pas' || $chosen_shipping[1] === 'sas')) {
		echo "<script>
					if(jQuery('#sucursal_oca_destino').val() === '-1'){
						jQuery('#sucursal_oca_destino').val('');
					}
		</script>";
		echo "<h4>Selecciona la sucursal de OCA donde quieres recibir tu compra</h4>";
		$centros = woo_oca_obtener_centros_oca(WC()->customer->get_shipping_postcode(), $chosen_shipping[5] );
		echo '<select id="SucursalesOcaDestino" style="margin-bottom:15px" onchange="cambiar_suc(this.value)" >';
		echo '<option value="">Seleccionar</option>';
		foreach($centros as $centro){
			echo '<option value="'.$centro['idCentroImposicion']."|".$centro['CodigoPostal'].'">'.$centro['Sucursal'].' / '.$centro['Calle'].' '.$centro['Numero'].'</option>';
		}
		echo '</select>';
		if(WC()->session->get('id_destino_sucursal_oca') !== ''){
			echo "<script>jQuery('#SucursalesOcaDestino').val(\"".WC()->session->get('id_destino_sucursal_oca')."\")</script>";
		}
	}else{
		echo "<script>cambiar_suc('-1')</script>";
	}
}

// =========================================================================
/**
 * Function obtener_centros_oca
 *
 */
function woo_oca_obtener_centros_oca($cp, $contrareembolso){
	$post_data = [ "CodigoPostal" => intval($cp) ];
	$url = 'http://webservice.oca.com.ar/epak_tracking/Oep_TrackEPak.asmx/GetCentrosImposicionConServiciosByCP';
	$response = wp_remote_get( $url, array(
			'method' => 'GET',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $post_data,
			'cookies' => array()
		)
	);
	$response = $response['http_response']->get_response_object()->body;
	$dom = new DOMDocument();
	@$dom->loadXML($response);
	$xpath = new DOMXpath($dom);

	$c_imp = array();
	if($contrareembolso === 'yes'){
		foreach (@$xpath->query("//CentrosDeImposicion/Centro") as $ci)
		{
			if( $ci->getElementsByTagName('TipoAgencia')->item(0)->nodeValue === 'Sucursal OCA'){
				$c_imp[] = array(	'idCentroImposicion'	=> $ci->getElementsByTagName('IdCentroImposicion')->item(0)->nodeValue,
									'Sucursal'				=> $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
									'Sigla'					=> $ci->getElementsByTagName('Sigla')->item(0)->nodeValue,
									'Sucursal'				=> $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
									'Calle'					=> $ci->getElementsByTagName('Calle')->item(0)->nodeValue,
									'Numero'				=> $ci->getElementsByTagName('Numero')->item(0)->nodeValue,
									'Localidad'				=> $ci->getElementsByTagName('Localidad')->item(0)->nodeValue,
									'Provincia'				=> $ci->getElementsByTagName('Provincia')->item(0)->nodeValue,
									'Telefono'				=> $ci->getElementsByTagName('Telefono')->item(0)->nodeValue,
									'CodigoPostal'			=> $ci->getElementsByTagName('CodigoPostal')->item(0)->nodeValue
								);
			}
		}
	}else{
		foreach (@$xpath->query("//CentrosDeImposicion/Centro") as $ci)
		{
			$c_imp[] = array(	'idCentroImposicion'	=> $ci->getElementsByTagName('IdCentroImposicion')->item(0)->nodeValue,
								'Sucursal'				=> $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
								'Sigla'					=> $ci->getElementsByTagName('Sigla')->item(0)->nodeValue,
								'Sucursal'				=> $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
								'Calle'					=> $ci->getElementsByTagName('Calle')->item(0)->nodeValue,
								'Numero'				=> $ci->getElementsByTagName('Numero')->item(0)->nodeValue,
								'Localidad'				=> $ci->getElementsByTagName('Localidad')->item(0)->nodeValue,
								'Provincia'				=> $ci->getElementsByTagName('Provincia')->item(0)->nodeValue,
								'Telefono'				=> $ci->getElementsByTagName('Telefono')->item(0)->nodeValue,
								'CodigoPostal'			=> $ci->getElementsByTagName('CodigoPostal')->item(0)->nodeValue
							);
		}
	}
	return $c_imp;
	
}

add_action( 'woocommerce_after_checkout_billing_form', 'woo_oca_checkout_field' );	
function woo_oca_checkout_field( $checkout ) {
	$id = WC()->session->get('id_destino_sucursal_oca');
	if($id !== ''){
		woocommerce_form_field( 'sucursal_oca_destino', array(
			'type'          => 'text',
			'class'         => array('form-row-first'),
			'label'         => __('Sucursal OCA'),
			'default' => explode("|",$id)[0],
			'required'      => true,			
			), $checkout->get_value( 'sucursal_oca_destino' ));
	}else{
		woocommerce_form_field( 'sucursal_oca_destino', array(
			'type'          => 'text',
			'class'         => array('form-row-first'),
			'label'         => __('Sucursal OCA'),
			'required'      => true,			
			), $checkout->get_value( 'sucursal_oca_destino' ));
	}
	
}

add_action( 'woocommerce_checkout_process', 'woo_oca_checkout_field_process');
function woo_oca_checkout_field_process() {
    // Check if set, if its not set add an error.
    if ( ! $_POST['sucursal_oca_destino'] )
        wc_add_notice( __( 'Por favor elige una sucursal de OCA' ), 'error' );
}

add_action( 'woocommerce_checkout_update_order_meta', 'woo_oca_checkout_field_update_order_meta' );
function woo_oca_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['sucursal_oca_destino'] ) ) {
		$data = filter_var ( $_POST['sucursal_oca_destino'], FILTER_SANITIZE_NUMBER_INT);
		if($data != -1){
			update_post_meta( $order_id, 'sucursal_oca_destino',  $data );
		}
    }
}


// =========================================================================
/**
 * Function woo_oca_guardar_precio_real
 *
 */
function woo_oca_guardar_precio_real( $order_id ) {
	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
	$chosen_shipping = $chosen_methods[0]; 
	$chosen_shipping = explode(" ",$chosen_shipping);
	if(WC()->session->get('precio_oca_pas_'.$chosen_shipping[3].'_yes') !== ''){
		update_post_meta( $order_id, 'precio_envio_oca_pas_'.$chosen_shipping[3].'_contrareembolso', WC()->session->get('precio_oca_pas_'.$chosen_shipping[3].'_yes') );
	}
	if(WC()->session->get('precio_oca_pap_'.$chosen_shipping[3].'_yes') !== ''){
		update_post_meta( $order_id, 'precio_envio_oca_pap_'.$chosen_shipping[3].'_contrareembolso', WC()->session->get('precio_oca_pap_'.$chosen_shipping[3].'_yes') );
	}
	if(WC()->session->get('precio_oca_sas_'.$chosen_shipping[3].'_yes') !== ''){
		update_post_meta( $order_id, 'precio_envio_oca_sas_'.$chosen_shipping[3].'_contrareembolso', WC()->session->get('precio_oca_sas_'.$chosen_shipping[3].'_yes') );
	}
	if(WC()->session->get('precio_oca_sap_'.$chosen_shipping[3].'_yes') !== ''){
		update_post_meta( $order_id, 'precio_envio_oca_sap_'.$chosen_shipping[3].'_contrareembolso', WC()->session->get('precio_oca_sap_'.$chosen_shipping[3].'_yes') );
	}
}
add_action('woocommerce_checkout_update_order_meta', 'woo_oca_guardar_precio_real');



// =========================================================================
/**
 * Function agregar_boton_etiqueta_oca
 *
 */
// Agregamos un botón a las ordenes completadas
add_filter( 'woocommerce_admin_order_actions', 'woo_oca_agregar_boton_etiqueta_oca', 100, 2 );
function woo_oca_agregar_boton_etiqueta_oca( $actions, $order ) {
	$envio_seleccionado = reset( $order->get_items( 'shipping' ) )->get_method_id();	
    if ( $order->has_status( array( 'completed' ) ) && strpos($envio_seleccionado, 'oca') !== false ) {
        // Imprimimos el botón
		printf( '<a class="button tips %s" href="%s" target="_blank" data-tip="%s">%s</a>', esc_attr( "view eti_oca" ), plugin_dir_url( __FILE__ ).'etiquetas/ver_eti.php?id='.$order->get_meta('ordenretiro_oca').'&nro='.$order->get_meta('numeroenvio_oca'), esc_attr( "Etiqueta" ), esc_attr( "Etiqueta" ) );
		
	}
    return $actions;
}



// =========================================================================
/**
 * Function boton_etiquetas_oca_css
 *
 */
// Icono para el botón de etiquetas dentro de manage orders
add_action( 'admin_head', 'woo_oca_boton_etiquetas_oca_css' );
function woo_oca_boton_etiquetas_oca_css() {
    echo '<style>.view.eti_oca::after { font-family: woocommerce; content: "\e028" !important; }</style>';
}


// =========================================================================
/**
 * Function agregar_columna_oca
 *
 */
// Agregar columna de numeros de tracking, en el panel de ordenes
add_filter( 'manage_edit-shop_order_columns', 'woo_oca_agregar_columna_oca');
function woo_oca_agregar_columna_oca( $columns ) {
	$new_columns = array();
	foreach ( $columns as $column_name => $column_info ) {
		$new_columns[ $column_name ] = $column_info;
		if ( 'order_total' === $column_name ) {
			$new_columns['rastreo_oca'] = __( 'OCA OrdenRetiro / NroEnvio', 'my-textdomain' );
		}
	}
	return $new_columns;
}


// =========================================================================
/**
 * Function agregar_contenido_columna_oca
 *
 */
add_action( 'manage_shop_order_posts_custom_column', 'woo_oca_agregar_contenido_columna_oca' );
function woo_oca_agregar_contenido_columna_oca( $column ) {
    global $post;
    if ( 'rastreo_oca' === $column ) {
		$order = wc_get_order( $post->ID );
		if($order->get_meta('ordenretiro_oca') !== ''){
			echo $order->get_meta('ordenretiro_oca').' | '.$order->get_meta('numeroenvio_oca');
		}
    }
}


// =========================================================================
/**
 * Function agregar_estilo_columna_oca
 *
 */
add_action( 'admin_print_styles', 'woo_oca_agregar_estilo_columna_oca' );
function woo_oca_agregar_estilo_columna_oca() {
		$css = '.column-rastreo_oca { width: 9%; }';
		wp_add_inline_style( 'woocommerce_admin_styles', $css );
	}



// =========================================================================
/**
 * Function woo_oca_clear_wc_shipping_rates_cache
 *
 */
add_filter('woocommerce_checkout_update_order_review', 'woo_oca_clear_wc_shipping_rates_cache');
function woo_oca_clear_wc_shipping_rates_cache(){
	$packages = WC()->cart->get_shipping_packages();
	foreach ($packages as $key => $value) {
		$shipping_session = "shipping_for_package_$key";
		unset(WC()->session->$shipping_session);
	}
}