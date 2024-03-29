<?php

class ExtendedDOMElement extends DOMElement
{

    /**
     * Añade un hijo al elemento DOM actual.
     *
     * @param string $name
     *            El nombre del elemento hijo.
     * @param ?string $value
     *            El valor del elemento hijo, si lo hay.
     * @return DOMElement El elemento hijo recién creado.
     */
    public function addChild(string $name, ?string $value = null): DOMElement
    {
        // Verificar si el valor es null para evitar advertencias
        if ($value !== null) {
            $child = $this->ownerDocument->createElement($name, $value);
        } else {
            $child = $this->ownerDocument->createElement($name);
        }
        $this->appendChild($child);
        return $child;
    }
}

include 'blocks/header.php'?>
<?php $proceso = $_POST['radios'];?>
<script>
    function manejarCheckbox(checkboxId) {
      var miCheckbox = document.getElementById(checkboxId);
      
      miCheckbox.disabled = false;  // Habilitar el checkbox
      miCheckbox.click();           // Simular un clic en el checkbox
      miCheckbox.disabled = true;   // Restaurar el estado de deshabilitado
    }
  </script>
<h4 class="mb-5">Paso 2 - Procesamiento del archivo <?php print ($proceso) ? 'para <img class="center bi mb-b" height="25" role="img" aria-label="Bootstrap" src="assets/img/'.$proceso.'.png">':''?></h4>

<div class="list-group mb-5">
	<label class="list-group-item d-flex gap-3"> <input id="checkCargando"
		class="form-check-input flex-shrink-0" type="checkbox" value=""
		style="font-size: 1.375em;" disabled> <span
		class="pt-1 form-checked-content"> <strong>Cargando el archivo</strong>
	</span>
	</label> <label class="list-group-item d-flex gap-3"> <input
		id="checkValidando" class="form-check-input flex-shrink-0"
		type="checkbox" value="" style="font-size: 1.375em;" disabled> <span
		class="pt-1 form-checked-content"> <strong>Validando el archivo</strong>
	</span>
	</label> <label class="list-group-item d-flex gap-3"> <input
		id="checkProcesando" class="form-check-input flex-shrink-0"
		type="checkbox" value="" style="font-size: 1.375em;" disabled> <span
		class="pt-1 form-checked-content"> <strong>Procesando el archivo</strong>
	</span>
	</label> <label class="list-group-item d-flex gap-3"> <input
		id="checkGenerando" class="form-check-input flex-shrink-0"
		type="checkbox" value="" style="font-size: 1.375em;" disabled> <span
		class="pt-1 form-checked-content"> <strong>Generando el archivo</strong>
	</span>
	</label>
</div>

<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Defino las variables para utilizar luego
$nombrearchivoxml = ''; // C2: Nombre de archivo xml
$MsgId = ''; // C3: Encabezado del XML
$empresa = ''; // C4: Nombre de la empresa
$nroempresa = ''; // C5: Numero de empresa

$CreDtTm = date("Y-m-d\TH:i:s"); // Fecha actual en formato → YYYY-MM-DDTHH:MM:SS
$CreDtTm2 = date("YmdHis"); // Fecha actual en formato → YYYY-MM-DDTHH:MM:SS


$CtrlSum = 0; // Suma total
$error = 0;
$confidencial = 0;
$msgs = array();

