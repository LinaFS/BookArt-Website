<?php
    session_start();
    if (!isset($_SESSION["usuario"])&&!isset($permiso_id["2"])){
        header("Location: Inicio_sesion.php");
        exit();
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
        <script src="../JavaScript/personalizada.js"></script>
    </body>
</html>