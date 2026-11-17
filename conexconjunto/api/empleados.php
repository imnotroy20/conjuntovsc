<?php

require __DIR__ . '/config.php';
require __DIR__ . '/conexion.php';


try {

    # C ---> INSERT
    if (isset($_GET["registrar"])) {
        $nombre      = $_POST["nombre"];
        $puesto      = $_POST["puesto"];
        $sueldo      = $_POST["sueldo"];
        $usuario     = $_POST["usuario"];
        $contrasena  = $_POST["contrasena"];
        $id_sucursal = $_POST["id_sucursal"];

        $sql = "INSERT INTO empleados(nombre, puesto, sueldo, usuario, contrasena, id_sucursal)
        VALUES (:nombre, :puesto, :sueldo, :usuario, SHA2(:contrasena, 256), :id_sucursal);";

        // Prepare the SQL query template
        $stmt = $conn->prepare($sql);
        // Bind parameters
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":puesto", $puesto);
        $stmt->bindParam(":sueldo", $sueldo);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->bindParam(":contrasena", $contrasena);
        $stmt->bindParam(":id_sucursal", $id_sucursal);
        $stmt->execute();

        echo "ok";
    }

    # R ---> SELECT (JOIN con sucursales para traer también el nombre de la sucursal)
    elseif (isset($_GET["buscar"])) {
        $busqueda = "";

        if (isset($_GET["busqueda"])) {
            $busqueda = $_GET["busqueda"];
        }

        $busqueda = "%$busqueda%";

        $sql = "SELECT e.id_empleado, e.nombre, e.puesto, e.sueldo, e.usuario,
                       e.id_sucursal, s.nombre_sucursal
        FROM empleados e
        JOIN sucursales s ON s.id_sucursal = e.id_sucursal
        WHERE e.nombre LIKE :busqueda1
           OR e.puesto LIKE :busqueda2
           OR s.nombre_sucursal LIKE :busqueda3
        ORDER BY e.nombre;";

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

        $sql = "SELECT e.id_empleado, e.nombre, e.puesto, e.sueldo, e.usuario,
                       e.id_sucursal, s.nombre_sucursal
        FROM empleados e
        JOIN sucursales s ON s.id_sucursal = e.id_sucursal
        WHERE e.id_empleado = :id
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
        $id          = $_POST["id"];
        $nombre      = $_POST["nombre"];
        $puesto      = $_POST["puesto"];
        $sueldo      = $_POST["sueldo"];
        $usuario     = $_POST["usuario"];
        $id_sucursal = $_POST["id_sucursal"];

        $sql = "UPDATE empleados
        SET nombre=:nombre, puesto=:puesto, sueldo=:sueldo,
            usuario=:usuario, id_sucursal=:id_sucursal
        WHERE id_empleado=:id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":puesto", $puesto);
        $stmt->bindParam(":sueldo", $sueldo);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->bindParam(":id_sucursal", $id_sucursal);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        echo "ok";
    }

    # D ---> DELETE
    elseif (isset($_GET["eliminar"])) {
        $id = $_POST["id"];

        $sql = "DELETE FROM empleados WHERE id_empleado=:id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        echo "ok";
    }
}
catch(PDOException $e) {
    // Por ejemplo: usuario repetido (UNIQUE) o una id_sucursal que no existe (llave foránea)
    http_response_code(400);
    echo "error";
    # echo "Error: " . $e->getMessage();
}

$conn = null;
