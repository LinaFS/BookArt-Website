/* ===================================
   BOOKART - LÓGICA GENERAL DEL SITIO
   Modales, navegación y menú responsive
   =================================== */

// ===================================
// MODAL DE MENSAJES (warning)
// ===================================

function mostrarModal() {
    var urlParams = new URLSearchParams(window.location.search);
    var mensaje = urlParams.get('mensaje');
    var activarModal = urlParams.get('modal');

    if (activarModal === 'true' && mensaje && mensaje.trim() !== '') {
        var modal = document.getElementById('warning');
        if (modal) {
            modal.showModal(); // Abre el modal
        }
    }

    // Agregar evento al botón de Aceptar para cerrar el modal
    const btnAcept = document.getElementById('btnAcept');
    if (btnAcept) {
        btnAcept.addEventListener('click', function() {
            var modal = document.getElementById('warning');
            if (modal) {
                modal.close(); // Cierra el modal
            }
            
            // Elimina el mensaje de la URL
            history.replaceState(null, null, window.location.pathname);
        });
    }
}

// Llamar a la función cuando se cargue la página
document.addEventListener('DOMContentLoaded', mostrarModal);

// ===================================
// LÓGICA PARA LIBRETAS PERSONALIZADAS
// ===================================

function seleccionarInput(id, valor) {
    const input = document.getElementById(id);
    if (input) {
        input.checked = true;
    }
    
    const cajaTxt = document.getElementById("opcFinal");
    if (cajaTxt) {
        cajaTxt.value = valor;
    }
}

function personalizada() {
    window.location = '/personalizada';
}

function catalogo() {
    window.location = '/catalogo';
}

function extension() {
    window.location = '/catalogo';
}

// ===================================
// MENÚ RESPONSIVE (Hamburguesa)
// ===================================

function toggleMenu() {
    const nav = document.getElementById('mainNav');
    const toggle = document.getElementById('menuToggle');
    
    if (nav) {
        nav.classList.toggle('active');
        
        if (toggle) {
            const icon = toggle.querySelector('.material-symbols-outlined');
            if (icon) {
                icon.textContent = nav.classList.contains('active') ? 'close' : 'menu';
            }
        }
        
        // Prevenir scroll cuando el menú está abierto
        if (nav.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    } else {
        // Fallback para menú antiguo (si existe)
        const menu = document.getElementById("menuV");
        if (menu) {
            menu.style.display = menu.style.display === "flex" ? "none" : "flex";
        }
    }
}

// Cerrar menú al hacer clic en enlaces (para el nuevo diseño)
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-links a, .btn-session');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                const nav = document.getElementById('mainNav');
                if (nav && nav.classList.contains('active')) {
                    nav.classList.remove('active');
                    document.body.style.overflow = '';
                    
                    const toggle = document.getElementById('menuToggle');
                    if (toggle) {
                        const icon = toggle.querySelector('.material-symbols-outlined');
                        if (icon) {
                            icon.textContent = 'menu';
                        }
                    }
                }
            }
        });
    });

    // Cerrar menú al hacer clic fuera (para el nuevo diseño)
    document.addEventListener('click', function(e) {
        const nav = document.getElementById('mainNav');
        const toggle = document.getElementById('menuToggle');
        
        if (nav && toggle && nav.classList.contains('active')) {
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('active');
                document.body.style.overflow = '';
                
                const icon = toggle.querySelector('.material-symbols-outlined');
                if (icon) {
                    icon.textContent = 'menu';
                }
            }
        }
    });
});

// ===================================
// COMPATIBILIDAD CON MENÚ ANTIGUO
// ===================================

// Evento para abrir/cerrar el menú al hacer clic en el icono del menú (versión antigua)
const indexMenu = document.getElementById("indexMenu");
if (indexMenu) {
    indexMenu.addEventListener("click", function(event) {
        event.stopPropagation();
        toggleMenu();
    });
}

// Evento para cerrar el menú al hacer clic fuera del menú (versión antigua)
document.addEventListener("click", function(event) {
    const menu = document.getElementById("menuV");
    const menuButton = document.getElementById("indexMenu");

    if (menu && menuButton) {
        // Verifica si el clic fue fuera del menú y del icono del menú
        if (!menu.contains(event.target) && !menuButton.contains(event.target)) {
            menu.style.display = "none";
        }
    }
});

// Evita que el menú se cierre si se hace clic dentro del menú (versión antigua)
const menuV = document.getElementById("menuV");
if (menuV) {
    menuV.addEventListener("click", function(event) {
        event.stopPropagation();
    });
}

// ===================================
// ANIMACIONES DE SCROLL
// ===================================

// Intersection Observer para animaciones al hacer scroll
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 150);
            }
        });
    }, observerOptions);

    // Observar tarjetas de características
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Observar items del catálogo
    const catalogItems = document.querySelectorAll('.catalog-item');
    catalogItems.forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(item);
    });
});

// ===================================
// UTILIDADES ADICIONALES
// ===================================

// Smooth scroll para enlaces internos
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});

// Prevenir cierre de modal al hacer clic dentro (para modales generales)
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('dialog');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                // Solo cerrar si se hace clic en el backdrop
                this.close();
            }
        });
    });
});

console.log('✅ BookArt - Lógica general cargada correctamente');