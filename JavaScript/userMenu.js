/* ===================================
   MENÚ DESPLEGABLE DE USUARIO
   =================================== */

document.addEventListener('DOMContentLoaded', function() {
    const userMenuWrapper = document.querySelector('.user-menu-wrapper');
    const userMenuTrigger = document.querySelector('.user-menu-trigger');
    
    if (userMenuTrigger) {
        // Toggle menú desplegable
        userMenuTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenuWrapper.classList.toggle('open');
        });
        
        // Cerrar al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!userMenuWrapper.contains(e.target)) {
                userMenuWrapper.classList.remove('open');
            }
        });
        
        // Cargar contador del carrito
        cargarContadorCarrito();
    }
});

function cargarContadorCarrito() {
    fetch('../PHP/carrito_actions.php?action=count')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                const badge = document.querySelector('.user-menu-badge');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = 'block';
                }
            }
        })
        .catch(error => console.error('Error al cargar contador:', error));
}

/* ===================================
   SISTEMA DE DIALOGS PERSONALIZADOS
   =================================== */

function mostrarDialog(mensaje, tipo = 'info') {
    const dialog = document.getElementById('warning');
    const mensajeEl = document.getElementById('mensaje');
    
    if (dialog && mensajeEl) {
        mensajeEl.textContent = mensaje;
        mensajeEl.className = tipo; // 'success', 'error', 'info'
        dialog.showModal();
    }
}

function cerrarDialog() {
    const dialog = document.getElementById('warning');
    if (dialog) {
        dialog.close();
    }
}

// Event listener para botón de aceptar
document.addEventListener('DOMContentLoaded', function() {
    const btnAcept = document.getElementById('btnAcept');
    if (btnAcept) {
        btnAcept.addEventListener('click', cerrarDialog);
    }
});

/* ===================================
   AGREGAR AL CARRITO (CATALOGO)
   =================================== */

function agregarAlCarritoCatalogo(idProducto) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('tipo', 'catalogo');
    formData.append('id', idProducto);
    
    fetch('../PHP/carrito_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarDialog('✅ ' + data.message, 'success');
            cargarContadorCarrito();
        } else {
            mostrarDialog('⚠️ ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarDialog('❌ Error al agregar al carrito', 'error');
    });
}

/* ===================================
   VALIDACIÓN DE FORMULARIO PERSONALIZADA
   =================================== */

function validarFormularioPersonalizada(form) {
    const opcion = document.getElementById('opcFinal').value;
    const tamanio = document.getElementById('tam').value;
    const tipoPapel = document.getElementById('tipoPap').value;
    const descripcion = document.getElementById('desc').value;
    
    if (!opcion) {
        mostrarDialog('⚠️ Por favor selecciona un tipo de encuadernación', 'error');
        return false;
    }
    
    if (!tamanio) {
        mostrarDialog('⚠️ Por favor selecciona un tamaño', 'error');
        return false;
    }
    
    if (!tipoPapel) {
        mostrarDialog('⚠️ Por favor selecciona un tipo de papel', 'error');
        return false;
    }
    
    if (!descripcion.trim()) {
        mostrarDialog('⚠️ Por favor describe tu portada ideal', 'error');
        return false;
    }
    
    return true;
}

// Agregar validación al formulario de personalizada
document.addEventListener('DOMContentLoaded', function() {
    const formPersonalizada = document.querySelector('form[action*="personalizada.php"]');
    
    if (formPersonalizada) {
        formPersonalizada.addEventListener('submit', function(e) {
            if (!validarFormularioPersonalizada(this)) {
                e.preventDefault();
            }
        });
    }
});

console.log('✅ BookArt - Sistema de menú de usuario cargado correctamente');