<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Opciones</title>
  <link rel="stylesheet" href="../css/styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      background-color: #f8f0f0;
      gap: 20px;
      margin: 0;
      padding-top: 80px;
    }

    .top-bar {
      width: 100%;
      height: 60px;
      background-color: rgb(71, 146, 167);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 30px;
      position: fixed;
      top: 0;
      left: 0;
      box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.2);
      z-index: 1000;
    }

    .top-bar h1 {
      color: white;
      font-size: 22px;
      margin: 0;
      font-weight: 600;
    }

    .top-bar button {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      background-color: white;
      color: rgb(71, 146, 167);
      cursor: pointer;
      font-weight: 600;
      transition: transform 0.2s ease;
    }

    .top-bar button:hover {
      transform: scale(1.05);
    }

    .container-wrapper {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
      width: 100%;
      max-width: 900px;
    }

    .container {
      width: 350px;
      height: 420px;
      padding: 20px;
      border-radius: 12px;
      border: 2px solid black; /* 👈 Borde negro */
      box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.1);
      background: white;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .container:hover {
      transform: translateY(-5px);
      box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.15);
    }

    .image img {
      width: 100%;
      height: 300px;
      object-fit: cover;
      border-radius: 10px;
      transition: transform 0.3s ease;
    }

    .image img:hover {
      transform: scale(1.03);
    }

    .card {
      padding-top: 20px;
    }

    .option-button {
      display: block;
      width: 100%;
      margin: 10px 0;
      padding: 12px;
      border: none;
      border-radius: 6px;
      background-color: rgb(87, 100, 100);
      color: white;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .option-button:hover {
      background-color: rgb(67, 80, 80);
      transform: scale(1.02);
    }
  </style>
</head>
<body>

  <div class="top-bar">
    <h1>Panel de Opciones</h1>
    <button onclick="location.href='inicio.php'">Volver al Inicio</button>
  </div>

  <div class="container-wrapper">
    <div class="container">
      <div class="image">
        <img src="../Image/inventario.png.png" alt="Inventario">
      </div>
      <div class="card">
        <a href="dashboard.php" class="option-button">Cobro</a>
      </div>
    </div>

    <div class="container">
      <div class="image">
        <img src="../Image/cobro.png" alt="Cobro">
      </div>
      <div class="card">
        <a href="dashboard2.php" class="option-button">Inventario</a>
      </div>
    </div>
  </div>

</body>
</html>
