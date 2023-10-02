<?php include 'blocks/header.php'?>
  <form action="procesar.php" method="post" enctype="multipart/form-data">
    <h3 class="mb-3" for="archivoExcel">Paso 1 - Selecciona un archivo de Excel</h3>
    <div class="w-100 justify-content-between">    
        <div class="row align-items-center">
            <div class="col-md-6 form-group">
                 <legend>Seleccionar banco</legend>
            </div>
            <div class="col-md-6 form-group">
                 <legend>Seleccionar Asiento Contable</legend>
            </div>
        </div>
        <div class="row align-items-center">   
        	<div class="col-md-6 form-group mb-5">
                 <div class="form-check">
                      <input type="radio" name="radios" class="form-check-input" id="citi" onchange="validarArchivo()" value="citi">
                      <img class="bi me-2" height="25" role="img" aria-label="Bootstrap" src="assets/img/citi.png">
                 </div>
                 <div class="form-check">
                      <input type="radio" name="radios" class="form-check-input" id="santander" onchange="validarArchivo()" value="santander">
                      <img class="bi me-2" height="25" role="img" aria-label="Bootstrap" src="assets/img/santander.png">
                 </div>
            </div>
            <div class="col-md-6 form-group mb-5">
                 <div class="form-check">
                      <input type="radio" name="radios" class="form-check-input" id="contable" onchange="validarArchivo()" value="contable">
                      <img class="bi me-2" height="50" role="img" aria-label="Bootstrap" src="assets/img/contable.png">
                 </div>
            </div>
        </div>
        <div class="row align-items-center">         
            <div class="col-md-6 mb-5">
            	<a href="assets/dist/EjemploImportacion.xls" target="_blank" class="mx-auto btn btn-success w-100">Descargar Bancos XLS de Ejemplo</a>
        	</div>
            <div class="col-md-6 mb-5">
            	<a href="assets/dist/EjemploImportacionContable.xls" target="_blank" class="mx-auto btn btn-success w-100">Descargar Contable XLS de Ejemplo</a>
        	</div>
    	</div>
    </div>
    <div class="form-group mb-5">
      <input type="file" class="form-control form-control-lg" id="archivoExcel" name="archivoExcel" onchange="validarArchivo()" accept=".xls, .xlsx">
    </div>
    <div class="d-grid gap-2 mb-5">
      <button type="submit" class="btn btn-primary" id="botonConvertir" disabled>Convertir
      	<svg class="bi ms-1" width="20" height="20"><use xlink:href="#arrow-right-short"></use></svg>
      </button>
    </div>
  </form>
  <br/>
  
  <script>
    function validarArchivo() {
      var inputArchivo = document.getElementById('archivoExcel');
      var checkciti = document.getElementById('citi');
      var checksantander = document.getElementById('santander');
      var checkcontable = document.getElementById('contable');
      var botonConvertir = document.getElementById('botonConvertir');
      
      if (inputArchivo.value !== '' && (checkciti.checked || checksantander.checked || checkcontable.checked)) {
        botonConvertir.removeAttribute('disabled');
      } else {
        botonConvertir.setAttribute('disabled', 'true');
      }
    }
  </script>
<?php include 'blocks/footer.php'?>


