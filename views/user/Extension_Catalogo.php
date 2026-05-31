<?php
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/conexionBDD.php';

$id_producto = $_GET['id']    ?? '';
$token       = $_GET['token'] ?? '';

if (empty($id_producto) || empty($token)) {
    http_response_code(400); echo 'Valores inválidos.'; exit;
}

if ($token !== hash_hmac('sha1', $id_producto, KEY_TOKEN)) {
    http_response_code(403); echo 'Token inválido.'; exit;
}

$stmt = mysqli_prepare($conexion, "SELECT nombre, img, descripcion, precio FROM catalogo WHERE id_producto = ?");
mysqli_stmt_bind_param($stmt, 's', $id_producto);
mysqli_stmt_execute($stmt);
$producto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
mysqli_close($conexion);

if (!$producto) {
    http_response_code(404); echo 'Producto no encontrado.'; exit;
}

$title   = htmlspecialchars($producto['nombre']) . ' - BookArt';
$extraJs = ['userMenu.js', 'funcionModal.js', 'extensionCatalogo.js'];
?>
<!DOCTYPE html>
<html lang="es">
<head><?php require __DIR__ . '/../partials/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="product-detail">
    <div class="product-detail-container">
        <div class="product-detail-gallery">
            <div class="product-main-image">
                <img src="<?= htmlspecialchars($producto['img']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
            </div>
        </div>
        <div class="product-detail-info">
            <div class="product-detail-breadcrumb">
                <a href="/">Inicio</a>
                <span>/</span>
                <a href="/catalogo">Catálogo</a>
                <span>/</span>
                <span><?= htmlspecialchars($producto['nombre']) ?></span>
            </div>
            <h1 class="product-detail-title"><?= htmlspecialchars($producto['nombre']) ?></h1>
            <div class="product-detail-price">$<?= htmlspecialchars($producto['precio']) ?> MXN</div>
            <p class="product-detail-description"><?= htmlspecialchars($producto['descripcion']) ?></p>
            <div class="product-detail-actions">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <button class="btn-add-cart" onclick="agregarAlCarritoCatalogo(<?= (int)$id_producto ?>, event)">
                        <span class="material-symbols-outlined">shopping_cart</span>
                        Agregar al carrito
                    </button>
                    <a href="/carrito" class="btn-buy-now" style="display:inline-flex;align-items:center;justify-content:center;gap:.5rem;text-decoration:none;">
                        <span class="material-symbols-outlined">shopping_cart_checkout</span>
                        Ver carrito
                    </a>
                <?php else: ?>
                    <a href="/inicio-sesion" class="btn-add-cart" style="display:inline-flex;align-items:center;justify-content:center;gap:.5rem;text-decoration:none;">
                        <span class="material-symbols-outlined">login</span>
                        Inicia sesión para comprar
                    </a>
                <?php endif; ?>
                <a href="/catalogo" class="btn-back-catalog">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Volver al catálogo
                </a>
            </div>
        </div>
    </div>
</section>

<section class="features-section" style="background:var(--crema-papel);padding:4rem 2rem;">
    <div class="section-header"><h2 class="section-title">Características del Producto</h2></div>
    <div class="features-grid" style="max-width:1200px;margin:0 auto;">
        <div class="feature-card">
            <div class="feature-icon"><span style="font-size:3rem;">✂️</span></div>
            <h3 class="feature-title">Hecho a Mano</h3>
            <p class="feature-description">Cada libreta es elaborada cuidadosamente con técnicas artesanales tradicionales</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><span style="font-size:3rem;">📄</span></div>
            <h3 class="feature-title">Papel de Calidad</h3>
            <p class="feature-description">Utilizamos papel de alta calidad que garantiza una experiencia de escritura superior</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><span style="font-size:3rem;">🎨</span></div>
            <h3 class="feature-title">Diseño Único</h3>
            <p class="feature-description">Cada pieza es única, con detalles artesanales que la hacen especial</p>
        </div>
    </div>
</section>

<dialog id="warning">
    <p id="mensaje"></p>
    <div class="btnModal"><button id="btnAcept">Aceptar</button></div>
</dialog>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
