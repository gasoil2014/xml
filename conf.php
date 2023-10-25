<?php   
$error = '';
include './blocks/header.php';
include './configuracion.php';

if(!isset($_GET['form'])){
    //Verifico si hay una sesion activa
    if (!isset($_SESSION["user"])) {
        $error = "Debe iniciar sesión para modificar la configuración.";
    }
} else if ($_GET['form'] === "formLogin") {
    //credenciales ingresadas por el usuario
    $user = $_POST['user'];
    $password = $_POST['password'];
    // Valido inicio de sesion
    if (array_key_exists($user, $users)) {
        if (password_verify($password, $users[$user])) {
            $_SESSION["user"] = $user;
        } else if (!password_verify($password, $users[$user])) {
            $error = 'Contraseña incorrecta';
        }
    } else {
        $error = "Usuario incorrecto";
    };
} else if ($_GET['form'] === "formConfig") {
    //Verifico si hay una sesion activa
    if (!isset($_SESSION["user"])) {
        $error = "Debe iniciar sesión para modificar la configuración.";
    } else {
        // Comprobar si el archivo existe
        if (!file_exists('configuracion.php')) {
            echo "El archivo no existe."; 
        } else{
            $newDirectorio = $_POST['directory'];
            $newFilaInicio = $_POST['filaInicio'];
            // Leer las líneas del archivo en un array
            $lineas = file('configuracion.php', FILE_SKIP_EMPTY_LINES);
            $contenido_modificado = "";
            // Recorrer cada línea con un foreach
            foreach ($lineas as $linea) {
                if("\$directory" === substr($linea, 0,10)){
                    $linea = "\$directory = '".$newDirectorio."';"."\n";
                }
                if("\$filaInicial" === substr($linea, 0,12)){
                    $linea = "\$filaInicial = ".$newFilaInicio.";";
                }
                $contenido_modificado = $contenido_modificado.$linea;
            };        
            file_put_contents('configuracion.php', $contenido_modificado);
            echo "<script>
                    let save = true;
            </script>";
        }
    }
}
?>

<div class="d-flex flex-column align-items-center justify-content-between">
    <?php if (!$error):?>
        <!-- Inicio de sesion correcto  -->
        <div class="d-flex flex-column align-items-center">
            <h2>Configuracion</h2>
        </div>
        <div id="liveAlertPlaceholder"></div>  
        <div class="w-75">
            <form class="m-5" id="configForm" name="form" action="conf.php?form=formConfig" method="post" enctype="multipart/form-data">
                <div class="mb-5">
                    <h3>General</h3>
                    <div class="mb-4">
                        <label for="directory" class="form-label">Directorio</label>
                        <input type="text" name="directory" class="form-control border border-secondary" id="directory" value=<?php echo $directory?> required>
                    </div>
                </div>
                <div class="mb-5">
                    <h3>Proceso Contable</h3>
                    <div class="mb-4">
                        <label for="filaInicio" class="form-label">Fila del XLS donde inician los registros</label>
                        <input type="number" name="filaInicio" class="form-control border border-secondary" id="filaInicio" value=<?php echo $filaInicial?> required>
                    </div>
                </div>
                <div class="d-flex justify-content-evenly">
                    <button type="submit" id="save" class="btn btn-primary">Guardar cambios</button>
                    <button type="button" id="reset" class="btn btn-danger">Reestablecer cambios</button>
                </div>
            </form>
        </div>
    <?php else:?>
        <!-- Inicio de sesion incorrecto  -->
        <div class="d-flex flex-column align-items-center">
            <h3><?php echo $error?></h3>
            <a href="index.php" class="btn btn-primary">Volver al inicio</a>
        </div>
    <?php endif;?>
</div>

<script>
    const btnReset = document.getElementById("reset");

    const alertPlaceholder = document.getElementById("liveAlertPlaceholder")
    const appendAlert = (type, icon, text) => {
        const wrapper = document.createElement("div");
        wrapper.innerHTML = [
            `<div class="d-flex alert alert-${type} alert-dismissible" role="alert">`,
            `<svg class="bi d-block mx-2 mb-1" width="24" height="24"><use xlink:href="#${icon}"/></svg>`,
            `<div>${text}</div>`,
            `<button id="close" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`,
            `</div>`,
        ].join("");
        alertPlaceholder.append(wrapper);
    };

    btnReset.addEventListener("click", () =>{
        document.getElementById("directory").value = "archive/";
        document.getElementById("filaInicio").value = 11;
        appendAlert("info", "info-fill", "Los cambios se reestablecieron, guarde la configuración para aplicarlos");
    })

    if (save === true) {
        appendAlert("success", "check-circle-fill", "Cambios guardados correctamente");
        const btnClose = document.getElementById("close");
        btnClose.addEventListener("click", () =>{
            window.location.href = "conf.php";
        })
    };
</script>
<?php include './blocks/footer.php'?>