// Validacion
if ($_SERVER["REQUEST_METHOD"] == "POST" && ! $error) {
    if ($_FILES["archivoExcel"]["error"] == UPLOAD_ERR_OK) {
        
        echo "<script>$(document).ready(function() {manejarCheckbox('checkCargando');});</script>";
       
        // Verifico proceso a realizar
        if ($proceso !== 'contable') {
            // Proceso para bancos

            $nombreArchivo = $_FILES["archivoExcel"]["name"];
            $rutaArchivo = $_FILES["archivoExcel"]["tmp_name"];

            // Crear un objeto PHPExcel para cargar el archivo Excel
            $excel = IOFactory::load($rutaArchivo);
            $sheet = $excel->getActiveSheet();

            // Valido que los registros cabecera estén correctos

            $nombrearchivoxml = $sheet->getCell('C2')->getValue(); // B2: Nombre de archivo xml
            $MsgId = $sheet->getCell('C3')->getValue(); // C2: Encabezado del XML
            $empresa = $sheet->getCell('C4')->getValue(); // C6: Nombre de la empresa
            $nroempresa = $sheet->getCell('C5')->getValue(); // F6: Numero de empresa
            $MonId = $sheet->getCell('E3')->getValue(); // E3: Moneda
            $lid = $sheet->getCell('E4')->getValue(); // E4: LID
            $formato = $sheet->getCell('E5')->getValue(); // E5: Formato
            $refpago = $sheet->getCell('G3')->getValue(); // G3: Referencia en pago

            if (empty($nombrearchivoxml) || empty($MsgId) || empty($empresa) || empty($nroempresa) || empty($MonId) || empty($lid) || empty($formato) || empty($refpago)) {
                $error ++;
                $msgs[] = array(
                    "danger",
                    "El encabezado posee campos vacíos que son obligatorios"
                );
                if (empty($nombrearchivoxml))
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar el nombre del archivo a generar"
                    );
                if (empty($MsgId))
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar el MsgId"
                    );
                if (empty($empresa))
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar el nombre de la empresa"
                    );
                if (empty($nroempresa))
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar el numero de la empresa"
                    );
                if (empty($MonId))
                    $msgs[] = array(
                        "danger",
                        "Falta completar la informacion sobre moneda"
                    );
                if (empty($lid))
                    $msgs[] = array(
                        "danger",
                        "Falta completar el LID de la empresa"
                    );
                if (empty($formato))
                    $msgs[] = array(
                        "danger",
                        "Falta completar el formato (Confidencial/Individual)"
                    );
                if (empty($refpago))
                    $msgs[] = array(
                        "danger",
                        "Falta completar la referencia del pago"
                    );
            }

            if ($MonId != 'UYU' && $MonId != 'USD' && $MonId != 'EUR') {
                $error ++;
                $msgs[$error] = array(
                    "danger",
                    "El campo moneda debe contener la palabra UYU o EUR o USD"
                );
            }
            if (strlen($lid) != 3) {
                $error ++;
                $msgs[$error] = array(
                    "danger",
                    "El campo LID debe contener 3 caracteres"
                );
            }

            if ($formato != 'CONFIDENCIAL' && $formato != 'INDIVIDUAL') {
                $error ++;
                $msgs[$error] = array(
                    "danger",
                    "El campo formato debe contener la palabra CONFIDENCIAL o INDIVIDUAL"
                );
            } else {
                if ($formato == 'CONFIDENCIAL') {
                    $confidencial = 1;
                }
            }

            // TO DO Tomar el directorio ('/archive') del config
            $nombrearchivoxml = 'archive/' . $nombrearchivoxml . '.xml';
            $MsgId = $MsgId . date('YmdHis');
            $NbOfTxs = 0;

            // Itera a través de las filas para contar cantidad de filas a procesar
            while ($sheet->cellExists('A' . ($NbOfTxs + 8))) {
                $cellValue = $sheet->getCell('A' . ($NbOfTxs + 8))->getValue();
                if (empty($cellValue)) {
                    break; // Si la celda está vacía, termina el bucle
                }
                $NbOfTxs ++;
            }

            // Valido el loop
            foreach ($sheet->getRowIterator(8, 8 + $NbOfTxs - 1) as $row) {

                foreach ($row->getCellIterator() as $cell) {
                    $valorCelda = $cell->getValue();
                    $coordenadas = $cell->getCoordinate();
                    $tipoCelda = $cell->getDataType();

                    if ($valorCelda == '') {
                        $error ++;
                        $msgs[] = array(
                            "danger",
                            "La celda '" . $coordenadas . "' esta vacia y es obligatoria"
                        );
                    }

                    if (substr($coordenadas, 0, 1) == "A") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo fecha (YYYY-MM-DD)"
                            );
                        }
                    }

                    if (substr($coordenadas, 0, 1) == "B") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        }
                    }

                    if (substr($coordenadas, 0, 1) == "C") {
                        if ($tipoCelda != "n") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo moneda"
                            );
                        }
                    }

                    if (substr($coordenadas, 0, 1) == "D") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        }
                    }

                    if (substr($coordenadas, 0, 1) == "G") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        }
                    }

                    if (substr($coordenadas, 0, 1) == "E") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        }
                    }

                    if (substr($coordenadas, 0, 1) == "F") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        }
                    }

                    if (substr($coordenadas, 0, 1) == "I") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        }
                    }
                }
            }

            // Genero el xml
            if (! $error) {
                echo "<script>$(document).ready(function() {manejarCheckbox('checkValidando');});</script>";

                // Crear un objeto SimpleXMLElement para generar el XML
                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->registerNodeClass('DOMElement', 'ExtendedDOMElement');

                // Ahora, cuando crees un elemento, será una instancia de ExtendedDOMElement
                $xml = $dom->createElement('Document');

                // Agregar los atributos xmlns y xmlns:xsi al elemento raíz
                $xml->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
                $xml->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');
                $dom->appendChild($xml);

                // Banco Santander Individual
                if ($proceso == 'santander' && $confidencial == 0) {

                    // Escribo el encabezado
                    $xmlCstmrCdtTrfInitn = $xml->addChild('CstmrCdtTrfInitn');
                    $xmlGrpHdr = $xmlCstmrCdtTrfInitn->addChild('GrpHdr');
                    $xmlGrpHdr->addChild('MsgId', $MsgId);
                    $xmlGrpHdr->addChild('CreDtTm', $CreDtTm);
                    $xmlGrpHdr->addChild('NbOfTxs', $NbOfTxs);
                    $xmlInitgPty = $xmlGrpHdr->addChild('InitgPty');
                    $xmlInitgPty->addChild('Nm', $empresa);

                    unset($xmlGrpHdr);
                    unset($xmlInitgPty);

                    // Recorrer las filas del archivo Excel y agregar los datos al XML
                    $i = 1;
                    foreach ($sheet->getRowIterator(8, 8 + $NbOfTxs - 1) as $row) {
                        // Levanto todos los valores del XLS
                        foreach ($row->getCellIterator() as $cell) {
                            $valorCelda = $cell->getValue();
                            $coordenadas = $cell->getCoordinate();

                            if (substr($coordenadas, 0, 1) == "A") {
                                $ReqdExctnDt = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "B") {
                                $EndToEndId = $valorCelda;
                                ;
                            }

                            if (substr($coordenadas, 0, 1) == "C") {
                                $InstdAmt = $valorCelda;
                                $CtrlSum = $CtrlSum + floatval($valorCelda);
                            }

                            if (substr($coordenadas, 0, 1) == "D") {
                                $MmbId = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "E") {
                                $Nm = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "F") {
                                $Id = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "G") {
                                $BrnchId = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "H") {
                                $Othr = $valorCelda;
                            }
                        }

                        // Escribo la parte PmtInf
                        $xmlPmtInf = $xmlCstmrCdtTrfInitn->addChild('PmtInf');
                        $xmlPmtInf->addChild('PmtInfId', $i);
                        $xmlPmtInf->addChild('PmtMtd', 'TRF');

                        $xmlPmtTpInf = $xmlPmtInf->addChild('PmtTpInf');
                        $xmlCtgyPurp = $xmlPmtTpInf->addChild('CtgyPurp');
                        $xmlCtgyPurp->addChild('Cd', 'SALA');

                        unset($xmlCtgyPurp);
                        unset($xmlPmtTpInf);

                        $xmlPmtInf->addChild('ReqdExctnDt', $ReqdExctnDt);

                        $xmlDbtr = $xmlPmtInf->addChild('Dbtr');
                        $xmlDbtr->addChild('Nm', $empresa);

                        unset($xmlDbtr);

                        $xmlDbtrAcct = $xmlPmtInf->addChild('DbtrAcct');
                        $xmlId = $xmlDbtrAcct->addChild('Id');
                        $xmlId->addChild('Othr')->addChild('Id', $nroempresa);

                        unset($xmlId);
                        unset($xmlDbtr);

                        $xmlDbtrAgt = $xmlPmtInf->addChild('DbtrAgt');
                        $xmlFinInstnId = $xmlDbtrAgt->addChild('FinInstnId');
                        $xmlFinInstnId->addChild('BIC', 'BSCHUYMM');

                        unset($xmlFinInstnId);
                        unset($xmlDbtrAgt);

                        $xmlPmtInf->addChild('ChrgBr', 'DEBT');

                        $xmlCdtTrfTxInf = $xmlPmtInf->addChild('CdtTrfTxInf');
                        $xmlPmtId = $xmlCdtTrfTxInf->addChild('PmtId');
                        $xmlPmtId->addChild('EndToEndId', $EndToEndId);

                        unset($xmlPmtId);

                        $xmlAmt = $xmlCdtTrfTxInf->addChild('Amt');

                        $xmlInstdAmt = $xmlAmt->addChild('InstdAmt', number_format(floatval($InstdAmt), 2, '.', ''));
                        $xmlInstdAmt->setAttribute('Ccy', $MonId);

                        unset($xmlInstdAmt);
                        unset($xmlAmt);

                        $xmlCdtrAgt = $xmlCdtTrfTxInf->addChild('CdtrAgt');
                        $xmlFinInstnId = $xmlCdtrAgt->addChild('FinInstnId');
                        $xmlClrSysMmbId = $xmlFinInstnId->addChild('ClrSysMmbId');
                        $xmlClrSysMmbId->addChild('MmbId', $MmbId);

                        unset($xmlClrSysMmbId);
                        unset($xmlFinInstnId);

                        $xmlBrnchId = $xmlCdtrAgt->addChild('BrnchId');
                        $xmlBrnchId->addChild('Id', $BrnchId);

                        unset($xmlBrnchId);
                        unset($xmlCdtrAgt);

                        $xmlCdtr = $xmlCdtTrfTxInf->addChild('Cdtr');
                        $xmlCdtr->addChild('Nm', $Nm);

                        unset($xmlCdtr);

                        $xmlCdtrAcct = $xmlCdtTrfTxInf->addChild('CdtrAcct');

                        $xmlCdtrAcct->addChild('Id')
                            ->addChild('Othr')
                            ->addChild('Id', $Othr);

                        unset($xmlCdtrAcct);
                        unset($xmlCdtTrfTxInf);
                        unset($xmlPmtInf);

                        $i ++;
                    }
                }

                // Banco Santander Confidencial
                if ($proceso == 'santander' && $confidencial == 1) {
                    // Escribo el encabezado
                    $xmlCstmrCdtTrfInitn = $xml->addChild('CstmrCdtTrfInitn');
                    $xmlGrpHdr = $xmlCstmrCdtTrfInitn->addChild('GrpHdr');
                    $xmlGrpHdr->addChild('MsgId', $MsgId);
                    $xmlGrpHdr->addChild('CreDtTm', $CreDtTm);
                    $xmlGrpHdr->addChild('NbOfTxs', $NbOfTxs);
                    $xmlInitgPty = $xmlGrpHdr->addChild('InitgPty');
                    $xmlInitgPty->addChild('Nm', $empresa);

                    unset($xmlGrpHdr);
                    unset($xmlInitgPty);

                    // Escribo la parte PmtInf
                    $xmlPmtInf = $xmlCstmrCdtTrfInitn->addChild('PmtInf');
                    $xmlPmtInf->addChild('PmtInfId', $lid . date('ymdHis'));
                    $xmlPmtInf->addChild('PmtMtd', 'TRF');

                    $xmlPmtTpInf = $xmlPmtInf->addChild('PmtTpInf');
                    $xmlCtgyPurp = $xmlPmtTpInf->addChild('CtgyPurp');
                    $xmlCtgyPurp->addChild('Cd', 'SALA');

                    unset($xmlCtgyPurp);
                    unset($xmlPmtTpInf);

                    $ReqdExctnDt = $sheet->getCell('A8')->getValue();
                    $xmlPmtInf->addChild('ReqdExctnDt', $ReqdExctnDt);

                    $xmlDbtr = $xmlPmtInf->addChild('Dbtr');
                    $xmlDbtr->addChild('Nm', $empresa);

                    unset($xmlDbtr);

                    $xmlDbtrAcct = $xmlPmtInf->addChild('DbtrAcct');
                    $xmlId = $xmlDbtrAcct->addChild('Id');
                    $xmlId->addChild('Othr')->addChild('Id', $nroempresa);

                    unset($xmlId);
                    unset($xmlDbtr);

                    $xmlDbtrAgt = $xmlPmtInf->addChild('DbtrAgt');
                    $xmlFinInstnId = $xmlDbtrAgt->addChild('FinInstnId');
                    $xmlFinInstnId->addChild('BIC', 'BSCHUYMM');

                    unset($xmlFinInstnId);

                    unset($xmlDbtrAgt);

                    $xmlPmtInf->addChild('ChrgBr', 'DEBT');

                    // Recorrer las filas del archivo Excel y agregar los datos al XML
                    $i = 1;
                    foreach ($sheet->getRowIterator(8, 8 + $NbOfTxs - 1) as $row) {
                        // Levanto todos los valores del XLS
                        foreach ($row->getCellIterator() as $cell) {
                            $valorCelda = $cell->getValue();
                            $coordenadas = $cell->getCoordinate();

                            if (substr($coordenadas, 0, 1) == "B") {
                                $EndToEndId = $valorCelda;
                                ;
                            }

                            if (substr($coordenadas, 0, 1) == "C") {
                                $InstdAmt = $valorCelda;
                                $CtrlSum = $CtrlSum + floatval($valorCelda);
                            }

                            if (substr($coordenadas, 0, 1) == "D") {
                                $MmbId = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "E") {
                                $Nm = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "F") {
                                $Id = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "G") {
                                $BrnchId = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "H") {
                                $Othr = $valorCelda;
                            }
                        }

                        $xmlCdtTrfTxInf = $xmlPmtInf->addChild('CdtTrfTxInf');
                        $xmlPmtId = $xmlCdtTrfTxInf->addChild('PmtId');
                        $xmlPmtId->addChild('EndToEndId', $EndToEndId);

                        unset($xmlPmtId);

                        $xmlAmt = $xmlCdtTrfTxInf->addChild('Amt');

                        $xmlInstdAmt = $xmlAmt->addChild('InstdAmt', number_format(floatval($InstdAmt), 2, '.', ''));
                        $xmlInstdAmt->setAttribute('Ccy', $MonId);

                        unset($xmlInstdAmt);
                        unset($xmlAmt);

                        $xmlCdtrAgt = $xmlCdtTrfTxInf->addChild('CdtrAgt');
                        $xmlFinInstnId = $xmlCdtrAgt->addChild('FinInstnId');
                        $xmlClrSysMmbId = $xmlFinInstnId->addChild('ClrSysMmbId');
                        $xmlClrSysMmbId->addChild('MmbId', $MmbId);

                        unset($xmlClrSysMmbId);
                        unset($xmlFinInstnId);

                        $xmlBrnchId = $xmlCdtrAgt->addChild('BrnchId');
                        $xmlBrnchId->addChild('Id', $BrnchId);

                        unset($xmlBrnchId);
                        unset($xmlCdtrAgt);

                        $xmlCdtr = $xmlCdtTrfTxInf->addChild('Cdtr');
                        $xmlCdtr->addChild('Nm', $Nm);

                        unset($xmlCdtr);

                        $xmlCdtrAcct = $xmlCdtTrfTxInf->addChild('CdtrAcct');

                        $xmlCdtrAcct->addChild('Id')
                            ->addChild('Othr')
                            ->addChild('Id', $Othr);

                        unset($xmlCdtrAcct);
                        unset($xmlCdtTrfTxInf);

                        $i ++;
                    }
                    unset($xmlPmtInf);
                }

                // Banco Citi Individual
                if ($proceso == 'citi' && $confidencial == 0) {
                    // Primero veo el total a transferir porque lo necesito en el Header foreach ($sheet->getRowIterator(8,8+$NbOfTxs-1) as $row) {
                    // Levanto todos los valores del XLS
                    for ($i = 8; $i < 8 + $NbOfTxs; $i ++) {
                        $CtrlSum = $CtrlSum + floatval($sheet->getCell('C' . $i)->getValue());
                    }

                    // Escribo el encabezado
                    $xmlCstmrCdtTrfInitn = $xml->addChild('CstmrCdtTrfInitn');
                    $xmlGrpHdr = $xmlCstmrCdtTrfInitn->addChild('GrpHdr');
                    $xmlGrpHdr->addChild('MsgId', $MsgId);
                    $xmlGrpHdr->addChild('CreDtTm', $CreDtTm);
                    $xmlGrpHdr->addChild('NbOfTxs', $NbOfTxs);
                    $xmlGrpHdr->addChild('CtrlSum', number_format(floatval($CtrlSum), 2, '.', ''));
                    $CtrlSum = 0;
                    $xmlInitgPty = $xmlGrpHdr->addChild('InitgPty');
                    $xmlInitgPty->addChild('Nm', $empresa);

                    unset($xmlGrpHdr);
                    unset($xmlInitgPty);

                    // Recorrer las filas del archivo Excel y agregar los datos al XML
                    $i = 1;
                    foreach ($sheet->getRowIterator(8, 8 + $NbOfTxs - 1) as $row) {
                        // Levanto todos los valores del XLS
                        foreach ($row->getCellIterator() as $cell) {
                            $valorCelda = $cell->getValue();
                            $coordenadas = $cell->getCoordinate();

                            if (substr($coordenadas, 0, 1) == "A") {
                                $ReqdExctnDt = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "B") {
                                $EndToEndId = $valorCelda;
                                ;
                            }

                            if (substr($coordenadas, 0, 1) == "C") {
                                $InstdAmt = $valorCelda;
                                $CtrlSum = $CtrlSum + floatval($valorCelda);
                            }

                            if (substr($coordenadas, 0, 1) == "D") {
                                $MmbId = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "E") {
                                $Nm = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "F") {
                                $Id = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "G") {
                                $BrnchId = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "H") {
                                $Othr = $valorCelda;
                            }
                            if (substr($coordenadas, 0, 1) == "I") {
                                $CdTipoCta = $valorCelda;
                            }
                        }

                        // Escribo la parte PmtInf
                        $xmlPmtInf = $xmlCstmrCdtTrfInitn->addChild('PmtInf');
                        $xmlPmtInf->addChild('PmtInfId', $i);
                        $xmlPmtInf->addChild('PmtMtd', 'TRF');

                        $xmlPmtTpInf = $xmlPmtInf->addChild('PmtTpInf');
                        $xmlSvcLvl = $xmlPmtTpInf->addChild('SvcLvl');
                        $xmlSvcLvl->addChild('Cd', 'NURG');

                        unset($xmlSvcLvl);
                        unset($xmlPmtTpInf);

                        $xmlPmtInf->addChild('ReqdExctnDt', $ReqdExctnDt);

                        $xmlDbtr = $xmlPmtInf->addChild('Dbtr');
                        $xmlDbtr->addChild('Nm', $empresa);
                        $xmlPstlAdr = $xmlDbtr->addChild('PstlAdr');
                        $xmlPstlAdr->addChild('Ctry', 'UY');
                        $xmlPstlAdr->addChild('AdrLine', 'SD');

                        unset($xmlPstlAdr);
                        unset($xmlDbtr);

                        $xmlDbtrAcct = $xmlPmtInf->addChild('DbtrAcct');
                        $xmlId = $xmlDbtrAcct->addChild('Id');
                        $xmlId->addChild('Othr')->addChild('Id', $nroempresa);

                        unset($xmlId);
                        unset($xmlDbtr);

                        $xmlDbtrAgt = $xmlPmtInf->addChild('DbtrAgt');
                        $xmlFinInstnId = $xmlDbtrAgt->addChild('FinInstnId');
                        $xmlFinInstnId->addChild('BIC', 'CITIUYMM');
                        $xmlPstlAdr = $xmlFinInstnId->addChild('PstlAdr');
                        $xmlPstlAdr->addChild('Ctry', 'UY');

                        unset($xmlPstlAdr);
                        // unset($$xmlFinInstnId);
                        unset($xmlDbtrAgt);

                        // $xmlPmtInf->addChild('ChrgBr','DEBT');

                        $xmlCdtTrfTxInf = $xmlPmtInf->addChild('CdtTrfTxInf');
                        $xmlPmtId = $xmlCdtTrfTxInf->addChild('PmtId');
                        $xmlPmtId->addChild('EndToEndId', $EndToEndId);

                        unset($xmlPmtId);

                        $xmlAmt = $xmlCdtTrfTxInf->addChild('Amt');

                        $xmlInstdAmt = $xmlAmt->addChild('InstdAmt', number_format(floatval($InstdAmt), 2, '.', ''));
                        $xmlInstdAmt->setAttribute('Ccy', $MonId);

                        unset($xmlInstdAmt);
                        unset($xmlAmt);

                        $xmlCdtTrfTxInf->addChild('ChqInstr')->addChild('PrtLctn', '000');

                        $xmlCdtrAgt = $xmlCdtTrfTxInf->addChild('CdtrAgt');
                        $xmlFinInstnId = $xmlCdtrAgt->addChild('FinInstnId');
                        $xmlClrSysMmbId = $xmlFinInstnId->addChild('ClrSysMmbId');
                        $xmlClrSysMmbId->addChild('MmbId', $MmbId);

                        unset($xmlClrSysMmbId);
                        unset($xmlFinInstnId);

                        // $xmlBrnchId = $xmlCdtrAgt->addChild('BrnchId');
                        // $xmlBrnchId->addChild('Id',$BrnchId);

                        // unset ($xmlBrnchId);
                        unset($xmlCdtrAgt);

                        $xmlCdtr = $xmlCdtTrfTxInf->addChild('Cdtr');
                        $xmlCdtr->addChild('Nm', $Nm);
                        $xmlPstlAdr = $xmlCdtr->addChild('PstlAdr');
                        $xmlPstlAdr->addChild('Ctry', 'UY');
                        $xmlPstlAdr->addChild('AdrLine', 'SD');

                        unset($xmlPstlAdr);

                        $xmlId = $xmlCdtr->addChild('Id');
                        $xmlPrvtId = $xmlId->addChild('PrvtId');
                        $xmlOthr = $xmlPrvtId->addChild('Othr');
                        $xmlOthr->addChild('Id', $Id);
                        $xmlOthr->addChild('SchmeNm')->addChild('Cd', 'TXID');

                        unset($xmlOthr);
                        unset($xmlPrvtId);
                        unset($xmlId);
                        unset($xmlCdtr);

                        $xmlCdtrAcct = $xmlCdtTrfTxInf->addChild('CdtrAcct');

                        $xmlCdtrAcct->addChild('Id')
                            ->addChild('Othr')
                            ->addChild('Id', $Othr);
                        $xmlTp = $xmlCdtrAcct->addChild('Tp');
                        $xmlTp->addChild('Cd', $CdTipoCta);
                        $xmlTp->addChild('Prtry', $CdTipoCta);

                        unset($xmlTp);
                        unset($xmlCdtrAcct);

                        $xmlCdtTrfTxInf->addChild('Purp')->addChild('Prtry', '00');
                        $xmlCdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $refpago);
                        $xmlRltdRmtInf = $xmlCdtTrfTxInf->addChild('RltdRmtInf');
                        $xmlRltdRmtInf->addChild('RmtLctnMtd', 'EMAL');
                        $xmlRltdRmtInf->addChild('RmtLctnElctrncAdr');

                        unset($xmlRltdRmtInf);
                        unset($xmlCdtTrfTxInf);
                        unset($xmlPmtInf);

                        $i ++;
                    }
                }

                // Banco Citi Confidencial
                if ($proceso == 'citi' && $confidencial == 1) {
                    // Primero veo el total a transferir porque lo necesito en el Header foreach ($sheet->getRowIterator(8,8+$NbOfTxs-1) as $row) {
                    // Levanto todos los valores del XLS
                    for ($i = 8; $i < 8 + $NbOfTxs; $i ++) {
                        $CtrlSum = $CtrlSum + floatval($sheet->getCell('C' . $i)->getValue());
                    }

                    // Escribo el encabezado
                    $xmlCstmrCdtTrfInitn = $xml->addChild('CstmrCdtTrfInitn');
                    $xmlGrpHdr = $xmlCstmrCdtTrfInitn->addChild('GrpHdr');
                    $xmlGrpHdr->addChild('MsgId', $MsgId);
                    $xmlGrpHdr->addChild('CreDtTm', $CreDtTm);
                    $xmlGrpHdr->addChild('NbOfTxs', $NbOfTxs);
                    $xmlGrpHdr->addChild('CtrlSum', number_format(floatval($CtrlSum), 2, '.', ''));
                    $CtrlSum = 0;
                    $xmlInitgPty = $xmlGrpHdr->addChild('InitgPty');
                    $xmlInitgPty->addChild('Nm', $empresa);

                    unset($xmlGrpHdr);
                    unset($xmlInitgPty);

                    // Recorrer las filas del archivo Excel y agregar los datos al XML
                    $i = 1;
                    foreach ($sheet->getRowIterator(8, 8 + $NbOfTxs - 1) as $row) {
                        // Levanto todos los valores del XLS
                        foreach ($row->getCellIterator() as $cell) {
                            $valorCelda = $cell->getValue();
                            $coordenadas = $cell->getCoordinate();

                            if (substr($coordenadas, 0, 1) == "A") {
                                $ReqdExctnDt = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "B") {
                                $EndToEndId = $valorCelda;
                                ;
                            }

                            if (substr($coordenadas, 0, 1) == "C") {
                                $InstdAmt = $valorCelda;
                                $CtrlSum = $CtrlSum + floatval($valorCelda);
                            }

                            if (substr($coordenadas, 0, 1) == "D") {
                                $MmbId = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "E") {
                                $Nm = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "F") {
                                $Id = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "G") {
                                $BrnchId = $valorCelda;
                            }

                            if (substr($coordenadas, 0, 1) == "H") {
                                $Othr = $valorCelda;
                            }
                            if (substr($coordenadas, 0, 1) == "I") {
                                $CdTipoCta = $valorCelda;
                            }
                        }

                        // Escribo la parte PmtInf
                        $xmlPmtInf = $xmlCstmrCdtTrfInitn->addChild('PmtInf');
                        $xmlPmtInf->addChild('PmtInfId', $lid . date('ymdHis'));
                        $xmlPmtInf->addChild('PmtMtd', 'TRF');

                        $xmlPmtTpInf = $xmlPmtInf->addChild('PmtTpInf');
                        $xmlSvcLvl = $xmlPmtTpInf->addChild('SvcLvl');
                        $xmlSvcLvl->addChild('Cd', 'NURG');

                        unset($xmlSvcLvl);
                        unset($xmlPmtTpInf);

                        $xmlPmtInf->addChild('ReqdExctnDt', $ReqdExctnDt);

                        $xmlDbtr = $xmlPmtInf->addChild('Dbtr');
                        $xmlDbtr->addChild('Nm', $empresa);
                        $xmlPstlAdr = $xmlDbtr->addChild('PstlAdr');
                        $xmlPstlAdr->addChild('Ctry', 'UY');
                        $xmlPstlAdr->addChild('AdrLine', 'SD');

                        unset($xmlPstlAdr);
                        unset($xmlDbtr);

                        $xmlDbtrAcct = $xmlPmtInf->addChild('DbtrAcct');
                        $xmlId = $xmlDbtrAcct->addChild('Id');
                        $xmlId->addChild('Othr')->addChild('Id', $nroempresa);

                        unset($xmlId);
                        unset($xmlDbtr);

                        $xmlDbtrAgt = $xmlPmtInf->addChild('DbtrAgt');
                        $xmlFinInstnId = $xmlDbtrAgt->addChild('FinInstnId');
                        $xmlFinInstnId->addChild('BIC', 'CITIUYMM');
                        $xmlPstlAdr = $xmlFinInstnId->addChild('PstlAdr');
                        $xmlPstlAdr->addChild('Ctry', 'UY');

                        unset($xmlPstlAdr);
                        // unset($$xmlFinInstnId);
                        unset($xmlDbtrAgt);

                        // $xmlPmtInf->addChild('ChrgBr','DEBT');

                        $xmlCdtTrfTxInf = $xmlPmtInf->addChild('CdtTrfTxInf');
                        $xmlPmtId = $xmlCdtTrfTxInf->addChild('PmtId');
                        $xmlPmtId->addChild('EndToEndId', $EndToEndId);

                        unset($xmlPmtId);

                        $xmlAmt = $xmlCdtTrfTxInf->addChild('Amt');

                        $xmlInstdAmt = $xmlAmt->addChild('InstdAmt', number_format(floatval($InstdAmt), 2, '.', ''));
                        $xmlInstdAmt->setAttribute('Ccy', $MonId);

                        unset($xmlInstdAmt);
                        unset($xmlAmt);

                        $xmlCdtTrfTxInf->addChild('ChqInstr')->addChild('PrtLctn', '000');

                        $xmlCdtrAgt = $xmlCdtTrfTxInf->addChild('CdtrAgt');
                        $xmlFinInstnId = $xmlCdtrAgt->addChild('FinInstnId');
                        $xmlClrSysMmbId = $xmlFinInstnId->addChild('ClrSysMmbId');
                        $xmlClrSysMmbId->addChild('MmbId', $MmbId);

                        unset($xmlClrSysMmbId);
                        unset($xmlFinInstnId);

                        // $xmlBrnchId = $xmlCdtrAgt->addChild('BrnchId');
                        // $xmlBrnchId->addChild('Id',$BrnchId);

                        // unset ($xmlBrnchId);
                        unset($xmlCdtrAgt);

                        $xmlCdtr = $xmlCdtTrfTxInf->addChild('Cdtr');
                        $xmlCdtr->addChild('Nm', $Nm);
                        $xmlPstlAdr = $xmlCdtr->addChild('PstlAdr');
                        $xmlPstlAdr->addChild('Ctry', 'UY');
                        $xmlPstlAdr->addChild('AdrLine', 'SD');

                        unset($xmlPstlAdr);

                        $xmlId = $xmlCdtr->addChild('Id');
                        $xmlPrvtId = $xmlId->addChild('PrvtId');
                        $xmlOthr = $xmlPrvtId->addChild('Othr');
                        $xmlOthr->addChild('Id', $Id);
                        $xmlOthr->addChild('SchmeNm')->addChild('Cd', 'TXID');

                        unset($xmlOthr);
                        unset($xmlPrvtId);
                        unset($xmlId);
                        unset($xmlCdtr);

                        $xmlCdtrAcct = $xmlCdtTrfTxInf->addChild('CdtrAcct');

                        $xmlCdtrAcct->addChild('Id')
                            ->addChild('Othr')
                            ->addChild('Id', $Othr);
                        $xmlTp = $xmlCdtrAcct->addChild('Tp');
                        $xmlTp->addChild('Cd', $CdTipoCta);
                        // $xmlTp->addChild('Prtry',$CdTipoCta);

                        unset($xmlTp);
                        unset($xmlCdtrAcct);
                        $xmlCdtTrfTxInf->addChild('InstrForDbtrAgt', '/CONFIDENTIAL/');
                        $xmlCdtTrfTxInf->addChild('Purp')->addChild('Prtry', '08');
                        $xmlCdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $refpago);
                        unset($xmlCdtTrfTxInf);
                        unset($xmlPmtInf);

                        $i ++;
                    }
                }

                echo "<script>$(document).ready(function() {manejarCheckbox('checkProcesando');});</script>";

                // Generar el archivo XML
                if (! empty($xml)) {
                    file_put_contents($nombrearchivoxml, $dom->saveXML($xml));
                    echo "<script>$(document).ready(function() {manejarCheckbox('checkGenerando');});</script>";
                } else {
                    $error ++;
                    $msgs[] = array(
                        "danger",
                        "Se ha producido un error, el xml se generó vacío, contacte al administrador"
                    );
                }
            }
            //----------------------------------------------------------------------------------
        } else {
            //PROCESO CONTABLE
            echo "<script>$(document).ready(function() {manejarCheckbox('checkValidando');});</script>";
            $nombreArchivo = $_FILES["archivoExcel"]["name"];
            $rutaArchivo = $_FILES["archivoExcel"]["tmp_name"];
            $comienzoIteracion = 11;
            
            // Crear un objeto PHPExcel para cargar el archivo Excel
            $excel = IOFactory::load($rutaArchivo);
            $sheet = $excel->getActiveSheet();

            // Valido que los registros cabecera estén correctos
            $lid = $sheet->getCell('C3')->getValue(); // C3: LID

            $dateValue = $sheet->getCell('C4')->getValue();
            if (is_string($dateValue)) {
                $error ++;
                $msgs[] = array(
                    "danger",
                    "La Fecha Desde es incorrecta"
                );
            } else{
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
            }
            $formattedDate = $date->format('m.y');
            $fechaDesde = $formattedDate; // C4: Fecha desde

            $dateValue = $sheet->getCell('C5')->getValue();
            if (is_string($dateValue)) {
                $error ++;
                $msgs[] = array(
                    "danger",
                    "La Fecha Hasta es incorrecta"
                );
            } else{
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
            }
            $formattedDate = $date->format('my');
            $fechaHasta = $formattedDate; // C5: Fecha hasta
            
            $liqDesde = $sheet->getCell('C6')->getValue(); // C6: Liq desde

            $dateValue = $sheet->getCell('C8')->getValue();
            if (is_string($dateValue)) {
                $error ++;
                $msgs[] = array(
                    "danger",
                    "Posting Date es incorrecta"
                );
            } else{
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
            }
            $formattedDate = $date->format('Y-m-d');
            $postingDate = $formattedDate; // C8: Posting Date

            $empresa = $sheet->getCell('E3')->getValue(); // E3: Nombre de la empresa
            $codEmpresa = $sheet->getCell('E4')->getValue(); // E4: Codigo de empresa
            $MonId = $sheet->getCell('E5')->getValue(); // E5: Moneda
            $LiqId = $sheet->getCell('E6')->getValue(); // E6: Tipo de liquidacion

            if (empty($lid) || empty($fechaDesde) || empty($fechaHasta) || empty($postingDate) || empty($empresa) ||
                empty($codEmpresa) || empty($MonId) || empty($LiqId)) {
                $error ++;
                $msgs[] = array(
                    "danger",
                    "El encabezado posee campos vacíos que son obligatorios"
                );
                if (empty($lid)){
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar el LID"
                    );
                };
                if (empty($fechaDesde)){
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar la Fecha Desde"
                    );
                };
                if (empty($fechaHasta)){
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar la Fecha Hasta"
                    );
                };
                if (empty($postingDate)){
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar el Posting Date"
                    );
                };
                if (empty($empresa)){
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar el nombre de la empresa (Company)"
                    );
                };
                if (empty($codEmpresa)){
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar el F&A Name"
                    );
                };
                if (empty($MonId)){
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar informacion sobre la moneda"
                    );
                };
                if (empty($LiqId)){
                    $msgs[$error] = array(
                        "danger",
                        "Falta completar datos de la liquidacion"
                    );
                };
            };

            if ($MonId != 'UYU' && $MonId != 'USD' && $MonId != 'EUR') {
                $error ++;
                $msgs[$error] = array(
                    "danger",
                    "El campo moneda debe contener la palabra UYU o EUR o USD"
                );
            }
            if (strlen($lid) != 3) {
                $error ++;
                $msgs[$error] = array(
                    "danger",
                    "El campo LID debe contener 3 caracteres"
                );
            }
            if (strlen($codEmpresa) > 4) {
                $error ++;
                $msgs[$error] = array(
                    "danger",
                    "El campo F&A Name debe contener 4 caracteres maximo"
                );
            }


            $NbOfTxs = 0;
            $documentsTitles = []; //array para guardar los archivos a generar y las filas donde empiezan

            // Itera a través de las filas para contar cantidad de filas a procesar
            $celdaanterior = $sheet->getCell('G' . ($NbOfTxs + $comienzoIteracion))->getValue();
            $i=1;
            
            while ($sheet->cellExists('G' . ($NbOfTxs + $comienzoIteracion))) {
                $cellValue = $sheet->getCell('G' . ($NbOfTxs + $comienzoIteracion))->getValue();
                
                // cada vez que haya un valor nuevo, lo agrego al array
                if($cellValue!=$celdaanterior){
                    $documentsTitles[$i] =  array($celdaanterior,$NbOfTxs);
                    $i++;
                }
                
                $celdaanterior=$cellValue;
                $NbOfTxs ++;
            }
            
            // Agrego ultimo registro al array
            $documentsTitles[$i] =  array($celdaanterior,$NbOfTxs);

            // Valido el loop
            foreach ($sheet->getRowIterator($comienzoIteracion, $comienzoIteracion + $NbOfTxs - 1) as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    $valorCelda = $cell->getValue();
                    $coordenadas = $cell->getCoordinate();
                    $tipoCelda = $cell->getDataType();

                    $columna = substr($coordenadas, 0, 1);
                    //celdas que pueden estar vacias
                    if ($valorCelda == '') {
                        switch ($columna) {
                            case "A":
                                break;
                            case "J":
                                break;
                            case "K":
                                break;  
                            case "L":
                                break;                            
                            case "M":
                                break;
                            default:
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' esta vacia y es obligatoria"
                            );
                        }
                    }
                    //celdas B deben ser numericas 
                    if (substr($coordenadas, 0, 1) == "B") {
                        if ($tipoCelda != "n") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo numerica"
                            );
                        }
                    }
                    //celdas C deben ser fechas
                    if (substr($coordenadas, 0, 1) == "C") {
                        if ($tipoCelda != "n") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo fecha"
                            );
                        }
                    }
                    //celda D debe ser "UYU", "USD" o "EUR"
                    if (substr($coordenadas, 0, 1) == "D") {
                        if ($valorCelda != 'UYU' && $valorCelda != 'USD' && $valorCelda != 'EUR') {
                            $error ++;
                            $msgs[$error] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe contener la palabra UYU o EUR o USD"
                            );
                        }
                    }
                    //celdas E deben ser string
                    if (substr($coordenadas, 0, 1) == "E") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        }
                    }
                    // celda F deben ser string
                    if (substr($coordenadas, 0, 1) == "F") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        }
                    }
                    //celdas G deben ser string de 25 caracteres max
                    if (substr($coordenadas, 0, 1) == "G") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        }
                        if(strlen($valorCelda) > 25){
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe tener 25 caracteres maximo"
                            );
                        }
                    }
                    //celdas H debe ser 'SC' o 'SD'
                    if (substr($coordenadas, 0, 1) == "H") {
                        if ($valorCelda != "SC" && $valorCelda != "SD") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser 'SC' o 'SD'"
                            );
                        }
                    }
                    //celdas I deben ser numericas de 10 caracteres max
                    if (substr($coordenadas, 0, 1) == "I") {
                        if($valorCelda != ''){ //puede estar vacia
                            if ($tipoCelda != "n") {
                                $error ++;
                                $msgs[] = array(
                                    "danger",
                                    "La celda '" . $coordenadas . "' debe ser de tipo numerico"
                                );
                            }
                            if(strlen($valorCelda) > 10){
                                $error ++;
                                $msgs[] = array(
                                    "danger",
                                    "La celda '" . $coordenadas . "' debe tener 10 caracteres maximo"
                                );
                            }
                        }
                    }
                    //celdas J deben ser numericas de 10 caracteres max
                    if (substr($coordenadas, 0, 1) == "J") {
                        if($valorCelda != ''){ //puede estar vacia
                            if ($tipoCelda != "n") {
                                $error ++;
                                $msgs[] = array(
                                    "danger",
                                    "La celda '" . $coordenadas . "' debe ser de tipo numerico"
                                );
                            }
                            if(strlen($valorCelda) > 10){
                                $error ++;
                                $msgs[] = array(
                                    "danger",
                                    "La celda '" . $coordenadas . "' debe tener 10 caracteres maximo"
                                );
                            }
                        }
                    }
                    //celdas K deben ser formato: 'S' + 11 numeros
                    if (substr($coordenadas, 0, 1) == "K") {
                        if($valorCelda != ''){ //puede estar vacia
                            $numero = (int)substr($valorCelda, 1, 12);
                            if(substr($valorCelda, 0, 1) != "S" || !$numero || strlen($numero) != 11){
                                $error ++;
                                $msgs[] = array(
                                    "danger",
                                    "La celda '" . $coordenadas . "' debe tener el formato 'S' + 11 Numeros. Ej: 'S01234567890'"
                                );
                            }
                        }
                    }
                    //celdas L deben ser string de 24 caracteres max
                    if (substr($coordenadas, 0, 1) == "L") {
                        if($valorCelda != ''){ //puede estar vacia
                            if ($tipoCelda != "s"){
                                $error ++;
                                $msgs[] = array(
                                    "danger",
                                    "La celda '" . $coordenadas . "' debe ser de tipo string"
                                );
                            }
                            if(strlen($valorCelda) > 18){
                                $error ++;
                                $msgs[] = array(
                                    "danger",
                                    "La celda '" . $coordenadas . "' debe tener 18 caracteres maximo"
                                );
                            }
                        }
                    }
                    //celdas M deben ser string de 18 caracteres max
                    if (substr($coordenadas, 0, 1) == "M") {
                        if($valorCelda != ''){ //puede estar vacia
                            if ($tipoCelda != "s") {
                                $error ++;
                                $msgs[] = array(
                                    "danger",
                                    "La celda '" . $coordenadas . "' debe ser de tipo string"
                                );
                            }
                            if(strlen($valorCelda) > 18){
                                $error ++;
                                $msgs[] = array(
                                    "danger",
                                    "La celda '" . $coordenadas . "' debe tener 18 caracteres maximo"
                                );
                            }
                        }
                    }
                    //celdas N deben ser numericas 
                    if (substr($coordenadas, 0, 1) == "N") {
                        if ($tipoCelda != "n") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo numerica"
                            );
                        }
                    }
                    //celdas O deben ser string de 50 caracteres max
                    if (substr($coordenadas, 0, 1) == "O") {
                        if ($tipoCelda != "s") {
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe ser de tipo string"
                            );
                        } 
                        if(strlen($valorCelda) > 50){
                            $error ++;
                            $msgs[] = array(
                                "danger",
                                "La celda '" . $coordenadas . "' debe tener 50 caracteres maximo"
                            );
                        }
                    }
                } 
            }

            //Proceso para generar varios xml
            // Itero el array generado
            //for ($i = 0; $i < count($documentsTitles); $i++) {
            if (! $error) {
                
                echo "<script>$(document).ready(function() {manejarCheckbox('checkProcesando');});</script>";
                // Proceso un archivo por cada iteracion
                foreach ($documentsTitles as $i=>$document) {
                    $documentTitleTag = $documentsTitles[$i][0];//varible para la etiqueta dentro del xml
                    $documentsTitles[$i][0] = $nombrearchivoxml = $document[0].'-'.date('YmdHis');
                    if($i==1) {
                        $filainicio = $comienzoIteracion;
                        
                    }else{
                        $filainicio = $comienzoIteracion + $documentsTitles[$i-1][1];
                    }
                    $filafinal = $comienzoIteracion + $document[1]-1;
                    
                    // Genero el xml
                    // Crear un objeto SimpleXMLElement para generar el XML
                    $dom = new DOMDocument('1.0', 'UTF-8');
                    $dom->registerNodeClass('DOMElement', 'ExtendedDOMElement');
                    
                    $xml = $dom->createElement('tns:SalaryDocument');
                                        
                    // Agregar los atributos 
                    $xml->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:tns', 'urn:upm.com:global:finance:accounting:postings');
                    $xml->setAttributeNS('', 'creationTime', $CreDtTm);
                    $xml->setAttributeNS('', 'sender', 'ADPPAYROLL.UY');
                    $xml->setAttributeNS('', 'recipient', 'UPM');
                    $xml->setAttributeNS('', 'transmissionId', $empresa.'-'.$MonId.'-'.$LiqId.$CreDtTm2);
                    $dom->appendChild($xml);
                    
                    $header = $xml->addChild('header');
                    $header->addChild('CompanyCode' , $codEmpresa);
                    $header->addChild('DocumentCurrency' , $MonId);
                    $header->addChild('DocumentTitle' , $documentTitleTag.' '.$fechaDesde);//ver formato de fecha!!!
                    $header->addChild('PostingDate' , $postingDate);//ver formato de fecha!!!
                    $header->addChild('DocumentDate' , $postingDate);//ver formato de fecha!!!
                    $header->addChild('ReferenceDocument' , $lid.$fechaHasta.'LIQ'.$liqDesde.$sheet->getCell('F'.($filainicio))->getValue());//ver formato de fecha!!!
                    $header->addChild('DocumentType' , $sheet->getCell('H'.($filainicio))->getValue());

                    foreach ($sheet->getRowIterator($filainicio, $filafinal) as $row) {
                        foreach ($row->getCellIterator() as $cell) {
                            $valorCelda = $cell->getValue();
                            $coordenadas = $cell->getCoordinate();
                            
                            if (substr($coordenadas, 0, 1) == "I") {
                                $GLAccount = $valorCelda;
                            }
                            
                            if (substr($coordenadas, 0, 1) == "N") {
                                $Amount = $valorCelda;
                            }
                            
                            if (substr($coordenadas, 0, 1) == "J") {
                                $CostCenter = $valorCelda;
                            }
                            
                            if (substr($coordenadas, 0, 1) == "M") {
                                $Assignment = $valorCelda;
                            }
                            
                            if (substr($coordenadas, 0, 1) == "K") {
                                $InternalOrder = $valorCelda;
                            }
                            
                            if (substr($coordenadas, 0, 1) == "L") {
                                $WBS = $valorCelda;
                            }
                            
                            if (substr($coordenadas, 0, 1) == "O") {
                                $Description = $valorCelda;
                            }
                        }
                        
                        $item = $xml->addChild('item');
                        $item->addChild('GLAccount' , $GLAccount);
                        $item->addChild('Amount' , number_format(floatval($Amount), 2, '.', ''));
                        $item->addChild('CostCenter' , $CostCenter);
                        $item->addChild('Assignment' , $Assignment);
                        $item->addChild('Order' , $InternalOrder);
                        $item->addChild('WBS' , $WBS);
                        $item->addChild('Description' , $fechaDesde.'-'.$Description);
                    }
                    
                    file_put_contents('archive/'.$nombrearchivoxml.'.xml', $dom->saveXML($xml));
                }
                echo "<script>$(document).ready(function() {manejarCheckbox('checkGenerando');});</script>";
            }
        }
    } else {
        $error ++;
        $errorCode = $_FILES["archivoExcel"]["error"];
        $errorMessage = "";
        
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                $errorMessage = "No hay error, el archivo se subió con éxito.";
                break;
            case UPLOAD_ERR_INI_SIZE:
                $errorMessage = "El archivo subido excede el tamaño máximo permitido: ".ini_get('upload_max_filesize').".";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage = "El archivo subido excede la directiva MAX_FILE_SIZE que fue especificada en el formulario HTML.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage = "El archivo subido fue sólo parcialmente subido.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMessage = "Ningún archivo fue subido.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMessage = "Falta una carpeta temporal.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMessage = "Falló al escribir el archivo en el disco.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMessage = "Una extensión de PHP detuvo la subida del archivo.";
                break;
            default:
                $errorMessage = "Error desconocido al subir el archivo.";
        }
        
        // Ahora puedes usar la variable $errorMessage donde la necesites.
        $msgs[] = array("danger",$errorMessage);
    }
} else {
    $error ++;
    $msgs[] = array(
        "danger",
        "No puede acceder a esta página directamente, por favor vaya a <a href=\"index.php\">Inicio</a>"
    );
}

