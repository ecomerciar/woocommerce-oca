Clase PHP para OCA Express Pak.

### Con Composer
Desde línea de comandos
```
composer require juanchorossi/oca-php-api:dev-master
```

Como dependencia en tu proyecto a través de composer.json

```json
{
    "require": {
        "juanchorossi/oca-php-api": "dev-master"
    }
}
```
### Uso

```php
### Unicamente para tarifar un envío requiere un número de operativa y CUIT válidos,
### autorizados por OCA para operar (at. al cliente 0800-999-7700). 
### Otros métodos no requieren esta autorización

$oca 	= new Oca($cuit = '20-12345678-7', $operativa = 12345);
$price 	= $oca->tarifarEnvioCorporativo(1, 1, 1640, 1006, 1, 0);
$envios = $oca->listEnvios($fechaDesde = '08-08-2015', $fechaHasta = '13-08-2015');

print_r ($envios);
print_r ($price);
```

Para más información y más documentación:
1) [http://webservice.oca.com.ar/oep_tracking](Web Service)
2) [https://www4.oca.com.ar/ocaepak/help/mododeuso.asp](Modo de Uso)

* Los siguientes métodos están disponibles en el web service original de OCA. 

	* **AnularOrdenGenerada**
		- Anulación de Orden de Retiro u Orden de Admisión
	* **GetCentroCostoPorOperativa**
		- Devuelve los Centros de Costo del cliente habilitas para ser utilizadon con la operativa indicada
	* **GetCentrosImposicion**
		- Devuelve todos los Centros de Imposición existentes
	* **GetCentrosImposicionPorCP**
		- Devuelve todos los Centros de Imposición existentes cercanos al CP
	* **GetEnviosUltimoEstado**
		- Detalle envíos entre fechas
	* **IngresoOR**
		- Ingreso de archivo de OR
	* **List_Envios**
		- Dado el CUIT del cliente con un rango de fechas se devuelve una lista con todos los Envíos realizados en dicho período
	* **Tarifar_Envio_Corporativo**
		- Tarifar un Envío Corporativo
	* **Tracking_OrdenRetiro**
		- Dado un nro. de Orden de Retiro, devuelve todas sus guías
	* **Tracking_Pieza**
		- Dado un envío se devuelven todos los eventos  