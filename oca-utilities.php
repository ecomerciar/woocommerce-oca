<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Agregamos un shortcode [sucursales_oca]
function sucursales_oca_func( $atts, $content= NULL) {
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
add_shortcode ( 'sucursales_oca' , 'sucursales_oca_func' );

add_action( 'wp_ajax_oca_leer_sucursales', 'oca_leer_sucursales' );
add_action( 'wp_ajax_nopriv_oca_leer_sucursales', 'oca_leer_sucursales' );
function oca_leer_sucursales(){
	
	$session = WC()->session;
	if ( ! isset( $session ) ) {
		wp_die();
	}

	$temp = $_REQUEST['data'];

	$cp = explode("|",$temp)[1];

	$session->set('cp_sucursal_oca', $cp);
	$session->set('id_destino_sucursal_oca', $temp);
}

function enqueue_oca_scripts() {
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
add_action( 'wp_enqueue_scripts', 'enqueue_oca_scripts' );

add_action( 'woocommerce_review_order_before_submit', 'check_if_oca' );
function check_if_oca( $chosen_method ) {
	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
	$chosen_shipping = $chosen_methods[0]; 
	$chosen_shipping = explode(" ",$chosen_shipping);
	$session = WC()->session;
	?><script>
		jQuery("#sucursal_oca_destino_field").hide();
		function cambiar_suc(val){
			if(val !== '0'){
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
		echo "<h4>Selecciona la sucursal de OCA donde quieres recibir tu compra</h4>";
		$centros = obtener_centros_oca(WC()->customer->get_shipping_postcode());
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
		echo "<script>cambiar_suc('0')</script>";
	}
}

function obtener_centros_oca($cp){
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
	return $c_imp;
	
}

function kia_filter_checkout_fields($fields){
    $fields['oca'] = array(
            'sucursal_oca_destino' => array(
                'type' => 'text',
                'required'      => true,
				'label' => __( 'Sucursal OCA' ),
				'class'      => array('form-row-wide'),
				'default' => ''	
			),
			);
	
	$id = WC()->session->get('id_destino_sucursal_oca');
	if($id !== ''){
		$fields['oca']['sucursal_oca_destino']['default'] = explode("|",$id)[0];
	}

    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'kia_filter_checkout_fields' );

function kia_extra_checkout_fields(){ 
		$checkout = WC()->checkout(); 
		// because of this foreach, everything added to the array in the previous function will display automagically
		foreach ( $checkout->checkout_fields['oca'] as $key => $field ) : ?>
	
				<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
	
			<?php endforeach; ?>
	
	<?php }
add_action( 'woocommerce_checkout_after_customer_details' ,'kia_extra_checkout_fields' );


// save the extra field when checkout is processed
function kia_save_extra_checkout_fields( $order, $data ){
 
    // don't forget appropriate sanitization if you are using a different field type
    if( isset( $data['sucursal_oca_destino'] ) ) {
		$order->update_meta_data( 'sucursal_oca_destino', $data['sucursal_oca_destino'] );
		$order->save();		
    }
}
add_action( 'woocommerce_checkout_create_order', 'kia_save_extra_checkout_fields', 10, 2 );


// Agregamos un botón a las ordenes completadas
add_filter( 'woocommerce_admin_order_actions', 'agregar_boton_etiqueta_oca', 100, 2 );
function agregar_boton_etiqueta_oca( $actions, $order ) {
	$envio_seleccionado = reset( $order->get_items( 'shipping' ) )->get_method_id();	
    if ( $order->has_status( array( 'completed' ) ) && strpos($envio_seleccionado, 'oca') !== false ) {
        // Imprimimos el botón
		printf( '<a class="button tips %s" href="%s" target="_blank" data-tip="%s">%s</a>', esc_attr( "view eti_oca" ), '../wp-content/plugins/woocommerce-oca/etiquetas/ver_eti.php?id='.$order->get_meta('ordenretiro_oca').'&nro='.$order->get_meta('numeroenvio_oca'), esc_attr( "Etiqueta" ), esc_attr( "Etiqueta" ) );
		
	}
    return $actions;
}
// Icono para el botón de etiquetas dentro de manage orders
add_action( 'admin_head', 'boton_etiquetas_oca_css' );
function boton_etiquetas_oca_css() {
    echo '<style>.view.eti_oca::after { font-family: woocommerce; content: "\e028" !important; }</style>';
}

// Agregar columna de numeros de tracking, en el panel de ordenes
function agregar_columna_oca( $columns ) {
	$new_columns = array();
	foreach ( $columns as $column_name => $column_info ) {
		$new_columns[ $column_name ] = $column_info;
		if ( 'order_total' === $column_name ) {
			$new_columns['rastreo_oca'] = __( 'OCA OrdenRetiro / NroEnvio', 'my-textdomain' );
		}
	}
	return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'agregar_columna_oca');

function agregar_contenido_columna_oca( $column ) {
    global $post;
    if ( 'rastreo_oca' === $column ) {
		$order = wc_get_order( $post->ID );
		if($order->get_meta('ordenretiro_oca') !== ''){
			echo $order->get_meta('ordenretiro_oca').' | '.$order->get_meta('numeroenvio_oca');
		}
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'agregar_contenido_columna_oca' );

function agregar_estilo_columna_oca() {
		$css = '.column-rastreo_oca { width: 9%; }';
		wp_add_inline_style( 'woocommerce_admin_styles', $css );
	}
add_action( 'admin_print_styles', 'agregar_estilo_columna_oca' );

