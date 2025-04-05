<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];

include('../includes/db.php');

$stmt = $pdo->query("SELECT numero_recibo, propietario, fecha_emision, estado_pago FROM recibos ORDER BY numero_recibo DESC");
$recibos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Recibos</title>
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f0f8ff;
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
            padding: 30px;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .recibo-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .recibo-header img {
            width: 24px;
        }

        .busqueda {
            margin-bottom: 20px;
            width: 100%;
            max-width: 800px;
            display: flex;
            justify-content: flex-end;
        }

        .busqueda input {
            padding: 8px 12px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px 0 0 5px;
        }

        .busqueda button {
            background-color: #0097A7;
            border: none;
            padding: 8px 12px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }

        .busqueda button img {
            width: 16px;
        }

        .recibo-lista {
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .recibo-card {
            background: linear-gradient(145deg, #e0f7fa, #b2ebf2);
            padding: 20px 25px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
        }

        .recibo-card:hover {
            transform: translateY(-5px);
        }

        .recibo-card div {
            flex: 1;
        }

        .recibo-card div:last-child {
            text-align: right;
        }

        .recibo-card strong {
            font-size: 16px;
            color: #006064;
        }

        .recibo-link {
            text-decoration: none;
            color: inherit;
        }

        .recibo-card .ver-icono img {
            width: 28px;
            height: 28px;
            transition: transform 0.2s;
        }

        .recibo-card .ver-icono img:hover {
            transform: scale(1.2);
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
            <a href="derechohabiente.php"><img src="../Image/avatar1.png" alt="Tipo de derechohabiente"> Tipo de derechohabiente ⏷</a>
            <div class="submenu">
                <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt="Agregar"> Agregar derechohabiente</a>
                <a href="Natural.php"><img src="../Image/usuario1.png" alt="Natural"> Natural</a>
                <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt="Jurídica"> Jurídica</a>
            </div>
            <a href="recibo.php"><img src="../Image/factura.png" alt="Recibo"> Recibo</a>
            <a href="listado.php"><img src="../Image/lista.png" alt="Listado"> Listado</a>
            <a href="reporte.php"><img src="../Image/reporte.png" alt="Reporte"> Reporte</a>
        </div>

        <div class="content">
            <div class="recibo-header">
                <img src="../Image/lista-de-verificacion.png" alt="Icono"> <h2>Lista de Recibos</h2>
            </div>

            <div class="busqueda">
                <input type="text" placeholder="Buscar Usuario">
                <button><img src="../Image/lupa1.png" alt="Buscar"></button>
            </div>

            <div class="recibo-lista">
                <?php if (count($recibos) > 0): ?>
                    <?php foreach ($recibos as $recibo): ?>
                        <a href="actualizarRecibo.php?numero=<?= urlencode($recibo['numero_recibo']) ?>" class="recibo-link">
                            <div class="recibo-card">
                                <div>
                                    <strong>N° Recibo:</strong><br>
                                    <?= htmlspecialchars($recibo['numero_recibo']) ?><br>
                                    <?= htmlspecialchars($recibo['propietario']) ?>
                                </div>
                                <div>
                                    <strong>Fecha emisión:</strong><br>
                                    <?= htmlspecialchars($recibo['fecha_emision']) ?>
                                </div>
                                <div>
                                    <strong>Estado:</strong><br>
                                    <?= htmlspecialchars($recibo['estado_pago']) ?>
                                </div>
                                <div class="ver-icono">
                                    <img src="../Image/ojo.png" alt="Ver">
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay recibos registrados aún.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>
</body>
</html>
