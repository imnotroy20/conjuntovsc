<?php
require "config.php";
require "conexion.php";

$nombreUsuario = $_POST['txtUsuario'];
$contrasena = $_POST['txtContrasena'];

try {
    // 1. Preparamos la consulta con PDO usando marcadores nombrados
    $select = $conn->prepare("SELECT id FROM usuarios WHERE nombreUsuario = :usuario AND contrasena = :contrasena");
    
    // 2. Ejecutamos pasando los parámetros en un arreglo asociativo
    $select->execute([
        ':usuario' => $nombreUsuario,
        ':contrasena' => $contrasena
    ]);

    // 3. Verificamos si se encontró el usuario
    if ($select->rowCount() > 0) {
        $usuario = $select->fetch(PDO::FETCH_ASSOC);
        $_SESSION['usuarioId'] = $usuario['id'];
        $_SESSION['nombre'] = $nombreUsuario;
        header('Location: bienvenida.php');
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }

} catch (PDOException $e) {
    // 4. EL BLOQUE CATCH OBLIGATORIO PARA CERRAR EL TRY
    echo "Error: " . $e->getMessage();
}
?>