<?php include 'blocks/header.php'?>
<?php $banco = $_POST['radios'];?>
  <script>
    function manejarCheckbox(checkboxId) {
      var miCheckbox = document.getElementById(checkboxId);
      
      miCheckbox.disabled = false;  // Habilitar el checkbox
      miCheckbox.click();           // Simular un clic en el checkbox
      miCheckbox.disabled = true;   // Restaurar el estado de deshabilitado
    }
  </script>
  <h4 class="mb-5">Paso 2 - Procesamiento del archivo <?php print ($banco) ? 'para banco <img class="center bi mb-b" height="25" role="img" aria-label="Bootstrap" src="assets/img/'.$banco.'.png">':''?></h4>
      
      <div class="list-group mb-5">
        <label class="list-group-item d-flex gap-3">
          <input id="checkCargando" class="form-check-input flex-shrink-0" type="checkbox" value="" style="font-size: 1.375em;" disabled>
          <span class="pt-1 form-checked-content">
            <strong>Cargando el archivo</strong>
          </span>
        </label>
        <label class="list-group-item d-flex gap-3">
          <input id="checkValidando" class="form-check-input flex-shrink-0" type="checkbox" value="" style="font-size: 1.375em;" disabled>
          <span class="pt-1 form-checked-content">
            <strong>Validando el archivo</strong>
          </span>
        </label>
        <label class="list-group-item d-flex gap-3">
          <input id="checkProcesando" class="form-check-input flex-shrink-0" type="checkbox" value="" style="font-size: 1.375em;" disabled>
          <span class="pt-1 form-checked-content">
            <strong>Procesando el archivo</strong>
          </span>
        </label>
        <label class="list-group-item d-flex gap-3">
          <input id="checkGenerando" class="form-check-input flex-shrink-0" type="checkbox" value="" style="font-size: 1.375em;" disabled>
          <span class="pt-1 form-checked-content">
            <strong>Generando el archivo</strong>
          </span>
        </label>
      </div>  
      
<?php
require 'assets/PHPExcel/PHPExcel.php';

// Defino las variables para utilizar luego
$nombrearchivoxml = ''; //B2: Nombre de archivo xml
$MsgId = ''; // C2: Encabezado del XML
$CreDtTm  = ''; // C3: FORMULA: hora de generacion del xml → YYYY-MM-DDTHH:MM:SS
$NbOfTxs  = ''; // C4: Cantidad de registros
$CtrlSum  = ''; // C5: Suma total
$empresa = ''; // C6: Nombre de la empresa
$nroempresa = ''; // F6: Numero de empresa

$error=0;
$msgs = array();

