<?php
    session_start();
    if (isset($_SESSION["usuario"])&&!isset($permiso_id["1"])){
        session_destroy();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookArt Encuadernaciones | Libretas Artesanales Hechas a Mano</title>
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

    <!-- HERO SECTION ESTILO SCRAPBOOK -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-text">
                <p class="hero-pretitle">✨ Hecho a mano con amor</p>
                <h2 class="hero-title">
                    <span class="highlight">BookArt:</span><br>
                    Encuadernaciones<br>
                    Artesanales
                </h2>
                <p class="hero-description">
                    "Captura tus ideas con estilo: Libretas Artesanales, ¡Donde la Tradición se une con la Creatividad!"
                </p>
                <div class="hero-cta">
                    <a href="Catalogo.php" class="btn-primary">
                        Ver Catálogo
                        <span class="material-symbols-outlined">auto_stories</span>
                    </a>
                    <a href="Personalizada.php" class="btn-secondary">
                        Diseña tu Libreta
                    </a>
                </div>
            </div>
            
            <div class="hero-image">
                <img src="../img/pantalla.png" alt="Libretas artesanales BookArt">
                <p class="hero-image-caption">Cada libreta cuenta una historia</p>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE CARACTERÍSTICAS ESTILO COLLAGE -->
    <section class="features-section">
        <div class="section-header">
            <h2 class="section-title">¿Por qué elegir BookArt?</h2>
            <p class="section-subtitle">
                Descubre lo que hace especiales nuestras encuadernaciones artesanales
            </p>
        </div>
        
        <div class="features-grid">
            <!-- Card 1 -->
            <article class="feature-card">
                <div class="feature-icon">
                    <img src="../img/card1.png" alt="Sobre nosotros">
                </div>
                <h3 class="feature-title">Sobre Nosotros</h3>
                <p class="feature-description">
                    BookArt Encuadernaciones es un negocio especializado en encuadernaciones artesanales de alta calidad en un proceso manual refinado y que se adapta a tus necesidades y estilo.
                </p>
                <a href="Contacto.php" class="feature-link">
                    Contáctanos
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </article>

            <!-- Card 2 -->
            <article class="feature-card">
                <div class="feature-icon">
                    <img src="../img/card3.png" alt="Nuestra técnica">
                </div>
                <h3 class="feature-title">Nuestra Técnica</h3>
                <p class="feature-description">
                    La encuadernación artesanal es un antiguo arte que combina habilidad, pasión y atención al detalle para crear hermosas y funcionales obras de arte en forma de libros.
                </p>
                <a href="Catalogo.php" class="feature-link">
                    Ver Catálogo
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </article>

            <!-- Card 3 -->
            <article class="feature-card">
                <div class="feature-icon">
                    <img src="../img/card2.png" alt="Nuestros productos">
                </div>
                <h3 class="feature-title">Nuestros Productos</h3>
                <p class="feature-description">
                    En BookArt nos jactamos de tener productos de la mejor calidad, además, nos adaptamos a tus necesidades, ¡Obtén tu libreta completamente personalizada!
                </p>
                <a href="Personalizada.php" class="feature-link">
                    Haz tu pedido
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </article>
        </div>
    </section>

    <!-- SECCIÓN CTA FINAL CON TEXTURA -->
    <section class="features-section" style="background: var(--color-crema); padding: 4rem 2rem;">
        <div style="max-width: 900px; margin: 0 auto; text-align: center; background: white; padding: 3rem; box-shadow: var(--paper-shadow); border-left: 8px solid var(--color-verde-principal);">
            <h2 style="font-family: var(--font-display); font-size: 2.5rem; color: var(--color-marron); margin-bottom: 1.5rem;">
                ¿Listo para crear algo único?
            </h2>
            <p style="font-size: 1.1rem; color: var(--color-marron-claro); margin-bottom: 2.5rem; line-height: 1.8;">
                Diseña tu libreta personalizada o explora nuestro catálogo de productos artesanales hechos a mano con dedicación y cariño
            </p>
            <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
                <a href="Personalizada.php" class="btn-primary">
                    Personalizar Libreta
                    <span class="material-symbols-outlined">edit</span>
                </a>
                <a href="Productos.php" class="btn-secondary">
                    Ver Todos los Productos
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER ESTILO KRAFT -->
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
            
            // Prevenir scroll
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
        
        // Animaciones de entrada
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = entry.target.style.transform.replace('translateY(30px)', 'translateY(0)');
                    }, index * 150);
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.feature-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>