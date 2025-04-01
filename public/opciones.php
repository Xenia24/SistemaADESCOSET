<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opciones</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
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
            justify-content: space-around;
            padding: 0 20px;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .top-bar h1 {
            color: white;
            font-size: 20px;
            margin: 0;
        }
        .top-bar button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background-color: white;
            color: rgb(71, 146, 167);
            cursor: pointer;
        }
        .container-wrapper {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            width: 100%;
            max-width: 800px;
        }
        .container {
            width: 350px;
            height: 400px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }
        .card {
            padding: 50px;
            border-radius: 10px;
        }
        .option-button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: rgb(87, 100, 100);
            color: white;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
        }
        .option-button:hover {
            background-color: rgb(67, 80, 80);
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <h1>Menú de Opciones</h1>
        <button onclick="location.href='../view/login.php'">Cerrar Sesion</button>
    </div>
    <div class="container-wrapper">
        <div class="container">
            <div class="image">
                <img src="../Image/inventario.png.png" alt="Logo" style="width: 100%; height: 300px; border-radius: 10px;">
            </div>
            <div class="card">
                <a href="../view/dashboard.php" class="option-button">Cobro</a>
            </div>
        </div>
        <div class="container">
            <div class="image">
                <img src="../Image/cobro.png" alt="Logo" style="width: 100%; height: 300px; border-radius: 10px;">
            </div>
            <div class="card">
                <a href="opcion2.php" class="option-button">Inventario</a>
            </div>
        </div>    
    </div>
</body>
</html>