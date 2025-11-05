// ===== NAVEGACIÓN ENTRE SECCIONES =====
function principal() {
    hideAllSections();
    document.getElementById('principal').classList.add('active');
    updateActiveNav('nav-principal');
    document.getElementById('page-title').textContent = 'Panel de Administración';
}

function catalogo() {
    hideAllSections();
    document.getElementById('catalogo').classList.add('active');
    updateActiveNav('nav-catalogo');
    document.getElementById('page-title').textContent = 'Gestión de Catálogo';
    cargarCatalogo();
}

function pedidos() {
    hideAllSections();
    document.getElementById('pedidos').classList.add('active');
    updateActiveNav('nav-pedidos');
    document.getElementById('page-title').textContent = 'Gestión de Pedidos';
}

function hideAllSections() {
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
}

function updateActiveNav(activeId) {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    document.getElementById(activeId)?.classList.add('active');
}

// ===== GESTIÓN DE CATÁLOGO =====
let productoEliminarId = null;
let modoEdicion = false;

function cargarCatalogo() {
    const tbody = document.getElementById('tablaBody');
    tbody.innerHTML = '<tr><td colspan="6" class="loading"><div class="spinner"></div>Cargando productos...</td></tr>';
    
    fetch('../PHP/ajax_catalogo.php?action=listar')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                mostrarProductos(data.productos);
                actualizarContadorProductos(data.productos.length);
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="error">Error al cargar productos: ' + data.message + '</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="6" class="error">Error de conexión</td></tr>';
        });
}

function mostrarProductos(productos) {
    const tbody = document.getElementById('tablaBody');
    
    if(productos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty">No hay productos en el catálogo</td></tr>';
        return;
    }
    
    tbody.innerHTML = productos.map(producto => `
        <tr data-id="${producto.id_producto}">
            <td>${producto.id_producto}</td>
            <td>
                <img src="${producto.img}" alt="${producto.nombre}" class="producto-img" 
                     onerror="this.src='../Catalogo/imgNoEncontrada.png'">
            </td>
            <td class="nombre-col">${producto.nombre}</td>
            <td class="desc-col">${producto.descripcion}</td>
            <td class="precio-col">$${parseFloat(producto.precio).toFixed(2)}</td>
            <td class="acciones-col">
                <button class="btn-icon btn-editar" onclick="editarProducto(${producto.id_producto})" title="Editar">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button class="btn-icon btn-eliminar" onclick="eliminarProducto(${producto.id_producto}, '${producto.nombre.replace(/'/g, "\\'")}' )" title="Eliminar">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </td>
        </tr>
    `).join('');
}

function actualizarContadorProductos(total) {
    const contador = document.getElementById('totalProductos');
    if(contador) {
        contador.textContent = total;
    }
}

function filtrarTabla() {
    const filtro = document.getElementById('buscarProducto').value.toLowerCase();
    const filas = document.querySelectorAll('#tablaBody tr');
    
    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
}

// ===== MODALES =====
function abrirModalAgregar() {
    modoEdicion = false;
    document.getElementById('modalTitulo').textContent = 'Agregar Producto';
    document.getElementById('formProducto').reset();
    document.getElementById('productoId').value = '';
    document.getElementById('preview').innerHTML = '';
    document.getElementById('modalProducto').showModal();
}

function editarProducto(id) {
    modoEdicion = true;
    document.getElementById('modalTitulo').textContent = 'Editar Producto';
    
    fetch(`../PHP/ajax_catalogo.php?action=obtener&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const p = data.producto;
                document.getElementById('productoId').value = p.id_producto;
                document.getElementById('nombreProducto').value = p.nombre;
                document.getElementById('descripcionProducto').value = p.descripcion;
                document.getElementById('precioProducto').value = p.precio;
                
                document.getElementById('preview').innerHTML = `
                    <img src="${p.img}" alt="Imagen actual" onerror="this.src='../Catalogo/imgNoEncontrada.png'">
                    <p class="preview-text">Imagen actual</p>
                `;
                
                document.getElementById('modalProducto').showModal();
            } else {
                mostrarAlerta('Error al cargar producto', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error de conexión', 'error');
        });
}

function eliminarProducto(id, nombre) {
    productoEliminarId = id;
    document.getElementById('mensajeConfirmar').textContent = 
        `¿Estás seguro de eliminar el producto "${nombre}"? Esta acción no se puede deshacer.`;
    document.getElementById('modalConfirmar').showModal();
}

function confirmarAccion() {
    if(!productoEliminarId) return;
    
    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', productoEliminarId);
    
    fetch('../PHP/ajax_catalogo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            mostrarAlerta(data.message, 'success');
            cargarCatalogo();
        } else {
            mostrarAlerta(data.message, 'error');
        }
        cerrarModalConfirmar();
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al eliminar producto', 'error');
        cerrarModalConfirmar();
    });
}

function cerrarModal() {
    document.getElementById('modalProducto').close();
}

function cerrarModalConfirmar() {
    document.getElementById('modalConfirmar').close();
    productoEliminarId = null;
}

function previsualizarImagen(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    
    if(file) {
        if(file.size > 3 * 1024 * 1024) {
            mostrarAlerta('La imagen no debe superar 3MB', 'warning');
            event.target.value = '';
            preview.innerHTML = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Vista previa">
                <p class="preview-text">Vista previa</p>
            `;
        };
        reader.readAsDataURL(file);
    }
}

// ===== ENVÍO DE FORMULARIO =====
document.addEventListener('DOMContentLoaded', function() {
    // Formulario de producto
    const form = document.getElementById('formProducto');
    if(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const accion = modoEdicion ? 'editar' : 'agregar';
            formData.append('action', accion);
            
            if(!modoEdicion && !formData.get('imagen').size) {
                mostrarAlerta('Debes seleccionar una imagen', 'warning');
                return;
            }
            
            fetch('../PHP/ajax_catalogo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    mostrarAlerta(data.message, 'success');
                    cerrarModal();
                    cargarCatalogo();
                } else {
                    mostrarAlerta(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error al guardar producto', 'error');
            });
        });
    }
    
    // Cargar principal por defecto
    principal();
});

// ===== SISTEMA DE ALERTAS =====
function mostrarAlerta(mensaje, tipo) {
    const modal = document.getElementById('warning');
    const mensajeEl = document.getElementById('mensaje');
    
    mensajeEl.textContent = mensaje;
    mensajeEl.className = tipo;
    
    modal.showModal();
    
    setTimeout(() => {
        modal.close();
    }, 3000);
}

// Cerrar alerta manualmente
document.addEventListener('DOMContentLoaded', function() {
    const btnAcept = document.getElementById('btnAcept');
    if(btnAcept) {
        btnAcept.addEventListener('click', function() {
            document.getElementById('warning').close();
        });
    }
});

// ===== CERRAR SESIÓN =====
function closeSesion() {
    window.location.href = '../PHP/cerrar_sesion.php';
}