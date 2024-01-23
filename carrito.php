<?php
session_start();

require 'db_connection.php';

if (isset($_POST['agregar_carrito'])) {
    $codigo_articulo = $_POST['codigo_articulo'];
    $cantidad = $_POST['cantidad'];

    $_SESSION['carrito'][$codigo_articulo] = $cantidad;
}

// Obtener detalles de artículos en el carrito
$carrito_detalles = [];
if (!empty($_SESSION['carrito'])) {
    $codigo_articulos = array_keys($_SESSION['carrito']);
    $placeholders = str_repeat('?,', count($codigo_articulos) - 1) . '?';

    $stmt = $pdo->prepare("SELECT * FROM Articulos WHERE Codigo IN ($placeholders)");
    $stmt->execute($codigo_articulos);
    $carrito_detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Procesar la actualización del carrito si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_carrito'])) {
    foreach ($_POST['cantidad'] as $codigo_articulo => $cantidad) {
        // Asegúrate de que la cantidad esté en el rango permitido (1 a 10)
        $cantidad = max(1, min(10, intval($cantidad)));
        $_SESSION['carrito'][$codigo_articulo] = $cantidad;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://kit.fontawesome.com/eb496ab1a0.js" crossorigin="anonymous"></script>   

    <style>
            body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding-top: 56px; /* Ajuste para la barra de navegación fija */
        }

        .container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
        }

        .cart-item {
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
        }

        .cart-item img {
            max-width: 150px; /* Ajusta el tamaño de la imagen */
            height: auto;
            margin-bottom: 10px;
        }

        .cart-item h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .cart-item p {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .cart-item a {
            color: #007bff;
            cursor: pointer;
        }

        .cart-buttons {
            margin-top: 20px;
        }

        input[name^="cantidad"] {
            width: 50px; /* Ajusta el ancho del input de cantidad */
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .empty-cart {
            color: #6c757d;
            font-size: 18px;
            margin-top: 20px;
        }

        .continue-shopping {
            margin-top: 20px;
        }

        .continue-shopping a {
            text-decoration: none;
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .continue-shopping a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container">
        <h2>Carrito de Compras</h2>
        <?php if (!empty($carrito_detalles)): ?>
            <form action="carrito.php" method="post">
                <?php foreach ($carrito_detalles as $articulo): ?>
                    <div class="cart-item">
                        <img src="<?php echo $articulo['Imagen']; ?>" alt="<?php echo $articulo['Nombre']; ?>">
                        <h3><?php echo $articulo['Nombre']; ?></h3>
                        <p>Cantidad: 
                            <input type="number" name="cantidad[<?php echo $articulo['Codigo']; ?>]" 
                                   value="<?php echo $_SESSION['carrito'][$articulo['Codigo']]; ?>" 
                                   min="1" max="10">
                        </p>
                        <p>Precio: <?php echo $articulo['Precio']; ?> €</p>
                        <a href="eliminar_del_carrito.php?codigo_articulo=<?php echo $articulo['Codigo']; ?>">Eliminar</a>
                    </div>
                <?php endforeach; ?>
                <div class="cart-buttons">
                    <button type="submit" name="actualizar_carrito">Actualizar Carrito</button>
                </div>
            </form>
            <form action="realizar_compra.php" method="post">
                <div class="cart-buttons">
                    <button type="submit" name="realizar_compra">Realizar Compra</button>
                </div>
            </form>
        <?php else: ?>
            <p class="empty-cart">El carrito está vacío.</p>
        <?php endif; ?>
        <div class="continue-shopping">
            <a href="index.php">Seguir Comprando</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
