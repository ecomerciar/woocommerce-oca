<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


// =========================================================================
/**
 * Shortcode [rastreo_oca]
 *
 */
add_shortcode('rastreo_oca', 'woo_oca_rastreo_oca_func');
function woo_oca_rastreo_oca_func($atts, $content = null)
{
    if ($_POST['id']) {
        $oca_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
        $_query_string = array(
            'Pieza' => $oca_id,
            'NroDocumentoCliente' => '',
            'Cuit' => '',
        );
        $ch = curl_init();
        $curl_opt_arr[CURLOPT_POST] = true;
        $curl_opt_arr[CURLOPT_POSTFIELDS] = http_build_query($_query_string);
        $curl_opt_arr[CURLOPT_RETURNTRANSFER] = true;
        $curl_opt_arr[CURLOPT_HEADER] = false;
        $curl_opt_arr[CURLOPT_URL] = "webservice.oca.com.ar/epak_tracking/Oep_TrackEPak.asmx/Tracking_Pieza";
        curl_setopt_array($ch, $curl_opt_arr);
        $res = curl_exec($ch);
        $dom = new DOMDocument();
        @$dom->loadXML($res);
        $xpath = new DOMXpath($dom);
        $envio = array();
        foreach (@$xpath->query("//NewDataSet/Table") as $tp) {

            $envio[] = array(
                "NumeroEnvio" => $tp->getElementsByTagName('NumeroEnvio')->item(0)->nodeValue,
                "Descripcion_Motivo" => $tp->getElementsByTagName('Descripcion_Motivo')->item(0)->nodeValue,
                "Desdcripcion_Estado" => $tp->getElementsByTagName('Desdcripcion_Estado')->item(0)->nodeValue,
                "SUC" => $tp->getElementsByTagName('SUC')->item(0)->nodeValue,
                "fecha" => $tp->getElementsByTagName('fecha')->item(0)->nodeValue,
            );
        }

        ob_start();
        if (isset($envio[0]['SUC'])) {
            echo '<h3>Envío Nro: ' . $envio[0]['NumeroEnvio'] . '</h3>';
            echo "<table>";
            echo "<tr>";
            echo "<td>Estado</td>";
            echo "<td>" . $envio[0]['Desdcripcion_Estado'] . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>Sucursal</td>";
            echo "<td>" . $envio[0]['SUC'] . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>Fecha</td>";
            echo "<td>" . $envio[0]['fecha'] . "</td>";
            echo "</tr>";
            echo "</table>";
        } else {
            wc_print_notice(__('Hubo un error, por favor intenta nuevamente', 'woocommerce'), 'error');
        }
        return ob_get_clean();
    }
}




// =========================================================================
/**
 * Shortcode [sucursales_oca]
 *
 */
