<?php
session_start();
include('../includes/db.php'); // Incluye la conexión a la base de datos

//Verificar si el formulario para agregar derechohabiente ha sido enviado
if (isset($_POST['guardar'])) {
  $nombre_completo  = $_POST['nombre_completo'];
  $correo           = $_POST['correo'];
  $telefono         = $_POST['telefono'];
  $numero_dui       = $_POST['numero_dui'];
  $nombre_usuario   = $_POST['nombre_usuario'];
  $contraseña       = $_POST['contraseña'];
  $estado           = $_POST['estado'];
  $tipo_usuario     = $_POST['tipo_usuario'];

  // Verificar que las contraseñas coincidan antes de procesar
  if ($contraseña != $_POST['repetir_contraseña']) {
    $error_agregar = "Las contraseñas no coinciden.";
  } else {
    try {
      $contraseña_cifrada = password_hash($contraseña, PASSWORD_BCRYPT);

      $stmt = $pdo->prepare("
                INSERT INTO usuariosag 
                    (nombre_completo, correo, telefono, numero_dui, nombre_usuario, contraseña, estado, tipo_usuario) 
                VALUES 
                    (:nombre_completo, :correo, :telefono, :numero_dui, :nombre_usuario, :password, :estado, :tipo_usuario)
            ");

      $stmt->bindParam(':nombre_completo', $nombre_completo);
      $stmt->bindParam(':correo', $correo);
      $stmt->bindParam(':telefono', $telefono);
      $stmt->bindParam(':numero_dui', $numero_dui);
      $stmt->bindParam(':nombre_usuario', $nombre_usuario);
      $stmt->bindParam(':password', $contraseña_cifrada);
      $stmt->bindParam(':estado', $estado);
      $stmt->bindParam(':tipo_usuario', $tipo_usuario);

      if ($stmt->execute()) {
        header("Location: AgregarUsuario.php?success=created");
        exit();
      } else {
        $error_agregar = "Error al guardar el registro.";
      }
    } catch (PDOException $e) {
      $error_agregar = "Error: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agregar Usuario – Sistema de Inventario</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      min-height: 100vh;
      background-color: #E0F7FA;
    }

    .top-bar {
      width: 100%;
      height: 60px;
      background-color: #0097A7;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      color: #fff;
    }

    .top-bar h2 {
      font-size: 18px;
    }

    .admin-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .admin-container span {
      display: flex;
      align-items: center;
      gap: 5px;
      font-weight: bold;
    }

    .admin-container a {
      text-decoration: none;
      background-color: red;
      color: #fff;
      padding: 8px 12px;
      border-radius: 5px;
    }

    .admin-container a:hover {
      background-color: darkred;
    }

    .container {
      display: flex;
      flex: 1;
      margin-top: 60px;
      /* espacio para la top-bar fija */
    }

    .sidebar {
      width: 230px;
      background-color: #0097A7;
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 60px;
      left: 0;
      bottom: 0;
      overflow-y: auto;
      transition: width 0.3s ease;
      width: 250px;
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

    .sidebar a {
      text-decoration: none;
      color: #fff;
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

    .sidebar a img {
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

    .submenu a {
      font-size: 14px;
      padding: 8px;
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 5px;
    }

    .submenu a:hover {
      background-color: rgba(255, 255, 255, 0.4);
    }

    .submenu a img {
      width: 16px;
      height: 16px;
    }

    .content {
      flex: 1;
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      margin-left: 250px;
      /* espacio para el sidebar */
      margin-bottom: 40px;
      /* espacio para la bottom-bar */
      transition: margin-left 0.3s ease;
    }

    .content.sidebar-hidden {
      margin-left: 20px;
    }

    /* ----- Diseño del formulario (tomado de ejemplo) ----- */
    .form-container {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      max-width: 700px;
      width: 100%;
      border: 2px solid #0097A7;
      margin: 0 auto;
      margin-top: 40px;
    }

    .form-container h1 {
      font-size: 1.6rem;
      margin-bottom: 20px;
      color: #0097A7;
      text-align: center;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-group label {
      margin-bottom: 6px;
      font-weight: bold;
      color: #37474F;
    }

    .form-group input,
    .form-group select {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      transition: border-color .2s, box-shadow .2s;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #0097A7;
      box-shadow: 0 0 0 3px rgba(0, 151, 167, 0.2);
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
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }

    /* Mensajes de éxito / error */
    .success {
      color: #2E7D32;
      font-weight: bold;
      text-align: center;
      margin-bottom: 20px;
    }

    .error {
      color: #C62828;
      font-weight: bold;
      text-align: center;
      margin-bottom: 20px;
    }

    @media (max-width: 600px) {
      .buttons {
        flex-direction: column;
      }

      .btn {
        width: 100%;
      }
    }

    /* Bottom bar */
    .bottom-bar {
      width: 100%;
      text-align: center;
      padding: 10px;
      background-color: #0097A7;
      color: #fff;
      position: fixed;
      bottom: 0;
      left: 0;
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
    <!-- Sidebar -->
    <div class="sidebar">
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET" class="logo">
            <h3>Sistema de Inventario</h3>

            <a href="dashboard2.php">
                <img src="../Image/hogarM.png" alt="Inicio"> Inicio
            </a>

            <a href="#" class="toggle-submenu">
                <i class="fa-solid fa-users"></i> Usuarios ⏷
            </a>

            <div class="submenu" id="submenu-usuarios" style="display: none;">
                <a href="AgregarUsuario.php">
                    <i class="fa-solid fa-user-plus"></i> Agregar Usuario
                </a>
                <a href="ListAdministrador.php">
                    <i class="fa-solid fa-user-tie"></i> Administradores
                </a>
                <a href="ListGeneral.php">
                    <i class="fa-solid fa-user-group"></i> Generales
                </a>

            </div>


            <a href="AgregarCat.php">
                <img src="../Image/factura.png" alt="Categorias"> Categorias
            </a>

            <a href="#" class="toggle-submenu2">
                <i class="fa-solid fa-truck"></i> Productos ⏷
            </a>


            <div class="submenu" id="submenu-productos" style="display: none;">
                <a href="ListProductos.php">
                    <i class="fa-solid fa-clipboard-list"></i> Lista de Productos
                </a>
                <a href="AgregarPro.php">
                    <i class="fa-solid fa-circle-plus"></i> Agregar Producto
                </a>
                <a href="RetirarPro.php">
                    <i class="fa-solid fa-cart-plus"></i> Retirar Productos
                </a>

            </div>


            <a href="Reportes.php">
                <img src="../Image/reporte.png" alt="Reporte"> Reportes
            </a>
        </div>

    <!-- Contenido principal -->
    <div class="content">
      <div class="form-container">
        <h1>Agregar Usuario</h1>

        <?php if (isset($success)): ?>
          <p class="success"><?= $success ?></p>
        <?php elseif (isset($error_agregar)): ?>
          <p class="error"><?= $error_agregar ?></p>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validarContrasenas();">
          <div class="form-grid">
            <!-- Nombre Completo -->
            <div class="form-group">
              <label for="nombre_completo">Nombre Completo</label>
              <input type="text" id="nombre_completo" name="nombre_completo" required>
            </div>

            <!-- Correo -->
            <div class="form-group">
              <label for="correo">Correo</label>
              <input type="email" id="correo" name="correo" required>
            </div>

            <!-- Teléfono -->
            <div class="form-group">
              <label for="telefono">Teléfono</label>
              <input type="text" id="telefono" name="telefono" required>
            </div>

            <!-- Número de DUI -->
            <div class="form-group">
              <label for="numero_dui">Número de DUI</label>
              <input type="text" id="numero_dui" name="numero_dui" required>
            </div>

            <!-- Nombre de Usuario -->
            <div class="form-group">
              <label for="nombre_usuario">Nombre de Usuario</label>
              <input type="text" id="nombre_usuario" name="nombre_usuario" required>
            </div>

            <!-- Contraseña -->
            <div class="form-group">
              <label for="contraseña">Contraseña</label>
              <input type="password" id="contraseña" name="contraseña" required>
            </div>

            <!-- Repetir Contraseña -->
            <div class="form-group">
              <label for="repetir_contraseña">Repetir Contraseña</label>
              <input type="password" id="repetir_contraseña" name="repetir_contraseña" required>
            </div>

            <!-- Estado -->
            <div class="form-group">
              <label>Estado</label>
              <div style="display: flex; gap: 20px; margin-top: 5px;">
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                  <input type="radio" id="activo" name="estado" value="activo" required>
                  <span style="width: 18px; height: 18px; border: 2px solid #ccc; border-radius: 4px; display: inline-block;"></span> Activo
                </label>
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                  <input type="radio" id="inactivo" name="estado" value="inactivo" required>
                  <span style="width: 18px; height: 18px; border: 2px solid #ccc; border-radius: 4px; display: inline-block;"></span> Inactivo
                </label>
              </div>
            </div>

            <!-- Tipo de Usuario -->
            <div class="form-group">
              <label for="tipo_usuario">Tipo de Usuario</label>
              <select id="tipo_usuario" name="tipo_usuario" required>
                <option value="Administrador">Usuario Administrador</option>
                <option value="General Cobro">Usuario General de Cobros</option>
                <option value="General Inventario">Usuario General de Inventario</option>
              </select>
            </div>
          </div>

          <div class="buttons">
            <a href="dashboard2.php" class="btn btn-cancel">Cancelar</a>
            <button type="submit" name="guardar" class="btn btn-save">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Función para validar que las contraseñas coincidan
    function validarContrasenas() {
      var password = document.getElementById('contraseña').value;
      var passwordRepeat = document.getElementById('repetir_contraseña').value;
      if (password !== passwordRepeat) {
        alert('Las contraseñas no coinciden. Por favor, intente nuevamente.');
        return false;
      }
      return true;
    }

    // Mostrar SweetAlert si el usuario fue creado exitosamente
    <?php if (isset($_GET['success']) && $_GET['success'] == 'created'): ?>
      Swal.fire({
        icon: 'success',
        title: 'Usuario guardado exitosamente!',
        showConfirmButton: false,
        timer: 1800
      });
    <?php endif; ?>

    // Toggle del sidebar
    document.addEventListener("DOMContentLoaded", function() {
      const toggleBtn = document.getElementById("toggleSidebarBtn");
      const sidebar = document.querySelector(".sidebar");
      const content = document.querySelector(".content");

      toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("hidden");
        content.classList.toggle("sidebar-hidden");
      });

      // Toggle submenú Usuarios
      const toggleLink = document.querySelector(".toggle-submenu");
      const submenu = document.getElementById("submenu-usuarios");
      toggleLink.addEventListener("click", function(e) {
        e.preventDefault();
        submenu.style.display = submenu.style.display === "none" ? "flex" : "none";
      });

      // Toggle submenú Productos
      const toggles2 = document.querySelectorAll(".toggle-submenu2");
      toggles2.forEach(function(toggle) {
        toggle.addEventListener("click", function(e) {
          e.preventDefault();
          const nextSubmenu = toggle.nextElementSibling;
          if (nextSubmenu && nextSubmenu.classList.contains("submenu")) {
            nextSubmenu.style.display = nextSubmenu.style.display === "none" ? "flex" : "none";
          }
        });
      });

      // Mostrar fecha actual en el top-bar
      function actualizarFecha() {
        const fechaElemento = document.getElementById("fecha-actual");
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
      // Actualizar cada 24 horas
      const ahora = new Date();
      const msHastaMedianoche = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate() + 1).getTime() - ahora.getTime();
      setTimeout(() => {
        actualizarFecha();
        setInterval(actualizarFecha, 24 * 60 * 60 * 1000);
      }, msHastaMedianoche);
    });
  </script>
</body>

</html>