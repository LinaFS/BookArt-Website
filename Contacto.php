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
        <title>Contacto - BookArt</title>
        <link rel="stylesheet" href="../CSS/reset.css">
        <link rel="stylesheet" href="../CSS/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
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
                    <?php
                        if (isset($_SESSION['usuario'])) {
                            echo '<a href="../PHP/cerrar_sesion.php" class="btn-session">Cerrar sesión</a>';
                        } else {
                            echo '<a href="Inicio_sesion.php" class="btn-session">Iniciar sesión</a>';
                        }
                    ?>
                </nav>
                
                <button class="menu-toggle" id="menuToggle" onclick="toggleMenu()">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </header>

        <!-- HERO DE CONTACTO -->
        <section class="contact-hero">
            <h1>¡Contáctanos!</h1>
            <p>Estamos aquí para responder tus preguntas y hacer realidad tus ideas</p>
        </section>

        <!-- SECCIÓN PRINCIPAL DE CONTACTO -->
        <section class="contact-main">
            <!-- MODAL DE MENSAJES -->
            <dialog id="warning">
                <p id="mensaje"><?php echo isset($_GET['mensaje']) ? $_GET['mensaje'] : ''; ?></p>
                <div class="btnModal">
                    <button id="btnAcept" style="
                        padding: 0.8rem 2rem;
                        background: var(--verde-bookart);
                        color: white;
                        border: 3px solid var(--marron-texto);
                        cursor: pointer;
                        font-family: var(--font-body);
                        font-weight: 700;
                        box-shadow: 3px 3px 0px var(--marron-texto);
                    ">Aceptar</button>
                </div>
            </dialog>

            <div class="contact-container">
                <!-- LADO IZQUIERDO: INFORMACIÓN -->
                <aside class="contact-info-side">
                    <h2 class="contact-info-title">Información de Contacto</h2>
                    <p class="contact-info-text">
                        Estamos encantados de escucharte. Ya sea que tengas una pregunta, 
                        quieras hacer un pedido personalizado o simplemente quieras saber más 
                        sobre nuestro trabajo artesanal, ¡contáctanos!
                    </p>

                    <div class="info-cards">
                        <!-- CORREO -->
                        <div class="info-card">
                            <div class="info-card-icon">
                                <span class="material-symbols-outlined">mail</span>
                            </div>
                            <div class="info-card-content">
                                <h3>Correo Electrónico</h3>
                                <p>bookart@encuadernaciones.com</p>
                            </div>
                        </div>

                        <!-- TELÉFONO -->
                        <div class="info-card">
                            <div class="info-card-icon">
                                <span class="material-symbols-outlined">phone</span>
                            </div>
                            <div class="info-card-content">
                                <h3>Teléfono</h3>
                                <p>+52 (55) 1234-5678</p>
                            </div>
                        </div>

                        <!-- UBICACIÓN -->
                        <div class="info-card">
                            <div class="info-card-icon">
                                <span class="material-symbols-outlined">location_on</span>
                            </div>
                            <div class="info-card-content">
                                <h3>Ubicación</h3>
                                <p>Ciudad de México, México</p>
                            </div>
                        </div>

                        <!-- HORARIO -->
                        <div class="info-card">
                            <div class="info-card-icon">
                                <span class="material-symbols-outlined">schedule</span>
                            </div>
                            <div class="info-card-content">
                                <h3>Horario</h3>
                                <p>Lun - Vie: 9:00 AM - 6:00 PM<br>Sáb: 10:00 AM - 2:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <!-- REDES SOCIALES -->
                    <div class="social-section">
                        <h3 class="social-title">Síguenos en Redes</h3>
                        <div class="social-links-grid">
                            <a href="https://www.facebook.com/profile.php?id=100090511421324" class="social-link" target="_blank">
                                <img src="../img/facebook-icon.png" alt="Facebook">
                                <span>Facebook</span>
                            </a>
                            <a href="https://www.instagram.com/bookart_encuadernaciones/" class="social-link" target="_blank">
                                <img src="../img/Instagram-Icon.png" alt="Instagram">
                                <span>Instagram</span>
                            </a>
                        </div>
                    </div>
                </aside>

                <!-- LADO DERECHO: FORMULARIO -->
                <div class="contact-form-side">
                    <h2 class="contact-form-title">Envíanos un Mensaje</h2>
                    <p class="contact-form-subtitle">
                        * Utilizamos tu información sólo para contactarnos contigo
                    </p>

                    <form method="post" action="../PHP/contact.php" class="contact-form">
                        <!-- NOMBRE -->
                        <div class="form-group-contact">
                            <label for="nombre">Nombre completo</label>
                            <input 
                                type="text" 
                                id="nombre" 
                                name="nombre" 
                                required 
                                placeholder="Juan Pérez García"
                            >
                        </div>

                        <!-- CORREO -->
                        <div class="form-group-contact">
                            <label for="email">Correo Electrónico</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                placeholder="tu@email.com"
                            >
                        </div>

                        <!-- TELÉFONO -->
                        <div class="form-group-contact">
                            <label for="telefono">Teléfono</label>
                            <input 
                                type="tel" 
                                id="telefono" 
                                name="tel" 
                                required 
                                placeholder="(55) 1234-5678"
                            >
                        </div>

                        <!-- MENSAJE -->
                        <div class="form-group-contact">
                            <label for="comentario">Tu Mensaje</label>
                            <textarea 
                                id="comentario" 
                                name="coment" 
                                required 
                                placeholder="Cuéntanos en qué podemos ayudarte..."
                            ></textarea>
                        </div>

                        <!-- BOTÓN DE ENVÍO -->
                        <button type="submit" class="btn-submit-contact">
                            <span class="material-symbols-outlined">send</span>
                            Enviar Mensaje
                        </button>

                        <!-- MENSAJE DE ÉXITO -->
                        <div class="success-message" id="successMessage">
                            <span class="material-symbols-outlined">check_circle</span>
                            ¡Mensaje enviado con éxito! Te contactaremos pronto.
                        </div>
                    </form>
                </div>
            </div>

            <!-- DECORACIÓN FINAL -->
            <div class="contact-decoration">
                <h3 class="contact-decoration-title">¿Listo para crear algo único?</h3>
                <p class="contact-decoration-text">
                    Ya sea que busques una libreta del catálogo o quieras diseñar una completamente personalizada, 
                    estamos aquí para ayudarte a hacer realidad tu visión artesanal.
                </p>
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
            // Toggle Menu
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

            // Modal de mensajes
            if (document.getElementById('mensaje').textContent.trim() !== '') {
                document.getElementById('warning').showModal();
            }

            document.getElementById('btnAcept').addEventListener('click', function() {
                document.getElementById('warning').close();
            });

            // Validación del formulario
            const form = document.querySelector('.contact-form');
            form.addEventListener('submit', function(e) {
                const inputs = form.querySelectorAll('input[required], textarea[required]');
                let valid = true;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        valid = false;
                        input.classList.add('error');
                    } else {
                        input.classList.remove('error');
                        input.classList.add('valid');
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    alert('Por favor, completa todos los campos requeridos');
                }
            });

            // Remover clase de error al escribir
            document.querySelectorAll('input, textarea').forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    if (this.value.trim()) {
                        this.classList.add('valid');
                    } else {
                        this.classList.remove('valid');
                    }
                });
            });

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