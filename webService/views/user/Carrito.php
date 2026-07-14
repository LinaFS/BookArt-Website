<?php
require_once __DIR__ . '/../../../apiService/core/config.php';
require_once __DIR__ . '/../../../apiService/core/conexionBDD.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: /inicio-sesion'); exit;
}

$usuarioId = $_SESSION['id_cuenta'] ?? null;

$items = [];
if ($usuarioId) {
    $sql = "SELECT p.idPedido, p.fecha, p.hora, p.estatus,
                   tp.descripcion as tipo,
                   CASE WHEN tp.id_tipoPedido = 1 THEN c.nombre    ELSE 'Libreta Personalizada' END as nombre,
                   CASE WHEN tp.id_tipoPedido = 1 THEN c.precio    ELSE 0 END as precio,
                   CASE WHEN tp.id_tipoPedido = 1 THEN c.img       ELSE per.portada END as img,
                   tp.id_tipoPedido
            FROM pedidos p
            INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
            LEFT JOIN catalogo c ON p.idCatalogo = c.id_producto AND tp.id_tipoPedido = 1
            LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada AND tp.id_tipoPedido = 2
            WHERE p.IdCuenta = ? AND p.estatus = 'carrito'
            ORDER BY p.fecha DESC, p.hora DESC";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
    mysqli_stmt_execute($stmt);
    $items = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
mysqli_close($conexion);

$totalCatalogo    = array_sum(array_column(array_filter($items, fn($i) => $i['id_tipoPedido'] == 1), 'precio'));
$tienePersonalizados = count(array_filter($items, fn($i) => $i['id_tipoPedido'] == 2)) > 0;

$title    = 'Mi Carrito - BookArt';
$extraCss = ['styleCarrito.css'];
$extraJs  = ['funcionModal.js', 'carrito.js', 'userMenu.js'];
?>
<!DOCTYPE html>
<html lang="es">
<head><?php require __DIR__ . '/../partials/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="cart-section">
    <div class="cart-container">
        <div class="cart-header">
            <h1>🛒 Mi Carrito de Compras</h1>
            <p>Revisa tus productos antes de realizar tu pedido</p>
        </div>

        <?php if (count($items) > 0): ?>
            <div class="cart-items">
                <?php foreach ($items as $item):
                    $isCatalogo = ($item['id_tipoPedido'] == 1); ?>
                    <div class="cart-item" data-id="<?= $item['idPedido'] ?>">
                        <div class="cart-item-image">
                            <img src="<?= $item['img'] ? htmlspecialchars($item['img']) : '/webService/wwwroot/catalogo/imgNoEncontrada.png' ?>"
                                 alt="<?= htmlspecialchars($item['nombre']) ?>">
                        </div>
                        <div class="cart-item-info">
                            <span class="cart-item-type"><?= $isCatalogo ? '📚 Catálogo' : '🎨 Personalizada' ?></span>
                            <h3><?= htmlspecialchars($item['nombre']) ?></h3>
                            <?php if (!$isCatalogo): ?>
                                <p style="color:var(--marron-texto);font-size:.9rem;margin-top:.5rem;">
                                    <strong>Nota:</strong> El precio se definirá al aprobar tu diseño
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="cart-item-price">
                            <?= $isCatalogo ? '$' . number_format($item['precio'], 2) : 'A cotizar' ?>
                        </div>
                        <div class="cart-item-actions">
                            <button class="btn-remove" onclick="removeItem('<?= $item['idPedido'] ?>','<?= $item['id_tipoPedido'] ?>')">
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
                    <span><strong>$<?= number_format($totalCatalogo, 2) ?></strong></span>
                </div>
                <div class="summary-line">
                    <span>Productos personalizados:</span>
                    <span><strong><?= $tienePersonalizados ? 'A cotizar' : '$0.00' ?></strong></span>
                </div>
                <div class="summary-line" style="border-bottom:none;padding-top:1.5rem;">
                    <span><strong>Total estimado:</strong></span>
                    <span class="summary-total">$<?= number_format($totalCatalogo, 2) ?><?= $tienePersonalizados ? '+' : '' ?></span>
                </div>
                <p style="margin-top:1rem;color:var(--marron-texto);font-size:.9rem;text-align:center;">
                    * Los productos personalizados se cotizarán individualmente
                </p>
                <div class="cart-actions">
                    <a href="/productos" class="btn-continue">Seguir Comprando</a>
                    <button class="btn-checkout" onclick="realizarPedido()">Realizar Pedido</button>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2>Tu carrito está vacío</h2>
                <p style="color:var(--marron-texto);margin:1rem 0 2rem;">¡Explora nuestros productos y agrega tus favoritos!</p>
                <a href="/productos" class="btn-checkout" style="display:inline-block;text-decoration:none;">Ver Productos</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<dialog id="confirmDialog" style="max-width:500px;padding:0;border:5px solid var(--marron-texto);border-radius:15px;">
    <div style="background:var(--amarillo-bookart);padding:1.5rem;border-bottom:3px solid var(--marron-texto);">
        <h2 style="font-family:var(--font-display);font-size:1.8rem;color:var(--marron-texto);margin:0;">¿Realizar pedido?</h2>
    </div>
    <div style="padding:2rem;">
        <p style="margin-bottom:2rem;color:var(--marron-texto);line-height:1.6;">Tu pedido será enviado y nos pondremos en contacto contigo para confirmar detalles.</p>
        <div style="display:flex;gap:1rem;justify-content:flex-end;">
            <button onclick="document.getElementById('confirmDialog').close()" style="flex:1;padding:1rem;background:var(--marron-texto);color:white;border:none;cursor:pointer;font-family:var(--font-body);font-weight:700;border-radius:8px;">Cancelar</button>
            <button onclick="confirmarPedido()" style="flex:1;padding:1rem;background:var(--verde-bookart);color:white;border:none;cursor:pointer;font-family:var(--font-body);font-weight:700;border-radius:8px;">Confirmar</button>
        </div>
    </div>
</dialog>

<dialog id="deleteDialog" style="max-width:450px;padding:0;border:5px solid var(--marron-texto);border-radius:15px;">
    <div style="background:var(--amarillo-bookart);padding:1.5rem;border-bottom:3px solid var(--marron-texto);">
        <h2 style="font-family:var(--font-display);font-size:1.6rem;color:var(--marron-texto);margin:0;">Eliminar producto</h2>
    </div>
    <div style="padding:2rem;">
        <p id="deleteMessage" style="margin-bottom:2rem;color:var(--marron-texto);line-height:1.6;">¿Estás seguro de que deseas eliminar este producto del carrito?</p>
        <div style="display:flex;gap:1rem;justify-content:flex-end;">
            <button onclick="cancelarEliminacion()" type="button" style="flex:1;padding:1rem;background:var(--marron-texto);color:white;border:none;cursor:pointer;font-family:var(--font-body);font-weight:700;border-radius:8px;">Cancelar</button>
            <button onclick="confirmarEliminacion()" type="button" style="flex:1;padding:1rem;background:var(--rojo-bookart,#c0392b);color:white;border:none;cursor:pointer;font-family:var(--font-body);font-weight:700;border-radius:8px;">Eliminar</button>
        </div>
    </div>
</dialog>

<dialog id="warning" style="max-width:500px;padding:0;border:5px solid var(--marron-texto);border-radius:15px;">
    <div style="padding:2rem;text-align:center;">
        <p id="mensaje" style="font-size:1.1rem;color:var(--marron-texto);margin-bottom:2rem;line-height:1.6;"></p>
        <div class="btnModal">
            <button id="btnAcept" style="padding:.8rem 2rem;background:var(--verde-bookart);color:white;border:3px solid var(--marron-texto);cursor:pointer;font-family:var(--font-body);font-weight:700;border-radius:8px;box-shadow:3px 3px 0px var(--marron-texto);">Aceptar</button>
        </div>
    </div>
</dialog>
</body>
</html>
