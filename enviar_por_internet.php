<?php include 'blocks/header.php'?>
<?php
if (isset($_GET['archivo'])) {
    $xmlFilePath = $_GET['archivo'];
    
    // Obtener el contenido del archivo XML
    $xmlContent = file_get_contents($xmlFilePath);
    
    // URL de destino para enviar el archivo
    $url = 'https://ejemplo.com/procesar_archivo.php'; // Cambia la URL a la que corresponda
    
    // Inicializar cURL
    $ch = curl_init($url);
    
    // Configurar opciones de cURL
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('xml' => $xmlContent));
    
    // Configurar opciones para recibir respuesta
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Ejecutar la solicitud cURL
    $response = curl_exec($ch);
    
    // Obtener informaci�n adicional sobre la solicitud
    $info = curl_getinfo($ch);
    
    // Cerrar la conexi�n cURL
    curl_close($ch);
    
    // Mostrar la respuesta del servidor y la informaci�n adicional
    echo "<pre>";
    echo "Respuesta del servidor:\n";
    echo $response . "\n\n";
    echo "Informaci�n de la solicitud:\n";
    print_r($info);
    echo "</pre>";
} else {
    echo "Archivo no especificado.";
}
?>
<?php include 'blocks/footer.php'?>
