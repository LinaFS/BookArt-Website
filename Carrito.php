<?php
    session_start();
    
    // 1. Bloque de seguridad para la sesión
    if (!isset($_SESSION["usuario"])) {
        header("Location: Inicio_sesion.php?origen=Carrito");
        exit();
    }
    
    // Inclusión de archivos de configuración
    require("PHP/conexionBDD.php");
    require("PHP/config.php");
    
    // Obtener ID del usuario con SENTENCIAS PREPARADAS (CORRECCIÓN DE SEGURIDAD: Inyección SQL)
    $usuario = $_SESSION["usuario"];
    $usuarioId = null;
    $queryId = "SELECT id_cuenta FROM cuenta WHERE correo = ? OR usuario = ?";
    
    if ($stmt = mysqli_prepare($conexion, $queryId)) {
        mysqli_stmt_bind_param($stmt, "ss", $usuario, $usuario);
        mysqli_stmt_execute($stmt);
        $executeQuery = mysqli_stmt_get_result($stmt);
        $usuarioIdArray = mysqli_fetch_assoc($executeQuery);
        
        if ($usuarioIdArray) {
            $usuarioId = $usuarioIdArray['id_cuenta'];
        }
        mysqli_stmt_close($stmt);
    }

    // 2. Consulta para obtener items del carrito
    $queryCarrito = "SELECT 
                        p.idPedido,
                        p.fecha,
                        p.hora,
                        p.estatus,
                        tp.descripcion as tipo,
                        CASE 
                            WHEN tp.id_tipoPedido = 1 THEN c.nombre
                            WHEN tp.id_tipoPedido = 2 THEN 'Libreta Personalizada'
                        END as nombre,
                        CASE 
                            WHEN tp.id_tipoPedido = 1 THEN c.precio
                            WHEN tp.id_tipoPedido = 2 THEN 0
                        END as precio,
                        CASE 
                            WHEN tp.id_tipoPedido = 1 THEN c.img
                            WHEN tp.id_tipoPedido = 2 THEN per.portada
                        END as img,
                        tp.id_tipoPedido
                    FROM pedidos p
                    INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
                    LEFT JOIN catalogo c ON p.idCatalogo = c.id_producto AND tp.id_tipoPedido = 1
                    LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada AND tp.id_tipoPedido = 2
                    WHERE p.IdCuenta = ? AND p.estatus = 'carrito'
                    ORDER BY p.fecha DESC, p.hora DESC";
    
    $items = [];
    if ($usuarioId && $stmtCarrito = mysqli_prepare($conexion, $queryCarrito)) {
        // Asumiendo que usuario_id es un entero, cambiamos 's' a 'i' si es el caso, sino se deja 's'
        mysqli_stmt_bind_param($stmtCarrito, "i", $usuarioId); 
        mysqli_stmt_execute($stmtCarrito);
        $resultCarrito = mysqli_stmt_get_result($stmtCarrito);
        $items = mysqli_fetch_all($resultCarrito, MYSQLI_ASSOC);
        mysqli_stmt_close($stmtCarrito);
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mi Carrito - BookArt</title>
        <link rel="stylesheet" href="../CSS/reset.css">
        <link rel="stylesheet" href="../CSS/style.css">
        <link rel="stylesheet" href="../CSS/styleCarrito.css">
        <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <script src="../JavaScript/userMenu.js"></script>
    </head>

    <body>
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
                <button class="menu-toggle" id="menuToggle" onclick="toggleMenu()" aria-label="Toggle navigation menu">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </header>

        <section class="cart-section">
            <div class="cart-container">
                <div class="cart-header">
                    <h1>🛒 Mi Carrito de Compras</h1>
                    <p>Revisa tus productos antes de realizar tu pedido</p>
                </div>

                <?php 
                    $total = 0; // Inicializar total fuera del bucle
                    
                    // Calculamos el total de catálogo y la cantidad de personalizados antes de pintar
                    $totalCatalogo = 0;
                    $tienePersonalizados = false;
                    foreach ($items as $item) {
                        if ($item['id_tipoPedido'] == 1) { // Catálogo
                            $totalCatalogo += floatval($item['precio']);
                        } else { // Personalizado
                            $tienePersonalizados = true;
                        }
                    }
                ?>

                <?php if (count($items) > 0): ?>
                    <div class="cart-items">
                        <?php foreach ($items as $item):  
                            $itemTotal = floatval($item['precio']);
                            $isCatalogo = ($item['id_tipoPedido'] == 1);
                        ?>
                            <div class="cart-item" data-id="<?php echo $item['idPedido']; ?>">
                                <div class="cart-item-image">
                                    <img src="<?php echo $item['img'] ? $item['img'] : '../Catalogo/imgNoEncontrada.png'; ?>" 
                                        alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                </div>
                                
                                <div class="cart-item-info">
                                    <span class="cart-item-type">
                                        <?php echo $isCatalogo ? '📚 Catálogo' : '🎨 Personalizada'; ?>
                                    </span>
                                    <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                                    <?php if (!$isCatalogo): ?>
                                        <p style="color: var(--marron-texto); font-size: 0.9rem; margin-top: 0.5rem;">
                                            <strong>Nota:</strong> El precio se definirá al aprobar tu diseño
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="cart-item-price">
                                    <?php 
                                        // CORRECCIÓN DE SINTAXIS: Se cerró la etiqueta PHP para imprimir el HTML correctamente
                                        if ($isCatalogo) {
                                            echo '$' . number_format($itemTotal, 2);
                                        } else {
                                            echo 'A cotizar';
                                        }
                                    ?>
                                </div>
                                
                                <div class="cart-item-actions">
                                    <button class="btn-remove" onclick="removeItem('<?php echo $item['idPedido']; ?>', '<?php echo $item['id_tipoPedido']; ?>')">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <h2>Resumen del Pedido</h2>
                        <div class="summary-line">
                            <span>Productos del catálogo:</span>
                            <span><strong>$<?php echo number_format($totalCatalogo, 2); ?></strong></span>
                        </div>
                        <div class="summary-line">
                            <span>Productos personalizados:</span>
                            <span><strong><?php echo $tienePersonalizados ? 'A cotizar' : '$0.00'; ?></strong></span>
                        </div>
                        <div class="summary-line" style="border-bottom: none; padding-top: 1.5rem;">
                            <span><strong>Total estimado:</strong></span>
                            <span class="summary-total">$<?php echo number_format($totalCatalogo, 2); ?><?php echo $tienePersonalizados ? '+' : ''; ?></span>
                        </div>
                        
                        <p style="margin-top: 1rem; color: var(--marron-texto); font-size: 0.9rem; text-align: center;">
                            * Los productos personalizados se cotizarán individualmente
                        </p>

                        <div class="cart-actions">
                            <a href="Productos.php" class="btn-continue">
                                Seguir Comprando
                            </a>
                            <button class="btn-checkout" onclick="realizarPedido()">
                                Realizar Pedido
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <h2>Tu carrito está vacío</h2>
                        <p style="color: var(--marron-texto); margin: 1rem 0 2rem;">
                            ¡Explora nuestros productos y agrega tus favoritos!
                        </p>
                        <a href="Productos.php" class="btn-checkout" style="display: inline-block; text-decoration: none;">
                            Ver Productos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

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
        <!-- Modal de confirmación de pedido -->
        <dialog id="confirmDialog" style="max-width: 500px; padding: 0; border: 5px solid var(--marron-texto); border-radius: 15px;">
            <div style="background: var(--amarillo-bookart); padding: 1.5rem; border-bottom: 3px solid var(--marron-texto);">
                <h2 style="font-family: var(--font-display); font-size: 1.8rem; color: var(--marron-texto); margin: 0;">
                    ¿Realizar pedido?
                </h2>
            </div>
            <div style="padding: 2rem;">
                <p style="margin-bottom: 2rem; color: var(--marron-texto); line-height: 1.6;">
                    Tu pedido será enviado y nos pondremos en contacto contigo para confirmar detalles de pago y entrega.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button onclick="document.getElementById('confirmDialog').close()" 
                            style="flex: 1; padding: 1rem; background: var(--marron-texto); color: white; border: none; cursor: pointer; font-family: var(--font-body); font-weight: 700; border-radius: 8px;">
                        Cancelar
                    </button>
                    <button onclick="confirmarPedido()" 
                            style="flex: 1; padding: 1rem; background: var(--verde-bookart); color: white; border: none; cursor: pointer; font-family: var(--font-body); font-weight: 700; border-radius: 8px;">
                        Confirmar
                    </button>
                </div>
            </div>
        </dialog>

        <dialog id="deleteDialog" style="max-width: 450px; padding: 0; border: 5px solid var(--marron-texto); border-radius: 15px;">
            <div style="background: var(--amarillo-bookart); padding: 1.5rem; border-bottom: 3px solid var(--marron-texto);">
                <h2 style="font-family: var(--font-display); font-size: 1.6rem; color: var(--marron-texto); margin: 0;">
                    Eliminar producto
                </h2>
            </div>

            <div style="padding: 2rem;">
                <p id="deleteMessage" style="margin-bottom: 2rem; color: var(--marron-texto); line-height: 1.6;">
                    ¿Estás seguro de que deseas eliminar este producto del carrito?
                </p>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button id="btnCancelDelete"
                            style="flex: 1; padding: 1rem; background: var(--marron-texto); color: white; border: none; cursor: pointer; font-family: var(--font-body); font-weight: 700; border-radius: 8px;">
                        Cancelar
                    </button>

                    <button id="btnConfirmDelete" onclick="confirmarEliminacion();"
                            style="flex: 1; padding: 1rem; background: var(--rojo-bookart, #c0392b); color: white; border: none; cursor: pointer; font-family: var(--font-body); font-weight: 700; border-radius: 8px;">
                        Eliminar
                    </button>
                </div>
            </div>
        </dialog>


        <!-- Modal de alerta general -->
        <dialog id="warning" style="max-width: 500px; padding: 0; border: 5px solid var(--marron-texto); border-radius: 15px;">
            <div style="padding: 2rem; text-align: center;">
                <p id="mensaje" style="font-size: 1.1rem; color: var(--marron-texto); margin-bottom: 2rem; line-height: 1.6;"></p>
                <div class="btnModal">
                    <button id="btnAcept" style="padding: 0.8rem 2rem; background: var(--verde-bookart); color: white; border: 3px solid var(--marron-texto); cursor: pointer; font-family: var(--font-body); font-weight: 700; border-radius: 8px; box-shadow: 3px 3px 0px var(--marron-texto);">
                        Aceptar
                    </button>
                </div>
            </div>
        </dialog>
    </body>
    <script src="../JavaScript/carrito.js"></script>
</html>

<?php mysqli_close($conexion); ?>