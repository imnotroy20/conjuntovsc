<?php

require __DIR__ . '/config.php';
require __DIR__ . '/conexion.php';

header("Content-Type: text/plain; charset=utf-8");

if (
    !isset($_POST["usuario"], $_POST["contrasena"]) ||
    !is_string($_POST["usuario"]) ||
    !is_string($_POST["contrasena"]) ||
    $_POST["usuario"] === "" ||
    $_POST["contrasena"] === ""
) {
    http_response_code(400);
    exit;
}

$usuario = $_POST["usuario"];
$contrasena = $_POST["contrasena"];

try {
    $select = $conn->prepare(
        "SELECT id_empleado, contrasena
         FROM empleados
         WHERE usuario = :usuario
         LIMIT 1;"
    );
    $select->execute([":usuario" => $usuario]);
    $empleado = $select->fetch();

    if (!$empleado) {
        http_response_code(401);
        exit;
    }

    $contrasenaGuardada = $empleado["contrasena"];
    $hashContrasena = hash("sha256", $contrasena);

    if (hash_equals($contrasenaGuardada, $hashContrasena)) {
        echo $empleado["id_empleado"];
        exit;
    }

    // Actualiza a SHA-256 las contraseñas en texto plano del respaldo inicial.
    if (hash_equals($contrasenaGuardada, $contrasena)) {
        $update = $conn->prepare(
            "UPDATE empleados
             SET contrasena = :hash
             WHERE id_empleado = :id AND contrasena = :actual;"
        );
        $update->execute([
            ":hash" => $hashContrasena,
            ":id" => $empleado["id_empleado"],
            ":actual" => $contrasenaGuardada
        ]);

        echo $empleado["id_empleado"];
        exit;
    }

    http_response_code(401);
}
catch (PDOException $e) {
    http_response_code(500);
}

$conn = null;
