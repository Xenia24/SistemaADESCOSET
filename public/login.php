<?php
session_start();
include('../includes/db.php');

if (isset($_POST['submit'])) {
    $correo     = $_POST['correo'];
    $contrasena = $_POST['contraseña'];

    // Buscamos al usuario por correo
    $stmt = $pdo->prepare("SELECT * FROM usuariosag WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contrasena, $usuario['contraseña'])) {
        // Guardamos en sesión los datos básicos
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['tipo_usuario']   = $usuario['tipo_usuario'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

        // Redirigimos según el tipo de usuario que venga en la base
        if ($usuario['tipo_usuario'] === 'Administrador') {
            header('Location: opciones.php');
        }
        elseif ($usuario['tipo_usuario'] === 'General Cobro') {
            header('Location: usuarioFac.php');
        }
        elseif ($usuario['tipo_usuario'] === 'General Inventario') {
            header('Location: usuarioInventario.php');
        }
        else {
            $error = "Tipo de usuario no válido.";
        }
        exit();
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #0090AF;
      --text-dark: #333;
      --text-light: #FFF;
      --bg-form: #FFF;
    }
    * { box-sizing:border-box; margin:0; padding:0; font-family:'Poppins',sans-serif; }
    html, body {
      width:100%; height:100%; overflow:hidden;
    }

    /* Contenedor full-screen */
    .login-wrapper {
      display: flex;
      width: 100vw;
      height: 100vh;
    }

    /* Panel formulario (izquierda) */
    .login-form {
      flex: 1;
      background: var(--bg-form);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 40px;
      animation: slideInLeft 0.8s ease-out both;
    }
    @keyframes slideInLeft {
      from { transform: translateX(-50px); opacity:0; }
      to   { transform: translateX(0);    opacity:1; }
    }
    .login-form .logo {
      width: 120px; margin-bottom: 25px; border-radius:50%;
      animation: rotateIn 1s ease-out both;
    }
    @keyframes rotateIn {
      from { transform: rotate(-360deg) scale(0.5); opacity:0; }
      to   { transform: rotate(0) scale(1); opacity:1; }
    }
    .login-form h1 {
      color: var(--text-dark);
      font-size:2rem; margin-bottom:8px; font-weight:600;
    }
    .login-form p.subtitle {
      color:#666; margin-bottom:30px;
    }
    .login-form form {
      width:100%; max-width:320px;
    }
    .input-container {
      position:relative; margin-bottom:18px;
    }
    .icon-wrapper {
      position:absolute; top:50%; left:12px;
      width:36px; height:36px;
      /*background:var(--primary);*/
      border-radius:6px;
      display:flex; align-items:center; justify-content:center;
      transform:translateY(-50%);
    }
    .icon-wrapper img {
      width:20px; height:20px;
    }
    .input-container input {
      width:100%;
      padding:10px 12px 10px 60px;
      border:1px solid #CCC;
      border-radius:4px;
      font-size:0.95rem;
      transition:border-color 0.3s, box-shadow 0.3s;
    }
    .input-container input:focus {
      border-color: var(--primary);
      box-shadow:0 0 6px rgba(0,144,175,0.5);
      outline:none;
    }
    .toggle-password {
      position:absolute; top:50%; right:12px;
      width:28px; height:28px;
      transform:translateY(-50%);
      cursor:pointer; opacity:0.6;
      transition:opacity 0.3s;
    }
    .toggle-password:hover { opacity:1; }
    .toggle-password img { width:100%; height:100%; }
    .login-form button {
      width:100%; padding:12px;
      background:var(--primary); color:var(--text-light);
      border:none; border-radius:4px;
      font-size:1rem; font-weight:600;
      cursor:pointer; margin-top:10px;
      transition:transform 0.2s, background 0.3s;
    }
    .login-form button:hover {
      background:#007993; transform:scale(1.05);
    }
    .login-form .error {
      color:red; text-align:center; margin-top:12px;
      animation:shake 0.5s ease-in-out;
    }
    @keyframes shake {
      0%,100%{ transform:translateX(0); }
      20%,60%{ transform:translateX(-4px); }
      40%,80%{ transform:translateX(4px); }
    }

    /* Panel ilustración (derecha) */
    .login-image {
      flex: 1;
      background: var(--primary) url('../Image/fon1.png') no-repeat center/cover;
      background-size:cover;
      animation: slideInRight 0.8s ease-out both;
    }
    @keyframes slideInRight {
      from { transform: translateX(50px); opacity:0; }
      to   { transform: translateX(0);    opacity:1; }
    }
  </style>
</head>
<body>

  <div class="login-wrapper">

    <div class="login-form">
      <img src="../Image/logoadesco.jpg" alt="Logo" class="logo">
      <h1>Bienvenido</h1>
      <p class="subtitle">Por favor ingrese sus datos</p>

      <form method="POST" action="login.php">
        <div class="input-container">
          <div class="icon-wrapper">
            <img src="../Image/avatar.png" alt="Usuario">
          </div>
          <input type="email" name="correo" placeholder="Correo" required>
        </div>
        <div class="input-container">
          <div class="icon-wrapper">
            <img src="../Image/candado.png" alt="Contraseña">
          </div>
          <input type="password" id="password" name="contraseña" placeholder="Contraseña" required>
          <span class="toggle-password" onclick="togglePassword()">
            <img src="../Image/ojo.png" alt="Mostrar/Ocultar">
          </span>
        </div>

        <button type="submit" name="submit">Iniciar Sesión</button>

        <?php if (isset($error)): ?>
          <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
      </form>
    </div>

    <div class="login-image"></div>
  </div>

  <script>
    function togglePassword() {
      const pwd = document.getElementById('password');
      const eye = document.querySelector('.toggle-password img');
      if (pwd.type === 'password') {
        pwd.type = 'text';
        eye.src = '../Image/ojoSi.png';
      } else {
        pwd.type = 'password';
        eye.src = '../Image/ojo.png';
      }
    }
  </script>
</body>
</html>
