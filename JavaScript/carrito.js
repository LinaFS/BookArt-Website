// REEMPLAZAR TODO EL BLOQUE <script> al final de Carrito.php

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

// Función auxiliar para mostrar diálogo personalizado
function mostrarDialog(mensaje, tipo = 'info') {
    const dialog = document.getElementById('warning');
    const mensajeEl = document.getElementById('mensaje');
    
    if (dialog && mensajeEl) {
        mensajeEl.textContent = mensaje;
        mensajeEl.className = tipo;
        dialog.showModal();
    }
}

// Event listener para cerrar diálogos
document.addEventListener('DOMContentLoaded', function() {
    const btnAcept = document.getElementById('btnAcept');
    if (btnAcept) {
        btnAcept.addEventListener('click', function() {
            const dialog = document.getElementById('warning');
            if (dialog) {
                dialog.close();
            }
        });
    }
});

// Diálogo de confirmación personalizado para eliminar
// --- Dialog ELIMINAR ---
let itemToRemove = null;

function removeItem(id, tipo) {
    itemToRemove = { id, tipo };
    document.getElementById('deleteDialog').showModal();
}

document.getElementById('btnCancelDelete').addEventListener('click', () => {
    document.getElementById('deleteDialog').close();
});

document.getElementById('btnConfirmDelete').addEventListener('click', confirmarEliminacion);

function confirmarEliminacion() {
    if (!itemToRemove) return;

    const { id, tipo } = itemToRemove;

    fetch('../PHP/carrito_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&id=${id}&tipo=${tipo}`
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('deleteDialog').close();

        if (data.success) {
            mostrarDialog('Producto eliminado del carrito', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            mostrarDialog('Error al eliminar: ' + (data.message || 'Desconocido'), 'error');
        }
    })
    .catch(err => {
        console.error(err);
        mostrarDialog('Error de conexión al eliminar.', 'error');
    });
}
// Diálogo de confirmación para realizar pedido

function realizarPedido() {
    document.getElementById('confirmDialog').showModal();
}

function confirmarPedido() {
    const dialog = document.getElementById('confirmDialog');
    
    fetch('../PHP/carrito_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=checkout'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        dialog.close();
        
        if (data.success) {
            window.location.href = 'MisPedidos.php?mensaje=' + 
                encodeURIComponent('¡Pedido realizado con éxito! Nos pondremos en contacto contigo pronto.') + 
                '&modal=true';
        } else {
            mostrarDialog('⚠️ ' + (data.message || 'Error al procesar el pedido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        dialog.close();
        mostrarDialog('❌ Error de conexión. Por favor, intenta nuevamente.', 'error');
    });
}