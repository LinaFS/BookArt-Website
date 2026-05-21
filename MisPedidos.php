<?php
    session_start();
    
    // 1. Bloque de seguridad para la sesión
    if (!isset($_SESSION["usuario"])) {
        header("Location: Inicio_sesion.php?origen=MisPedidos");
        exit();
    }
    
    // Inclusión de archivos de configuración
    require("PHP/conexionBDD.php");
    
    // --- Obtener ID del usuario con SENTENCIAS PREPARADAS (CORRECCIÓN DE SEGURIDAD: Inyección SQL) ---
    $usuario = $_SESSION["usuario"];
    $usuarioId = null;
    $correoUsuario = null;
    
    $queryId = "SELECT id_cuenta, correo FROM cuenta WHERE correo = ? OR usuario = ?";
    
    if ($stmt = mysqli_prepare($conexion, $queryId)) {
        mysqli_stmt_bind_param($stmt, "ss", $usuario, $usuario);
        mysqli_stmt_execute($stmt);
        $executeQuery = mysqli_stmt_get_result($stmt);
        $usuarioData = mysqli_fetch_assoc($executeQuery);
        
        if ($usuarioData) {
            $usuarioId = $usuarioData['id_cuenta'];
            $correoUsuario = $usuarioData['correo'];
        }
        mysqli_stmt_close($stmt);
    }

    // --- Obtener todos los pedidos del usuario (excepto carrito) con SENTENCIAS PREPARADAS ---
    $queryPedidos = "SELECT 
                        p.idPedido,
                        p.fecha,
                        p.hora,
                        p.estatus,
                        p.mensaje,
                        tp.descripcion as tipo_descripcion,
                        tp.id_tipoPedido,
                        CASE 
                            WHEN tp.id_tipoPedido = 1 THEN c.nombre
                            WHEN tp.id_tipoPedido = 2 THEN 'Libreta Personalizada'
                        END as nombre,
                        CASE 
                            WHEN tp.id_tipoPedido = 1 THEN c.precio
                            WHEN tp.id_tipoPedido = 2 THEN COALESCE(per.precio, 0)
                        END as precio,
                        CASE 
                            WHEN tp.id_tipoPedido = 1 THEN c.img
                            WHEN tp.id_tipoPedido = 2 THEN per.portada
                        END as img,
                        CASE 
                            WHEN tp.id_tipoPedido = 1 THEN c.descripcion
                            WHEN tp.id_tipoPedido = 2 THEN per.descripcion
                        END as descripcion
                    FROM pedidos p
                    INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
                    LEFT JOIN catalogo c ON p.idCatalogo = c.id_producto AND tp.id_tipoPedido = 1
                    LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada AND tp.id_tipoPedido = 2
                    WHERE p.idCuenta = ? AND p.estatus != 'carrito'
                    ORDER BY p.fecha DESC, p.hora DESC";
    
    $pedidos = [];
    if ($usuarioId && $stmtPedidos = mysqli_prepare($conexion, $queryPedidos)) {
        // Asumiendo que usuario_id es un entero ('i')
        mysqli_stmt_bind_param($stmtPedidos, "i", $usuarioId); 
        mysqli_stmt_execute($stmtPedidos);
        $resultPedidos = mysqli_stmt_get_result($stmtPedidos);
        $pedidos = mysqli_fetch_all($resultPedidos, MYSQLI_ASSOC);
        mysqli_stmt_close($stmtPedidos);
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - BookArt</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/styleMisPedidos.css">
    <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="../JavaScript/misPedidos.js" defer></script>
    <script src="../JavaScript/userMenu.js" defer></script>
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
            
            <button class="menu-toggle" id="menuToggle" onclick="toggleMenu()">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </header>

    <dialog id="warning">
        <p id="mensaje">
            <?php 
            // CORRECCIÓN: Usamos htmlspecialchars para prevenir XSS si el mensaje viene de la URL
            echo isset($_GET['mensaje']) ? htmlspecialchars($_GET['mensaje']) : ''; 
            ?>
        </p>
        <div class="btnModal">
            <button id="btnAcept">Aceptar</button>
        </div>
    </dialog>

    <section class="pedidos-section">
        <div class="pedidos-container">
            <div class="pedidos-header">
                <h1>📦 Mis Pedidos</h1>
                <p>Aquí puedes ver el estado de todos tus pedidos</p>
            </div>

            <?php if (count($pedidos) > 0): ?>
                <?php foreach ($pedidos as $pedido): 
                    $isCatalogo = ($pedido['id_tipoPedido'] == 1);
                ?>
                    <div class="pedido-card">
                        <div class="pedido-header">
                            <div class="pedido-id">
                                Pedido #<?php echo str_pad($pedido['idPedido'], 5, '0', STR_PAD_LEFT); ?>
                            </div>
                            <div class="pedido-fecha">
                                📅 <?php echo date('d/m/Y', strtotime($pedido['fecha'])); ?> 
                                🕐 <?php echo date('H:i', strtotime($pedido['hora'])); ?>
                            </div>
                        </div>
                        
                        <div class="pedido-body">
                            <div class="pedido-image">
                                <img src="<?php echo $pedido['img'] ? htmlspecialchars($pedido['img']) : '../Catalogo/imgNoEncontrada.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($pedido['nombre']); ?>">
                            </div>
                            
                            <div class="pedido-info">
                                <span class="pedido-tipo">
                                    <?php echo $isCatalogo ? '📚 Catálogo' : '🎨 Personalizada'; ?>
                                </span>
                                <h3><?php echo htmlspecialchars($pedido['nombre']); ?></h3>
                                <?php if (!empty($pedido['descripcion'])): ?>
                                    <p class="pedido-descripcion">
                                        <?php echo htmlspecialchars(substr($pedido['descripcion'], 0, 150)) . '...'; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <div class="pedido-precio">
                                    <?php 
                                    if ($isCatalogo) {
                                        echo '$' . number_format($pedido['precio'], 2);
                                    } elseif ($pedido['precio'] > 0) {
                                        echo '$' . number_format($pedido['precio'], 2);
                                    } else {
                                        echo 'A cotizar';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pedido-status">
                            <?php
                            $estatus = strtolower($pedido['estatus']);
                            $statusClass = 'status-' . $estatus;
                            $statusIcon = '';
                            $statusText = '';
                            
                            switch($estatus) {
                                case 'pendiente':
                                    $statusIcon = '⏳';
                                    $statusText = 'Pendiente de revisión';
                                    break;
                                case 'visto':
                                    $statusIcon = '👀';
                                    $statusText = 'Visto por el administrador';
                                    break;
                                case 'aprobado':
                                    $statusIcon = '✅';
                                    $statusText = 'Aprobado';
                                    break;
                                case 'declinado':
                                    $statusIcon = '❌';
                                    $statusText = 'Declinado';
                                    break;
                                case 'proceso':
                                    $statusIcon = '🔨';
                                    $statusText = 'En proceso de elaboración';
                                    break;
                                case 'terminado':
                                    $statusIcon = '🎉';
                                    $statusText = 'Terminado - Listo para entrega';
                                    break;
                                case 'entregado':
                                    $statusIcon = '📦';
                                    $statusText = 'Entregado';
                                    break;
                                default:
                                    $statusIcon = '❓';
                                    $statusText = 'Estado desconocido';
                                    break;
                            }
                            ?>
                            
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $statusIcon . ' ' . $statusText; ?>
                            </span>
                            <?php
                            // Solo mostrar acciones si el pedido está en estado pendiente o visto
                            $puedeEditar = in_array(strtolower($pedido['estatus']), ['pendiente', 'visto']);
                            ?>

                            <?php if ($puedeEditar): ?>
                            <div class="pedido-actions">
                                <?php if ($pedido['id_tipoPedido'] == 2): // Solo personalizadas se pueden "editar" ?>
                                    <button onclick="editarPedido('<?php echo $pedido['idPedido']; ?>', <?php echo $pedido['id_tipoPedido']; ?>)" 
                                            class="btn-action btn-editar-pedido">
                                        <span class="material-symbols-outlined">edit</span>
                                        Editar
                                    </button>
                                <?php endif; ?>
                                
                                <button onclick="cancelarPedido('<?php echo $pedido['idPedido']; ?>')" 
                                        class="btn-action btn-cancelar-pedido">
                                    <span class="material-symbols-outlined">cancel</span>
                                    Cancelar Pedido
                                </button>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($estatus == 'aprobado' || $estatus == 'proceso' || $estatus == 'terminado'): ?>
                                <div class="mensaje-admin">
                                    <div class="mensaje-admin-header">
                                        <span class="material-symbols-outlined">mail</span>
                                        Mensaje del administrador:
                                    </div>
                                    <p>
                                        <?php if (!empty($pedido['mensaje'])): ?>
                                            <?php echo nl2br(htmlspecialchars($pedido['mensaje'])); ?>
                                        <?php else: ?>
                                            Nos pondremos en contacto contigo al correo <strong><?php echo htmlspecialchars($correoUsuario); ?></strong> 
                                            para confirmar detalles de pago y entrega. ¡Gracias por tu preferencia!
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if ($estatus == 'declinado'): ?>
                                <div class="mensaje-admin" style="border-left-color: var(--rojo-bookart);">
                                    <div class="mensaje-admin-header" style="color: var(--rojo-bookart);">
                                        <span class="material-symbols-outlined">info</span>
                                        Información importante:
                                    </div>
                                    <p>
                                        <?php if (!empty($pedido['mensaje'])): ?>
                                            <?php echo nl2br(htmlspecialchars($pedido['mensaje'])); ?>
                                        <?php else: ?>
                                            Lo sentimos, no pudimos procesar tu pedido tal como fue solicitado. 
                                            Te contactaremos al correo <strong><?php echo htmlspecialchars($correoUsuario); ?></strong> 
                                            para ofrecerte alternativas viables.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if ($estatus == 'entregado' && !empty($pedido['mensaje'])): ?>
                                <div class="mensaje-admin" style="border-left-color: var(--verde-bookart);">
                                    <div class="mensaje-admin-header" style="color: var(--verde-bookart);">
                                        <span class="material-symbols-outlined">check_circle</span>
                                        Mensaje de entrega:
                                    </div>
                                    <p>
                                        <?php echo nl2br(htmlspecialchars($pedido['mensaje'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if ($estatus == 'visto' && !empty($pedido['mensaje'])): ?>
                                <div class="mensaje-admin" style="border-left-color: var(--azul-bookart);">
                                    <div class="mensaje-admin-header" style="color: var(--azul-bookart);">
                                        <span class="material-symbols-outlined">visibility</span>
                                        Actualización:
                                    </div>
                                    <p>
                                        <?php echo nl2br(htmlspecialchars($pedido['mensaje'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-pedidos">
                    <div class="empty-pedidos-icon">📦</div>
                    <h2>No tienes pedidos aún</h2>
                    <p style="color: var(--marron-texto); margin: 1rem 0 2rem;">
                        ¡Explora nuestros productos y realiza tu primer pedido!
                    </p>
                    <a href="Productos.php" class="btn-primary" style="display: inline-block; text-decoration: none; padding: 1rem 2rem;">
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
    <!-- Dialog de confirmación de cancelación -->
    <dialog id="dialogConfirmCancel" style="max-width: 500px; padding: 0;">
        <div style="background: var(--amarillo-bookart); padding: 1.5rem; border-bottom: 3px solid var(--marron-texto);">
            <h2 style="font-family: var(--font-display); font-size: 1.8rem; color: var(--marron-texto); margin: 0;">
                ⚠️ ¿Cancelar pedido?
            </h2>
        </div>
        <div style="padding: 2rem;">
            <p id="mensajeConfirmCancel" style="color: var(--marron-texto); margin-bottom: 2rem; line-height: 1.6;">
                ¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button onclick="cerrarDialogConfirmCancel()" 
                        style="padding: 0.8rem 1.5rem; background: var(--marron-texto); color: white; border: none; cursor: pointer; font-family: var(--font-body); font-weight: 700;">
                    No, mantener
                </button>
                <button id="btnConfirmCancel" onclick="confirmarCancelacion()" 
                        style="padding: 0.8rem 1.5rem; background: var(--rojo-bookart); color: white; border: none; cursor: pointer; font-family: var(--font-body); font-weight: 700;">
                    Sí, cancelar pedido
                </button>
            </div>
        </div>
    </dialog>
</body>

</html>

<?php mysqli_close($conexion); ?>