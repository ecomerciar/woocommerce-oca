<?php
/**
* $Id$
*
* Copyright (c) 2015, Juancho Rossi.  All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
*   this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright
*   notice, this list of conditions and the following disclaimer in the
*   documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
* AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
* ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
* LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
* CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
* SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
* INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
* CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
* ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* OCA Express y OCA Express Pak son propiedad de Organización Coordinadora Argentina (OCA)
*/
/**
* OCA PHP API Class
*
* @link https://github.com/juanchorossi/OCA-PHP-API
* @version 0.1.1
*/
class Oca
{
	const VERSION				= '0.1.1';
	protected $webservice_url	= 'webservice.oca.com.ar';
	const CURL_DEFAULT_TIMEOUT = 360;
	const FRANJA_HORARIA_8_17HS = 1;
	const FRANJA_HORARIA_8_12HS = 2;
	const FRANJA_HORARIA_14_17HS = 3;
	private $Cuit;
	private $Operativa;
	private $curl_opt_arr;
	// ========================================================================
	public function __construct($cuit = '', $operativa = '')
	{
		$this->Cuit 		= trim($cuit);
		$this->Operativa 	= trim($operativa);
		$this->setCurlOptArr(array(	
									CURLOPT_RETURNTRANSFER	=> TRUE,
									CURLOPT_HEADER			=> FALSE,
									CURLOPT_USERAGENT		=> $this->setUserAgent(),
									CURLOPT_CONNECTTIMEOUT	=> 5,
									CURLOPT_TIMEOUT			=> Self::CURL_DEFAULT_TIMEOUT,
									// CURLOPT_POST			=> TRUE,
									// CURLOPT_POSTFIELDS		=> http_build_query($_query_string),
									// CURLOPT_URL				=> "{$this->webservice_url}/epak_tracking/Oep_TrackEPak.asmx/Tarifar_Envio_Corporativo",
									CURLOPT_FOLLOWLOCATION	=> TRUE
									));
	}
	/**
	*	obtiene las opciones de cUrl por defecto
	*	@return Array
	*/
	public function getCurlOptsArr()
	{
		return $this->curl_opt_arr;
	}
	/**
	*	Setea las opciones de cUrl por defecto
	*	@param Array $curl_arr
	*	@return void
	*/
	public function setCurlOptArr($curl_arr){
		$this->curl_opt_arr = $curl_arr;
	}
	public function getOperativa()
	{
		return $this->Operativa;
	}
	public function setOperativa($operativa)
	{
		$this->Operativa = $operativa;
	}
	public function getCuit($cuit)
	{
		return $this->Cuit;
	}
	public function setCuit($cuit)
	{
		$this->Cuit = $cuit;
	}
	// =========================================================================
	
