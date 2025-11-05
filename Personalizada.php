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
        <title>¡Personaliza tu libreta! - BookArt</title>
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

        <!-- HERO DE PERSONALIZACIÓN -->
        <section class="personalized-hero">
            <h1>¡Diseña tu Libreta Soñada!</h1>
            <p>
                En BookArt, te damos la libertad total de diseñar tu libreta perfecta. 
                Elige desde el tamaño hasta el tipo de hojas que deseas. 
                ¡Todo es posible cuando se trata de tu creatividad!
            </p>
        </section>

        <!-- SEPARADOR DECORATIVO -->
        <div class="section-divider"></div>

        <!-- FORMULARIO DE PERSONALIZACIÓN -->
        <section class="personalized-form-section">
            <div class="personalized-form-container">
                <h2 class="form-section-title">Comencemos a Crear</h2>
                <p class="form-section-subtitle">
                    Selecciona las opciones que mejor se adapten a tu estilo y necesidades
                </p>

                <!-- MODAL DE MENSAJES -->
                <dialog id="warning">
                    <p id="mensaje"><?php echo isset($_GET['mensaje']) ? $_GET['mensaje'] : ''; ?></p>
                    <div class="btnModal">
                        <button id="btnAcept">Aceptar</button>
                    </div>
                </dialog>

                <form action="../PHP/personalizada.php" method="post" enctype="multipart/form-data">
                    
                    <!-- SECCIÓN: TIPO DE ENCUADERNACIÓN -->
                    <div class="options-section">
                        <h3 class="options-section-title">Tipo de Encuadernación</h3>
                        <p style="text-align: center; color: var(--marron-texto); margin-bottom: 2rem;">
                            Elige el estilo que más te guste
                        </p>

                        <input type="hidden" id="opcFinal" name="opcFinal" value="">

                        <div class="binding-options-grid">
                            <!-- OPCIÓN 1: CLÁSICA -->
                            <label class="binding-option">
                                <input type="radio" id="opcClasica" name="opcion" value="encuadernacionClasica">
                                <div class="binding-content">
                                    <div class="binding-image">
                                        <img src="../img/libretaEncuadernada.jpg" alt="Encuadernación Clásica">
                                    </div>
                                    <h4 class="binding-title">Encuadernación Clásica</h4>
                                    <p class="binding-description">
                                        Técnica tradicional con costura expuesta que da un toque artesanal elegante
                                    </p>
                                </div>
                            </label>

                            <!-- OPCIÓN 2: PIEL -->
                            <label class="binding-option">
                                <input type="radio" id="opcPiel" name="opcion" value="diseñoPiel">
                                <div class="binding-content">
                                    <div class="binding-image">
                                        <img src="../img/libretaPiel.jpg" alt="Diseño en Piel">
                                    </div>
                                    <h4 class="binding-title">Diseño en Piel</h4>
                                    <p class="binding-description">
                                        Cubierta en piel auténtica con acabados de lujo y personalización única
                                    </p>
                                </div>
                            </label>

                            <!-- OPCIÓN 3: ENGARGOLADO -->
                            <label class="binding-option">
                                <input type="radio" id="opcEngargolado" name="opcion" value="diseñoEngargolado">
                                <div class="binding-content">
                                    <div class="binding-image">
                                        <img src="../img/libretaEngargolada.jpg" alt="Diseño Engargolado">
                                    </div>
                                    <h4 class="binding-title">Diseño Engargolado</h4>
                                    <p class="binding-description">
                                        Práctico y funcional, perfecto para escritura diaria con apertura de 360°
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- FORMULARIO DE PERSONALIZACIÓN -->
                    <div class="customization-form">
                        <div class="form-row">
                            <!-- TAMAÑO -->
                            <div class="form-field">
                                <label for="tam">Tamaño de tu libreta</label>
                                <select name="tamaño" id="tam" required>
                                    <option value="Chica 11cm x 12cm">Chica (11cm x 12cm)</option>
                                    <option value="Mediana 14cm x 23cm" selected>Mediana (14cm x 23cm)</option>
                                    <option value="Grande 18cm x 23cm">Grande (18cm x 23cm)</option>
                                </select>
                            </div>

                            <!-- TIPO DE PAPEL -->
                            <div class="form-field">
                                <label for="tipoPap">Tipo de papel</label>
                                <select name="tipoPapel" id="tipoPap" required>
                                    <option value="Ahuesado">Ahuesado</option>
                                    <option value="Capuchino">Capuchino (Reciclado)</option>
                                    <option value="Blanco" selected>Blanco</option>
                                </select>
                            </div>
                        </div>

                        <!-- COLOR PERSONALIZADO -->
                        <div class="form-field" style="margin-top: 2rem;">
                            <label for="color">Color de detalles</label>
                            <p style="color: var(--marron-texto); margin-bottom: 1rem; font-size: 0.9rem;">
                                Escoge el tono para resaltar el lomo y otros detalles clave de tu libreta
                            </p>
                            <div class="color-picker-wrapper">
                                <input type="color" name="color" id="color" class="color-picker-preview" value="#1E9332">
                                <input type="text" class="color-picker-input" value="#1E9332" readonly>
                            </div>
                        </div>

                        <!-- PORTADA PERSONALIZADA -->
                        <div class="form-field" style="margin-top: 2rem;">
                            <label for="portada">Portada personalizada (opcional)</label>
                            <div class="file-upload-wrapper">
                                <label for="portada" class="file-upload-label">
                                    <span class="file-upload-icon">📁</span>
                                    <span class="file-upload-text">Haz clic para subir tu diseño</span>
                                    <span class="file-upload-hint">Formatos: JPG, PNG (Máx. 5MB)</span>
                                </label>
                                <input type="file" name="portada" id="portada" class="file-upload-input" accept="image/*">
                            </div>
                            <div id="imagePreview" class="image-preview" style="display: none;">
                                <img id="previewImg" src="" alt="Vista previa">
                                <button type="button" class="remove-preview" onclick="removePreview()">×</button>
                            </div>
                        </div>

                        <!-- DESCRIPCIÓN -->
                        <div class="form-field" style="margin-top: 2rem;">
                            <label for="desc">Describe tu portada ideal</label>
                            <textarea name="descripcion" id="desc" placeholder="Cuéntanos cómo imaginas tu portada perfecta. ¿Qué colores, patrones o elementos te gustaría incluir?"></textarea>
                        </div>

                        <!-- NOTA INFORMATIVA -->
                        <div class="form-note">
                            <p class="form-note-text">
                                <strong>📝 Nota importante:</strong> Si escogiste libreta de piel, por favor describe el color deseado y cualquier especificación adicional en el campo de descripción. Nuestros artesanos trabajarán para hacer realidad tu visión.
                            </p>
                        </div>

                        <!-- BOTÓN DE ENVÍO -->
                        <div class="submit-section">
                            <button type="submit" class="btn-submit-custom">
                                <span class="material-symbols-outlined">send</span>
                                Enviar mi Diseño
                            </button>
                        </div>
                    </div>
                </form>
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

            // Seleccionar opción de encuadernación
            document.querySelectorAll('.binding-option input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('opcFinal').value = this.value;
                });
            });

            // Preview de imagen
            document.getElementById('portada').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewImg').src = e.target.result;
                        document.getElementById('imagePreview').style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Remover preview
            function removePreview() {
                document.getElementById('portada').value = '';
                document.getElementById('imagePreview').style.display = 'none';
                document.getElementById('previewImg').src = '';
            }

            // Color picker sincronizado
            document.getElementById('color').addEventListener('input', function(e) {
                document.querySelector('.color-picker-input').value = e.target.value;
            });

            // Modal de mensajes
            if (document.getElementById('mensaje').textContent.trim() !== '') {
                document.getElementById('warning').showModal();
            }

            document.getElementById('btnAcept').addEventListener('click', function() {
                document.getElementById('warning').close();
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