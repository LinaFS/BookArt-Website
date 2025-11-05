<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Próximamente - BookArt</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--crema-papel);
        }

        .coming-soon-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, var(--crema-papel) 0%, var(--blanco) 100%);
        }

        .coming-soon-container {
            max-width: 800px;
            text-align: center;
            background: var(--blanco);
            padding: 4rem 3rem;
            border: 6px solid var(--marron-texto);
            box-shadow: var(--sombra-profunda);
            position: relative;
        }

        /* Chincheta decorativa */
        .coming-soon-container::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: radial-gradient(circle at 30% 30%, var(--amarillo-claro), var(--amarillo-bookart));
            border-radius: 50%;
            border: 4px solid var(--marron-texto);
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
        }

        /* Cinta decorativa */
        .coming-soon-container::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 10px;
            background: repeating-linear-gradient(
                90deg,
                var(--amarillo-bookart) 0px,
                var(--amarillo-bookart) 25px,
                var(--verde-bookart) 25px,
                var(--verde-bookart) 50px,
                var(--rojo-bookart) 50px,
                var(--rojo-bookart) 75px
            );
        }

        .coming-soon-icon {
            font-size: 8rem;
            margin-bottom: 2rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .coming-soon-title {
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 5vw, 4rem);
            color: var(--marron-texto);
            margin-bottom: 1.5rem;
            position: relative;
        }

        .coming-soon-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 5px;
            background: linear-gradient(90deg, var(--amarillo-bookart), var(--verde-bookart));
            border-radius: 3px;
        }

        .coming-soon-text {
            font-size: 1.3rem;
            color: var(--marron-texto);
            margin-bottom: 3rem;
            line-height: 1.8;
        }

        .coming-soon-image {
            width: 100%;
            max-width: 400px;
            margin: 2rem auto;
            padding: 1rem;
            background: var(--crema-papel);
            border: 4px dashed var(--marron-texto);
        }

        .coming-soon-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1.2rem 2.5rem;
            background: var(--verde-bookart);
            color: var(--blanco);
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            border: 4px solid var(--marron-texto);
            box-shadow: 5px 5px 0px var(--marron-texto);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: var(--font-body);
        }

        .back-button:hover {
            transform: translate(3px, 3px);
            box-shadow: 2px 2px 0px var(--marron-texto);
            background: var(--verde-oscuro);
        }

        @media screen and (max-width: 768px) {
            .coming-soon-container {
                padding: 3rem 2rem;
            }

            .coming-soon-icon {
                font-size: 5rem;
            }

            .coming-soon-title {
                font-size: 2rem;
            }

            .coming-soon-text {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER ARTESANAL -->
    <header>
        <div class="header-content">
            <div class="logo-container">
                <a href="index.php">
                    <img class="logo" src="../img/Logo.png" alt="BookArt Logo">
                </a>
                <h1 class="site-title">BookArt</h1>
            </div>
            
            <nav class="main-nav" id="mainNav">
                <ul class="nav-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="Productos.php">Productos</a></li>
                    <li><a href="Contacto.php">Contacto</a></li>
                </ul>
                <a href="Inicio_sesion.php" class="btn-session">Iniciar sesión</a>
            </nav>
            
            <button class="menu-toggle" id="menuToggle" onclick="toggleMenu()">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </header>

    <!-- SECCIÓN PRÓXIMAMENTE -->
    <section class="coming-soon-section">
        <div class="coming-soon-container">
            <div class="coming-soon-icon">🛒</div>
            
            <h1 class="coming-soon-title">¡Próximamente!</h1>
            
            <p class="coming-soon-text">
                Estamos preparando una experiencia de compra increíble para ti. 
                Nuestro carrito de compras estará disponible muy pronto con todas 
                las funciones que necesitas para adquirir tus libretas artesanales favoritas.
            </p>

            <div class="coming-soon-image">
                <p>En construcción</p>
            </div>

            <p class="coming-soon-text" style="font-size: 1rem; margin-bottom: 2rem;">
                Mientras tanto, puedes explorar nuestro catálogo y contactarnos 
                directamente para hacer tu pedido personalizado.
            </p>

            <a href="index.php" class="back-button">
                <span class="material-symbols-outlined">arrow_back</span>
                Volver al Inicio
            </a>
        </div>
    </section>

    <!-- FOOTER ARTESANAL -->
    <footer>
        <div class="footer-content">
            <img src="../img/Logo.png" alt="BookArt Logo" class="footer-logo">
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
    </script>
</body>
</html>