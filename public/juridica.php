<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Eliminar derechohabiente si se recibe una solicitud POST
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

// Obtener registros desde la base de datos para tipo jurídica
function obtenerDerechohabientes($pdo)
{
    // Mostrar solo los registros de tipo JURÍDICA
    $stmt = $pdo->prepare("SELECT * FROM agregarderechohabiente WHERE tipo_derechohabiente = 'juridica'");
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

        /* Barra superior */
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

        .top-bar h2 {
            font-size: 18px;
        }

        .admin-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-container a {
            text-decoration: none;
            background-color: red;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .admin-container a:hover {
            background-color: darkred;
        }

        /* Contenedor principal */
        .container {
            display: flex;
            flex: 1;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #0097A7;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar img {
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
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #007c91;
        }

        /* Contenido principal */
        .content {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px;
        }

        /* Estilo de la barra de búsqueda */
        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }

        .search-container input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            max-width: 300px;
            outline: none;
        }

        .search-container button {
            background-color: #0097A7;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            margin-left: 5px;
        }

        .search-container button i {
            font-size: 16px;
        }

        /* Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
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

        /* Botones de acción */
        .action-btn {
            border: none;
            padding: 8px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-view {
            background-color: #5bc0de;
            color: white;
        }

        .btn-edit {
            background-color: #5cb85c;
            color: white;
        }

        .btn-delete {
            background-color: #d9534f;
            color: white;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .modal-content h3 {
            margin-bottom: 10px;
            font-size: 20px;
        }

        .modal-content p {
            margin-top: 10px;
            font-size: 14px;
            color: #888;
        }

        /* Estilo del ícono */
        .modal-icon {
            font-size: 50px;
            color: #f39c12;
            margin-bottom: 10px;
        }

        .modal-btns {
            margin-top: 20px;
            display: flex;
            justify-content: space-around;
        }

        .btn-confirm,
        .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-confirm {
            background-color: #d9534f;
            color: white;
        }

        .btn-cancel {
            background-color: #5bc0de;
            color: white;
        }

        /* Barra inferior */
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

    <!-- Barra superior -->
    <div class="top-bar">
        <h2>Sistema de Cobro</h2>
        <div class="admin-container">
            <span class="icon">🔄</span>
            <span>Admin name 👤</span>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Sidebar (Menú) -->
        <div class="sidebar">
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET">
            <h3>Sistema de Cobro</h3>
            <a href="dashboard.php">🏠 Inicio</a>
            <a href="derechohabiente.php">👤 Tipo de derechohabiente ⏷</a>
            <a href="Agregarderecho.php">➕ Agregar derechohabiente</a>
            <a href="Natural.php">📌 Natural</a>
            <a href="juridica.php">📌 Jurídica</a>
            <a href="recibo.php">🧾 Recibo</a>
            <a href="listado.php">📋 Listado</a>
            <a href="reporte.php">📊 Reporte</a>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <h2>Lista de Derechohabientes - Tipo: Jurídica</h2>

            <!-- Barra de búsqueda -->
            <div class="search-container">
                <input type="text" id="search" placeholder="Buscar Derechohabiente" onkeyup="buscarDerechohabiente()">
                <button onclick="buscarDerechohabiente()"><i class="fas fa-search"></i></button>
            </div>

            <!-- Tabla -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Identificación</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
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
                                <a href='verJ.php?codigo={$row['codigo']}' class='action-btn btn-view'><i class='fas fa-eye'></i></a>
                                <a href='editarJ.php?codigo={$row['codigo']}' class='action-btn btn-edit'><i class='fas fa-edit'></i></a>
                                <button class='action-btn btn-delete' onclick='confirmarEliminacion({$row['codigo']})'><i class='fas fa-trash-alt'></i></button>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="modalEliminar" class="modal">
        <div class="modal-content">
            <i class="fas fa-exclamation-circle modal-icon"></i>
            <h3>¿Estás seguro de eliminar?</h3>
            <p>¡Esta acción no se puede deshacer!</p>
            <div class="modal-btns">
                <button class="btn-confirm" onclick="eliminarDerechohabiente()">Sí, eliminar</button>
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Barra inferior -->
    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

    <script>
        let idEliminar = 0;

        function confirmarEliminacion(id) {
            idEliminar = id;
            document.getElementById('modalEliminar').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalEliminar').style.display = 'none';
        }

        function eliminarDerechohabiente() {
            if (idEliminar !== 0) {
                // Crear un formulario dinámico para enviar el ID a eliminar
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'eliminar_id';
                input.value = idEliminar;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

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
