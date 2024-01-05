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
    $procesos = [];
    $fechas = [];
    $archivos = [];
    $respuestas = [];
    $datas = [];

    // Procesar cada bloque
    foreach ($logs as $log) {
        // Dividir el bloque en líneas
        $lineas = explode("\n", $log);

        // Inicializar variables
        $fecha = $archivo = $respuesta = $data = $proceso = '';
        $errorLog = 0;

        foreach ($lineas as $linea) {
            if(!preg_match('/Error: (.+)/', $linea)){
                // Extraer información utilizando expresiones regulares
                if (preg_match('/Proceso: (.+?) --/', $linea, $matches)) {
                    $proceso = $matches[1];
                }
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
            $procesos[] = $proceso;
            $fechas[] = $fecha;
            $archivos[] = $archivo;
            $respuestas[] = $respuesta;
            $datas[] = $data;
        }
    }
    ?>
    <h4 id="cantArchivos" class="text-center mb-5">Archivos enviados</h4>
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
    <div class="mb-3">
        <ul class="nav nav-tabs justify-content-evenly">
        <li class="nav-item">
            <button id="tabSantander" class="nav-link active" aria-current="page" href="#">Banco Santander</button>
        </li>
        <li class="nav-item">
            <button id="tabContable" class="nav-link">Contable</button>
        </li>
        <li class="nav-item">
            <button id="tabCiti" class="nav-link disabled">Banco Citi</button>
        </li>
        </ul>
    </div>
    <script>
        const btnSantander = document.getElementById("tabSantander");
        const btnContable = document.getElementById("tabContable");
        const btnCiti = document.getElementById("tabCiti");

        //Declaracion de variables para contar archivos
        let cantArchivosSantander = 0;
        let cantArchivosContable = 0;

        btnSantander.onclick = () =>{
            btnSantander.classList = "nav-link active";
            btnContable.classList = "nav-link";
            btnCiti.classList = "nav-link disabled";

            document.getElementById("cantArchivos").innerText = "Archivos enviados: " + cantArchivosSantander;

            document.querySelectorAll("#santander").forEach(element => {
                element.style.display = "block"
            });;
            document.querySelectorAll("#contable").forEach(element => {
                element.style.display = "none"
            });;
            document.querySelectorAll("#citi").forEach(element => {
                element.style.display = "none"
            });;
        }

        btnContable.onclick = () =>{
            btnSantander.classList = "nav-link";
            btnContable.classList = "nav-link active";
            btnCiti.classList = "nav-link disabled";

            document.getElementById("cantArchivos").innerText = "Archivos enviados: " + cantArchivosContable;

            document.querySelectorAll("#santander").forEach(element => {
                element.style.display = "none"
            });;
            document.querySelectorAll("#contable").forEach(element => {
                element.style.display = "block"
            });;
            document.querySelectorAll("#citi").forEach(element => {
                element.style.display = "none"
            });;
        }
    </script>
    <div class="list-group">
    <?php $i = 0 ?>
    <?php foreach($archivos as $archivo):?>
	  <?php 
        //Escribir respuesta de la api
        if(!empty($archivo)){
          $xml = simplexml_load_string($respuestas[$i]);
          $dom = new DOMDocument('1.0');
          $dom->preserveWhiteSpace = false;
          $dom->formatOutput = true;
          $dom->loadXML($xml->asXML());
          $xmlString = highlight_string($dom->saveXML(), true);
        }

        //Formatear fecha
        $fechaOriginal = $fechas[$i];
        $dateTime = new DateTime($fechaOriginal);
        $fechaFormateada = $dateTime->format('H:i, j\/m\/Y');

        if ($procesos[$i] == "Santander"): ?>
        <script>
            cantArchivosSantander++;
            document.getElementById("cantArchivos").innerText = "Archivos enviados: " + cantArchivosSantander;
        </script>
        <div id="santander" class="mb-5">
            <a <?php echo (file_exists($archivo)) ? "href='".$archivo."' download" : "href=# onclick=noFile()"?> class="list-group-item list-group-item-action" aria-current="true">
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
    <?php elseif($procesos[$i] == "Contable"): ?>
        <script>
            cantArchivosContable++;
        </script>
        <div id="contable" class="mb-5" style="display: none">
            <a <?php echo (file_exists($archivo)) ? "href='".$archivo."' download" : "href=# onclick=noFile()"?> class="list-group-item list-group-item-action" aria-current="true">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class=""><?php echo $archivo?></h5>
                    <small><?php echo $fechaFormateada?></small>
                </div>
            </a>
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
    <?php endif; ?>
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
