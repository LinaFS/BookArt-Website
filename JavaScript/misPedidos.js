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

        // Modal de mensajes
        document.addEventListener('DOMContentLoaded', function() {
            const mensajeElement = document.getElementById('mensaje');
            const warningDialog = document.getElementById('warning');
            const btnAcept = document.getElementById('btnAcept');
            
            if (mensajeElement && mensajeElement.textContent.trim() !== '') {
                warningDialog.showModal();
            }

            if (btnAcept) {
                btnAcept.addEventListener('click', function() {
                    warningDialog.close();
                    // Limpiar URL al cerrar el modal
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            }
        });

let pedidoIdParaCancelar = null;

function cancelarPedido(idPedido) {
    pedidoIdParaCancelar = idPedido;
    document.getElementById('dialogConfirmCancel').showModal();
}

function cerrarDialogConfirmCancel() {
    document.getElementById('dialogConfirmCancel').close();
    pedidoIdParaCancelar = null;
}

function confirmarCancelacion() {
    if (!pedidoIdParaCancelar) return;
    
    const formData = new FormData();
    formData.append('action', 'cancel');
    formData.append('id', pedidoIdParaCancelar);
    
    fetch('../PHP/pedidos_usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        cerrarDialogConfirmCancel();
        
        if (data.success) {
            mostrarDialog('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarDialog('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarDialog('❌ Error al cancelar el pedido', 'error');
        cerrarDialogConfirmCancel();
    });
}

function editarPedido(idPedido, tipo) {
    // Redirigir a la página de edición según el tipo
    if (tipo == 1) { // Catálogo
        mostrarDialog('ℹ️ Los pedidos del catálogo no se pueden editar. Puedes cancelarlo y crear uno nuevo.', 'info');
    } else { // Personalizada
        window.location.href = `EditarPedido.php?id=${idPedido}`;
    }
}

function mostrarDialog(mensaje, tipo) {
    const dialog = document.getElementById('warning');
    const mensajeEl = document.getElementById('mensaje');
    
    if (dialog && mensajeEl) {
        mensajeEl.textContent = mensaje;
        mensajeEl.className = tipo;
        dialog.showModal();
    }
}