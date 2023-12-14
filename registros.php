<?php include 'blocks/header.php'; ?>
<?php
$archivo = 'generadorxml.log';
$error = 0;
if(!filesize($archivo)){
    $error++;
} else {
    $fp = fopen($archivo,'r');
    $texto = fread($fp, filesize($archivo));
    if (strpos($texto, "Respuesta") == false) {
        $error++;
    }
}?>

<?php if(!$error):?>
    <?php
    //abrimos el archivo en lectura
    $fp = fopen($archivo,'r');
    //leemos el archivo
    $texto = fread($fp, filesize($archivo));
    $logs = explode("\n\n", $texto);

    // Variables para almacenar los valores
    $fechas = [];
    $archivos = [];
    $respuestas = [];
    $datas = [];

    // Procesar cada bloque
    foreach ($logs as $log) {
        // Dividir el bloque en líneas
        $lineas = explode("\n", $log);

        // Inicializar variables
        $fecha = $archivo = $respuesta = $data = '';
        $errorLog = 0;

        foreach ($lineas as $linea) {
            if(!preg_match('/Error: (.+)/', $linea)){
                // Extraer información utilizando expresiones regulares
                if (preg_match('/Fecha: (.+?) --/', $linea, $matches)) {
                    $fecha = $matches[1];
                }
                if (preg_match('/Archivo: (.+)/', $linea, $matches)) {
                    $archivo = $matches[1];
                }
                if (preg_match('/Respuesta: (.+)/', $linea, $matches)) {
                    $respuesta = $matches[1];
                }
                if (preg_match('/Data: (.+)/', $linea, $matches)) {
                    $data = $matches[1];
                }
            } else {
                $errorLog++;
            }
        }

        // Agregar valores a los arrays
        if(!$errorLog){
            $fechas[] = $fecha;
            $archivos[] = $archivo;
            $respuestas[] = $respuesta;
            $datas[] = $data;
        }
    }
    ?>
    <h4 class="text-center mb-5">Archivos enviados</h4>
    <div id="liveAlertPlaceholder"></div>
    <script>
        const noFile = () => {
            const alertPlaceholder = document.getElementById("liveAlertPlaceholder");
            const wrapper = document.createElement("div");
            wrapper.innerHTML = [
                '<div class="d-flex alert alert-danger alert-dismissible" role="alert">',
                '<svg class="bi d-block mx-2 mb-1" width="24" height="24"><use xlink:href="#exclamation-triangle-fill"/></svg>',
                '<div class="w-100 text-center">El archivo no existe en el directorio</div>',
                '<button id="close" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                '</div>',
            ].join("");
            alertPlaceholder.append(wrapper);
        };
    </script>
    <!-- Mostrar resultados -->
    <div class="list-group">
    <?php $i = 0 ?>
    <?php foreach($archivos as $archivo):?>
	<?php //Escribir respuesta de la api
        $xml = simplexml_load_string($respuestas[$i]);
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $xmlString = highlight_string($dom->saveXML(), true);

        //Formatear fecha
        $fechaOriginal = $fechas[$i];
        $dateTime = new DateTime($fechaOriginal);
        $fechaFormateada = $dateTime->format('H:i, j\/m\/Y');
    	?>

	<div class="mb-5">
            <a <?php echo (file_exists($archivo)) ? "href=".$archivo." download" : "href=# onclick=noFile()"?> class="list-group-item list-group-item-action" aria-current="true">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class=""><?php echo $archivo?></h5>
                    <small><?php echo $fechaFormateada?></small>
                </div>
            </a>
             <div class="accordion w-100" id="accordionExample">
                <div class="accordion-item">
                    <h4 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" <?php echo "data-bs-target=#".$i."data"?>
                        aria-expanded="false" aria-controls="collapseOne">
                            Ver datos
                        </button>
                    </h4>
                    <div <?php echo "id=".$i."data"?> class="accordion-collapse collapse" data-bs-parent="#accordionExample" style="">
                        <div class="accordion-body">
                        <?php echo $datas[$i]?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion w-100" id="accordionExample">
                <div class="accordion-item">
                    <h4 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" <?php echo "data-bs-target=#".$i."xml"?>
                        aria-expanded="false" aria-controls="collapseOne">
                            Ver respuesta
                        </button>
                    </h4>
                    <div <?php echo "id=".$i."xml"?> class="accordion-collapse collapse" data-bs-parent="#accordionExample" style="">
                        <div class="accordion-body">
                            <?php echo $xmlString; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php $i++; ?>
    <?php endforeach;?>
    </div>
<?php else: ?>
    <div class="d-flex flex-column align-items-center">
        <h3 class="mb-5">No se enviaron archivos</h3>
        <a href="index.php" class="btn btn-primary">Volver al inicio</a>
    </div>
<?php endif;?>
<?php include 'blocks/footer.php';?>
