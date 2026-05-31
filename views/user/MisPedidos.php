<?php
require_once __DIR__ . '/../../core/conexionBDD.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: /inicio-sesion'); exit;
}

$usuarioId     = $_SESSION['id_cuenta'] ?? null;
$correoUsuario = $_SESSION['correo']    ?? $_SESSION['usuario'] ?? null;

$pedidos = [];
if ($usuarioId) {
    $sql = "SELECT p.idPedido, p.fecha, p.hora, p.estatus, p.mensaje,
                   tp.descripcion as tipo_descripcion, tp.id_tipoPedido,
                   CASE WHEN tp.id_tipoPedido = 1 THEN c.nombre    ELSE 'Libreta Personalizada' END as nombre,
                   CASE WHEN tp.id_tipoPedido = 1 THEN c.precio    ELSE COALESCE(per.precio,0) END as precio,
                   CASE WHEN tp.id_tipoPedido = 1 THEN c.img       ELSE per.portada END as img,
                   CASE WHEN tp.id_tipoPedido = 1 THEN c.descripcion ELSE per.descripcion END as descripcion
            FROM pedidos p
            INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
            LEFT JOIN catalogo c ON p.idCatalogo = c.id_producto AND tp.id_tipoPedido = 1
            LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada AND tp.id_tipoPedido = 2
            WHERE p.idCuenta = ? AND p.estatus != 'carrito'
            ORDER BY p.fecha DESC, p.hora DESC";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
    mysqli_stmt_execute($stmt);
    $pedidos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
mysqli_close($conexion);

$title    = 'Mis Pedidos - BookArt';
$extraCss = ['styleMisPedidos.css'];
$extraJs  = ['funcionModal.js', 'misPedidos.js', 'userMenu.js'];
?>
<!DOCTYPE html>
<html lang="es">
<head><?php require __DIR__ . '/../partials/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../partials/header.php'; ?>

<dialog id="warning">
    <p id="mensaje"></p>
    <div class="btnModal"><button id="btnAcept">Aceptar</button></div>
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
                $estatus    = strtolower($pedido['estatus']);
                $statusMap  = [
                    'pendiente' => ['⏳','Pendiente de revisión'],
                    'visto'     => ['👀','Visto por el administrador'],
                    'aprobado'  => ['✅','Aprobado'],
                    'declinado' => ['❌','Declinado'],
                    'proceso'   => ['🔨','En proceso de elaboración'],
                    'terminado' => ['🎉','Terminado - Listo para entrega'],
                    'entregado'  => ['📦','Entregado'],
                    'cancelado'  => ['🚫','Cancelado'],
                ];
                [$icon, $text] = $statusMap[$estatus] ?? ['❓','Estado desconocido'];
                $puedeEditar = in_array($estatus, ['pendiente','visto']);
            ?>
            <div class="pedido-card">
                <div class="pedido-header">
                    <div class="pedido-id">Pedido #<?= str_pad($pedido['idPedido'], 5, '0', STR_PAD_LEFT) ?></div>
                    <div class="pedido-fecha">
                        📅 <?= date('d/m/Y', strtotime($pedido['fecha'])) ?>
                        🕐 <?= date('H:i', strtotime($pedido['hora'])) ?>
                    </div>
                </div>
                <div class="pedido-body">
                    <div class="pedido-image">
                        <img src="<?= $pedido['img'] ? htmlspecialchars($pedido['img']) : '/wwwroot/catalogo/imgNoEncontrada.png' ?>"
                             alt="<?= htmlspecialchars($pedido['nombre']) ?>">
                    </div>
                    <div class="pedido-info">
                        <span class="pedido-tipo"><?= $isCatalogo ? '📚 Catálogo' : '🎨 Personalizada' ?></span>
                        <h3><?= htmlspecialchars($pedido['nombre']) ?></h3>
                        <?php if (!empty($pedido['descripcion'])): ?>
                            <p class="pedido-descripcion"><?= htmlspecialchars(substr($pedido['descripcion'], 0, 150)) ?>...</p>
                        <?php endif; ?>
                    </div>
                    <div class="pedido-precio">
                        <?= $isCatalogo || $pedido['precio'] > 0 ? '$' . number_format($pedido['precio'], 2) : 'A cotizar' ?>
                    </div>
                </div>
                <div class="pedido-status">
                    <span class="status-badge status-<?= $estatus ?>"><?= $icon . ' ' . $text ?></span>

                    <?php if ($puedeEditar): ?>
                    <div class="pedido-actions">
                        <?php if ($pedido['id_tipoPedido'] == 2): ?>
                            <button onclick="editarPedido('<?= $pedido['idPedido'] ?>',<?= $pedido['id_tipoPedido'] ?>)" class="btn-action btn-editar-pedido">
                                <span class="material-symbols-outlined">edit</span> Editar
                            </button>
                        <?php endif; ?>
                        <button onclick="cancelarPedido('<?= $pedido['idPedido'] ?>')" class="btn-action btn-cancelar-pedido">
                            <span class="material-symbols-outlined">cancel</span> Cancelar Pedido
                        </button>
                    </div>
                    <?php endif; ?>

                    <?php if (in_array($estatus, ['aprobado','proceso','terminado','visto','declinado','entregado']) && !empty($pedido['mensaje'])): ?>
                    <div class="mensaje-admin">
                        <div class="mensaje-admin-header">
                            <span class="material-symbols-outlined">mail</span> Mensaje del administrador:
                        </div>
                        <p><?= nl2br(htmlspecialchars($pedido['mensaje'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-pedidos">
                <div class="empty-pedidos-icon">📦</div>
                <h2>No tienes pedidos aún</h2>
                <p style="color:var(--marron-texto);margin:1rem 0 2rem;">¡Explora nuestros productos y realiza tu primer pedido!</p>
                <a href="/productos" class="btn-primary" style="display:inline-block;text-decoration:none;padding:1rem 2rem;">Ver Productos</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<dialog id="dialogConfirmCancel" style="max-width:500px;padding:0;">
    <div style="background:var(--amarillo-bookart);padding:1.5rem;border-bottom:3px solid var(--marron-texto);">
        <h2 style="font-family:var(--font-display);font-size:1.8rem;color:var(--marron-texto);margin:0;">⚠️ ¿Cancelar pedido?</h2>
    </div>
    <div style="padding:2rem;">
        <p id="mensajeConfirmCancel" style="color:var(--marron-texto);margin-bottom:2rem;line-height:1.6;">
            ¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.
        </p>
        <div style="display:flex;gap:1rem;justify-content:flex-end;">
            <button onclick="cerrarDialogConfirmCancel()" style="padding:.8rem 1.5rem;background:var(--marron-texto);color:white;border:none;cursor:pointer;font-family:var(--font-body);font-weight:700;">No, mantener</button>
            <button id="btnConfirmCancel" onclick="confirmarCancelacion()" style="padding:.8rem 1.5rem;background:var(--rojo-bookart);color:white;border:none;cursor:pointer;font-family:var(--font-body);font-weight:700;">Sí, cancelar pedido</button>
        </div>
    </div>
</dialog>
</body>
</html>