	/**
	 * Sets the useragent for PHP to use
	 * 
	 * @return string
	 */
	public function setUserAgent()
	{
		return 'OCA-PHP-API ' . self::VERSION . ' - github.com/juanchorossi/OCA-PHP-API';
	}
	// =========================================================================
	/**
	 * Tarifar un Envío Corporativo
	 *
	 * @param string $PesoTotal
	 * @param string $VolumenTotal
	 * @param string $CodigoPostalOrigen
	 * @param string $CodigoPostalDestino
	 * @param string $CantidadPaquetes
	 * @param string $ValorDeclarado
	 * @return array $e_corp conteniendo el tipo de tarifador y el precio del envío.
	 */
	public function tarifarEnvioCorporativo($PesoTotal, $VolumenTotal, $CodigoPostalOrigen, $CodigoPostalDestino, $CantidadPaquetes, $ValorDeclarado)
	{
		$_query_string = array(	'PesoTotal'				=> floatval($PesoTotal),
								'VolumenTotal'			=> floatval($VolumenTotal),
								'CodigoPostalOrigen'	=> intval($CodigoPostalOrigen),
								'CodigoPostalDestino'	=> intval($CodigoPostalDestino),
								'CantidadPaquetes'		=> $CantidadPaquetes,
								'ValorDeclarado'		=> $ValorDeclarado,
								'Cuit'					=> $this->Cuit,
								'Operativa'				=> intval($this->Operativa)
							);		

		$_query_string_tipo = array(	'PesoTotal'		=> gettype($PesoTotal),
								'VolumenTotal'			=> gettype($VolumenTotal),
								'CodigoPostalOrigen'	=> gettype($CodigoPostalOrigen),
								'CodigoPostalDestino'	=> gettype($CodigoPostalDestino),
								'CantidadPaquetes'		=> gettype($CantidadPaquetes),
								'ValorDeclarado'		=> gettype($ValorDeclarado),
								'Cuit'					=> gettype($this->Cuit),
								'Operativa'				=> gettype($this->Operativa));							
		$ch = curl_init();
		
		$curl_opt_arr = $this->getCurlOptsArr();
		$curl_opt_arr[CURLOPT_POST] 		= true;
		$curl_opt_arr[CURLOPT_POSTFIELDS] 	= http_build_query($_query_string);
		$curl_opt_arr[CURLOPT_URL] 			= "{$this->webservice_url}/epak_tracking/Oep_TrackEPak.asmx/Tarifar_Envio_Corporativo";
		curl_setopt_array($ch, $curl_opt_arr);
		$dom = new DOMDocument();
		$res = curl_exec($ch);
		@$dom->loadXML($res);
		$xpath = new DOMXpath($dom);
		$e_corp = array();
		foreach (@$xpath->query("//NewDataSet/Table") as $envio_corporativo)
		{
			$e_corp[] = array(	'Tarifador'		=> $envio_corporativo->getElementsByTagName('Tarifador')->item(0)->nodeValue,
								'Precio'		=> $envio_corporativo->getElementsByTagName('Precio')->item(0)->nodeValue,
								'Ambito'		=> $envio_corporativo->getElementsByTagName('Ambito')->item(0)->nodeValue,
								'PlazoEntrega'	=> $envio_corporativo->getElementsByTagName('PlazoEntrega')->item(0)->nodeValue,
								'Adicional'		=> $envio_corporativo->getElementsByTagName('Adicional')->item(0)->nodeValue,
								'Total'			=> $envio_corporativo->getElementsByTagName('Total')->item(0)->nodeValue,
							);
		}
		foreach (@$xpath->query("//NewDataSet/Table1") as $envio_corporativo)
		{
			$e_corp[] = array(	'error'		=> $envio_corporativo->getElementsByTagName('Error')->item(0)->nodeValue	);
		}
		$e_corp['mensaje'] = $res;
		$e_corp['enviado'] = $_query_string;
		return $e_corp;
	}
	// =========================================================================
	/**
	 * Ingresa un envio al carrito de envios
	 *
	 * @param string $usuarioEPack: Usuario de ePak
	 * @param string $passwordEPack: Password de acceso a ePak
	 * @param string $xmlDatos: XML con los datos de Retiro, Entrega y características de los paquetes.
	 * @param boolean $confirmarRetiro: Si se envía False, el envío quedará alojado en el
	 *                                  Carrito de Envíos de ePak a la espera de la confirmación del mismo.
	 *                                  Si se envía True, la confirmación será instantánea.
	 * @return array $resumen
	 */
	public function ingresoORMultiplesRetiros($usuarioEPack, $passwordEPack, $xmlDatos, $confirmarRetiro = false)
	{
		$_query_string = array(
			'usr' => $usuarioEPack,
			'psw' => $passwordEPack,
			'xml_Datos' => $xmlDatos,
			'ConfirmarRetiro' => $confirmarRetiro ? 'true' : 'false',
			'ArchivoCliente' => '',
			'ArchivoProceso' => ''
			);
		$ch = curl_init();
		$curl_opt_arr = $this->getCurlOptsArr();
		// $curl_opt_arr[CURLOPT_POST] 		= true;
		$curl_opt_arr[CURLOPT_POSTFIELDS] 	= http_build_query($_query_string);
		$curl_opt_arr[CURLOPT_URL] 			= "{$this->webservice_url}/epak_tracking/Oep_TrackEPak.asmx/IngresoORMultiplesRetiros";
		curl_setopt_array($ch, $curl_opt_arr);
		$xml = curl_exec($ch);
		file_put_contents('ingresoORMultiplesRetiros.xml', $xml);
		$dom = new DOMDocument();
		@$dom->loadXml($xml);
		$xpath = new DOMXPath($dom);
		$xml_detalle_ingresos = @$xpath->query("//Resultado/DetalleIngresos ");
		$detalle_ingresos = array();
		foreach($xml_detalle_ingresos as $item)
		{
			$detalle_ingresos[] = array(
				'Operativa' => $item->getElementsByTagName('Operativa')->item(0)->nodeValue,
				'OrdenRetiro' => $item->getElementsByTagName('OrdenRetiro')->item(0)->nodeValue,
				'NumeroEnvio' => $item->getElementsByTagName('NumeroEnvio')->item(0)->nodeValue,
				'Remito' => $item->getElementsByTagName('Remito')->item(0)->nodeValue,
				'Estado' => $item->getElementsByTagName('Estado')->item(0)->nodeValue,
				'sucursalDestino' => $item->getElementsByTagName('sucursalDestino')->item(0)->nodeValue
				 );
		}

		foreach( @$xpath->query("//Errores/Error ") as $item)
		{
			$detalle_ingresos[] = array( 'error' => $item->getElementsByTagName('Descripcion')->item(0)->nodeValue	 );
		}

		return $detalle_ingresos;
	}
}