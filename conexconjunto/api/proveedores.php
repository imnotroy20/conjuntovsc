<?php

require __DIR__ . '/config.php';
require __DIR__ . '/conexion.php';

header("Content-Type: application/json; charset=utf-8");

function responder($datos, $codigo = 200)
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function campoPostRequerido($campo)
{
    if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
        responder([
            "success" => false,
            "message" => "Falta el campo requerido: $campo."
        ], 400);
    }

    $valor = trim($_POST[$campo]);
    if ($valor === "") {
        responder([
            "success" => false,
            "message" => "El campo $campo no puede estar vacío."
        ], 400);
    }

    return $valor;
}

function longitudTexto($texto)
{
    return preg_match_all('/./us', $texto);
}

$acciones = ["buscar", "editar", "registrar", "modificar", "eliminar"];
$accionesSolicitadas = [];
foreach ($acciones as $accionDisponible) {
    if (array_key_exists($accionDisponible, $_GET)) {
        $accionesSolicitadas[] = $accionDisponible;
    }
}

if (count($accionesSolicitadas) !== 1) {
    responder([
        "success" => false,
        "message" => "Indica una sola acción: buscar, editar, registrar, modificar o eliminar."
    ], 400);
}

$accion = $accionesSolicitadas[0];
$metodo = $_SERVER["REQUEST_METHOD"];
$metodoEsperado = in_array($accion, ["buscar", "editar"], true) ? "GET" : "POST";

if ($metodo !== $metodoEsperado) {
    header("Allow: " . $metodoEsperado . ", OPTIONS");
    responder([
        "success" => false,
        "message" => "La acción $accion requiere el método $metodoEsperado."
    ], 405);
}

try {
    if ($accion === "buscar") {
        $busqueda = $_GET["busqueda"] ?? "";
        if (!is_string($busqueda)) {
            responder([
                "success" => false,
                "message" => "El parámetro busqueda debe ser texto."
            ], 400);
        }

        $patron = "%" . trim($busqueda) . "%";
        $consulta = $conn->prepare(
            "SELECT id_proveedor, nombre_proveedor, contacto, categoria
             FROM proveedores
             WHERE nombre_proveedor LIKE :nombre
                OR contacto LIKE :contacto
                OR categoria LIKE :categoria
             ORDER BY nombre_proveedor;"
        );
        $consulta->execute([
            ":nombre" => $patron,
            ":contacto" => $patron,
            ":categoria" => $patron
        ]);

        responder([
            "success" => true,
            "data" => $consulta->fetchAll()
        ]);
    }

    if ($accion === "editar") {
        $id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 1]
        ]);
        if ($id === false) {
            responder([
                "success" => false,
                "message" => "El parámetro id debe ser un entero positivo."
            ], 400);
        }

        $consulta = $conn->prepare(
            "SELECT id_proveedor, nombre_proveedor, contacto, categoria
             FROM proveedores
             WHERE id_proveedor = :id
             LIMIT 1;"
        );
        $consulta->execute([":id" => $id]);
        $proveedor = $consulta->fetch();

        if (!$proveedor) {
            responder([
                "success" => false,
                "message" => "No se encontró el proveedor."
            ], 404);
        }

        responder([
            "success" => true,
            "data" => $proveedor
        ]);
    }

    if ($accion === "registrar" || $accion === "modificar") {
        $nombre = campoPostRequerido("nombre_proveedor");
        $contacto = campoPostRequerido("contacto");
        $categoria = campoPostRequerido("categoria");

        $limites = [
            "nombre_proveedor" => [ $nombre, 100 ],
            "contacto" => [ $contacto, 100 ],
            "categoria" => [ $categoria, 50 ]
        ];
        foreach ($limites as $campo => $limite) {
            $longitud = longitudTexto($limite[0]);
            if ($longitud === false || $longitud > $limite[1]) {
                responder([
                    "success" => false,
                    "message" => "El campo $campo excede su longitud permitida."
                ], 422);
            }
        }

        if ($accion === "registrar") {
            $insertar = $conn->prepare(
                "INSERT INTO proveedores (nombre_proveedor, contacto, categoria)
                 VALUES (:nombre, :contacto, :categoria);"
            );
            $insertar->execute([
                ":nombre" => $nombre,
                ":contacto" => $contacto,
                ":categoria" => $categoria
            ]);

            responder([
                "success" => true,
                "message" => "Proveedor registrado.",
                "data" => ["id_proveedor" => (int) $conn->lastInsertId()]
            ], 201);
        }

        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 1]
        ]);
        if ($id === false) {
            responder([
                "success" => false,
                "message" => "El campo id debe ser un entero positivo."
            ], 400);
        }

        $actualizar = $conn->prepare(
            "UPDATE proveedores
             SET nombre_proveedor = :nombre, contacto = :contacto, categoria = :categoria
             WHERE id_proveedor = :id;"
        );
        $actualizar->execute([
            ":nombre" => $nombre,
            ":contacto" => $contacto,
            ":categoria" => $categoria,
            ":id" => $id
        ]);

        $verificar = $conn->prepare(
            "SELECT id_proveedor FROM proveedores WHERE id_proveedor = :id;"
        );
        $verificar->execute([":id" => $id]);
        if (!$verificar->fetch()) {
            responder([
                "success" => false,
                "message" => "No se encontró el proveedor."
            ], 404);
        }

        responder([
            "success" => true,
            "message" => "Proveedor actualizado."
        ]);
    }

    if ($accion === "eliminar") {
        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 1]
        ]);
        if ($id === false) {
            responder([
                "success" => false,
                "message" => "El campo id debe ser un entero positivo."
            ], 400);
        }

        $eliminar = $conn->prepare(
            "DELETE FROM proveedores WHERE id_proveedor = :id;"
        );
        $eliminar->execute([":id" => $id]);

        if ($eliminar->rowCount() === 0) {
            responder([
                "success" => false,
                "message" => "No se encontró el proveedor."
            ], 404);
        }

        responder([
            "success" => true,
            "message" => "Proveedor eliminado."
        ]);
    }
}
catch (PDOException $e) {
    if ($accion === "eliminar" && $e->getCode() === "23000") {
        responder([
            "success" => false,
            "message" => "No se puede eliminar: hay productos que usan este proveedor."
        ], 409);
    }

    error_log($e->getMessage());
    responder([
        "success" => false,
        "message" => "Ocurrió un error interno al procesar el proveedor."
    ], 500);
}
