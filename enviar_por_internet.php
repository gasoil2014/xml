<?php
include 'blocks/header.php';
include 'configuracion.php';
?>
<?php
if ($_GET['proceso'] == "santander") {
	if (isset($_GET['archivo'])) { //Verifica ruta del archivo
	    $xmlFilePath = $_GET['archivo'];
	
		if (file_exists($xmlFilePath)){ //Verifica existencia del archivo
			// Obtener el contenido del archivo XML
			$xmlContent = file_get_contents($xmlFilePath); //Verifica contenido del archivo
	
			if(!empty($xmlContent)){
				//URL de destino para enviar el archivo
				$url = $urlProd; // Cambia la URL a la que corresponde
	
				$ch = curl_init();
	
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlContent);
				curl_setopt($ch, CURLOPT_USERPWD, $curlUserProd . ':' . $curlPassProd);
	
				$headers = array();
				$headers[] = 'Content-Type: text/xml;charset=UTF-8';
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_STDERR, $verbose = fopen("php://temp", "rw+"));
	
				$result = curl_exec($ch);
	
				if (curl_errno($ch)) {
					echo "Error: ".curl_error($ch);
					if (strpos(curl_error($ch), "Could not resolve host") !== false) {
						echo "<h3>El servidor no esta disponible, consulte con el administrador</h3>";
						$logString = "Error: Santander ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- ".curl_error($ch)."\n\n";
					}
	
				} else {
					if (strpos($result, "Unauthorized") !== false){
						echo "<h3>Se ha producido un error al enviar la informacion, consulte con el administrador</h3>";
						$logString = "Error: Santander ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- Acceso no autorizado\n\n";
					} else if(strpos($result, "Forbidden") !== false){
						echo "<h3>Se ha producido un error al enviar la informacion, consulte con el administrador</h3>";
						$logString = "Error: Santander ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- Acceso denegado\n\n";
					} else {
						// Convierte el string XML en un objeto SimpleXMLElement
						$xml = simplexml_load_string($result);
	
						// Crea un objeto DOMDocument para formatear el XML
						$dom = new DOMDocument('1.0');
						$dom->preserveWhiteSpace = false;
						$dom->formatOutput = true;
	
						// Importa el SimpleXMLElement al DOMDocument
						$dom->loadXML($xml->asXML());
	
						$xmlString = highlight_string($dom->saveXML(), true);
	
						// Verifica si el XML contiene la palabra específica
						if (strpos($xmlString, 'OK/OK') ) {
							echo "<h3> La validacion del resultado fue correcta</h3>";
							$processingTime =  substr($xmlString, 189, 15);
							echo '<h6> El tiempo de procesamiento fue de: '.$processingTime.'</h6>';
							$logString = "Proceso: Santander -- Fecha: ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath."\nRespuesta: ".$result."\n";
							//CODIGO PARA LEER XML GENERADO POR EL EXCEL Y MOSTRAR MAS DATOS EN LA TABLA
							//Leer archivo xml generado a partir del excel y extraer datos
							$excelXml = fopen($xmlFilePath,'r');
							$excelTexto = fread($excelXml, filesize($xmlFilePath));
							$excelTexto = new SimpleXMLElement($excelTexto);
							$lid = substr($excelTexto->CstmrCdtTrfInitn->GrpHdr->MsgId, 0, 3);
							if (!is_numeric($lid)){
									$lid = substr(substr($excelTexto->CstmrCdtTrfInitn->GrpHdr->MsgId, -17), 0 , 3);
							}
							$msgId = substr($excelTexto->CstmrCdtTrfInitn->GrpHdr->MsgId,0 , -14);
							$date = new DateTime($excelTexto->CstmrCdtTrfInitn->PmtInf->ReqdExctnDt);
							$date = $date->format("M\, d");
							$company = $excelTexto->CstmrCdtTrfInitn->GrpHdr->InitgPty->Nm;
							$pagos = $excelTexto->CstmrCdtTrfInitn->GrpHdr->NbOfTxs;
							$totalPagos = 0;
							for ($i=0; $i < $pagos ; $i++) {
									$totalPagos += $excelTexto->CstmrCdtTrfInitn->PmtInf->CdtTrfTxInf[$i]->Amt->InstdAmt;
							}
							$totalPagos = number_format($totalPagos, 2, ',', '.');
	
							$datos = 'Data: <table class="table text-center"><thead><tr><th scope="col">LID</th><th scope="col">Pagos</th><th scope="col">$ Total</th><th scope="col">Company</th><th scope="col">Message ID</th><th scope="col"                           >Fecha</th></tr></thead><tbody><tr><td>'.$lid.'</td><td>'.$pagos.'</td><td>$'.$totalPagos.'</td><td>'.$company.'</td><td>'.$msgId.'</td><td>'.$date.'</td></tr></tbody></table>'."\n\n";
							$logString = $logString.$datos;
	
						} else {
							echo "<h3> La validacion del resultado fue incorrecta</h3 ";
							$xmlString = $result ;
							$logString = "Error: Santander ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- ".$result."\n\n";
						}
	
						echo '<div class="bd-example mt-3 border-0">';
						echo    '<div class="accordion" id="accordionExample">';
						echo        '<div class="accordion-item">';
						echo            '<h4 class="accordion-header">';
						echo                '<button class="accordion-button collapsed" type="button"';
						echo                ' data-bs-toggle="collapse" data-bs-target="#collapseOne"';
						echo                'aria-expanded="false" aria-controls="collapseOne">';
						echo                    'Ver respuesta';
						echo                '</button>';
						echo            '</h4>';
						echo            '<div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample" style="">';
						echo                '<div class="accordion-body">';
						echo                     '<pre>';
						echo                      $xmlString;
						echo                     '</pre>';
						echo                 '</div>';
						echo             '</div>';
						echo        '</div>';
						echo    '</div>';
						echo '</div>';
						echo '<div class="d-grid gap-2 mt-3">';
						echo '<a href="'.$xmlFilePath.'" download target="_blank"';
						echo     'class="btn btn-success">Descargar Archivo XML<br/>'.$xmlFilePath;
						echo '</a>';
						echo '</div>';
	
	
					}
					curl_close($ch);
				}
	
	
			} else {
				echo '<div class="d-flex flex-column align-items-center">';
				echo	'<h3 class="mb-5">Ocurrio un error, archivo XML vacio</h3>';
				echo	'<a href="index.php" class="btn btn-primary">Volver al inicio</a>';
				echo '</div>';
				$logString = "Error: Santander ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- Archivo XML vacio\n\n";
			}
	    } else {
			echo '<div class="d-flex flex-column align-items-center">';
			echo	'<h3 class="mb-5">Ocurrio un error, archivo XML inexistente</h3>';
			echo	'<a href="index.php" class="btn btn-primary">Volver al inicio</a>';
			echo '</div>';
			$logString = "Error: Santander ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- Archivo XML inexistente\n\n";
	    }
	} else {
		echo '<div class="d-flex flex-column align-items-center">';
		echo	'<h3 class="mb-5">Ocurrio un error, archivo no especificado</h3>';
		echo	'<a href="index.php" class="btn btn-primary">Volver al inicio</a>';
		echo '</div>';
		$logString = "Error: Santander ".date("Y-m-d\TH:i:s")." -- Archivo: Ruta no especificada\n\n";
	}

	//Ruta al archivo de log
	$rutaArchivoLog = 'generadorxml.log';
	$logsActuales = file_get_contents($rutaArchivoLog);
	$logString = $logString.$logsActuales;
	file_put_contents($rutaArchivoLog, $logString);

} elseif ($_GET['proceso'] == "contable") {
	$archivosEnviar = $_POST['archivos'];
	if (isset($archivosEnviar)) { 
		//Un curl por cada archivo
		foreach ($archivosEnviar as $key => $archivo) {
			$xmlFilePath = $archivo;
			if (file_exists($xmlFilePath)){ //Verifica existencia del archivo
				// Obtener el contenido del archivo XML
				$xmlContent = file_get_contents($xmlFilePath); //Verifica contenido del archivo
		
				if(!empty($xmlContent)){
					//URL de destino para enviar el archivo
					$url = $urlTest; // Cambia la URL a la que corresponde
		
					$ch = curl_init();
		
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlContent);
					curl_setopt($ch, CURLOPT_USERPWD, $curlUserTest . ':' . $curlPassTest);
		
					$headers = array();
					$headers[] = 'Content-Type: text/xml;charset=UTF-8';
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_VERBOSE, true);
					curl_setopt($ch, CURLOPT_STDERR, $verbose = fopen("php://temp", "rw+"));
		
					$result = curl_exec($ch);
					if (curl_errno($ch)) {
						echo "Error: ".curl_error($ch);
						if (strpos(curl_error($ch), "Could not resolve host") !== false) {
							echo "<h3>El servidor no esta disponible, consulte con el administrador</h3>";
							$logString = "Error: Contable ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- ".curl_error($ch)."\n\n";
						}
		
					} else {
						if (strpos($result, "Unauthorized") !== false){
							echo "<h3>Se ha producido un error al enviar la informacion, consulte con el administrador</h3>";
							$logString = "Error: Contable ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- Acceso no autorizado\n\n";
							$xmlString = $result;
						} else if(strpos($result, "Forbidden") !== false){
							echo "<h3>Se ha producido un error al enviar la informacion, consulte con el administrador</h3>";
							$logString = "Error: Contable ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- Acceso denegado\n\n";
							$xmlString = $result;
						} else {
							// Verifica si el XML contiene la palabra específica
							if (strpos($result, 'OK/OK') ) {
								// Convierte el string XML en un objeto SimpleXMLElement
								$xml = simplexml_load_string($result);
			
								// Crea un objeto DOMDocument para formatear el XML
								$dom = new DOMDocument('1.0');
								$dom->preserveWhiteSpace = false;
								$dom->formatOutput = true;
			
								// Importa el SimpleXMLElement al DOMDocument
								$dom->loadXML($xml->asXML());
			
								$xmlString = highlight_string($dom->saveXML(), true);
		
								echo "<h3> La validacion del resultado fue correcta</h3>";
								echo "<h6>".$xmlFilePath."</h6>";
								$processingTime =  substr($xmlString, 189, 15);
								echo '<h6> El tiempo de procesamiento fue de: '.$processingTime.'</h6>';
								$logString = "Proceso: Contable -- Fecha: ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath."\nRespuesta: ".$result."\n\n";
		
							} else {
								echo "<h3> La validacion del resultado fue incorrecta</h3>";
								echo "<h6>".$xmlFilePath."</h6>";
								$xmlString = $result;
								$logString = "Error: Contable ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- ".$result."\n\n";
							}
						}	

						echo '<div class="bd-example mt-3 mb-2 border-0">';
						echo    '<div class="accordion" id="accordionExample">';
						echo        '<div class="accordion-item">';
						echo            '<h4 class="accordion-header">';
						echo                '<button class="accordion-button collapsed" type="button"';
						echo                ' data-bs-toggle="collapse" data-bs-target="#'.$key.'"';
						echo                'aria-expanded="false" aria-controls="collapseOne">';
						echo                    'Ver respuesta';
						echo                '</button>';
						echo            '</h4>';
						echo            '<div id="'.$key.'" class="accordion-collapse collapse" data-bs-parent="#accordionExample" style="">';
						echo                '<div class="accordion-body">';
						echo                     '<pre>';
						echo                      $xmlString;
						echo                     '</pre>';
						echo                 '</div>';
						echo             '</div>';
						echo        '</div>';
						echo    '</div>';
						echo '</div>';
						echo '<div class="d-grid gap-2 mb-5">';
						echo '<a href="'.$xmlFilePath.'" download target="_blank"';
						echo '	class="btn btn-success">Descargar Archivo XML<br/>'.$xmlFilePath;
						echo '</a>';
						echo '</div>';
                          
						curl_close($ch);
					}
		
				} else {
					echo '<div class="mb-5">';
					echo	'<h3>Ocurrio un error, archivo XML vacio</h3>';
					echo 	'<h6>'.$xmlFilePath.'</h6>';
					echo '</div>';
					$logString = "Error: Contable ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- Archivo XML vacio\n\n";
				}
			} else {
				echo '<div class="mb-5">';
				echo	'<h3>Ocurrio un error, archivo XML inexistente</h3>';
				echo 	'<h6>'.$xmlFilePath.'</h6>';
				echo '</div>';
				$logString = "Error: Contable ".date("Y-m-d\TH:i:s")." -- Archivo: ".$xmlFilePath." -- Archivo XML inexistente\n\n";
			}

			// Ruta al archivo de log
			$rutaArchivoLog = 'generadorxml.log';
			$logsActuales = file_get_contents($rutaArchivoLog);
			$logString = $logString.$logsActuales;
			file_put_contents($rutaArchivoLog, $logString);
		}
	} else {
		echo '<div class="d-flex flex-column align-items-center">';
		echo	'<h3 class="mb-5">Ocurrio un error, archivos no especificado</h3>';
		echo	'<a href="index.php" class="btn btn-primary">Volver al inicio</a>';
		echo '</div>';
		$logString = "Error: Contable ".date("Y-m-d\TH:i:s")." -- Archivo: Rutas no especificada\n\n";

		// Ruta al archivo de log
		$rutaArchivoLog = 'generadorxml.log';
		$logsActuales = file_get_contents($rutaArchivoLog);
		$logString = $logString.$logsActuales;
		file_put_contents($rutaArchivoLog, $logString);
	}
}
?>
<?php include 'blocks/footer.php'?>
