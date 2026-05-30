// REEMPLAZAR el bloque <script> en Personalizada.php

// Toggle Menu
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

// Función auxiliar para mostrar diálogo
function mostrarDialog(mensaje, tipo = 'info') {
    window.BookArtNotification.show(mensaje, tipo);
}

// Event listeners de diálogo
document.addEventListener('DOMContentLoaded', function() {
    const mensajeElement = document.getElementById('mensaje');
    const warningDialog = document.getElementById('warning');
    const btnAcept = document.getElementById('btnAcept');
    
    // Mostrar mensaje si existe
    if (mensajeElement && mensajeElement.textContent.trim() !== '') {
        warningDialog.showModal();
    }

    if (btnAcept) {
        btnAcept.addEventListener('click', function() {
            warningDialog.close();
        });
    }
});

// Seleccionar opción de encuadernación
document.querySelectorAll('.binding-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('opcFinal').value = this.value;
    });
});

// Validación del formulario antes de enviar
const form = document.querySelector('form[action*="personalizada.php"]');
if (form) {
    form.addEventListener('submit', function(e) {
        const opcion = document.getElementById('opcFinal').value;
        const tamanio = document.getElementById('tam').value;
        const tipoPapel = document.getElementById('tipoPap').value;
        const descripcion = document.getElementById('desc').value;
        
        if (!opcion) {
            e.preventDefault();
            mostrarDialog('⚠️ Por favor selecciona un tipo de encuadernación', 'error');
            return false;
        }
        
        if (!tamanio) {
            e.preventDefault();
            mostrarDialog('⚠️ Por favor selecciona un tamaño', 'error');
            return false;
        }
        
        if (!tipoPapel) {
            e.preventDefault();
            mostrarDialog('⚠️ Por favor selecciona un tipo de papel', 'error');
            return false;
        }
        
        if (!descripcion.trim()) {
            e.preventDefault();
            mostrarDialog('⚠️ Por favor describe tu portada ideal', 'error');
            return false;
        }
    });
}

// Preview de imagen
document.getElementById('portada').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validar tamaño (5MB máximo)
        if (file.size > 5 * 1024 * 1024) {
            mostrarDialog('⚠️ La imagen no debe superar 5MB', 'error');
            this.value = '';
            return;
        }
        
        // Validar tipo
        const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!tiposPermitidos.includes(file.type)) {
            mostrarDialog('⚠️ Formato de imagen no válido. Usa JPG, PNG o WEBP', 'error');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Remover preview
function removePreview() {
    document.getElementById('portada').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('previewImg').src = '';
}

// Color picker sincronizado
document.getElementById('color').addEventListener('input', function(e) {
    document.querySelector('.color-picker-input').value = e.target.value;
});

// Cerrar menú al hacer clic en enlaces
document.querySelectorAll('.nav-links a, .btn-session').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            const nav = document.getElementById('mainNav');
            nav.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// Cerrar menú al hacer clic fuera
document.addEventListener('click', function(e) {
    const nav = document.getElementById('mainNav');
    const toggle = document.getElementById('menuToggle');
    
    if (nav.classList.contains('active') && 
        !nav.contains(e.target) && 
        !toggle.contains(e.target)) {
        toggleMenu();
    }
});