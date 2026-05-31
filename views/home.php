<?php
$title   = 'BookArt Encuadernaciones | Libretas Artesanales Hechas a Mano';
$extraJs = ['userMenu.js'];
?>
<!DOCTYPE html>
<html lang="es">
<head><?php require __DIR__ . '/partials/head.php'; ?></head>
<body>
<?php require __DIR__ . '/partials/header.php'; ?>

<!-- HERO -->
<section class="hero-section">
    <div class="hero-container">
        <div class="hero-text">
            <p class="hero-pretitle">✨ Hecho a mano con amor</p>
            <h2 class="hero-title">
                <span class="highlight">BookArt:</span><br>
                Encuadernaciones<br>
                Artesanales
            </h2>
            <p class="hero-description">
                "Captura tus ideas con estilo: Libretas Artesanales, ¡Donde la Tradición se une con la Creatividad!"
            </p>
            <div class="hero-cta">
                <a href="/catalogo" class="btn-primary">
                    Ver Catálogo
                    <span class="material-symbols-outlined">auto_stories</span>
                </a>
                <a href="/personalizada" class="btn-secondary">Diseña tu Libreta</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="/wwwroot/img/pantalla.png" alt="Libretas artesanales BookArt">
            <p class="hero-image-caption">Cada libreta cuenta una historia</p>
        </div>
    </div>
</section>

<!-- CARACTERÍSTICAS -->
<section class="features-section">
    <div class="section-header">
        <h2 class="section-title">¿Por qué elegir BookArt?</h2>
        <p class="section-subtitle">Descubre lo que hace especiales nuestras encuadernaciones artesanales</p>
    </div>
    <div class="features-grid">
        <article class="feature-card">
            <div class="feature-icon"><img src="/wwwroot/img/card1.png" alt="Sobre nosotros"></div>
            <h3 class="feature-title">Sobre Nosotros</h3>
            <p class="feature-description">
                BookArt Encuadernaciones es un negocio especializado en encuadernaciones artesanales de alta calidad en un proceso manual refinado y que se adapta a tus necesidades y estilo.
            </p>
            <a href="/contacto" class="feature-link">
                Contáctanos <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </article>
        <article class="feature-card">
            <div class="feature-icon"><img src="/wwwroot/img/card3.png" alt="Nuestra técnica"></div>
            <h3 class="feature-title">Nuestra Técnica</h3>
            <p class="feature-description">
                La encuadernación artesanal es un antiguo arte que combina habilidad, pasión y atención al detalle para crear hermosas y funcionales obras de arte en forma de libros.
            </p>
            <a href="/catalogo" class="feature-link">
                Ver Catálogo <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </article>
        <article class="feature-card">
            <div class="feature-icon"><img src="/wwwroot/img/card2.png" alt="Nuestros productos"></div>
            <h3 class="feature-title">Nuestros Productos</h3>
            <p class="feature-description">
                En BookArt nos jactamos de tener productos de la mejor calidad, además, nos adaptamos a tus necesidades, ¡Obtén tu libreta completamente personalizada!
            </p>
            <a href="/personalizada" class="feature-link">
                Haz tu pedido <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </article>
    </div>
</section>

<!-- CTA FINAL -->
<section class="features-section" style="background:var(--color-crema);padding:4rem 2rem;">
    <div style="max-width:900px;margin:0 auto;text-align:center;background:white;padding:3rem;box-shadow:var(--paper-shadow);border-left:8px solid var(--color-verde-principal);">
        <h2 style="font-family:var(--font-display);font-size:2.5rem;color:var(--color-marron);margin-bottom:1.5rem;">
            ¿Listo para crear algo único?
        </h2>
        <p style="font-size:1.1rem;color:var(--color-marron-claro);margin-bottom:2.5rem;line-height:1.8;">
            Diseña tu libreta personalizada o explora nuestro catálogo de productos artesanales hechos a mano con dedicación y cariño
        </p>
        <div style="display:flex;gap:1.5rem;justify-content:center;flex-wrap:wrap;">
            <a href="/personalizada" class="btn-primary">
                Personalizar Libreta <span class="material-symbols-outlined">edit</span>
            </a>
            <a href="/productos" class="btn-secondary">Ver Todos los Productos</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

<script>
const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.style.opacity = '1';
                entry.target.style.transform = entry.target.style.transform.replace('translateY(30px)', 'translateY(0)');
            }, index * 150);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.feature-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
});
</script>
</body>
</html>
