<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se está editando un registro
$modo_edicion = false;
$Usuarios = [
    'id' => '',
    'nombre_completo' => '',
    'correo' => '',
    'telefono' => '',
    'numero_dui' => '',
    'nombre_usuario' => '',
    'estado' => '',
    'tipo_usuario' => 'General',
];

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM usuariosag WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $Usuarios = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($Usuarios) {
        $modo_edicion = true;
    } else {
        echo "<script>alert('¡No se encontró Usuario General!'); window.location.href='ListGeneral.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id'];
    $nombre_completo = $_POST['nombre_completo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $numero_dui = $_POST['numero_dui'];
    $nombre_usuario = $_POST['nombre_usuario'];
    $estado = $_POST['estado'];
    $tipo_usuario = $_POST['tipo_usuario'];

    // Comprobar si es edición
    $stmt_check = $pdo->prepare("SELECT id FROM usuariosag WHERE id = :id");
    $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_check->execute();
    $modo_edicion = $stmt_check->fetch() ? true : false;

    try {
        if ($modo_edicion) {
            $stmt = $pdo->prepare("UPDATE usuariosag SET 
                                    nombre_completo = :nombre_completo,
                                    correo = :correo,
                                    telefono = :telefono,
                                    numero_dui = :numero_dui,
                                    nombre_usuario = :nombre_usuario,
                                    estado = :estado,
                                    tipo_usuario = :tipo_usuario
                                    WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $mensaje_exito = "¡Registro actualizado exitosamente!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuariosag 
                                    (nombre_completo, correo, telefono, numero_dui, nombre_usuario, estado, tipo_usuario)
                                    VALUES (:nombre_completo, :correo, :telefono, :numero_dui, :nombre_usuario, :estado, :tipo_usuario)");
            $mensaje_exito = "¡Registro guardado exitosamente!";
        }

        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':numero_dui', $numero_dui);
        $stmt->bindParam(':nombre_usuario', $nombre_usuario);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':tipo_usuario', $tipo_usuario);

        if ($stmt->execute()) {
            echo "<script>alert('$mensaje_exito'); window.location.href='ListGeneral.php';</script>";
        } else {
            echo "<script>alert('Error al guardar los cambios.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Usuario General</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* ----------------------------------------
       RESET Y ESTILOS GENERALES
    ---------------------------------------- */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-color: #E0F7FA;
    }
    label {
      font-weight: bold;
      color: #37474F;
      display: block;
      margin-bottom: 6px;
    }
    input, select {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      transition: border-color .2s, box-shadow .2s;
      width: 100%;
    }
    input:focus, select:focus {
      outline: none;
      border-color: #0097A7;
      box-shadow: 0 0 0 3px rgba(0,151,167,0.2);
    }

    /* ----------------------------------------
       BARRA SUPERIOR FIJA
    ---------------------------------------- */
    .top-bar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: #0097A7;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      color: #fff;
      z-index: 1000;
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
      background-color: red;
      color: #fff;
      text-decoration: none;
      padding: 8px 12px;
      border-radius: 5px;
      transition: background .2s;
    }
    .admin-container a:hover {
      background-color: darkred;
    }

    /* ----------------------------------------
       CONTENEDOR PRINCIPAL
    ---------------------------------------- */
    .container {
      display: flex;
      flex: 1;
      margin-top: 60px; /* espacio para top-bar fija */
      margin-bottom: 60px; /* espacio para bottom-bar fija */
    }

    /* ----------------------------------------
       SIDEBAR (sin cambios de funcionalidad)
    ---------------------------------------- */
    .sidebar {
      width: 250px;
      background: #0097A7;
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 60px;
      left: 0;
      bottom: 0;
      overflow-y: auto;
      transition: width .3s ease;
    }
    .sidebar.hidden {
      width: 0;
      padding: 0;
      overflow: hidden;
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
    .sidebar a, .sidebar .toggle {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      border-radius: 5px;
      color: #fff;
      text-decoration: none;
      transition: background .3s;
      cursor: pointer;
    }
    .sidebar a:hover, .sidebar .toggle:hover {
      background-color: #007c91;
    }
    .sidebar a img, .toggle img {
      width: 20px;
      height: 20px;
    }
    .submenu {
      display: none;
      flex-direction: column;
      gap: 5px;
      padding-left: 20px;
      margin-top: 8px;
    }
    .submenu.show {
      display: flex;
    }
    .submenu a {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px;
      background: rgba(255,255,255,0.2);
      border-radius: 5px;
      color: #fff;
      text-decoration: none;
      transition: background .3s;
    }
    .submenu a:hover {
      background-color: rgba(255,255,255,0.4);
    }
    .submenu a img {
      width: 16px;
      height: 16px;
    }

    /* ----------------------------------------
       CONTENIDO
    ---------------------------------------- */
    .content {
      flex: 1;
      background: #fff;
      margin-left: 270px; /* espacio para sidebar */
      margin-right: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 0 20px; /* centrado vertical ya automático por align-items */
      border-radius: 10px;
      overflow-y: auto;
      transition: margin-left .3s ease;
    }
    .content.sidebar-hidden {
      margin-left: 20px;
    }

    /* ----------------------------------------
       DISEÑO “TARJETA + GRID” PARA FORMULARIO
    ---------------------------------------- */
    .form-card {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      max-width: 700px;
      width: 100%;
      border: 2px solid #0097A7;
    }
    .form-card h1 {
      font-size: 1.6rem;
      margin-bottom: 20px;
      color: #0097A7;
      text-align: center;
    }
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px,1fr));
      gap: 20px;
    }
    .form-group {
      display: flex;
      flex-direction: column;
    }
    .buttons {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 30px;
    }
    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: transform .1s, box-shadow .1s;
    }
    .btn-save {
      background: #0097A7;
      color: #fff;
    }
    .btn-cancel {
      background: red;
      color: #fff;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.12);
    }
    @media (max-width: 600px) {
      .buttons {
        flex-direction: column;
      }
      .btn {
        width: 100%;
      }
    }

    /* ----------------------------------------
       BARRA INFERIOR FIJA
    ---------------------------------------- */
    .bottom-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: #0097A7;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>
