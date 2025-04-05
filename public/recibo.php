<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];

include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO recibos (
                numero_recibo, fecha_emision, fecha_vencimiento,
                propietario, direccion, fecha_lectura,
                numero_suministro, numero_medidor,
                metros_cubicos, lectura_anterior, lectura_actual,
                meses_pendiente, multas, total
            ) VALUES (
                :numero_recibo, :fecha_emision, :fecha_vencimiento,
                :propietario, :direccion, :fecha_lectura,
                :numero_suministro, :numero_medidor,
                :metros_cubicos, :lectura_anterior, :lectura_actual,
                :meses_pendiente, :multas, :total
            )
        ");

        $stmt->execute([
            ':numero_recibo' => $_POST['numero_recibo'],
            ':fecha_emision' => $_POST['fecha_emision'],
            ':fecha_vencimiento' => $_POST['fecha_vencimiento'],
            ':propietario' => $_POST['propietario'],
            ':direccion' => $_POST['direccion'],
            ':fecha_lectura' => $_POST['fecha_lectura'],
            ':numero_suministro' => $_POST['numero_suministro'],
            ':numero_medidor' => $_POST['numero_medidor'],
            ':metros_cubicos' => $_POST['metros_cubicos'],
            ':lectura_anterior' => $_POST['lectura_anterior'],
            ':lectura_actual' => $_POST['lectura_actual'],
            ':meses_pendiente' => $_POST['meses_pendiente'],
            ':multas' => $_POST['multas'],
            ':total' => $_POST['total']
        ]);

        header("Location: listado.php");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('❌ Error al guardar: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo</title>

    <!-- Iconos y calendario -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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
            width: 120px;
            margin: 0 auto 20px auto;
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
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #007c91;
        }

        .submenu {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding-left: 20px;
        }

        .submenu a {
            font-size: 14px;
            padding: 8px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }

        .submenu a:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }

        .content {
            flex: 1;
            background-color: white;
            padding: 30px;
        }

        .form-box {
            background-color: #e0e0e0;
            border: 2px solid #0097A7;
            padding: 20px;
            max-width: 1000px;
            margin: auto;
        }

        .form-box h3 {
            text-align: center;
            background-color: #ccc;
            padding: 5px;
            margin-bottom: 15px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .field {
            flex: 1;
            min-width: 200px;
            position: relative;
        }

        .field label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .field input {
            width: 100%;
            padding: 6px 30px 6px 10px;
        }

        .icon-field i {
            position: absolute;
            right: 10px;
            top: 37px;
            color: #555;
            pointer-events: none;
        }

        .buttons {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin: 0 10px;
        }

        .btn-limpiar {
            background-color: #f08080;
            color: white;
        }

        .btn-guardar {
            background-color: #0097A7;
            color: white;
        }

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
            <a href="logout.php" style="color:white;">Cerrar sesión</a>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET" class="logo">
            <h3>Sistema de Cobro</h3>
            <a href="dashboard.php"><img src="../Image/hogarM.png" alt="Inicio"> Inicio</a>
            <a href="derechohabiente.php"><img src="../Image/avatar1.png" alt="Tipo de derechohabiente"> Tipo de derechohabiente ⏷</a>
            <div class="submenu">
                <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt="Agregar derechohabiente"> Agregar derechohabiente</a>
                <a href="Natural.php"><img src="../Image/usuario1.png" alt="Natural"> Natural</a>
                <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt="Jurídica"> Jurídica</a>
            </div>
            <a href="recibo.php"><img src="../Image/factura.png" alt="Recibo"> Recibo</a>
            <a href="listado.php"><img src="../Image/lista.png" alt="Listado"> Listado</a>
            <a href="reporte.php"><img src="../Image/reporte.png" alt="Reporte"> Reporte</a>
        </div>

        <!-- Contenido -->
        <div class="content">
            <div class="form-box">
                <form action="" method="POST">
                    <h3>Recibo</h3>

                    <div class="row">
                        <div class="field">
                            <label>N° Recibo:</label>
                            <input type="number" name="numero_recibo" required>
                        </div>
                        <div class="field icon-field">
                            <label>Fecha Emisión:</label>
                            <input type="text" name="fecha_emision" class="datepicker" required>
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="field icon-field">
                            <label>Fecha Vencimiento:</label>
                            <input type="text" name="fecha_vencimiento" class="datepicker" required>
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                    </div>

                    <h3>Informe Propietario</h3>
                    <div class="row">
                        <div class="field">
                            <label>Propietario:</label>
                            <input type="text" name="propietario" required>
                        </div>
                        <div class="field">
                            <label>Dirección:</label>
                            <input type="text" name="direccion" required>
                        </div>
                    </div>

                    <h3>Informe de Consumo</h3>
                    <div class="row">
                        <div class="field icon-field">
                            <label>Fecha Lectura:</label>
                            <input type="text" name="fecha_lectura" class="datepicker" required>
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="field">
                            <label>Metros Cúbicos:</label>
                            <input type="number" step="0.01" name="metros_cubicos" required>
                        </div>
                        <div class="field">
                            <label>N° Suministro:</label>
                            <input type="text" name="numero_suministro" required>
                        </div>
                        <div class="field">
                            <label>N° Medidor:</label>
                            <input type="text" name="numero_medidor" required>
                        </div>
                        <div class="field">
                            <label>L. Anterior:</label>
                            <input type="number" step="0.01" name="lectura_anterior" required>
                        </div>
                        <div class="field">
                            <label>L. Actual:</label>
                            <input type="number" step="0.01" name="lectura_actual" required>
                        </div>
                    </div>

                    <h3>Estado de Cuenta</h3>
                    <div class="row">
                        <div class="field">
                            <label>Meses Pendientes:</label>
                            <input type="number" name="meses_pendiente" required>
                        </div>
                        <div class="field">
                            <label>Multas:</label>
                            <input type="number" step="0.01" name="multas" required>
                        </div>
                        <div class="field">
                            <label>Total:</label>
                            <input type="number" step="0.01" name="total" required>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="reset" class="btn btn-limpiar"><i class="fa-solid fa-broom"></i> Limpiar</button>
                        <button type="submit" class="btn btn-guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            locale: "es"
        });
    </script>
</body>
</html>
