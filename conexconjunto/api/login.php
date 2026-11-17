<?php

require __DIR__ . '/config.php';
require __DIR__ . '/conexion.php';

$usuario    = $_POST["usuario"];
$contrasena = $_POST["contrasena"];

try {
    // La contraseña se guardó con SHA2 en la base, así que se compara con SHA2 también
    $sql = "SELECT id_empleado FROM empleados
    WHERE usuario = :usuario AND contrasena = SHA2(:contrasena, 256);";

    // Execute the SQL query
    $select = $conn->prepare($sql);
    $select->bindParam(":usuario", $usuario);
    $select->bindParam(":contrasena", $contrasena);
    $select->execute();

    // Process the result set
    $id = "";
    if ($select->rowCount() > 0) {
        // Output data of each row
        while($empleado = $select->fetch()) {
            $id = $empleado["id_empleado"];
        }
    }
    echo $id;   // vacío = usuario o contraseña incorrectos
}
catch(PDOException $e) {
    # echo "Error: " . $e->getMessage();
}

$conn = null;