?>
<?php if (!$error):?>
<div id="divExito" class="">
	<h4 class="mb-3">El archivo Excel se ha convertido a XML correctamente.</h4>
	<h6 class="mb-3">
		Se proceso un archivo <?php if($proceso!='contable'):?>para el banco <?php endif;?><strong><?php echo ucfirst($proceso);?></strong>
		<?php if ($proceso != 'contable'):?>en formato <strong><?php echo $formato;?></strong><?php endif;?>
	</h6>
	<h6 class="mb-3">
		Se proceso un archivo para la empresa <strong><?php echo $empresa;?></strong>
		(<strong>LID: <?php echo $lid;?></strong>)
	</h6>
	<h6 class="mb-3">
		Se procesaron un total de <strong><?php echo $NbOfTxs;?> registros</strong>
	</h6>
	<?php if ($proceso != 'contable'):?>
	<h6 class="mb-5">
		El importe total procesado fue de <strong><?php echo $MonId;?> <?php echo number_format($CtrlSum, 2, ',', '.');?></strong>
	</h6>
	<?php else:?>
	<h6 class="mb-5">
		Se generaron un total de <strong><?php echo count($documentsTitles);?> archivos</strong>
	</h6>
	<?php endif;?>
	<?php if($proceso!='contable'):?>
	<div class="bd-example-snippet bd-code-snippet">
		<div class="bd-example mb-5 border-0">
			<div class="accordion" id="accordionExample">
				<div class="accordion-item">
					<h4 class="accordion-header">
						<button class="accordion-button collapsed" type="button"
							data-bs-toggle="collapse" data-bs-target="#collapseOne"
							aria-expanded="false" aria-controls="collapseOne">
                <?php echo $nombrearchivoxml?>
              </button>
					</h4>
					<div id="collapseOne" class="accordion-collapse collapse"
						data-bs-parent="#accordionExample" style="">
						<div class="accordion-body">
							<pre>
    <?php
    // Crear un objeto DOMDocument
    // $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    // $dom->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

    // Obtener el XML formateado como string
    $prettyXml = $dom->saveXML();

    echo highlight_string($prettyXml, true);
    ?>
                </pre>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<h4 class="mb-5">Qué deseas hacer?</h4>
	<div class="d-grid gap-2 mb-3">
		<a href="<?php echo $nombrearchivoxml; ?>" download target="_blank"
			class="btn btn-success">Descargar Archivo XML</a>
	</div>
	<?php else:?>
	<?php foreach($documentsTitles as $k=>$document):?>
	
	<div class="bd-example-snippet bd-code-snippet">
		<div class="bd-example mb-3 border-0">
			<div class="accordion" id="accordionExample">
				<div class="accordion-item">
					<h4 class="accordion-header">
						<button class="accordion-button collapsed" type="button"
							data-bs-toggle="collapse" data-bs-target="#collapse<?=$k;?>"
							aria-expanded="false" aria-controls="collapseOne">
                <?php echo $document[0]?>.xml
              </button>
					</h4>
					<div id="collapse<?=$k;?>" class="accordion-collapse collapse"
						data-bs-parent="#accordionExample" style="">
						<div class="accordion-body">
							<pre>
    <?php
    // Obtener el contenido del archivo XML como una cadena
    $xmlContent = file_get_contents('archive/'.$document[0].'.xml');
    
    // Crear un nuevo objeto DOMDocument
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    
    
    // Cargar la cadena en el objeto DOMDocument
    $dom->loadXML($xmlContent);
    
    // Si deseas obtener el XML formateado como string
    $prettyXml = $dom->saveXML();

    echo highlight_string($prettyXml, true);
    ?>
                </pre>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endforeach;?>
	
	<h4 class="mb-5">Qué deseas hacer?</h4>
	
	<?php foreach($documentsTitles as $k=>$document):?>
		
	<div class="d-grid gap-2 mb-3">
		<a href="archive/<?php echo $document[0]; ?>.xml" download target="_blank"
			class="btn btn-success">Descargar Archivo XML<br/><?php echo $document[0] ?>.xml
		</a>
	</div>
	<?php endforeach;?>
	<?php endif;?>
	<?php if($proceso === "santander"):?>
   <div class="d-grid gap-2 mb-5">
     <a 
       href="enviar_por_internet.php?proceso=santander&archivo=<?php echo $nombrearchivoxml; ?>" 
       class="btn btn-primary">Enviar por Internet</a>
  		<p>
  			<em>* La acción será registrada</em>
  		</p>
	  </div>
	<?php elseif($proceso === "contable"):?>
    <!-- Form invisible con un foreach para poder enviar varios archivos -->
    <form id="formEnviar" action="enviar_por_internet.php?proceso=contable" method="post">
      <?php foreach($documentsTitles as $k=>$document):?>
      <input type="hidden" name="archivos[]" value="archive/<?php echo $document[0] ?>.xml">
      <?php endforeach;?>
    </form>
    <div class="d-grid gap-2 mb-5">
            <a
                onclick="document.getElementById('formEnviar').submit();"
                class="btn btn-primary">Enviar por Internet</a>
            <p>
                <em>* La acción será registrada</em>
            </p>
    </div>
  <?php elseif($proceso === "citi"):?>
    <div class="d-grid gap-2 mb-5">
      <a
              href="enviar_por_internet.php?proceso=citi&archivo=<?php echo $nombrearchivoxml; ?>"
              class="btn btn-primary disabled">Enviar por Internet</a>
      <p>
              <em>* La acción será registrada</em>
      </p>
    </div>
	<?php endif;?>
	<div class="d-grid gap-2 mb-5">
		<a type="button" href="index.php"
			class="float-right btn btn-outline-secondary">Volver</a>
	</div>
	<br>
</div>
<?php endif;?>  
  
 <?php
if ($error) {
    print '<div id="divError" class="">';
    print '  <h4 class="mb-3">Se ha producido un error</h4>';
    print '  <table class="table mb-5">';
    print '    <thead>';
    print '      <tr>';
    print '        <th scope="col">Listado de errores</th>';
    print '      </tr>';
    print '    </thead>';
    print '    <tbody>';
    foreach ($msgs as $msg) {
        print '        <tr class="table-' . $msg[0] . '">';
        print '          <th scope="row">' . $msg[1] . '</th>';
        print '        </tr>';
    }
    print '    </tbody>';
    print '  </table>';
    print '  <div class="d-grid gap-2 mb-5">';
    print '    <a href="index.php" class="btn btn-primary">Volver</a>';
    print '  </div>';
    print '</div>';
    print '<br>';
}

?>
<?php include 'blocks/footer.php'?>
