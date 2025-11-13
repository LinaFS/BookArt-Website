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
    
    $queryId = "SELECT usuario_id, correo FROM cuenta WHERE correo = ? OR usuario = ?";
    
    if ($stmt = mysqli_prepare($conexion, $queryId)) {
        mysqli_stmt_bind_param($stmt, "ss", $usuario, $usuario);
        mysqli_stmt_execute($stmt);
        $executeQuery = mysqli_stmt_get_result($stmt);
        $usuarioData = mysqli_fetch_assoc($executeQuery);
        
        if ($usuarioData) {
            $usuarioId = $usuarioData['usuario_id'];
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
                        tp.descripcion as tipo_descripcion,
                        tp.id_tipoPedido,
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
                        CASE 
                            WHEN tp.id_tipoPedido = 1 THEN c.descripcion
                            WHEN tp.id_tipoPedido = 2 THEN per.descripcion
                        END as descripcion
                    FROM pedidos p
                    INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
                    LEFT JOIN catalogo c ON p.idCatalogo = c.id_producto AND tp.id_tipoPedido = 1
                    LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada AND tp.id_tipoPedido = 2
                    WHERE p.IdCuenta = ? AND p.estatus != 'carrito'
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
    <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        /* [Estilos CSS - Sin cambios mayores, se mantiene tu diseño] */
        .pedidos-section {
            padding: 4rem 2rem;
            background: var(--crema-papel);
            min-height: 70vh;
        }
        
        .pedidos-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .pedidos-header {
            background: var(--blanco);
            padding: 2rem;
            border: 5px solid var(--marron-texto);
            border-left: 10px solid var(--verde-bookart);
            margin-bottom: 2rem;
            box-shadow: var(--sombra-papel);
        }
        
        .pedidos-header h1 {
            font-family: var(--font-display);
            font-size: 2.5rem;
            color: var(--marron-texto);
            margin-bottom: 0.5rem;
        }
        
        .pedido-card {
            background: var(--blanco);
            border: 5px solid var(--marron-texto);
            box-shadow: var(--sombra-papel);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .pedido-header {
            background: var(--amarillo-bookart);
            padding: 1.5rem;
            border-bottom: 3px solid var(--marron-texto);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .pedido-id {
            font-family: var(--font-display);
            font-size: 1.3rem;
            color: var(--marron-texto);
        }
        
        .pedido-fecha {
            color: var(--marron-texto);
            font-weight: 600;
        }
        
        .pedido-body {
            padding: 2rem;
            display: grid;
            grid-template-columns: 150px 1fr auto;
            gap: 2rem;
            align-items: start;
        }
        
        .pedido-image {
            width: 150px;
            height: 150px;
            border: 4px solid var(--marron-texto);
            padding: 0.5rem;
            background: var(--crema-papel);
        }
        
        .pedido-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .pedido-info h3 {
            font-family: var(--font-display);
            font-size: 1.8rem;
            color: var(--marron-texto);
            margin-bottom: 0.5rem;
        }
        
        .pedido-tipo {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: var(--verde-bookart);
            color: var(--blanco);
            border: 2px solid var(--marron-texto);
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .pedido-descripcion {
            color: var(--marron-texto);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .pedido-precio {
            font-family: var(--font-display);
            font-size: 2.2rem;
            color: var(--verde-bookart);
            font-weight: bold;
        }
        
        .pedido-status {
            padding: 1.5rem;
            border-top: 3px dashed var(--marron-texto);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border: 3px solid var(--marron-texto);
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 3px 3px 0px var(--marron-texto);
        }
        
        .status-pendiente {
            background: var(--amarillo-bookart);
            color: var(--marron-texto);
        }
        
        .status-visto {
            background: #89CFF0;
            color: var(--marron-texto);
        }
        
        .status-aprobado {
            background: #90EE90;
            color: var(--marron-texto);
        }
        
        .status-declinado {
            background: var(--rojo-bookart);
            color: var(--blanco);
        }
        
        .status-proceso {
            background: var(--azul-bookart);
            color: var(--blanco);
        }
        
        .status-terminado {
            background: var(--verde-bookart);
            color: var(--blanco);
        }
        
        .status-entregado {
            background: var(--marron-texto);
            color: var(--blanco);
        }
        
        .mensaje-admin {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--crema-papel);
            border-left: 4px solid var(--verde-bookart);
        }
        
        .mensaje-admin-header {
            font-weight: 700;
            color: var(--verde-bookart);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .empty-pedidos {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--blanco);
            border: 5px solid var(--marron-texto);
            box-shadow: var(--sombra-papel);
        }
        
        .empty-pedidos-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
        }
        
        .empty-pedidos h2 {
            font-family: var(--font-display);
            font-size: 2.2rem;
            color: var(--marron-texto);
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .pedido-body {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .pedido-image {
                margin: 0 auto;
            }
            
            .pedido-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
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
                    <li><a href="Carrito.php">Carrito</a></li>
                    </ul>
                <a href="../PHP/cerrar_sesion.php" class="btn-session">Cerrar sesión</a>
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
                                    // CORRECCIÓN DE SINTAXIS: Se cerró la etiqueta PHP de forma correcta
                                    if ($isCatalogo) {
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
                            
                            <?php if ($estatus == 'aprobado' || $estatus == 'proceso' || $estatus == 'terminado'): ?>
                                <div class="mensaje-admin">
                                    <div class="mensaje-admin-header">
                                        <span class="material-symbols-outlined">mail</span>
                                        Mensaje del administrador:
                                    </div>
                                    <p>
                                        Nos pondremos en contacto contigo al correo <strong><?php echo htmlspecialchars($correoUsuario); ?></strong> 
                                        para confirmar detalles de pago y entrega. ¡Gracias por tu preferencia!
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
                                        Lo sentimos, no pudimos procesar tu pedido tal como fue solicitado. 
                                        Te contactaremos al correo <strong><?php echo htmlspecialchars($correoUsuario); ?></strong> 
                                        para ofrecerte alternativas viables.
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

        // Modal de mensajes
        document.addEventListener('DOMContentLoaded', function() {
            const mensajeElement = document.getElementById('mensaje');
            const warningDialog = document.getElementById('warning');
            const btnAcept = document.getElementById('btnAcept');
            
            if (mensajeElement && mensajeElement.textContent.trim() !== '') {
                warningDialog.showModal();
            }

            if (btnAcept) {
                btnAcept.addEventListener('click', function() {
                    warningDialog.close();
                    // Limpiar URL al cerrar el modal
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            }
        });
    </script>
</body>
</html>

<?php mysqli_close($conexion); ?>