<body>
  <!-- Barra superior -->
  <div class="top-bar">
    <div style="display: flex; align-items: center; gap: 10px;">
      <h2 style="margin: 0;">Sistema de Inventario</h2>
      <button id="toggleSidebarBtn" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">
        <i class="fas fa-bars"></i>
      </button>
    </div>
    <span id="fecha-actual" style="font-size: 16px;"></span>
    <div class="admin-container">
      <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <!-- Contenedor principal -->
  <div class="container">
    <!-- Sidebar (sin cambios de funcionalidad) -->
    <div class="sidebar">
      <img src="../Image/logoadesco.jpg" class="logo" alt="Logo ADESCOSET">
      <h3>Sistema de Inventario</h3>

      <a href="dashboard2.php"><img src="../Image/hogarM.png" alt="Inicio"> Inicio</a>

      <a href="#" class="toggle-submenu">
        <i class="fa-solid fa-users"></i> Usuarios ⏷
      </a>
      <div class="submenu" id="submenu-usuarios">
        <a href="AgregarUsuario.php">
          <i class="fa-solid fa-user-plus"></i> Agregar Usuario
        </a>
        <a href="ListAdministrador.php">
          <i class="fa-solid fa-user-tie"></i> Administradores
        </a>
        <a href="ListGeneral.php">
          <i class="fa-solid fa-user-group"></i> Geerales
        </a>
      </div>

      <a href="AgregarCat.php"><img src="../Image/factura.png" alt="Categorías"> Categorias</a>

      <a href="#" class="toggle-submenu2">
        <i class="fa-solid fa-truck"></i> Productos ⏷
      </a>
      <div class="submenu" id="submenu-productos">
        <a href="ListProductos.php">
          <i class="fa-solid fa-clipboard-list"></i>Lista de Productos
        </a>
        <a href="AgregarPro.php">
          <i class="fa-solid fa-circle-plus"></i> Agregar Producto
        </a>
        <a href="RetirarPro.php">
          <i class="fa-solid fa-cart-plus"></i>Retirar Productos
        </a>
      </div>

      <a href="Reportes.php"><img src="../Image/reporte.png" alt="Reporte"> Reportes</a>
    </div>

    <!-- Contenido principal (formulario centrado) -->
    <div class="content">
      <div class="form-card">
        <h1><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Usuario General</h1>
        <form method="POST" action="">
          <div class="form-grid">
            <div class="form-group">
              <label for="nombre_completo">Nombre Completo</label>
              <input type="text" id="nombre_completo" name="nombre_completo" value="<?= htmlspecialchars($Usuarios['nombre_completo']) ?>" required>
            </div>
            <div class="form-group">
              <label for="correo">Correo</label>
              <input type="text" id="correo" name="correo" value="<?= htmlspecialchars($Usuarios['correo']) ?>" required>
            </div>
            <div class="form-group">
              <label for="telefono">Teléfono</label>
              <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($Usuarios['telefono']) ?>" required>
            </div>
            <div class="form-group">
              <label for="numero_dui">Número de DUI</label>
              <input type="text" id="numero_dui" name="numero_dui" value="<?= htmlspecialchars($Usuarios['numero_dui']) ?>" required>
            </div>
            <div class="form-group">
              <label for="nombre_usuario">Nombre de Usuario</label>
              <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?= htmlspecialchars($Usuarios['nombre_usuario']) ?>" required>
            </div>
            <div class="form-group">
              <label for="estado">Estado</label>
              <select id="estado" name="estado" required>
                <option value="activo" <?= $Usuarios['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $Usuarios['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
              </select>
            </div>
            <div class="form-group">
              <label for="tipo_usuario">Tipo de Usuario</label>
              <select id="tipo_usuario" name="tipo_usuario" required>
                <option value="General Cobro" <?= $Usuarios['tipo_usuario'] === 'General Cobro' ? 'selected' : '' ?>>General Cobro</option>
                <option value="General Inventario" <?= $Usuarios['tipo_usuario'] === 'General Inventario' ? 'selected' : '' ?>>General Inventario</option>
              </select>
            </div>
          </div>
          <div class="buttons">
            <a href="ListGeneral.php" class="btn btn-cancel">Cancelar</a>
            <button type="submit" class="btn btn-save"><?= $modo_edicion ? 'Actualizar' : 'Guardar' ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Barra inferior fija -->
  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Alternar visibilidad del submenú Usuarios
    document.querySelector('.toggle-submenu').addEventListener('click', () => {
      document.getElementById('submenu-usuarios').classList.toggle('show');
    });
    // Alternar visibilidad del submenú Productos
    document.querySelector('.toggle-submenu2').addEventListener('click', () => {
      document.getElementById('submenu-productos').classList.toggle('show');
    });
    // Alternar visibilidad del sidebar completo
    document.getElementById('toggleSidebarBtn').addEventListener('click', () => {
      document.querySelector('.sidebar').classList.toggle('hidden');
      document.querySelector('.content').classList.toggle('sidebar-hidden');
    });
    // Mostrar fecha actual en la top-bar
    function actualizarFecha() {
      const fechaElemento = document.getElementById('fecha-actual');
      const fecha = new Date();
      const opciones = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      };
      fechaElemento.textContent = fecha.toLocaleDateString('es-ES', opciones);
    }
    actualizarFecha();
    const ahora = new Date();
    const msHastaMedianoche = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate() + 1).getTime() - ahora.getTime();
    setTimeout(() => {
      actualizarFecha();
      setInterval(actualizarFecha, 24 * 60 * 60 * 1000);
    }, msHastaMedianoche);
  </script>
</body>
</html>
