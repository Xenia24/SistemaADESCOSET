<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include('../includes/db.php');

$recibo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE recibos SET 
            fecha_emision = :fecha_emision,
            fecha_vencimiento = :fecha_vencimiento,
            propietario = :propietario,
            direccion = :direccion,
            fecha_lectura = :fecha_lectura,
            metros_cubicos = :metros_cubicos,
            numero_suministro = :numero_suministro,
            numero_medidor = :numero_medidor,
            lectura_anterior = :lectura_anterior,
            lectura_actual = :lectura_actual,
            meses_pendiente = :meses_pendiente,
            multas = :multas,
            total = :total,
            estado_pago = :estado_pago
        WHERE numero_recibo = :numero_recibo
    ");

    $stmt->execute([
        ':fecha_emision' => $_POST['fecha_emision'],
        ':fecha_vencimiento' => $_POST['fecha_vencimiento'],
        ':propietario' => $_POST['propietario'],
        ':direccion' => $_POST['direccion'],
        ':fecha_lectura' => $_POST['fecha_lectura'],
        ':metros_cubicos' => $_POST['metros_cubicos'],
        ':numero_suministro' => $_POST['numero_suministro'],
        ':numero_medidor' => $_POST['numero_medidor'],
        ':lectura_anterior' => $_POST['lectura_anterior'],
        ':lectura_actual' => $_POST['lectura_actual'],
        ':meses_pendiente' => $_POST['meses_pendiente'],
        ':multas' => $_POST['multas'],
        ':total' => $_POST['total'],
        ':estado_pago' => $_POST['estado_pago'],
        ':numero_recibo' => $_POST['numero_recibo']
    ]);

    echo "<script>alert('✅ Recibo actualizado correctamente'); window.location.href = 'listado.php';</script>";
    exit();
}

if (isset($_GET['numero'])) {
    $numero = $_GET['numero'];
    $stmt = $pdo->prepare("SELECT * FROM recibos WHERE numero_recibo = ?");
    $stmt->execute([$numero]);
    $recibo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recibo) {
        echo "Recibo no encontrado.";
        exit();
    }
} else {
    echo "No se proporcionó un número de recibo.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Recibo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }

        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .top-bar {
            background-color: #0097A7;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        }

        .sidebar img.logo {
            width: 120px;
            margin: 0 auto 20px;
            display: block;
            border-radius: 10px;
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 15px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background-color: #007c91;
        }

        .submenu {
            padding-left: 20px;
            display: flex;
            flex-direction: column;
        }

        .submenu a {
            font-size: 14px;
            padding: 8px;
            background-color: rgba(255,255,255,0.2);
            border-radius: 5px;
        }

        .submenu a:hover {
            background-color: rgba(255,255,255,0.4);
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
        }

        .field label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .field input, .field select {
            width: 100%;
            padding: 6px;
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
            background-color: #0097A7;
            color: white;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <h2>Sistema de Cobro</h2>
        <div>
            <span>Admin 👤</span> |
            <a href="logout.php" style="color: white;">Cerrar sesión</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET" class="logo">
            <h3>Sistema de Cobro</h3>
            <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>
            <a href="derechohabiente.php"><img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷</a>
            <div class="submenu">
                <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente</a>
                <a href="Natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
                <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt=""> Jurídica</a>
            </div>
            <a href="recibo.php"><img src="../Image/factura.png" alt=""> Recibo</a>
            <a href="listado.php"><img src="../Image/lista.png" alt=""> Listado</a>
            <a href="reporte.php"><img src="../Image/reporte.png" alt=""> Reporte</a>
        </div>

        <div class="content">
            <div class="form-box">
                <form action="" method="POST">
                    <h3>Actualizar Recibo</h3>
                    <input type="hidden" name="numero_recibo" value="<?= $recibo['numero_recibo'] ?>">

                    <div class="row">
                        <div class="field">
                            <label>Fecha Emisión:</label>
                            <input type="text" name="fecha_emision" class="datepicker" value="<?= $recibo['fecha_emision'] ?>" required>
                        </div>
                        <div class="field">
                            <label>Fecha Vencimiento:</label>
                            <input type="text" name="fecha_vencimiento" class="datepicker" value="<?= $recibo['fecha_vencimiento'] ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="field">
                            <label>Propietario:</label>
                            <input type="text" name="propietario" value="<?= $recibo['propietario'] ?>" required>
                        </div>
                        <div class="field">
                            <label>Dirección:</label>
                            <input type="text" name="direccion" value="<?= $recibo['direccion'] ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="field">
                            <label>Fecha Lectura:</label>
                            <input type="text" name="fecha_lectura" class="datepicker" value="<?= $recibo['fecha_lectura'] ?>" required>
                        </div>
                        <div class="field">
                            <label>Metros Cúbicos:</label>
                            <input type="number" step="0.01" name="metros_cubicos" value="<?= $recibo['metros_cubicos'] ?>" required>
                        </div>
                        <div class="field">
                            <label>N° Suministro:</label>
                            <input type="text" name="numero_suministro" value="<?= $recibo['numero_suministro'] ?>" required>
                        </div>
                        <div class="field">
                            <label>N° Medidor:</label>
                            <input type="text" name="numero_medidor" value="<?= $recibo['numero_medidor'] ?>" required>
                        </div>
                        <div class="field">
                            <label>L. Anterior:</label>
                            <input type="number" step="0.01" name="lectura_anterior" value="<?= $recibo['lectura_anterior'] ?>" required>
                        </div>
                        <div class="field">
                            <label>L. Actual:</label>
                            <input type="number" step="0.01" name="lectura_actual" value="<?= $recibo['lectura_actual'] ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="field">
                            <label>Meses Pendientes:</label>
                            <input type="number" name="meses_pendiente" value="<?= $recibo['meses_pendiente'] ?>" required>
                        </div>
                        <div class="field">
                            <label>Multas:</label>
                            <input type="number" step="0.01" name="multas" value="<?= $recibo['multas'] ?>" required>
                        </div>
                        <div class="field">
                            <label>Total:</label>
                            <input type="number" step="0.01" name="total" value="<?= $recibo['total'] ?>" required>
                        </div>
                        <div class="field">
                            <label>Estado:</label>
                            <select name="estado_pago" required>
                                <option value="">Seleccione estado</option>
                                <option value="Pagado" <?= $recibo['estado_pago'] === 'Pagado' ? 'selected' : '' ?>>Pagado</option>
                                <option value="No pagado" <?= $recibo['estado_pago'] === 'No pagado' ? 'selected' : '' ?>>No pagado</option>
                                <option value="En mora" <?= $recibo['estado_pago'] === 'En mora' ? 'selected' : '' ?>>En mora</option>
                                <option value="Pagado fuera de fecha" <?= $recibo['estado_pago'] === 'Pagado fuera de fecha' ? 'selected' : '' ?>>Pagado fuera de fecha</option>
                            </select>
                        </div>
                    </div>

                    <div class="buttons">
                        <a href="listado.php" class="btn btn-limpiar"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                        <button type="submit" class="btn btn-guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
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
            dateFormat: "Y-m-d"
        });
    </script>
</body>
</html>
