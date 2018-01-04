<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

//Creamos nuestra clase WC_OCA
function woo_oca_envios_oca_init() {
	if ( ! class_exists( 'WC_OCA' ) ) {
		class WC_OCA extends WC_Shipping_Method {
			/**
			 * Constructor de la clase
			 *
			 * @access public
			 * @return void
			 */
			public function __construct($instance_id=0) {
				$this->id                 = 'oca'; // Id for your shipping method. Should be unique.
				$this->method_title       = 'OCA';  // Title shown in admin
				$this->method_description = __('Envios con OCA','woocommerce'); // Description shown in admin
				$this->title = __('Envío con OCA', 'oca');
				$this->instance_id = absint( $instance_id );
				$this->supports             = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal'
				);
				// Definimos la configuración
				$this->init();

				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );				
			}			 

			/**
			 * Inicialización de las opciones
			 *
			 * @access public
			 * @return void
			 */
			function init() {
				$this->form_fields     = array(); // No hay config global, solo de instancia
				$this->instance_form_fields =  array(
                    'username' => array(
							'title' => __( 'Usuario de OCA', 'woocommerce' ),
							'default'     => '',
                            'type' => 'text'
                    ),
                    'password' => array(
							'title' => __( 'Password', 'woocommerce' ),
							'default'     => '',
                            'type' => 'password'
                    ),
                    'nrocuenta' => array(
							'title' => __( 'Número de cuenta', 'woocommerce' ),
							'default'     => '',
                            'type' => 'text'
					),
					'nombre' => array(
						'title' => __( 'Nombre de contacto', 'woocommerce' ),
						'default'     => '',
						'type' => 'text'
					),
					'nombre_empresa' => array(
							'title' => __( 'Nombre empresa', 'woocommerce' ),
							'default'     => '',
							'type' => 'text'
					),
                    'calle' => array(
							'title' => __( 'Calle', 'woocommerce' ),
							'default'     => '',
                            'type' => 'text'
                    ),
                    'nro' => array(
							'title' => __( 'Número', 'woocommerce' ),
							'default'     => '',
                            'type' => 'text'
                    ),
                    'piso' => array(
							'title' => __( 'Piso (opcional)', 'woocommerce' ),
							'default'     => '',
							'type' => 'text',
                    ),
                    'dpto' => array(
							'title' => __( 'Departamento (opcional)', 'woocommerce' ),
							'default'     => '',
							'type' => 'text',
                    ),
                    'cp' => array(
							'title' => __( 'Código Postal', 'woocommerce' ),
							'default'     => '',
							'type' => 'text',
                    ),
                    'localidad' => array(
							'title' => __( 'Localidad', 'woocommerce' ),
							'default'     => '',
							'type' => 'text',
                    ),
                    'provincia' => array(
							'title' => __( 'Provincia', 'woocommerce' ),
							'default'     => '',
							'type' => 'text',
                    ),
                    'email' => array(
							'title' => __( 'Email de contacto', 'woocommerce' ),
							'default'     => '',
							'type' => 'text',
                    ),
					'idfranjahoraria' => array(
						'title'       => 'Franja horaria',
						'type'        => 'select',
						'default'     => '',
						'desc_tip'    => true,
						'options'     => array(
							'1' => '8 a 17',
							'2' => '8 a 12',
							'3' => '14 a 17'
							)
						),
					'idcentroimposicionorigen' => array(
							'title' => __( '<a target="_blank" href="oca-sucursales">ID Sucursal de Origen</a>', 'woocommerce' ),
							'default'     => '',
							'type' => 'text',
					),
                    'cuit' => array(
							'title' => __( 'CUIT', 'woocommerce' ),
							'default'     => '',						
							'type' => 'text',
							'desc_tip' => true,
							'description'        => 'Formato XX-XXXXXXXX-X',
					),
					'clase' => array(
						'title'       => 'Si existe la clase',
						'type'        => 'select',
						'default'     => '',
						'desc_tip'    => true,
						'options'     => array(
							'nada' => 'Seleccionar'
						)
					),
					'accion' => array(
						'title'       => 'Entonces',
						'type'        => 'select',
						'default'     => '',
						'desc_tip'    => true,
						'options'     => array(
							'nada' => 'No hacer nada',
							'desactivar_metodo' => 'Desactivar método de envio',
							'activar_metodo' => 'Activar método de envio',
							'envio_gratis' => 'Envio gratis'
						)
					),
					'envio_general_gratis' => array(
						'title' => __( 'Envío gratis', 'woocommerce' ),
						'type' => 'checkbox'
					),
					'debug' => array(
                            'title' => __( 'Debug log?', 'woocommerce' ),
                            'type' => 'checkbox'
					),
					'TituloOp1' => array(
						'title' => __( 'Operativa 1', 'textdomain' ), 
						'type' => 'title'
					),
					'nombre_operativa1' => array(
						'title'       => 'Nombre Operativa',
						'type'        => 'text',
						'description'        => 'Nombre visible para el comprador',
						'default'     => '',
						'desc_tip'    => true,
					),
					'tipo_operativa1' => array(
						'title'       => 'Operativa',
						'type'        => 'select',
						'default'     => '',
						'desc_tip'    => true,
						'options'     => array(
							'nada' => 'Seleccionar',
							'pap' => 'Puerta a puerta',
							'pas' => 'Puerta a sucursal',
							'sap' => 'Sucursal a puerta',
							'sas' => 'Sucursal a sucursal',
						)
					),
					'operativa1' => array(
						'title'       => 'Codigo Operativa',
						'type'        => 'text',
						'description'        => 'Dejar vacío si no se quiere usar',
						'default'     => '',
						'desc_tip'    => true,
					),
					'contrareembolso_operativa1' => array(
						'title' => __( 'Contrareembolso?', 'woocommerce' ),
						'type' => 'checkbox'
					),
					'TituloOp2' => array(
						'title' => __( 'Operativa 2', 'textdomain' ), 
						'type' => 'title'
					),
					'nombre_operativa2' => array(
						'title'       => 'Nombre Operativa',
						'type'        => 'text',
						'description'        => 'Nombre visible para el comprador',
						'default'     => '',
						'desc_tip'    => true,
					),
					'tipo_operativa2' => array(
						'title'       => 'Operativa',
						'type'        => 'select',
						'default'     => '',
						'desc_tip'    => true,
						'options'     => array(
							'nada' => 'Seleccionar',
							'pap' => 'Puerta a puerta',
							'pas' => 'Puerta a sucursal',
							'sap' => 'Sucursal a puerta',
							'sas' => 'Sucursal a sucursal',
						)
					),
					'operativa2' => array(
						'title'       => 'Codigo Operativa',
						'type'        => 'text',
						'description'        => 'Dejar vacío si no se quiere usar',
						'default'     => '',
						'desc_tip'    => true,
					),
					'contrareembolso_operativa2' => array(
						'title' => __( 'Contrareembolso?', 'woocommerce' ),
						'type' => 'checkbox'
					),
					'TituloOp3' => array(
						'title' => __( 'Operativa 3', 'textdomain' ), 
						'type' => 'title'
					),
					'nombre_operativa3' => array(
						'title'       => 'Nombre Operativa',
						'type'        => 'text',
						'description'        => 'Nombre visible para el comprador',
						'default'     => '',
						'desc_tip'    => true,
					),
					'tipo_operativa3' => array(
						'title'       => 'Operativa',
						'type'        => 'select',
						'default'     => '',
						'desc_tip'    => true,
						'options'     => array(
							'nada' => 'Seleccionar',
							'pap' => 'Puerta a puerta',
							'pas' => 'Puerta a sucursal',
							'sap' => 'Sucursal a puerta',
							'sas' => 'Sucursal a sucursal',
						)
					),
					'operativa3' => array(
						'title'       => 'Codigo Operativa',
						'type'        => 'text',
						'description'        => 'Dejar vacío si no se quiere usar',
						'default'     => '',
						'desc_tip'    => true,
					),
					'contrareembolso_operativa3' => array(
						'title' => __( 'Contrareembolso?', 'woocommerce' ),
						'type' => 'checkbox'
					),
					'TituloOp4' => array(
						'title' => __( 'Operativa 4', 'textdomain' ), 
						'type' => 'title'
					),
					'nombre_operativa4' => array(
						'title'       => 'Nombre Operativa',
						'type'        => 'text',
						'description'        => 'Nombre visible para el comprador',
						'default'     => '',
						'desc_tip'    => true,
					),
					'tipo_operativa4' => array(
						'title'       => 'Operativa',
						'type'        => 'select',
						'default'     => '',
						'desc_tip'    => true,
						'options'     => array(
							'nada' => 'Seleccionar',
							'pap' => 'Puerta a puerta',
							'pas' => 'Puerta a sucursal',
							'sap' => 'Sucursal a puerta',
							'sas' => 'Sucursal a sucursal',
						)
					),
					'operativa4' => array(
						'title'       => 'Codigo Operativa',
						'type'        => 'text',
						'description'        => 'Dejar vacío si no se quiere usar',
						'default'     => '',
						'desc_tip'    => true,
					),
					'contrareembolso_operativa4' => array(
						'title' => __( 'Contrareembolso?', 'woocommerce' ),
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
			private function fix_format( $value ) {
				$value = str_replace( ',', '.', $value );

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
			public function calculate_shipping( $package = array() ) {
				$productos = array();
				if($this->get_instance_option('debug') === 'yes'){
					$log = new WC_Logger();		
					$log->add( 'oca', " ====== Inicio de cálculo de precio ======");						
				}
				$envio_gratis = $this->get_instance_option('envio_general_gratis');
				$accion = $this->verificar_clases($productos);

				$peso_total = $precio_total = 0;
				$hay_producto_cero = false;
				foreach($productos as $producto){
					$peso_total += $producto['peso'];
					$largo_total += $producto['largo'];
					$ancho_total += $producto['ancho'];
					$alto_total += $producto['alto'];
					if($producto['peso'] == 0 || $producto['peso'] == '' || $producto['alto'] == 0 || $producto['alto'] == '' || $producto['largo'] == 0 || $producto['largo'] == '' || $producto['ancho'] == 0 || $producto['ancho'] == '' ){
						$hay_producto_cero = true;
						$producto_cero = $producto;
						break;
					}
				}			
				if($hay_producto_cero || $peso_total === '' || $largo_total === '' || $alto_total === '' || $ancho_total === ''){
					if($this->get_instance_option('debug') === 'yes'){
						$log = new WC_Logger();
						if(isset($producto_cero)){
							$log->add('oca','Detectado producto con malas dimensiones o peso: ');
							$log->add('oca', print_r($producto_cero,true));
						}else{
							$log->add('oca','Detectado dimension/peso 0');
						}
					}
					return;
				}

				
				if($accion === 'envio_gratis' || $envio_gratis === 'yes'){

					$this->addRate('gratis');

				}else if($accion === 'activar_metodo' || $accion === 'nada'){

					$this->cargar_dependencias();
					$medidas_totales = $this->calcular_medidas($productos);
					if($medidas_totales['volumen'] === -1){
						if($this->get_instance_option('debug') === 'yes'){
							$log = new WC_Logger();		
							$log->add( 'oca', "Medidas incorrectas, producto muy pequeño");						
						}
						return;
					}
					$operativas = $this->cargar_operativas();
					foreach($operativas as $nombre_operativa => $cod_operativa){
						$nombre = explode(" ", $nombre_operativa);
						$operativa_seleccionada = array_shift($nombre);
						$tipo_operativa = array_shift($nombre);
						$contrareembolso = array_shift($nombre);
						$nombre = implode(" ", $nombre);
						if($this->get_instance_option('debug') === 'yes'){
							$log = new WC_Logger();		
							$log->add( 'oca', "Nombre: ".$nombre." | Operativa Seleccionada: ".$operativa_seleccionada." | Tipo Operativa: ".$tipo_operativa);						
						}
						$oca = new Oca($this->get_instance_option('cuit'), $cod_operativa);
						if(WC()->session->get('cp_sucursal_oca') !== '' && WC()->session->get('cp_sucursal_oca') !== NULL && ($tipo_operativa == 'pas' || $tipo_operativa == 'sas' )){
							if($this->get_instance_option('debug') === 'yes'){
								$log = new WC_Logger();		
								$log->add( 'oca', "Hay CP en la sesion: ".WC()->session->get('cp_sucursal_oca')." Y el tipo de operativa es: ".$tipo_operativa);						
							}
							$tarifa = $oca->tarifarEnvioCorporativo($medidas_totales['peso'], $medidas_totales['volumen'], $this->get_instance_option('cp'), WC()->session->get('cp_sucursal_oca'), count($productos), 0);					
						}else if($tipo_operativa == 'pas' || $tipo_operativa == 'sas'){
							$tarifa[0] = array(
								'Total' => 0
							);
							if($this->get_instance_option('debug') === 'yes'){
								$log = new WC_Logger();		
								$log->add( 'oca', "No hay CP en la sesion y el tipo de operativa es: ".$tipo_operativa." Por lo tanto usamos precio gratis");						
							}
						}else{
							$cp = preg_replace( '/[^0-9]/', '', WC()->customer->get_shipping_postcode() );
							$tarifa = $oca->tarifarEnvioCorporativo($medidas_totales['peso'], $medidas_totales['volumen'], $this->get_instance_option('cp'), $cp, count($productos), 0);					
							if($this->get_instance_option('debug') === 'yes'){
								$log = new WC_Logger();		
								$log->add( 'oca', "Se toma el CP del usuario: ".$cp." Y calculamos el precio: ".print_r($tarifa,true));						
							}
						}
						if(! isset($tarifa[0]['error'])){
							if($contrareembolso === 'yes'){
								$precio = number_format($tarifa[0]['Total'], 2);
								if($precio == 0){
									$this->addRate('',0, $nombre.' (Pago a destino)', $operativa_seleccionada, $tipo_operativa, $tarifa[0]['Total'], 'yes');								
								}else{
									$this->addRate('',0, $nombre.' (Pago a destino - $'.$precio.')', $operativa_seleccionada, $tipo_operativa, $tarifa[0]['Total'], 'yes');
								}
								WC()->session->set('precio_oca_'.$tipo_operativa.'_'.$operativa_seleccionada.'_'.$contrareembolso, $precio);								
							}else{
								$this->addRate('',$tarifa[0]['Total'], $nombre, $operativa_seleccionada, $tipo_operativa, $tarifa[0]['Total'], 'no');
							}
						}else if($this->get_instance_option('debug') === 'yes'){
							$log = new WC_Logger();		
							$log->add( 'oca', "Hubo un error al calcular el precio del envío: ".$tarifa[0]['error']);	
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
			public function verificar_clases(&$productos){
				$accion = $this->get_instance_option('accion');
				$clase = $this->get_instance_option('clase');
				$items = WC()->cart->get_cart();
				
				if($accion !== 'nada' && $clase !== 'nada'){
					$condicion = false;
					$contador = 0;
					foreach($items as $item => $values) { 
				
						$product =  wc_get_product( $values['data']->get_id());
						if($clase === $product->get_shipping_class()){
							$condicion = true;
							$contador++;
						}
						$producto = array();
						if ( method_exists( $product, 'get_height' ) ) {
							$peso = $product->get_weight();
							if($peso < 0){
								$peso = $peso * -1;
							}
							$producto = array(
								"peso" => floatval(wc_get_dimension( $this->fix_format( $peso ), 'kg' )),
								"largo" => floatval(wc_get_dimension( $this->fix_format( $product->get_length() ), 'm' )),
								"ancho" => floatval(wc_get_dimension( $this->fix_format( $product->get_width() ), 'm' )),
								"alto" => floatval(wc_get_dimension( $this->fix_format( $product->get_height() ), 'm' ))
							);
						} else {
							$peso = $product->weight;
							if($peso < 0){
								$peso = $peso * -1;
							}
							$producto = array(
								"peso" => floatval(wc_get_dimension( $this->fix_format( $peso ), 'kg' )),
								"largo" => floatval(wc_get_dimension( $this->fix_format( $product->length ), 'm' )),
								"ancho" => floatval(wc_get_dimension( $this->fix_format( $product->width ), 'm' )),
								"alto" => floatval(wc_get_dimension( $this->fix_format( $product->height ), 'm' ))
							);
						}
						for ($x = 0; $x < $values['quantity']; $x++) {
							array_push($productos,$producto);
						}
					}
					// Si hay envio gratis para X producto, y en el carrito hay otros items, entonces se desactiva el envio gratis
					if($accion === 'envio_gratis' && $contador !== count($items)){
						$condicion = false;
					}
				}else{
					foreach($items as $item => $values) { 
						$product =  wc_get_product( $values['data']->get_id());
						$producto = array();
						if ( method_exists( $product, 'get_height' ) ) {
							$peso = $product->get_weight();
							if($peso < 0){
								$peso = $peso * -1;
							}
							$producto = array(
								"peso" => floatval(wc_get_weight( $this->fix_format( $peso ), 'kg' )),
								"largo" => floatval(wc_get_dimension( $this->fix_format( $product->get_length() ), 'm' )),
								"ancho" => floatval(wc_get_dimension( $this->fix_format( $product->get_width() ), 'm' )),
								"alto" => floatval(wc_get_dimension( $this->fix_format( $product->get_height() ), 'm' ))
							);
						} else {
							$peso = $product->weight;
							if($peso < 0){
								$peso = $peso * -1;
							}
							$producto = array(
								"peso" => floatval(wc_get_weight( $this->fix_format( $peso ), 'kg' )),
								"largo" => floatval(wc_get_dimension( $this->fix_format( $product->length ), 'm' )),
								"ancho" => floatval(wc_get_dimension( $this->fix_format( $product->width ), 'm' )),
								"alto" => floatval(wc_get_dimension( $this->fix_format( $product->height ), 'm' ))
							);
						}
					
						for ($x = 0; $x < $values['quantity']; $x++) {
							array_push($productos,$producto);
						}
					}
					$condicion = false;
				}
				

				if($condicion){
					return $accion;
				}else if(!$condicion && $accion === 'activar_metodo'){
					return 'desactivar_metodo';
				}else{
					return 'nada';
				}
			}

			// =========================================================================
			/**
			 * function cargar_operativas
			 *
			 * @access private
			 * @return array
			 */
			private function cargar_operativas(){
				$res = array();
				$res['operativa1 '.$this->get_instance_option('tipo_operativa1').' '.$this->get_instance_option('contrareembolso_operativa1').' '.$this->get_instance_option('nombre_operativa1')] = $this->get_instance_option('operativa1');
				$res['operativa2 '.$this->get_instance_option('tipo_operativa2').' '.$this->get_instance_option('contrareembolso_operativa2').' '.$this->get_instance_option('nombre_operativa2')] = $this->get_instance_option('operativa2');
				$res['operativa3 '.$this->get_instance_option('tipo_operativa3').' '.$this->get_instance_option('contrareembolso_operativa3').' '.$this->get_instance_option('nombre_operativa3')] = $this->get_instance_option('operativa3');
				$res['operativa4 '.$this->get_instance_option('tipo_operativa4').' '.$this->get_instance_option('contrareembolso_operativa4').' '.$this->get_instance_option('nombre_operativa4')] = $this->get_instance_option('operativa4');
				$res = array_filter($res);
				return $res;		
			}



			
			// =========================================================================
			/**
			 * function calcular_medidas
			 *
			 * @access private
			 * @return array
			 */
			private function calcular_medidas($productos = array()){
				$res = array(
					'peso' => 0,
				);
				$largo = $ancho = $alto = 0;
				foreach($productos as $producto){						
					$res['peso'] += $producto['peso'];
					$largo += $producto['largo'];
					$ancho += $producto['ancho'];
					$alto += $producto['alto'];
				}
				$res['volumen'] = $largo * $ancho * $alto;
				
				$res['peso'] = number_format($res['peso'], 2);
				if($res['volumen'] < 0.000001 ){
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
			private function cargar_dependencias(){
				require_once 'oca/autoload.php';
			}


			// =========================================================================
			/**
			 * funcion addRate.
			 * 
			 * Agrega el metodo de envio al carrito
			 *
			 * @access public
			 * @param string $tipo, $precio, $servicio
			 * @return array
			 */
			public function addRate($tipo = '', $precio = '', $nombre = '' , $operativa = '', $tipo_op = '', $precio_declarado = '', $cr){
				if($tipo === 'gratis'){

					$rate = array(
						'id' => 'oca '.$tipo_op,
						'label' => $this->get_instance_option('nombre')." Gratuito",
						'cost' => 0,
						'calc_tax' => 'per_item'
					);

				}else if($precio !== ''){
				
					$rate = array(
						'id' => "oca ".$tipo_op." ".$this->get_instance_option_key()." ".$operativa." ".$cr,
						'label' => $nombre,
						'cost' => $precio,
						'calc_tax' => 'per_item'
					);					

				}
				$this->add_rate( $rate );		
			}

		}
	}
}
add_action( 'woocommerce_shipping_init', 'woo_oca_envios_oca_init' );