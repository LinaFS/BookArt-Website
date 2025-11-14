<?php
    session_start();
    require("PHP/config.php");

    $id_producto = isset($_GET['id']) ? $_GET['id'] : '';
    $token = isset($_GET['token']) ? $_GET['token'] : '';

    if($id_producto == '' || $token == ''){
        echo 'Existen valores vacíos';
        exit;
    }else{
        $token_tmp = hash_hmac('sha1', $id_producto, KEY_TOKEN);
        if($token == $token_tmp){
            if (!isset($_SESSION["usuario"])&&!isset($permiso_id["2"])) {
                session_destroy();
            }
            require("PHP/conexionBDD.php");
        
            $sql = "SELECT count(id_producto) FROM catalogo WHERE id_producto='$id_producto'";
            $result = mysqli_query($conexion, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                $sql = "SELECT * FROM catalogo WHERE id_producto='$id_producto'";
                $result = mysqli_query($conexion, $sql);
                if(mysqli_num_rows($result) > 0){
                    $producto = mysqli_fetch_assoc($result);
                    $nombre = $producto['nombre'];
                    $img = $producto['img'];
                    $descripcion = $producto['descripcion'];
                    $precio = $producto['precio'];
                }
            } else {
                echo "Error en la consulta: " . mysqli_error($conexion);
            }
        
            mysqli_close($conexion);
        }else{
            'Error al procesar la petición';
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($nombre); ?> - BookArt</title>
        <link rel="stylesheet" href="../CSS/reset.css">
        <link rel="stylesheet" href="../CSS/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <script src="../JavaScript/userMenu.js"></script>
        <script src="../JavaScript/extensionCatalogo.js"></script>
    </head>
    
    <body>
        <!-- HEADER ARTESANAL -->
        <header>
            <div class="header-content">
                <div class="logo-container">
                    <a href="index.php">
                        <img class="logo" src="../img/Logo/BookArt Positivo_B-N.png" alt="BookArt Logo">
                    </a>
                    <h1 class="site-title">BookArt</h1>
                </div>
                
                <nav class="main-nav" id="mainNav">
                    <ul class="nav-links">
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="Productos.php">Productos</a></li>
                        <li><a href="Contacto.php">Contacto</a></li>
                    </ul>
                    
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <!-- MENÚ DE USUARIO DESPLEGABLE -->
                        <div class="user-menu-wrapper active">
                            <button class="user-menu-trigger" type="button">
                                <span class="material-symbols-outlined">account_circle</span>
                                <span class="user-name-display">
                                    <?php 
                                    // Mostrar solo el nombre de usuario o primera parte del correo
                                    $displayName = $_SESSION['usuario'];
                                    if (strpos($displayName, '@') !== false) {
                                        $displayName = explode('@', $displayName)[0];
                                    }
                                    echo htmlspecialchars(substr($displayName, 0, 15)); 
                                    ?>
                                </span>
                                <span class="material-symbols-outlined">expand_more</span>
                            </button>
                            
                            <div class="user-dropdown">
                                <div class="user-dropdown-header">
                                    <div class="user-dropdown-avatar">
                                        <span class="material-symbols-outlined">person</span>
                                    </div>
                                    <div class="user-dropdown-name">
                                        <?php echo htmlspecialchars($displayName); ?>
                                    </div>
                                    <div class="user-dropdown-email">
                                        <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                                    </div>
                                </div>
                                
                                <div class="user-dropdown-menu">
                                    <a href="MisPedidos.php" class="user-dropdown-item">
                                        <span class="material-symbols-outlined">receipt_long</span>
                                        <span>Mis Pedidos</span>
                                    </a>
                                    
                                    <a href="Carrito.php" class="user-dropdown-item" style="position: relative;">
                                        <span class="material-symbols-outlined">shopping_cart</span>
                                        <span>Mi Carrito</span>
                                        <span class="user-menu-badge" style="display: none;">0</span>
                                    </a>
                                    
                                    <a href="../PHP/cerrar_sesion.php" class="user-dropdown-item logout">
                                        <span class="material-symbols-outlined">logout</span>
                                        <span>Cerrar Sesión</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- BOTÓN DE INICIO DE SESIÓN -->
                        <a href="Inicio_sesion.php" class="btn-session">Iniciar sesión</a>
                    <?php endif; ?>
                </nav>
                
                <button class="menu-toggle" id="menuToggle" onclick="toggleMenu()">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </header>

        <!-- DETALLE DEL PRODUCTO -->
        <section class="product-detail">
            <div class="product-detail-container">
                <!-- GALERÍA -->
                <div class="product-detail-gallery">
                    <div class="product-main-image">
                        <img src="<?php echo htmlspecialchars($img);?>" alt="<?php echo htmlspecialchars($nombre); ?>">
                    </div>
                </div>

                <!-- INFORMACIÓN -->
                <div class="product-detail-info">
                    <div class="product-detail-breadcrumb">
                        <a href="index.php">Inicio</a>
                        <span>/</span>
                        <a href="Catalogo.php">Catálogo</a>
                        <span>/</span>
                        <span><?php echo htmlspecialchars($nombre); ?></span>
                    </div>

                    <h1 class="product-detail-title"><?php echo htmlspecialchars($nombre);?></h1>
                    
                    <div class="product-detail-price">$<?php echo htmlspecialchars($precio);?> MXN</div>
                    
                    <p class="product-detail-description">
                        <?php echo htmlspecialchars($descripcion);?>
                    </p>

                    <div class="product-detail-actions">
                        <?php if (isset($_SESSION['usuario'])): ?>
                            <button class="btn-add-cart" onclick="agregarAlCarritoCatalogo(<?php echo $id_producto; ?>, event)">
                                <span class="material-symbols-outlined">shopping_cart</span>
                                Agregar al carrito
                            </button>
                            <a href="Carrito.php" class="btn-buy-now" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none;">
                                <span class="material-symbols-outlined">shopping_cart_checkout</span>
                                Ver carrito
                            </a>
                        <?php else: ?>
                            <a href="Inicio_sesion.php?origen=Extension_Catalogo&id=<?php echo $id_producto; ?>&token=<?php echo $token; ?>" 
                            class="btn-add-cart" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none;">
                                <span class="material-symbols-outlined">login</span>
                                Inicia sesión para comprar
                            </a>
                        <?php endif; ?>
                        
                        <a href="Catalogo.php" class="btn-back-catalog">
                            <span class="material-symbols-outlined">arrow_back</span>
                            Volver al catálogo
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CARACTERÍSTICAS DEL PRODUCTO -->
        <section class="features-section" style="background: var(--crema-papel); padding: 4rem 2rem;">
            <div class="section-header">
                <h2 class="section-title">Características del Producto</h2>
            </div>
            <div class="features-grid" style="max-width: 1200px; margin: 0 auto;">
                <div class="feature-card">
                    <div class="feature-icon">
                        <span style="font-size: 3rem;">✂️</span>
                    </div>
                    <h3 class="feature-title">Hecho a Mano</h3>
                    <p class="feature-description">
                        Cada libreta es elaborada cuidadosamente con técnicas artesanales tradicionales
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <span style="font-size: 3rem;">📄</span>
                    </div>
                    <h3 class="feature-title">Papel de Calidad</h3>
                    <p class="feature-description">
                        Utilizamos papel de alta calidad que garantiza una experiencia de escritura superior
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <span style="font-size: 3rem;">🎨</span>
                    </div>
                    <h3 class="feature-title">Diseño Único</h3>
                    <p class="feature-description">
                        Cada pieza es única, con detalles artesanales que la hacen especial
                    </p>
                </div>
            </div>
        </section>

        <!-- Modal Alerta -->
        <dialog id="warning">
            <p id="mensaje"></p>
            <div class="btnModal">
                <button id="btnAcept">Aceptar</button>
            </div>
        </dialog>
        
        <!-- FOOTER ARTESANAL -->
        <footer>
            <div class="footer-content">
                <img src="../img/Logo/BookArt Negativo_B-N.png" alt="BookArt Logo" class="footer-logo">
                <p class="footer-text">
                    Encuadernaciones artesanales hechas con pasión<br>
                    Cada libreta es única, como tus ideas
                </p>
                <div class="footer-bottom">
                    <p>&copy; 2024 BookArt Encuadernaciones. Hecho con ❤️ y dedicación</p>
                </div>
            </div>
        </footer>
    </body>
</html>