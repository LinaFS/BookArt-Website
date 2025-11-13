<?php
    session_start();
    if (!isset($_SESSION["usuario"])&&!isset($permiso_id["2"])){
        session_destroy();
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Productos - BookArt Encuadernaciones</title>
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
        
        <!-- HERO DE PRODUCTOS -->
        <section class="products-hero">
            <h1>Nuestros Productos</h1>
            <p>Elige entre nuestro catálogo o diseña tu libreta completamente personalizada</p>
        </section>

        <!-- OPCIONES DE PRODUCTOS -->
        <section class="products-options">
            <div class="products-grid">
                <!-- OPCIÓN 1: LIBRETAS PERSONALIZADAS -->
                <a href="Personalizada.php" class="product-option">
                    <div class="product-option-image">
                        <img src="../img/Libreta.png" alt="Libretas Personalizadas">
                    </div>
                    <div class="product-option-content">
                        <h2 class="product-option-title">Libretas Personalizadas</h2>
                        <p class="product-option-description">
                            ¡Crea tu propio diseño! Elige el tamaño, tipo de papel, colores y hasta puedes agregar tu propia portada. Hazla única como tú.
                        </p>
                        <span class="product-option-cta">
                            Diseña tu libreta
                            <span class="material-symbols-outlined">brush</span>
                        </span>
                    </div>
                </a>

                <!-- OPCIÓN 2: CATÁLOGO -->
                <a href="Catalogo.php" class="product-option">
                    <div class="product-option-image">
                        <img src="../img/Hojas.png" alt="Catálogo">
                    </div>
                    <div class="product-option-content">
                        <h2 class="product-option-title">Catálogo</h2>
                        <p class="product-option-description">
                            ¡Escoge entre nuestra variedad de diseños! Explora nuestra colección de libretas artesanales ya diseñadas y listas para ti.
                        </p>
                        <span class="product-option-cta">
                            Ver catálogo
                            <span class="material-symbols-outlined">auto_stories</span>
                        </span>
                    </div>
                </a>
            </div>
        </section>

        <!-- SECCIÓN INFORMATIVA -->
        <section class="features-section" style="background: var(--crema-papel); padding: 4rem 2rem;">
            <div style="max-width: 900px; margin: 0 auto; text-align: center; background: white; padding: 3rem; box-shadow: var(--sombra-papel); border-left: 8px solid var(--verde-bookart); border: 5px solid var(--marron-texto); border-left-width: 8px;">
                <h2 style="font-family: var(--font-display); font-size: 2.5rem; color: var(--marron-texto); margin-bottom: 1.5rem;">
                    ¿Por qué elegir BookArt?
                </h2>
                <p style="font-size: 1.1rem; color: var(--marron-texto); margin-bottom: 2rem; line-height: 1.8;">
                    Cada libreta es una obra de arte única, elaborada con técnicas tradicionales de encuadernación y materiales de la más alta calidad. Ya sea que elijas un diseño de nuestro catálogo o crees tu propia libreta personalizada, recibirás un producto hecho con pasión y dedicación.
                </p>
                <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <span style="font-size: 3rem;">✂️</span>
                        <h3 style="font-family: var(--font-display); font-size: 1.3rem; color: var(--marron-texto); margin: 1rem 0 0.5rem;">Hecho a Mano</h3>
                        <p style="color: var(--marron-texto);">Proceso artesanal</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <span style="font-size: 3rem;">🎨</span>
                        <h3 style="font-family: var(--font-display); font-size: 1.3rem; color: var(--marron-texto); margin: 1rem 0 0.5rem;">Personalizable</h3>
                        <p style="color: var(--marron-texto);">A tu medida</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <span style="font-size: 3rem;">⭐</span>
                        <h3 style="font-family: var(--font-display); font-size: 1.3rem; color: var(--marron-texto); margin: 1rem 0 0.5rem;">Alta Calidad</h3>
                        <p style="color: var(--marron-texto);">Materiales premium</p>
                    </div>
                </div>
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
            
            document.querySelectorAll('.nav-links a, .btn-session').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        const nav = document.getElementById('mainNav');
                        nav.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });
            
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