add_shortcode('sucursales_oca', 'woo_oca_sucursales_oca_func');
function woo_oca_sucursales_oca_func($atts, $content = null)
{
    if (isset($_POST['cp']) && !empty($_POST['cp'])) {
        $cp = filter_var($_POST['cp'], FILTER_SANITIZE_NUMBER_INT);
        $post_data = ["CodigoPostal" => intval($cp)];
        $url = 'http://webservice.oca.com.ar/epak_tracking/Oep_TrackEPak.asmx/GetCentrosImposicionConServiciosByCP';
        $response = wp_remote_get($url, array(
            'method' => 'GET',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => $post_data,
            'cookies' => array()
        ));
        $response = $response['http_response']->get_response_object()->body;
        $dom = new DOMDocument();
        @$dom->loadXML($response);
        $xpath = new DOMXpath($dom);

        $c_imp = array();
        foreach (@$xpath->query("//CentrosDeImposicion/Centro") as $ci) {
            $c_imp[] = array(
                'idCentroImposicion' => $ci->getElementsByTagName('IdCentroImposicion')->item(0)->nodeValue,
                'Sucursal' => $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
                'Sigla' => $ci->getElementsByTagName('Sigla')->item(0)->nodeValue,
                'Sucursal' => $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
                'Calle' => $ci->getElementsByTagName('Calle')->item(0)->nodeValue,
                'Numero' => $ci->getElementsByTagName('Numero')->item(0)->nodeValue,
                'Localidad' => $ci->getElementsByTagName('Localidad')->item(0)->nodeValue,
                'Provincia' => $ci->getElementsByTagName('Provincia')->item(0)->nodeValue,
                'Telefono' => $ci->getElementsByTagName('Telefono')->item(0)->nodeValue,
                'CodigoPostal' => $ci->getElementsByTagName('CodigoPostal')->item(0)->nodeValue
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
        foreach ($c_imp as $centro) {
            echo '<tr>
					<td>' . $centro['idCentroImposicion'] . '</td>
					<td>' . $centro['Sucursal'] . '</td>
					<td>' . $centro['Calle'] . '</td>
					<td>' . $centro['Numero'] . '</td>
					<td>' . $centro['Localidad'] . '</td>
					<td>' . $centro['Telefono'] . '</td>
					<td>' . $centro['CodigoPostal'] . '</td>
	  			</tr>';
        }
        echo '</table>';
        return ob_get_clean();
    }
}


// =========================================================================
/**
 * Function oca_set_sucursal_en_sesion
 *
 */
add_action('wp_ajax_oca_set_sucursal_en_sesion', 'woo_oca_set_sucursal_en_sesion');
add_action('wp_ajax_nopriv_oca_set_sucursal_en_sesion', 'woo_oca_set_sucursal_en_sesion');
function woo_oca_set_sucursal_en_sesion()
{

    if (!isset(WC()->session)) {
        wp_send_json_error();
    }

    $data = filter_var($_POST['data'], FILTER_SANITIZE_STRING);
    if (empty($data) || !wp_verify_nonce($_POST['security-nonce'], 'oca-update-session-nonce')) {
        unset(WC()->session->cp_sucursal_oca);
        unset(WC()->session->id_destino_sucursal_oca);
    } else {
        $cp = explode("|", $data)[1];
        WC()->session->set('cp_sucursal_oca', $cp);
        WC()->session->set('id_destino_sucursal_oca', $data);
    }

    wp_send_json_success();
}


// =========================================================================
/**
 * Function enqueue_oca_scripts
 *
 */
add_action('wp_enqueue_scripts', 'woo_oca_enqueue_oca_scripts');
function woo_oca_enqueue_oca_scripts()
{
    if (function_exists('is_woocommerce')) {
        if (!is_checkout()) {
            wp_dequeue_script('woo-oca-script-checkout');
        } else {
            wp_register_script('woo-oca-script-checkout', plugin_dir_url(__FILE__) . 'js/oca.js', array('jquery'));
            wp_localize_script(
                'woo-oca-script-checkout',
                'ajaxObject',
                ['ajaxUrl' => admin_url('admin-ajax.php'), 'securityNonce' => wp_create_nonce('oca-update-session-nonce')]
            );
        }
    }
}


// =========================================================================
/**
 * Function check_if_oca_selected
 *
 */
//add_action('woocommerce_review_order_before_submit', 'woo_oca_check_if_oca_selected');
add_action('woocommerce_after_shipping_rate', 'woo_oca_check_if_oca_selected', 10, 2);
function woo_oca_check_if_oca_selected($method, $index)
{
    wp_enqueue_script('woo-oca-script-checkout');

    // Print the select field only in the shipping method needed
    $current_method = $method->get_id();
    $current_method = explode(" ", $current_method);
    $current_operativa = str_replace('~', '"', $current_method[1]);
    $current_operativa = unserialize($current_operativa);

    // Show this select field ONLY when it is selected
    $chosen_methods = WC()->session->get('chosen_shipping_methods');
    $chosen_shipping = $chosen_methods[0];
    $chosen_shipping = explode(" ", $chosen_shipping);
    $chosen_operativa = $chosen_shipping[1];
    $chosen_operativa = str_replace('~', '"', $chosen_operativa);
    $chosen_operativa = unserialize($chosen_operativa);

    if (
        $chosen_shipping[0] === 'oca' && ($chosen_operativa['type'] === 'pas' || $chosen_operativa['type'] === 'sas')
        && ($current_operativa['type'] === 'pas' || $current_operativa['type'] === 'sas')
    ) {
        $customer_postcode = WC()->customer->get_shipping_postcode();
        if (empty($customer_postcode)) {
            echo '<span style="display:block; margin: 10px 0; font-size: .85em; class="oca-no-customer-address-message">Por favor ingresá tu dirección para ver las sucursales cercanas disponibles</span>';
        } else {
            echo '<span style="display:block; margin: 10px 0; font-size: .85em;" class="oca-select-office-message">Selecciona la sucursal de OCA donde quieres recibir tu compra</span>';
            $sucursales = woo_oca_obtener_centros_oca($customer_postcode, $chosen_operativa['contrareembolso']);
            $sucursal_seleccionada = WC()->session->get('id_destino_sucursal_oca');
            echo '<select name="oca_sucursal" style="margin:15px 0; width: 100%;">';
            echo '<option value="">Seleccionar</option>';
            foreach ($sucursales as $centro) {
                $value = trim($centro['idCentroImposicion'] . '|' . $centro['CodigoPostal']);
                echo '<option value="' . $value . '" ' . ($value === $sucursal_seleccionada ? 'selected' : '') . '>' . $centro['Sucursal'] . ' / ' . $centro['Calle'] . ' ' . $centro['Numero'] . '</option>';
            }
            echo '</select>';
        }
    }
}

// =========================================================================
/**
 * Function obtener_centros_oca
 *
 */
function woo_oca_obtener_centros_oca($cp, $contrareembolso)
{
    $post_data = ["CodigoPostal" => intval($cp)];
    $url = 'http://webservice.oca.com.ar/epak_tracking/Oep_TrackEPak.asmx/GetCentrosImposicionConServiciosByCP';
    $response = wp_remote_get($url, array(
        'method' => 'GET',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => $post_data,
        'cookies' => array()
    ));
    $response = $response['http_response']->get_response_object()->body;
    $dom = new DOMDocument();
    @$dom->loadXML($response);
    $xpath = new DOMXpath($dom);

    $c_imp = array();
    if ($contrareembolso) {
        foreach (@$xpath->query("//CentrosDeImposicion/Centro") as $ci) {
            if ($ci->getElementsByTagName('TipoAgencia')->item(0)->nodeValue === 'Sucursal OCA') {
                $c_imp[] = array(
                    'idCentroImposicion' => $ci->getElementsByTagName('IdCentroImposicion')->item(0)->nodeValue,
                    'Sucursal' => $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
                    'Sigla' => $ci->getElementsByTagName('Sigla')->item(0)->nodeValue,
                    'Sucursal' => $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
                    'Calle' => $ci->getElementsByTagName('Calle')->item(0)->nodeValue,
                    'Numero' => $ci->getElementsByTagName('Numero')->item(0)->nodeValue,
                    'Localidad' => $ci->getElementsByTagName('Localidad')->item(0)->nodeValue,
                    'Provincia' => $ci->getElementsByTagName('Provincia')->item(0)->nodeValue,
                    'Telefono' => $ci->getElementsByTagName('Telefono')->item(0)->nodeValue,
                    'CodigoPostal' => $ci->getElementsByTagName('CodigoPostal')->item(0)->nodeValue
                );
            }
        }
    } else {
        foreach (@$xpath->query("//CentrosDeImposicion/Centro") as $ci) {
            $servicios_ci = $ci->getElementsByTagName('Servicios')->item(0)->nodeValue;
            if (strpos($servicios_ci, 'Entrega de paquetes') !== false) {
                $c_imp[] = array(
                    'idCentroImposicion' => $ci->getElementsByTagName('IdCentroImposicion')->item(0)->nodeValue,
                    'Sucursal' => $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
                    'Sigla' => $ci->getElementsByTagName('Sigla')->item(0)->nodeValue,
                    'Sucursal' => $ci->getElementsByTagName('Sucursal')->item(0)->nodeValue,
                    'Calle' => $ci->getElementsByTagName('Calle')->item(0)->nodeValue,
                    'Numero' => $ci->getElementsByTagName('Numero')->item(0)->nodeValue,
                    'Localidad' => $ci->getElementsByTagName('Localidad')->item(0)->nodeValue,
                    'Provincia' => $ci->getElementsByTagName('Provincia')->item(0)->nodeValue,
                    'Telefono' => $ci->getElementsByTagName('Telefono')->item(0)->nodeValue,
                    'CodigoPostal' => $ci->getElementsByTagName('CodigoPostal')->item(0)->nodeValue
                );
            }
        }
    }
    return $c_imp;
}

add_action('woocommerce_checkout_process', 'woo_oca_check_post_data_on_purchase');
function woo_oca_check_post_data_on_purchase()
{
    $chosen_methods = WC()->session->get('chosen_shipping_methods');
    $chosen_shipping = $chosen_methods[0];
    $chosen_shipping = explode(" ", $chosen_shipping);
    $chosen_operativa = $chosen_shipping[1];
    $chosen_operativa = str_replace('~', '"', $chosen_operativa);
    $chosen_operativa = unserialize($chosen_operativa);

    if (
        $chosen_shipping[0] === 'oca'
        && ($chosen_operativa['type'] === 'pas' || $chosen_operativa['type'] === 'sas')
        && (empty($_POST['oca_sucursal']) || empty(WC()->session->id_destino_sucursal_oca))
    ) {
        wc_add_notice(__('Por favor elige una sucursal de OCA'), 'error');
    }
}

add_action('woocommerce_checkout_update_order_meta', 'woo_oca_check_order_after_purchase');
function woo_oca_check_order_after_purchase($order_id)
{
    $chosen_shipping_method = WC()->session->get('chosen_shipping_methods');
    $chosen_shipping_method = $chosen_shipping_method[0];
    $chosen_shipping_method = explode(" ", $chosen_shipping_method);
    if ($chosen_shipping_method[0] === 'oca') {
        $order = wc_get_order($order_id);
        $operativa = $chosen_shipping_method[1];
        $operativa = str_replace('~', '"', $operativa);
        $order->update_meta_data('oca_shipping_info', $operativa);
        if ($operativa['contrareembolso']) {
            $order->update_meta_data('precio_real_envio', WC()->session->get('precio_oca_' . $operativa['code']));
        }
        if (isset($_POST['oca_sucursal'])) {
            $sucursal = filter_var($_POST['oca_sucursal'], FILTER_SANITIZE_STRING);
            if (!empty($sucursal)) {
                $sucursal = explode('|', $sucursal)[0];
                $order->update_meta_data('sucursal_oca_destino', $sucursal);
            }
        }
        $order->save();
    }
    unset(WC()->session->cp_sucursal_oca);
    unset(WC()->session->id_destino_sucursal_oca);
}



// =========================================================================
/**
 * Function agregar_boton_etiqueta_oca
 *
 */
// Agregamos un botón a las ordenes completadas
add_filter('woocommerce_admin_order_actions', 'woo_oca_agregar_boton_etiqueta_oca', 100, 2);
function woo_oca_agregar_boton_etiqueta_oca($actions, $order)
{
    $envio_seleccionado = $order->get_items('shipping');
    if (empty($envio_seleccionado)) return $actions;
    $envio_seleccionado = reset($envio_seleccionado);
    $envio_seleccionado = $envio_seleccionado->get_method_id();
    if ($order->has_status(array('completed')) && $envio_seleccionado === 'oca') {
        // Imprimimos el botón
        printf('<a class="button tips %s" href="%s" target="_blank" data-tip="%s">%s</a>', esc_attr("view eti_oca"), plugin_dir_url(__FILE__) . 'etiquetas/ver_eti.php?id=' . $order->get_meta('ordenretiro_oca') . '&nro=' . $order->get_meta('numeroenvio_oca'), esc_attr("Etiqueta"), esc_attr("Etiqueta"));
    }
    return $actions;
}



// =========================================================================
/**
 * Function boton_etiquetas_oca_css
 *
 */
// Icono para el botón de etiquetas dentro de manage orders
add_action('admin_head', 'woo_oca_boton_etiquetas_oca_css');
function woo_oca_boton_etiquetas_oca_css()
{
    echo '<style>.view.eti_oca::after {content: "\f497" !important ;}</style>';
}


// =========================================================================
/**
 * Function agregar_columna_oca
 *
 */
// Agregar columna de numeros de tracking, en el panel de ordenes
add_filter('manage_edit-shop_order_columns', 'woo_oca_agregar_columna_oca');
function woo_oca_agregar_columna_oca($columns)
{
    $new_columns = array();
    foreach ($columns as $column_name => $column_info) {
        $new_columns[$column_name] = $column_info;
        if ('order_total' === $column_name) {
            $new_columns['rastreo_oca'] = __('OCA OrdenRetiro / NroEnvio', 'my-textdomain');
        }
    }
    return $new_columns;
}


// =========================================================================
/**
 * Function agregar_contenido_columna_oca
 *
 */
add_action('manage_shop_order_posts_custom_column', 'woo_oca_agregar_contenido_columna_oca');
function woo_oca_agregar_contenido_columna_oca($column)
{
    global $post;
    if ('rastreo_oca' === $column) {
        $order = wc_get_order($post->ID);
        if ($order->get_meta('ordenretiro_oca') !== '') {
            echo $order->get_meta('ordenretiro_oca') . ' | ' . $order->get_meta('numeroenvio_oca');
        }
    }
}


// =========================================================================
/**
 * Function agregar_estilo_columna_oca
 *
 */
add_action('admin_print_styles', 'woo_oca_agregar_estilo_columna_oca');
function woo_oca_agregar_estilo_columna_oca()
{
    $css = '.column-rastreo_oca { width: 9%; }';
    wp_add_inline_style('woocommerce_admin_styles', $css);
}



// =========================================================================
/**
 * Function woo_oca_clear_wc_shipping_rates_cache
 *
 */
add_filter('woocommerce_shipping_packages', 'woo_oca_clear_wc_shipping_rates_cache');
function woo_oca_clear_wc_shipping_rates_cache($packages)
{
    foreach ($packages as $key => $value) {
        $shipping_session = "shipping_for_package_$key";
        unset(WC()->session->$shipping_session);
    }
    return $packages;
}
