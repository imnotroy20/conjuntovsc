<?php

require __DIR__ . '/config.php';
require __DIR__ . '/conexion.php';

try {

    # C ---> INSERT
    if (isset($_GET["registrar"])) {
        $nombre    = $_POST["nombre_sucursal"];
        $direccion = $_POST["direccion"];
        $telefono  = $_POST["telefono"];

        $sql = "INSERT INTO sucursales(nombre_sucursal, direccion, telefono)
        VALUES (:nombre, :direccion, :telefono);";

        // Prepare the SQL query template
        $stmt = $conn->prepare($sql);
        // Bind parameters
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":telefono", $telefono);
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

        $sql = "SELECT * FROM sucursales
        WHERE nombre_sucursal LIKE :busqueda1 OR direccion LIKE :busqueda2
        ORDER BY nombre_sucursal;";

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

    # U ---> UPDATE (editar: trae el registro / modificar: guarda los cambios)
    elseif (isset($_GET["editar"])) {
        $id = $_GET["id"];

        $sql = "SELECT * FROM sucursales
        WHERE id_sucursal = :id
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
        $nombre    = $_POST["nombre_sucursal"];
        $direccion = $_POST["direccion"];
        $telefono  = $_POST["telefono"];

        $sql = "UPDATE sucursales
        SET nombre_sucursal=:nombre, direccion=:direccion, telefono=:telefono
        WHERE id_sucursal=:id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        echo "ok";
    }

    # D ---> DELETE
    elseif (isset($_GET["eliminar"])) {
        $id = $_POST["id"];

        $sql = "DELETE FROM sucursales WHERE id_sucursal=:id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        echo "ok";
    }
}
catch(PDOException $e) {
    // Por ejemplo: borrar una sucursal que todavía tiene empleados (ON DELETE RESTRICT)
    http_response_code(400);
    echo "error";
    # echo "Error: " . $e->getMessage();
}

$conn = null;
