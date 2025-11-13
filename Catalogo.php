<?php
    session_start();
    require("PHP/config.php");
    // Inicializar la variable $catalogo
    $catalogo = [];

    if (!isset($_SESSION["usuario"])&&!isset($permiso_id["2"])) {
        header("Location: Inicio_sesion.php");
        exit();
    }
    require("PHP/conexionBDD.php");

    $sql = "SELECT id_producto, nombre, precio, img FROM catalogo";
    $result = mysqli_query($conexion, $sql);

    if ($result) {
        $catalogo = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        echo "Error en la consulta: " . mysqli_error($conexion);
    }

    mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Catálogo - BookArt Encuadernaciones</title>
        <link rel="stylesheet" href="../CSS/reset.css">
        <link rel="stylesheet" href="../CSS/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <script src="../JavaScript/userMenu.js"></script>
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
        
        <!-- HERO DEL CATÁLOGO -->
        <section class="catalog-hero">
            <h1>Nuestro Catálogo Artesanal</h1>
            <p>Explora nuestra colección de libretas hechas a mano con amor y dedicación</p>
        </section>

        <!-- CATÁLOGO DE PRODUCTOS -->
        <section class="catalog-section">
            <div class="catalog-container">
                <?php if (!empty($catalogo)): ?>
                    <div class="catalog-grid">
                        <?php foreach ($catalogo as $row): ?>
                            <a href="Extension_Catalogo.php?id=<?php echo $row['id_producto'];?>&token=<?php echo hash_hmac('sha1', $row['id_producto'], KEY_TOKEN)?>" class="catalog-item">
                                <div class="catalog-item-image">
                                    <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                                </div>
                                <div class="catalog-item-info">
                                    <h3 class="catalog-item-title"><?php echo htmlspecialchars($row['nombre']); ?></h3>
                                    <p class="catalog-item-description">Libreta artesanal hecha a mano con materiales de la más alta calidad</p>
                                    <div class="catalog-item-footer">
                                        <span class="catalog-item-price">$<?php echo htmlspecialchars($row['precio']); ?></span>
                                        <span class="catalog-item-action">
                                            Ver más
                                            <span class="material-symbols-outlined">arrow_forward</span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="catalog-empty">
                        <div class="catalog-empty-icon">📚</div>
                        <h3>No hay productos disponibles</h3>
                        <p>Estamos trabajando en nuevos diseños increíbles. ¡Vuelve pronto!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
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

        <!-- JAVASCRIPT -->
        <script>
            function toggleMenu() {
                const nav = document.getElementById('mainNav');
                const toggle = document.getElementById('menuToggle');
                nav.classList.toggle('active');
                
                const icon = toggle.querySelector('.material-symbols-outlined');
                icon.textContent = nav.classList.contains('active') ? 'close' : 'menu';
                
                if (nav.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
            
            // Cerrar menú al hacer clic en enlaces
            document.querySelectorAll('.nav-links a, .btn-session').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        const nav = document.getElementById('mainNav');
                        nav.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });
            
            // Cerrar menú al hacer clic fuera
            document.addEventListener('click', function(e) {
                const nav = document.getElementById('mainNav');
                const toggle = document.getElementById('menuToggle');
                
                if (nav.classList.contains('active') && 
                    !nav.contains(e.target) && 
                    !toggle.contains(e.target)) {
                    toggleMenu();
                }
            });
        </script>
    </body>
</html>