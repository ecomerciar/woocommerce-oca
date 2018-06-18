<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

//Creamos nuestra clase WC_OCA
function woo_oca_envios_oca_init()
{
	if (!class_exists('WC_OCA')) {
		class WC_OCA extends WC_Shipping_Method
		{
			/**
			 * Constructor de la clase
			 *
			 * @access public
			 * @return void
			 */
			public function __construct($instance_id = 0)
			{
				$this->id = 'oca'; // Id for your shipping method. Should be unique.
				$this->method_title = 'OCA';  // Title shown in admin
				$this->method_description = __('Envios con OCA', 'woocommerce'); // Description shown in admin
				$this->title = __('Envío con OCA', 'oca');
				$this->instance_id = absint($instance_id);
				$this->supports = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal'
				);
				$this->logger = wc_get_logger();
				$this->settings = unserialize(get_option('oca_fields_settings'));
				// Definimos la configuración
				$this->init();

				add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
			}

			/**
			 * Inicialización de las opciones
			 *
			 * @access public
			 * @return void
			 */
			function init()
			{
				$this->form_fields = array(); // No hay config global, solo de instancia
				$this->instance_form_fields = array(
					'clase' => array(
						'title' => 'Si existe la clase',
						'type' => 'select',
						'default' => '',
						'desc_tip' => true,
						'options' => array(
							'nada' => 'Seleccionar'
						)
					),
					'accion' => array(
						'title' => 'Entonces',
						'type' => 'select',
						'default' => '',
						'desc_tip' => true,
						'options' => array(
							'nada' => 'No hacer nada',
							'desactivar_metodo' => 'Desactivar método de envio',
							'activar_metodo' => 'Activar método de envio',
							'envio_gratis' => 'Envio gratis'
						)
					),
					'envio_general_gratis' => array(
						'title' => __('Envío gratis', 'woocommerce'),
						'type' => 'checkbox'
					),
					'debug' => array(
						'title' => __('Debug log?', 'woocommerce'),
						'type' => 'checkbox'
					)
				);
				// Cargamos todas las clases disponibles de WC y las insertamos en la config de oca
				$clases = WC()->shipping->get_shipping_classes();
				foreach ($clases as $clase) {
					$this->instance_form_fields['clase']['options'][$clase->name] = $clase->name;
				}
			}

			// =========================================================================
			/**
			 * Replace comma by dot.
			 *
			 * @param  mixed $value Value to fix.
			 *
			 * @return mixed
			 */
			private function fix_format($value)
			{
				$value = str_replace(',', '.', $value);

				return $value;
			}

			// =========================================================================
			/**
			 * function calculate_shipping.
			 *
			 * @access public
			 * @param mixed $package
			 * @return void
			 */
			public function calculate_shipping($package = array())
			{
				$productos = array();
				if ($this->get_instance_option('debug') === 'yes') {
					$this->logger->debug(" ====== Inicio de cálculo de precio ======", unserialize(OCA_LOGGER_CONTEXT));
				}
				$envio_gratis = $this->get_instance_option('envio_general_gratis');
				$accion = $this->verificar_clases($productos);

				$peso_total = $precio_total = $largo_total = $ancho_total = $alto_total = 0;
				$hay_producto_cero = false;

				foreach ($productos as $producto) {
					$peso_total += $producto['peso'];
					$largo_total += $producto['largo'];
					$ancho_total += $producto['ancho'];
					$alto_total += $producto['alto'];
					if (!$producto['peso'] || !$producto['alto'] || !$producto['largo'] || !$producto['ancho']) {
						$hay_producto_cero = true;
						$producto_cero = $producto;
						break;
					}
				}

				if ($hay_producto_cero || !$peso_total || !$largo_total || !$alto_total || !$ancho_total) {
					if (isset($producto_cero)) {
						$this->logger->error('Detectado producto con malas dimensiones o peso: ', unserialize(OCA_LOGGER_CONTEXT));
						$this->logger->error(print_r($producto_cero, true), unserialize(OCA_LOGGER_CONTEXT));
					} else {
						$this->logger->error('Detectado dimension/peso 0', unserialize(OCA_LOGGER_CONTEXT));
					}
					return;
				}

				if ($accion === 'activar_metodo' || $accion === 'nada' || $accion === 'envio_gratis' || $envio_gratis === 'yes') {

					$this->cargar_dependencias();
					$medidas_totales = $this->calcular_medidas($productos);
					if ($medidas_totales['volumen'] === -1) {
						$this->logger->error("Medidas incorrectas, producto muy pequeño", unserialize(OCA_LOGGER_CONTEXT));
						return;
					}
					$precio_total = $this->get_precio_total($productos);
					$operativas = unserialize(get_option('oca_operativas'));
					foreach ($operativas as $operativa) {
						if (!$operativa['active']) {
							continue;
						}
						$nombre = $operativa['name'];
						$cod_operativa = $operativa['code'];
						$tipo_operativa = $operativa['type'];
						$contrareembolso = $operativa['contrareembolso'];

						if ($accion === 'envio_gratis' || $envio_gratis === 'yes') {
							$this->addRate(0, $nombre, serialize($operativa));
							continue;
						}

						// Calc price
						$oca = new Oca($this->settings['cuit'], $cod_operativa);
						if (WC()->session->get('cp_sucursal_oca') !== '' && WC()->session->get('cp_sucursal_oca') !== null && ($tipo_operativa == 'pas' || $tipo_operativa == 'sas')) {
							$tarifa = $oca->tarifarEnvioCorporativo($medidas_totales['peso'], $medidas_totales['volumen'], $this->settings['postal-code'], WC()->session->get('cp_sucursal_oca'), count($productos), $precio_total);
						} else if ($tipo_operativa == 'pas' || $tipo_operativa == 'sas') {
							$tarifa[0] = array('Total' => 0);
						} else {
							$cp = preg_replace('/[^0-9]/', '', WC()->customer->get_shipping_postcode());
							$tarifa = $oca->tarifarEnvioCorporativo($medidas_totales['peso'], $medidas_totales['volumen'], $this->settings['postal-code'], $cp, count($productos), $precio_total);
						}

						// Set price
						if (!isset($tarifa[0]['error'])) {
							if ($contrareembolso) {
								$precio = number_format($tarifa[0]['Total'], 2);
								$this->addRate(0, $nombre . ' (Pago a destino' . ($precio == 0 ? ')' : ' - $' . $precio . ')'), serialize($operativa));
								WC()->session->set('precio_oca_' . $cod_operativa, $precio);
							} else {
								$this->addRate($tarifa[0]['Total'], $nombre, serialize($operativa));
							}
						} else {
							$this->logger->error("Hubo un error al calcular el precio del envío: " . $tarifa[0]['error'], unserialize(OCA_LOGGER_CONTEXT));
						}
					}
				}
			}

			// =========================================================================
			/**
			 * funcion verificar_clases
			 * Verifica si la condicion de la config del plugin se cumple, retorna la accion a tomar si se cumple la condicion, en caso contrario devuelve 'nada'
			 *
			 * @access public
			 * @param array $productos
			 * @return string
			 */
			public function verificar_clases(&$productos)
			{
				$action = $this->get_instance_option('accion');
				$class = $this->get_instance_option('clase');
				$products = WC()->cart->get_cart();
				if (!$products) {
					return false;
				}
				$condition = false;
				if (!empty($action) && $action !== 'nada' && !empty($class) && $class !== 'nada') {
                    // If action is desactivar, we search for the classes using OR logic, finding just one
					if ($action === 'desactivar_metodo') {
						foreach ($products as $item) {
							$product = wc_get_product($item['id']);
							if ($class === $product->get_shipping_class()) {
								$condition = true;
								$break;
							}
						}
					} else {
						$condition = true;
						foreach ($products as $item) {
							$product = wc_get_product($item['id']);
							if ($class !== $product->get_shipping_class()) {
								$condition = false;
								$break;
							}
						}
					}
				} else {
					$condition = $action = 'nada';
				}
				foreach ($products as $item) {
					$product = $item['data'];
					$new_product = array(
						"peso" => wc_get_dimension(floatval($product->get_weight()), 'kg'),
						"largo" => wc_get_dimension(floatval($product->get_length()), 'm'),
						"ancho" => wc_get_dimension(floatval($product->get_width()), 'm'),
						"alto" => wc_get_dimension(floatval($product->get_height()), 'm'),
						"precio" => $product->get_price()
					);
					for ($x = 0; $x < $item['quantity']; $x++) {
						array_push($productos, $new_product);
					}
				}
				if ($condition) {
					return $action;
				} else if ($action === 'activar_metodo') {
					return 'desactivar_metodo';
				}
				return 'nada';
			}
			
			// =========================================================================
			/**
			 * function get_precio_total
			 *
			 * @access private
			 * @return float
			 */
			private function get_precio_total($productos)
			{
				$precio = 0;
				foreach ($productos as $producto) {
					$precio += $producto['precio'];
				}
				return $precio;
			}

			// =========================================================================
			/**
			 * function calcular_medidas
			 *
			 * @access private
			 * @return array
			 */
			private function calcular_medidas($productos = array())
			{
				$res = array(
					'peso' => 0,
				);
				$largo = $ancho = $alto = 0;
				foreach ($productos as $producto) {
					$res['peso'] += $producto['peso'];
					$largo += $producto['largo'];
					$ancho += $producto['ancho'];
					$alto += $producto['alto'];
				}
				$res['volumen'] = $largo * $ancho * $alto;

				$res['peso'] = number_format($res['peso'], 2);
				if ($res['volumen'] < 0.000001) {
					$res['volumen'] = -1;
				}
				return $res;
			}

			// =========================================================================
			/**
			 * function cargar_dependencias
			 *
			 * @access private
			 * @return void
			 */
			private function cargar_dependencias()
			{
				require_once 'oca/autoload.php';
			}


			// =========================================================================
			/**
			 * funcion addRate.
			 * 
			 * Agrega el metodo de envio al carrito
			 *
			 * @access public
			 * @param string $precio, $nombre, $tipo_op
			 * @return array
			 */
			public function addRate($precio = '', $nombre = '', $operativa = '')
			{
				$operativa = unserialize($operativa);
				unset($operativa['name']);
				unset($operativa['active']);
				$operativa = serialize($operativa);
				$operativa = str_replace('"', '~', $operativa);
				$rate = array(
					'id' => 'oca ' . $operativa,
					'label' => $nombre,
					'cost' => $precio,
					'calc_tax' => 'per_order'
				);

				$this->add_rate($rate);

				if ($this->get_instance_option('debug') === 'yes') {
					$this->logger->debug("Precio calculado: " . $precio, unserialize(OCA_LOGGER_CONTEXT));
				}
			}

		}
	}
}
add_action('woocommerce_shipping_init', 'woo_oca_envios_oca_init');

// =========================================================================
/**
 * Function agregar_envio_oca
 *
 */
add_filter('woocommerce_shipping_methods', 'woo_oca_agregar_envio_oca');
function woo_oca_agregar_envio_oca($methods)
{
	$methods['oca'] = 'WC_OCA';
	return $methods;
}