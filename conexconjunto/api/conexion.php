<?php

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "conjunto";

try {
    // charset=utf8mb4: necesario para que los acentos (Pérez, Gómez) salgan bien en el JSON
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Configurar el modo de error de PDO para excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Cada fila se devuelve solo con los nombres de columna (sin duplicar con números)
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
    http_response_code(500);
    echo "Connection failed: " . $e->getMessage();
    exit;
}
