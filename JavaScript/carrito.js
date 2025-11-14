function toggleMenu() {
    const nav = document.getElementById('mainNav');
    const toggle = document.getElementById('menuToggle');
    
    if (!nav || !toggle) return;
    
    nav.classList.toggle('active');
    
    const icon = toggle.querySelector('.material-symbols-outlined');
    if (icon) {
        icon.textContent = nav.classList.contains('active') ? 'close' : 'menu';
    }
    
    document.body.style.overflow = nav.classList.contains('active') ? 'hidden' : '';
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

// --- Variables globales ---
let itemToRemove = null;

// --- FUNCIÓN PARA ABRIR MODAL DE ELIMINACIÓN ---
function removeItem(id, tipo) {
    itemToRemove = { id, tipo };
    const dialog = document.getElementById('deleteDialog');
    if (dialog) {
        dialog.showModal();
    }
}

// --- FUNCIÓN PARA CANCELAR ELIMINACIÓN ---
function cancelarEliminacion() {
    const dialog = document.getElementById('deleteDialog');
    if (dialog) {
        dialog.close();
    }
    itemToRemove = null;
}

// --- FUNCIÓN PARA CONFIRMAR ELIMINACIÓN ---
function confirmarEliminacion() {
    if (!itemToRemove) return;

    const { id, tipo } = itemToRemove;
    
    // Cerrar el modal INMEDIATAMENTE
    const dialog = document.getElementById('deleteDialog');
    if (dialog) {
        dialog.close();
    }

    // Hacer la petición al servidor
    fetch('../PHP/carrito_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&id=${id}&tipo=${tipo}`
    })
    .then(res => res.json())
    .then(data => {
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
    })
    .finally(() => {
        itemToRemove = null;
    });
}

// --- FUNCIÓN PARA REALIZAR PEDIDO ---
function realizarPedido() {
    const dialog = document.getElementById('confirmDialog');
    if (dialog) {
        dialog.showModal();
    }
}

// --- FUNCIÓN PARA CONFIRMAR PEDIDO ---
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
        if (dialog) {
            dialog.close();
        }
        
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
        if (dialog) {
            dialog.close();
        }
        mostrarDialog('❌ Error de conexión. Por favor, intenta nuevamente.', 'error');
    });
}