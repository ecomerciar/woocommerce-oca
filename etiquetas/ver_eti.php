<?php
require(dirname(__FILE__) . '/../../../../wp-load.php');
if(file_exists('oca-etiqueta-'.$_GET['id'].'.pdf')){
	$file = 'oca-etiqueta-'.$_GET['id'].'.pdf';
	$filename = $file;
	header('Content-type: application/pdf');
	header('Content-Disposition: inline; filename="' . $filename . '"');
	header('Content-Transfer-Encoding: binary');
	header('Accept-Ranges: bytes');
	@readfile($file);
} else {
	$post_data = array(
		'idOrdenRetiro' => $_GET['id'],
		'nroEnvio' => '',
		'logisticaInversa' => 'false'
	);
	$url = 'http://webservice.oca.com.ar/oep_tracking/Oep_Track.asmx/GetPdfDeEtiquetasPorOrdenOrNumeroEnvio';
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
	$response = explode(">",$response)[2];
	$response = explode("<",$response)[0];

	$pdf_codificado = $response;
	$pdf_decod = base64_decode($pdf_codificado); //Lo decodificamos

	header('Content-Type: application/pdf'); //Preparamos la página para mostrar un pdf
	echo $pdf_decod; //Finalmente mostramos nuestro pdf decodificado
	// Creamos nuestro archivo en el servidor
	$pdf = fopen ('oca-etiqueta-'.$_GET['id'].'.pdf','w');
	fwrite ($pdf,$pdf_decod);
	fclose ($pdf);
}