if($banco=="citi"){
    $error++;
    $msgs[$error] = array("danger","El proceso del banco '".$banco."' aún no ha sido configurado");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$error) {
  if ($_FILES["archivoExcel"]["error"] == UPLOAD_ERR_OK) {
    
        echo "<script>$(document).ready(function() {manejarCheckbox('checkCargando');});</script>"; 
    
        $nombreArchivo = $_FILES["archivoExcel"]["name"];
        $rutaArchivo = $_FILES["archivoExcel"]["tmp_name"];
    

        // Crear un objeto PHPExcel para cargar el archivo Excel
        $excel = PHPExcel_IOFactory::load($rutaArchivo);
        $sheet = $excel->getActiveSheet();
        
        // Valido que los registros cabecera estén correctos
        // TO DO Tomar el directorio del config
        $nombrearchivoxml = 'archive/'.$sheet->getCell('B1')->getValue(); //B2: Nombre de archivo xml
        $MsgId = $sheet->getCell('C2')->getValue(); // C2: Encabezado del XML
        $MsgId = $MsgId.date('YmdHis');
        $CreDtTm  = $sheet->getCell('C3')->getOldCalculatedValue();// C3: FORMULA: hora de generacion del xml → YYYY-MM-DDTHH:MM:SS
        $NbOfTxs  = intval($sheet->getCell('C4')->getOldCalculatedValue());// C4: Cantidad de registros
        $CtrlSum  = $sheet->getCell('C5')->getOldCalculatedValue(); // C5: Suma total
        $empresa = $sheet->getCell('C6')->getValue();// C6: Nombre de la empresa
        $nroempresa = $sheet->getCell('F6')->getValue(); // F6: Numero de empresa
        
        if(empty($nombrearchivoxml) || empty($MsgId) || empty($CreDtTm) || empty($NbOfTxs) || empty($CtrlSum) || empty($empresa) || empty($nroempresa)){
           $error++; 
           $msgs[$error] = array("danger","El encabezado posee campos vacíos que son obligatorios");
        }
        
        //Valido el loop
        foreach ($sheet->getRowIterator(8,8+$NbOfTxs-1) as $row) {
            
            foreach ($row->getCellIterator() as $cell) {
                $valorCelda = $cell->getValue();
                $coordenadas = $cell->getCoordinate();
                $tipoCelda = $cell->getDataType();
                
                if($valorCelda == ''){
                    $error++;
                    $msgs[$error] = array("danger","La celda '".$coordenadas."' esta vacia y es obligatoria");
                }
                
                if (substr($coordenadas, 0,1)=="A"){
                    if($tipoCelda != "s"){
                        $error++;
                        $msgs[$error] = array("danger","La celda '".$coordenadas."' debe ser de tipo fecha (YYYY-MM-DD)");
                    }
                }
                
                if (substr($coordenadas, 0,1)=="B"){
                    if($tipoCelda != "s"){
                        $error++;
                        $msgs[$error] = array("danger","La celda '".$coordenadas."' debe ser de tipo string");
                    }
                }
                
                if (substr($coordenadas, 0,1)=="C"){
                    if($tipoCelda != "n"){
                        $error++;
                        $msgs[$error] = array("danger","La celda '".$coordenadas."' debe ser de tipo moneda");
                    }
                }
    
                if (substr($coordenadas, 0,1)=="D"){
                    if($tipoCelda != "s"){
                        $error++;
                        $msgs[$error] = array("danger","La celda '".$coordenadas."' debe ser de tipo string");
                    }
                }
    
                if (substr($coordenadas, 0,1)=="G"){
                    if($tipoCelda != "s"){
                        $error++;
                        $msgs[$error] = array("danger","La celda '".$coordenadas."' debe ser de tipo string");
                    }
                }
    
                if (substr($coordenadas, 0,1)=="E"){
                    if($tipoCelda != "s"){
                        $error++;
                        $msgs[$error] = array("danger","La celda '".$coordenadas."' debe ser de tipo string");
                    }
                }
    
                if (substr($coordenadas, 0,1)=="F"){
                    if($tipoCelda != "s"){
                        $error++;
                        $msgs[$error] = array("danger","La celda '".$coordenadas."' debe ser de tipo string");
                    }
                }
            }
        }

        // Genero el xml
        if(!$error){
            echo "<script>$(document).ready(function() {manejarCheckbox('checkValidando');});</script>";
        
            // Crear un objeto SimpleXMLElement para generar el XML
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document></Document>');
            $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');
            $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            
            // Escribo el encabezado        
            $xmlCstmrCdtTrfInitn = $xml->addChild('CstmrCdtTrfInitn');
            $xmlGrpHdr = $xmlCstmrCdtTrfInitn->addChild('GrpHdr');
            $xmlGrpHdr->addChild('MsgId',$MsgId);
            $xmlGrpHdr->addChild('CreDtTm',$CreDtTm);  
            $xmlGrpHdr->addChild('NbOfTxs',$NbOfTxs);
            $xmlInitgPty = $xmlGrpHdr->addChild('InitgPty');
            $xmlInitgPty->addChild('Nm',$empresa);
            
            unset($xmlGrpHdr);
            unset($xmlInitgPty);
            
            // Recorrer las filas del archivo Excel y agregar los datos al XML
            $i = 1;
            foreach ($sheet->getRowIterator(8,8+$NbOfTxs-1) as $row) {
              // Levanto todos los valores del XLS
              foreach ($row->getCellIterator() as $cell) {
                  $valorCelda = $cell->getValue();
                  $coordenadas = $cell->getCoordinate();
                  
                  if (substr($coordenadas, 0,1)=="A"){
                      $ReqdExctnDt = $valorCelda;
                  }
                  
                  if (substr($coordenadas, 0,1)=="B"){
                      $EndToEndId = $valorCelda;
                  }
                  
                  if (substr($coordenadas, 0,1)=="C"){
                      $InstdAmt = $valorCelda;
                  }
                  
                  if (substr($coordenadas, 0,1)=="D"){
                      $MmbId = $valorCelda;
                  }

                  if (substr($coordenadas, 0,1)=="E"){
                      $Nm = $valorCelda;
                  }
                  
                  if (substr($coordenadas, 0,1)=="F"){
                      $Id = $valorCelda;
                  }
                  
                  if (substr($coordenadas, 0,1)=="G"){
                      $BrnchId = $valorCelda;
                  }
                  
                  if (substr($coordenadas, 0,1)=="H"){
                      $Othr = $valorCelda;
                  }
              }
              
              // Escribo la parte PmtInf
              $xmlPmtInf = $xmlCstmrCdtTrfInitn->addChild('PmtInf');
              $xmlPmtInf->addChild('PmtInfId', $i);
              $xmlPmtInf->addChild('PmtMtd', 'TRF');
              
              $xmlPmtTpInf = $xmlPmtInf->addChild('PmtTpInf');
              $xmlCtgyPurp = $xmlPmtTpInf->addChild('CtgyPurp');
              $xmlCtgyPurp->addChild('Cd','SALA');
              
              unset($xmlCtgyPurp);
              unset($xmlPmtTpInf);
              
              $xmlPmtInf->addChild('ReqdExctnDt',$ReqdExctnDt);
              
              $xmlDbtr = $xmlPmtInf->addChild('Dbtr');
              $xmlDbtr->addChild('Nm',$empresa);
              
              unset($xmlDbtr);
              
              $xmlDbtrAcct = $xmlPmtInf->addChild('DbtrAcct');
              $xmlId = $xmlDbtrAcct->addChild('Id');
              $xmlId->addChild('Othr')->addChild('Id',$nroempresa);
              
              unset($xmlId);
              unset($xmlDbtr);
              
              $xmlDbtrAgt = $xmlPmtInf->addChild('DbtrAgt');
              $xmlDbtrAgt->addChild('FinInstnId');
              
              unset($xmlDbtrAgt);
              
              $xmlPmtInf->addChild('ChrgBr','DEBT');
              
              $xmlCdtTrfTxInf = $xmlPmtInf->addChild('CdtTrfTxInf');
              $xmlPmtId = $xmlCdtTrfTxInf->addChild('PmtId');
              $xmlPmtId->addChild('EndToEndId',$EndToEndId);
              
              unset($xmlPmtId);
              
              $xmlAmt = $xmlCdtTrfTxInf->addChild('Amt');
              
              $xmlInstdAmt = $xmlAmt->addChild('InstdAmt',$InstdAmt);
              $xmlInstdAmt->addAttribute('Ccy','UYU');
              
              unset($xmlInstdAmt);
              unset($xmlAmt);
              
              $xmlCdtrAgt = $xmlCdtTrfTxInf->addChild('CdtrAgt');
              $xmlFinInstnId = $xmlCdtrAgt->addChild('FinInstnId');
              $xmlClrSysMmbId =  $xmlFinInstnId->addChild('ClrSysMmbId');
              $xmlClrSysMmbId->addChild('MmbId',$MmbId);
              
              unset($xmlClrSysMmbId);
              unset($xmlFinInstnId);
              
              $xmlBrnchId = $xmlCdtrAgt->addChild('BrnchId');
              $xmlBrnchId->addChild('Id',$BrnchId);
              
              unset ($xmlBrnchId);
              unset ($xmlCdtrAgt);

              $xmlCdtr = $xmlCdtTrfTxInf->addChild('Cdtr');
              $xmlCdtr->addChild('Nm',$Nm);
              
              unset($xmlCdtr);
              
              $xmlCdtrAcct = $xmlCdtTrfTxInf->addChild('CdtrAcct');
              
              $xmlCdtrAcct->addChild('Id')->addChild('Othr')->addChild('Id',$Othr);
              
              unset($xmlCdtrAcct);
              unset($xmlCdtTrfTxInf);
              unset($xmlPmtInf);
              
              $i++;
            }
        
            echo "<script>$(document).ready(function() {manejarCheckbox('checkProcesando');});</script>";
            
            // Generar el archivo XML
            file_put_contents($nombrearchivoxml, $xml->asXML());
            
            echo "<script>$(document).ready(function() {manejarCheckbox('checkGenerando');});</script>";
        }
  } else {
      $error++;
      $msgs[$error] = array("danger","No se encontró el archivo para procesar");
  }
}else{
    $error++;
    $msgs[$error] = array("danger","No puede acceder a esta página directamente, por favor vaya a <a href=\"index.php\">Inicio</a>");
}

