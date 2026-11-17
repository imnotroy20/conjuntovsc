<?php

require __DIR__ . '/config.php';
require __DIR__ . '/conexion.php';

try {

    # C ---> INSERT
    if (isset($_GET["registrar"])) {
        $nombre    = $_POST["nombre_proveedor"];
        $contacto  = $_POST["contacto"];
        $categoria = $_POST["categoria"];

        $sql = "INSERT INTO proveedores(nombre_proveedor, contacto, categoria)
        VALUES (:nombre, :contacto, :categoria);";

        // Prepare the SQL query template
        $stmt = $conn->prepare($sql);
        // Bind parameters
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":categoria", $categoria);
        $stmt->execute();

        echo "ok";
    }

    # R ---> SELECT
    elseif (isset($_GET["buscar"])) {
        $busqueda = "";

        if (isset($_GET["busqueda"])) {
            $busqueda = $_GET["busqueda"];
        }

        $busqueda = "%$busqueda%";

        $sql = "SELECT * FROM proveedores
        WHERE nombre_proveedor LIKE :busqueda1 OR categoria LIKE :busqueda2
        ORDER BY nombre_proveedor;";

        // Execute the SQL query
        $select = $conn->prepare($sql);
        $select->bindParam(":busqueda1", $busqueda);
        $select->bindParam(":busqueda2", $busqueda);
        $select->execute();

        $array = array();

        // Process the result set
        if ($select->rowCount() > 0) {
            // Output data of each row
            while($row = $select->fetch()) {
                $array[] = $row;
            }
        }

        header("Content-Type: application/json");
        echo json_encode($array);
        exit;
    }

    # U ---> UPDATE 
    elseif (isset($_GET["editar"])) {
        $id = $_GET["id"];

        $sql = "SELECT * FROM proveedores
        WHERE id_proveedor = :id
        LIMIT 1;";

        $select = $conn->prepare($sql);
        $select->bindParam(":id", $id);
        $select->execute();

        $array = array();

        if ($select->rowCount() > 0) {
            while($row = $select->fetch()) {
                $array[] = $row;
            }
        }

        header("Content-Type: application/json");
        echo json_encode($array);
        exit;
    }

    elseif (isset($_GET["modificar"])) {
        $id        = $_POST["id"];
        $nombre    = $_POST["nombre_proveedor"];
        $contacto  = $_POST["contacto"];
        $categoria = $_POST["categoria"];

        $sql = "UPDATE proveedores
        SET nombre_proveedor=:nombre, contacto=:contacto, categoria=:categoria
        WHERE id_proveedor=:id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":categoria", $categoria);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        echo "ok";
    }

    # D ---> DELETE
    elseif (isset($_GET["eliminar"])) {
        $id = $_POST["id"];

        $sql = "DELETE FROM proveedores WHERE id_proveedor=:id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        echo "ok";
    }
}
catch(PDOException $e) {
    // por ejemplo al borrar un proveedor que todavía tiene productos (ON DELETE RESTRICT)
    http_response_code(400);
    echo "error";
    # echo "Error: " . $e->getMessage();
}

$conn = null;
