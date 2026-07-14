<?php
$title   = 'Productos - BookArt Encuadernaciones';
$extraJs = ['userMenu.js'];
?>
<!DOCTYPE html>
<html lang="es">
<head><?php require __DIR__ . '/../partials/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="products-hero">
    <h1>Nuestros Productos</h1>
    <p>Descubre toda nuestra gama de encuadernaciones artesanales</p>
</section>

<section class="products-options">
    <div class="products-grid">
        <!-- OPCIÓN 1: LIBRETAS PERSONALIZADAS -->
        <a href="/personalizada" class="product-option">
            <div class="product-option-image">
                <img src="/webService/wwwroot/img/Libreta.png" alt="Libretas Personalizadas">
            </div>
            <div class="product-option-content">
                <h2 class="product-option-title">Libretas Personalizadas</h2>
                <p class="product-option-description">
                    ¡Crea tu propio diseño! Elige el tamaño, tipo de papel, colores y hasta puedes agregar tu propia portada. Hazla única como tú.
                </p>
                <span class="product-option-cta">
                    Diseña tu libreta
                    <span class="material-symbols-outlined">brush</span>
                </span>
            </div>
        </a>

        <!-- OPCIÓN 2: CATÁLOGO -->
        <a href="/catalogo" class="product-option">
            <div class="product-option-image">
                <img src="/webService/wwwroot/img/Hojas.png" alt="Catálogo">
            </div>
            <div class="product-option-content">
                <h2 class="product-option-title">Catálogo</h2>
                <p class="product-option-description">
                    ¡Escoge entre nuestra variedad de diseños! Explora nuestra colección de libretas artesanales ya diseñadas y listas para ti.
                </p>
                <span class="product-option-cta">
                    Ver catálogo
                    <span class="material-symbols-outlined">auto_stories</span>
                </span>
            </div>
        </a>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
