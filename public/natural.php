<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $eliminar_id = $_POST['eliminar_id'];
    $stmt = $pdo->prepare("DELETE FROM agregarderechohabiente WHERE codigo = :codigo");
    $stmt->bindParam(':codigo', $eliminar_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo "<script>alert('¡Registro eliminado exitosamente!');</script>";
    } else {
        echo "<script>alert('Error al eliminar el registro.');</script>";
    }
}

function obtenerDerechohabientes($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM agregarderechohabiente WHERE tipo_derechohabiente = 'natural'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cobro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .top-bar {
            width: 100%;
            height: 60px;
            background-color: #0097A7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            color: white;
        }

        .container {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 250px;
            background-color: #0097A7;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar img.logo {
            width: 100px;
            margin: 0 auto 15px auto;
            display: block;
            border-radius: 10px;
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 15px;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #007c91;
        }

        .sidebar a img {
            width: 20px;
            height: 20px;
        }

        .content {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px;
            overflow-x: auto;
        }

        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }

        .search-container input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            max-width: 300px;
            width: 100%;
        }

        .search-container button {
            background-color: #0097A7;
            border: none;
            padding: 10px;
            border-radius: 5px;
            color: white;
            margin-left: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }

        th {
            background-color: #5cb85c;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .action-btn {
            border: none;
            padding: 8px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-view { background-color: #5bc0de; color: white; }
        .btn-edit { background-color: #5cb85c; color: white; }
        .btn-delete { background-color: #d9534f; color: white; }

        .bottom-bar {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #0097A7;
            color: white;
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <h2>Sistema de Cobro</h2>
        <div class="admin-container">
            <span class="icon">🔄</span>
            <span>Admin name 👤</span>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET" class="logo">
            <h3>Sistema de Cobro</h3>
            <a href="dashboard.php"><img src="../Image/hogarM.png" alt="Inicio"> Inicio</a>
            <a href="derechohabiente.php"><img src="../Image/avatar1.png" alt="Tipo"> Tipo de derechohabiente ⏷</a>
            <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt="Agregar"> Agregar derechohabiente</a>
            <a href="Natural.php"><img src="../Image/usuario1.png" alt="Natural"> Natural</a>
            <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt="Jurídica"> Jurídica</a>
            <a href="recibo.php"><img src="../Image/factura.png" alt="Recibo"> Recibo</a>
            <a href="listado.php"><img src="../Image/lista.png" alt="Listado"> Listado</a>
            <a href="reporte.php"><img src="../Image/reporte.png" alt="Reporte"> Reporte</a>
        </div>

        <div class="content">
            <h2>Lista de Derechohabientes - Tipo: Natural</h2>
            <div class="search-container">
                <input type="text" id="search" placeholder="Buscar Derechohabiente" onkeyup="buscarDerechohabiente()">
                <button onclick="buscarDerechohabiente()"><i class="fas fa-search"></i></button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Nombre</th><th>Identificación</th><th>Dirección</th><th>Teléfono</th><th>Tipo</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaDerechohabientes">
                    <?php
                    $registros = obtenerDerechohabientes($pdo);
                    foreach ($registros as $row) {
                        echo "<tr>
                            <td>{$row['codigo']}</td>
                            <td>{$row['nombre_completo']}</td>
                            <td>{$row['identificacion']}</td>
                            <td>{$row['direccion']}</td>
                            <td>{$row['telefono']}</td>
                            <td>{$row['tipo_derechohabiente']}</td>
                            <td>{$row['estado']}</td>
                            <td>
                                <a href='ver.php?codigo={$row['codigo']}' class='action-btn btn-view'><i class='fas fa-eye'></i></a>
                                <a href='editar.php?codigo={$row['codigo']}' class='action-btn btn-edit'><i class='fas fa-edit'></i></a>
                                <button class='action-btn btn-delete' onclick='confirmarEliminacion({$row['codigo']})'><i class='fas fa-trash-alt'></i></button>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

    <script>
        function buscarDerechohabiente() {
            let input = document.getElementById("search").value.toLowerCase();
            let rows = document.querySelectorAll("#tablaDerechohabientes tr");
            rows.forEach(row => {
                let nombre = row.cells[1].innerText.toLowerCase();
                row.style.display = nombre.includes(input) ? "" : "none";
            });
        }
    </script>
</body>
</html>
