<?php
$title = 'Página no encontrada - BookArt';
?>
<!DOCTYPE html>
<html lang="es">
<head><?php require __DIR__ . '/partials/head.php'; ?></head>
<body>
<?php require __DIR__ . '/partials/header.php'; ?>

<section style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:4rem 2rem;text-align:center;">
    <div>
        <p style="font-family:var(--font-display);font-size:8rem;color:var(--color-verde-principal);margin:0;line-height:1;">404</p>
        <h1 style="font-family:var(--font-display);font-size:2.5rem;color:var(--color-marron);margin:1rem 0;">
            ¡Ups! Página no encontrada
        </h1>
        <p style="color:var(--color-marron-claro);font-size:1.1rem;margin-bottom:2.5rem;line-height:1.6;">
            La página que buscas no existe o fue movida.<br>
            ¡Pero tenemos muchas libretas esperándote!
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="/" class="btn-primary">
                <span class="material-symbols-outlined">home</span>
                Ir al inicio
            </a>
            <a href="/catalogo" class="btn-secondary">
                Ver Catálogo
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
