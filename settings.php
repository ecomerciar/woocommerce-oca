<?php

namespace Ecomerciar\OCA\Settings;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

function init_settings()
{
	register_setting('ecom_oca', 'ecom_oca_options');

	add_settings_section(
		'ecom_oca',
		'Configuración',
		'',
		'oca_settings'
	);

	add_settings_field(
		'fields',
		'Datos del vendedor',
		__NAMESPACE__ . '\print_fields',
		'oca_settings',
		'ecom_oca'
	);

	add_settings_field(
		'operativas',
		'Operativas',
		__NAMESPACE__ . '\print_table',
		'oca_settings',
		'ecom_oca'
	);
}

function add_assets_files($hook)
{
	if ($hook !== 'settings_page_oca_settings') {
		return;
	}
	wp_enqueue_script('settings.js', plugin_dir_url(__FILE__) . 'js/settings.js', array(), 1.0001);
	wp_enqueue_style('settings.css', plugin_dir_url(__FILE__) . 'css/settings.css', array(), 1.0001);
}

function print_fields()
{
	echo '<table class="widefat fields-table">';
	$previous_config = unserialize(get_option('oca_fields_settings'));
	wp_nonce_field('update_operativas', 'input_fields');
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Usuario de OCA</span></td>';
	echo '<td><input type="text" name="oca[username]" value="' . (isset($previous_config['username']) ? $previous_config['username'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Contraseña</span></td>';
	echo '<td><input type="password" name="oca[password]" value="' . (isset($previous_config['password']) ? $previous_config['password'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Numero de cuenta</span></td>';
	echo '<td><input type="text" name="oca[account-number]" value="' . (isset($previous_config['account-number']) ? $previous_config['account-number'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Nombre de contacto</span></td>';
	echo '<td><input type="text" name="oca[contact-name]" value="' . (isset($previous_config['contact-name']) ? $previous_config['contact-name'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Nombre de empresa</span></td>';
	echo '<td><input type="text" name="oca[store-name]" value="' . (isset($previous_config['store-name']) ? $previous_config['store-name'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Calle</span></td>';
	echo '<td><input type="text" name="oca[street]" value="' . (isset($previous_config['street']) ? $previous_config['street'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Numero</span></td>';
	echo '<td><input type="text" name="oca[street-number]" value="' . (isset($previous_config['street-number']) ? $previous_config['street-number'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Piso (Opcional)</span></td>';
	echo '<td><input type="text" name="oca[floor]" value="' . (isset($previous_config['floor']) ? $previous_config['floor'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Departamento (Opcional)</span></td>';
	echo '<td><input type="text" name="oca[apartment]" value="' . (isset($previous_config['apartment']) ? $previous_config['apartment'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Codigo Postal</span></td>';
	echo '<td><input type="text" name="oca[postal-code]" value="' . (isset($previous_config['postal-code']) ? $previous_config['postal-code'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Localidad</span></td>';
	echo '<td><input type="text" name="oca[locality]" value="' . (isset($previous_config['locality']) ? $previous_config['locality'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Provincia</span></td>';
	echo '<td><input type="text" name="oca[province]" value="' . (isset($previous_config['province']) ? $previous_config['province'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Email de contacto</span></td>';
	echo '<td><input type="text" name="oca[email]" value="' . (isset($previous_config['email']) ? $previous_config['email'] : '') . '"></td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">Franja horaria</span></td>';
	echo '<td><select class="select " name="oca[timezone]">
			<option value="1" ' . (isset($previous_config['timezone']) && $previous_config['timezone'] === '1' ? 'selected' : '') . '>8 a 17</option>
			<option value="2" ' . (isset($previous_config['timezone']) && $previous_config['timezone'] === '2' ? 'selected' : '') . '>8 a 12</option>
			<option value="3" ' . (isset($previous_config['timezone']) && $previous_config['timezone'] === '3' ? 'selected' : '') . '>14 a 17</option>
		</select></td></tr>';
	echo '<tr><td style="width: 20%"><a target="_blank" href="' . esc_url(get_permalink(get_page_by_title('OCA Sucursales'))) . '"><span style="font-weight:700">ID Sucursal de origen</span></a></td>';
	echo '<td><input type="number" name="oca[id-origin]" value="' . (isset($previous_config['id-origin']) ? $previous_config['id-origin'] : '') . '">';
	echo '<p class="text-info">Clickeá en ID Sucursal de origen o buscá la página directamente en tu sitio para buscar la sucursal más cercana donde OCA realizará los retiros de ser necesario, recuerda que debes colocar solo el ID indicado en la tabla</p>';
	echo '</td></tr>';
	echo '<tr><td style="width: 20%"><span style="font-weight:700">CUIT</span></td>';
	echo '<td><input type="text" placeholder="XX-XXXXXXXX-X" name="oca[cuit]" value="' . (isset($previous_config['cuit']) ? $previous_config['cuit'] : '') . '">';
	echo '<p class="text-info">Formato XX-XXXXXXXX-X</p>';
	echo '</td></tr>';
	echo '</table>';
}


function print_table()
{
	wp_nonce_field('update_operativas', 'table_field');
	echo '<table id="operativas-table" class="widefat">
		<thead>
			<tr>
				<th style="text-align:center">Activa</th>
				<th style="text-align:center">Nombre operativa</th>
				<th style="text-align:center">Operativa</th>
				<th style="text-align:center">Codigo operativa</th>
				<th style="text-align:center">Contrareembolso</th>
				<th style="text-align:center">Eliminar</th>
			</tr>
		</thead>
		<tbody>';
	$previous_config = unserialize(get_option('oca_operativas'));
	$i = 0;
	do {
		echo '<tr>
				<td style="text-align:center">
					<input name="operativas[' . $i . '][active]" type="hidden" value="0"/>
					<input type="checkbox" name="operativas[' . $i . '][active]" value="1" ' . (isset($previous_config[$i]['active']) && $previous_config[$i]['active'] ? 'checked' : '') . '>
				</td>
				<td style="text-align:center">
				<input type="text" name="operativas[' . $i . '][name]" placeholder="Nombre en Checkout" value="' . (isset($previous_config[$i]['name']) && !empty($previous_config[$i]['name']) ? $previous_config[$i]['name'] : '') . '">				
				</td>
				<td>
					<select name="operativas[' . $i . '][type]">
						<option value="pap" ' . (isset($previous_config[$i]['type']) && $previous_config[$i]['type'] === 'pap' ? 'selected' : '') . '>Puerta a Puerta</option>
						<option value="pas" ' . (isset($previous_config[$i]['type']) && $previous_config[$i]['type'] === 'pas' ? 'selected' : '') . '>Puerta a Sucursal</option>
						<option value="sap" ' . (isset($previous_config[$i]['type']) && $previous_config[$i]['type'] === 'sap' ? 'selected' : '') . '>Sucursal a Puerta</option>
						<option value="sas" ' . (isset($previous_config[$i]['type']) && $previous_config[$i]['type'] === 'sas' ? 'selected' : '') . '>Sucursal a Sucursal</option>
					</select>
				</td>
				<td style="text-align:center">
					<input type="text" name="operativas[' . $i . '][code]" value="' . (isset($previous_config[$i]['code']) && !empty($previous_config[$i]['code']) ? $previous_config[$i]['code'] : '') . '">
				</td>
				<td style="text-align:center">
					<input name="operativas[' . $i . '][contrareembolso]" type="hidden" value="0"/>
					<input type="checkbox" name="operativas[' . $i . '][contrareembolso]" value="1" ' . (isset($previous_config[$i]['contrareembolso']) && $previous_config[$i]['contrareembolso'] ? 'checked' : '') . '>
				</td>
				<td style="text-align:center">
					<a class="button delete-site">Eliminar</a>
				</td>
			</tr>';
		$i++;
	} while ($i < count($previous_config));
	echo '</tbody>
			<tfoot>
				<tr>
					<td colspan="10">
						<a class="button-primary add-operativa">Agregar Operativa</a>
					</td>
				</tr>
				<tr>
				</tr>
			</tfoot>
		</table>';
}

function create_menu_option()
{
	add_options_page(
		'Configuración de OCA',
		'Configuración de OCA',
		'manage_options',
		'oca_settings',
		__NAMESPACE__ . '\settings_page_content'
	);
}

function settings_page_content()
{

	if (!current_user_can('manage_options')) {
		return;
	}

	if (isset($_POST['oca']) && !empty($_POST['oca'])) {
		check_admin_referer('update_operativas', 'input_fields');
		update_option('oca_fields_settings', serialize($_POST['oca']));
	}

	if (isset($_POST['operativas']) && !empty($_POST['operativas'])) {
		check_admin_referer('update_operativas', 'table_field');
		update_option('oca_operativas', serialize($_POST['operativas']));
	}

	?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<form action="options-general.php?page=oca_settings" method="post">
			<?php
		settings_fields('oca_settings');
		do_settings_sections('oca_settings');
		submit_button('Guardar');
		?>
		</form>
	</div>
	<?php

}
