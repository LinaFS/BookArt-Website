<?php
require_once __DIR__ . '/../../../apiService/core/config.php';
require_once __DIR__ . '/../../../apiService/core/conexionBDD.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: /inicio-sesion'); exit;
}

$catalogo = [];
$sql = "SELECT id_producto, nombre, precio, img FROM catalogo";
$result = mysqli_query($conexion, $sql);
if ($result) {
    $catalogo = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
mysqli_close($conexion);

$title   = 'Catálogo - BookArt Encuadernaciones';
$extraJs = ['userMenu.js'];
?>
<!DOCTYPE html>
<html lang="es">
<head><?php require __DIR__ . '/../partials/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="catalog-hero">
    <h1>Nuestro Catálogo Artesanal</h1>
    <p>Explora nuestra colección de libretas hechas a mano con amor y dedicación</p>
</section>

<section class="catalog-section">
    <div class="catalog-container">
        <?php if (!empty($catalogo)): ?>
            <div class="catalog-grid">
                <?php foreach ($catalogo as $row): ?>
                    <a href="/extension-catalogo?id=<?= $row['id_producto'] ?>&token=<?= hash_hmac('sha1', $row['id_producto'], KEY_TOKEN) ?>" class="catalog-item">
                        <div class="catalog-item-image">
                            <img src="<?= htmlspecialchars($row['img']) ?>" alt="<?= htmlspecialchars($row['nombre']) ?>">
                        </div>
                        <div class="catalog-item-info">
                            <h3 class="catalog-item-title"><?= htmlspecialchars($row['nombre']) ?></h3>
                            <p class="catalog-item-description">Libreta artesanal hecha a mano con materiales de la más alta calidad</p>
                            <div class="catalog-item-footer">
                                <span class="catalog-item-price">$<?= htmlspecialchars($row['precio']) ?></span>
                                <span class="catalog-item-action">
                                    Ver más
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="catalog-empty">
                <div class="catalog-empty-icon">📚</div>
                <h3>No hay productos disponibles</h3>
                <p>Estamos trabajando en nuevos diseños increíbles. ¡Vuelve pronto!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