?>
<?php if (!$error):?>  
    <div id="divExito" class="">
      <h4 class="mb-5">El archivo Excel se ha convertido a XML correctamente.</h4>
      <h4 class="mb-5">Qué deseas hacer?</h4>
      <div class="bd-example-snippet bd-code-snippet"><div class="bd-example mb-5 border-0">
        <div class="accordion" id="accordionExample">
          <div class="accordion-item">
            <h4 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                <?php echo $nombrearchivoxml?>.xml
              </button>
            </h4>
            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample" style="">
              <div class="accordion-body">
              	<?php echo '<pre>'.htmlentities($xml->asXML()).'</pre>'; ?>
              </div>
            </div>
          </div>
        </div>
        </div></div>
      
      <div class="d-grid gap-2 mb-3">
        <a href="<?php echo $nombrearchivoxml; ?>" target="_blank" class="btn btn-success">Descargar Archivo XML</a>
      </div>
      
      <div class="d-grid gap-2 mb-5">
        <a href="enviar_por_internet.php?archivo=<?php echo $nombrearchivoxml; ?>" class="btn btn-primary">Enviar por Internet</a>
        <p><em>* La acción será registrada</em></p>
      </div>
      
      <div class="d-grid gap-2 mb-5">
        <button type=""button href="index.php" class="float-right btn btn-outline-secondary">Volver</button>
      </div>
      <br>
    </div>
<?php endif;?>  
  
 <?php    
 if ($error){
    print '<div id="divError" class="">';
    print '  <h4 class="mb-3">Se ha producido un error</h4>';
    print '  <table class="table mb-5">';
    print '    <thead>';
    print '      <tr>';
    print '        <th scope="col">Listado de errores</th>';
    print '      </tr>';
    print '    </thead>';
    print '    <tbody>';
    foreach ($msgs as $msg){
        print '        <tr class="table-'.$msg[0].'">';
        print '          <th scope="row">'.$msg[1].'</th>';
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
