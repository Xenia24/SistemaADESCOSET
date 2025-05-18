<?php
session_start();
include('../includes/db.php');

if (isset($_POST['submit'])) {
    $correo    = $_POST['correo'];
    $contrasena = $_POST['contraseña'];

    $stmt = $pdo->prepare("SELECT * FROM usuariosag WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contrasena, $usuario['contraseña'])) {
        $_SESSION['usuario_id']    = $usuario['id'];
        $_SESSION['tipo_usuario']  = $usuario['tipo_usuario'];
        $_SESSION['nombre_usuario']= $usuario['nombre_usuario'];

        if ($usuario['tipo_usuario'] === 'Administrador') {
            header('Location: opciones.php');
        } elseif ($usuario['tipo_usuario'] === 'General') {
            header('Location: usuarioFac.php');
        } else {
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    * { box-sizing:border-box; margin:0; padding:0; font-family:'Poppins',sans-serif; }
    body {
      background: url('../Image/fon1.png') no-repeat center center fixed;
      background-size: cover;
      display:flex; flex-direction:column;
      align-items:center; justify-content:center;
      min-height:100vh; padding-top:80px; padding-bottom:60px;
    }
    .top-bar {
      position:fixed; top:0; left:0;
      width:100%; height:50px;
      background:rgba(0,144,175,0.85);
      display:flex; align-items:center; padding:0 15px;
      color:#fff; z-index:1000;
    }
    .top-bar img {
      width:24px; height:24px; border-radius:50%;
      margin-right:8px;
    }
    .container {
      background:rgba(255,255,255,0.95);
      padding:30px 25px; width:330px;
      border:1px solid #333; border-radius:6px;
      box-shadow:0 6px 12px rgba(0,0,0,0.2);
      text-align:center;
    }
    .container .user-icon img {
      width:80px; height:80px; margin-bottom:15px;
    }
    h2 { margin-bottom:20px; font-weight:600; }
    .input-container {
      position:relative; margin-bottom:18px;
    }
    .input-container img {
      position:absolute; top:50%; left:10px;
      width:20px; height:20px; transform:translateY(-50%);
    }
    .input-container input {
      width:100%; padding:10px 10px 10px 40px;
      border:1px solid #ccc; border-radius:4px;
    }
    button {
      width:100%; padding:10px; border:none;
      border-radius:4px; background:#576464;
      color:#fff; font-weight:bold; cursor:pointer;
    }
    button:hover { background:#434f4f; }
    .error { color:red; margin-top:10px; }
    .footer-bar {
      position:fixed; bottom:0; left:0;
      width:100%; height:50px;
      background:rgba(0,144,175,0.85);
      color:#fff; display:flex; align-items:center;
      justify-content:center; font-size:14px;
    }
  </style>
</head>
<body>

  <div class="top-bar">
    <?php if (isset($_SESSION['nombre_usuario'])): ?>
      <img src="../Image/avatar-small.png" alt="Usuario">
      <span><?= htmlspecialchars($_SESSION['nombre_usuario']) ?></span>
    <?php else: ?>
      <img src="../Image/avatar-small.png" alt="Usuario">
      <span>Invitado</span>
    <?php endif; ?>
  </div>

  <div class="container">
    <div class="user-icon">
      <img src="../Image/avatar.png" alt="Icono usuario" />
    </div>
    <h2>INICIO DE SESIÓN</h2>
    <form method="POST" action="login.php">
      <div class="input-container">
        <img src="../Image/avatar.png" alt="Usuario">
        <input type="email" name="correo" placeholder="Correo" required />
      </div>
      <div class="input-container">
        <img src="../Image/candado.png" alt="Contraseña">
        <input type="password" name="contraseña" placeholder="Contraseña" required />
      </div>
      <button type="submit" name="submit">Entrar</button>
      <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
    </form>
  </div>

  <div class="footer-bar">
    &copy; 2025 Xenia, Erick, Ivania — Todos los derechos reservados
  </div>

</body>
</html>
