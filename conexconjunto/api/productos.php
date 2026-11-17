<?php

require __DIR__ . '/config.php';
require __DIR__ . '/conexion.php';

try {

    # C ---> INSERT
    if (isset($_GET["registrar"])) {
        $nombre       = $_POST["nombre_producto"];
        $precio       = $_POST["precio"];
        $categoria    = $_POST["categoria"];
        $id_proveedor = $_POST["id_proveedor"];

        $sql = "INSERT INTO productos(nombre_producto, precio, categoria, id_proveedor)
        VALUES (:nombre, :precio, :categoria, :id_proveedor);";

        // Prepare the SQL query template
        $stmt = $conn->prepare($sql);
        // Bind parameters
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":categoria", $categoria);
        $stmt->bindParam(":id_proveedor", $id_proveedor);
        $stmt->execute();

        echo "ok";
    }

    # R ---> SELECT (JOIN con proveedores para traer también el nombre del proveedor)
    elseif (isset($_GET["buscar"])) {
        $busqueda = "";

        if (isset($_GET["busqueda"])) {
            $busqueda = $_GET["busqueda"];
        }

        $busqueda = "%$busqueda%";

        $sql = "SELECT p.id_producto, p.nombre_producto, p.precio, p.categoria,
                       p.id_proveedor, pr.nombre_proveedor
        FROM productos p
        JOIN proveedores pr ON pr.id_proveedor = p.id_proveedor
        WHERE p.nombre_producto LIKE :busqueda1
           OR p.categoria LIKE :busqueda2
           OR pr.nombre_proveedor LIKE :busqueda3
        ORDER BY p.nombre_producto;";

        // Execute the SQL query
        $select = $conn->prepare($sql);
        $select->bindParam(":busqueda1", $busqueda);
        $select->bindParam(":busqueda2", $busqueda);
        $select->bindParam(":busqueda3", $busqueda);
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

        $sql = "SELECT p.id_producto, p.nombre_producto, p.precio, p.categoria,
                       p.id_proveedor, pr.nombre_proveedor
        FROM productos p
        JOIN proveedores pr ON pr.id_proveedor = p.id_proveedor
        WHERE p.id_producto = :id
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
        $id           = $_POST["id"];
        $nombre       = $_POST["nombre_producto"];
        $precio       = $_POST["precio"];
        $categoria    = $_POST["categoria"];
        $id_proveedor = $_POST["id_proveedor"];

        $sql = "UPDATE productos
        SET nombre_producto=:nombre, precio=:precio,
            categoria=:categoria, id_proveedor=:id_proveedor
        WHERE id_producto=:id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":categoria", $categoria);
        $stmt->bindParam(":id_proveedor", $id_proveedor);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        echo "ok";
    }

    # D ---> DELETE
    elseif (isset($_GET["eliminar"])) {
        $id = $_POST["id"];

        $sql = "DELETE FROM productos WHERE id_producto=:id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        echo "ok";
    }
}
catch(PDOException $e) {
    // Por ejemplo: una id_proveedor que no existe (llave foránea)
    http_response_code(400);
    echo "error";
    # echo "Error: " . $e->getMessage();
}

$conn = null;
