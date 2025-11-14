// ===== VARIABLES GLOBALES (DECLARADAS AL INICIO) =====
let productoEliminarId = null;
let modoEdicion = false;
let pedidosData = [];
let filtroActualEstatus = 'pendiente';

// FUNCIÓN DE ALERTA PERSONALIZADA PARA ADMIN
// Agregar al INICIO de logicaAdmin.js (después de las variables globales)

function mostrarAlerta(mensaje, tipo = 'info') {
    const modal = document.getElementById('warning');
    const mensajeEl = document.getElementById('mensaje');
    
    if (modal && mensajeEl) {
        mensajeEl.textContent = mensaje;
        mensajeEl.className = tipo; // 'success', 'error', 'warning', 'info'
        
        modal.showModal();
        
        // Auto-cerrar después de 3 segundos para success
        if (tipo === 'success') {
            setTimeout(() => {
                modal.close();
            }, 3000);
        }
    }
}

// ===== INICIALIZACIÓN AL CARGAR LA PÁGINA =====
document.addEventListener('DOMContentLoaded', function() {
    // Cargar principal por defecto
    principal();
    
    // Cargar estadísticas iniciales
    cargarEstadisticas();
    
    // Event listeners para formularios
    setupFormularios();
});

// ===== SETUP DE FORMULARIOS =====
function setupFormularios() {
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
    
    // Formulario de cambio de estatus
    const formEstatus = document.getElementById('formCambiarEstatus');
    if (formEstatus) {
        formEstatus.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'cambiar_estatus');
            
            fetch('../PHP/admin_pedidos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    mostrarAlerta(data.message, 'success');
                    cerrarModalEstatus();
                    cargarPedidos();
                } else {
                    mostrarAlerta(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error al actualizar estatus', 'error');
            });
        });
    }
    
    // Botón de aceptar en modal de alerta
    const btnAcept = document.getElementById('btnAcept');
    if(btnAcept) {
        btnAcept.addEventListener('click', function() {
            document.getElementById('warning').close();
        });
    }
}

// Actualizar fecha y hora
function updateDateTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateElement = document.getElementById('currentDate');
    if (dateElement) {
        dateElement.textContent = now.toLocaleDateString('es-ES', options);
    }
}
updateDateTime();
setInterval(updateDateTime, 60000);

// ===== NAVEGACIÓN ENTRE SECCIONES =====
function principal() {
    hideAllSections();
    document.getElementById('principal').classList.add('active');
    updateActiveNav('nav-principal');
    document.getElementById('page-title').textContent = 'Panel de Administración';
    
    // Cargar estadísticas
    cargarEstadisticas();
}

function cargarEstadisticas() {
    // 1. Cargar total de productos
    fetch('../PHP/ajax_catalogo.php?action=listar')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const totalElement = document.getElementById('totalProductos');
                if (totalElement) {
                    totalElement.textContent = data.productos.length;
                }
            }
        })
        .catch(error => console.error('Error cargando productos:', error));
    
    // 2. Cargar estadísticas de pedidos
    fetch('../PHP/admin_pedidos.php?action=estadisticas')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const statCards = document.querySelectorAll('.stat-card');
                statCards.forEach(card => {
                    const text = card.textContent;
                    if (text.includes('Pedidos Activos')) {
                        card.querySelector('.stat-info h3').textContent = data.pedidos_activos || 0;
                    } else if (text.includes('Clientes')) {
                        card.querySelector('.stat-info h3').textContent = data.total_clientes || 0;
                    } else if (text.includes('Ventas del Mes')) {
                        card.querySelector('.stat-info h3').textContent = 
                            '$' + (data.ventas_mes ? parseFloat(data.ventas_mes).toFixed(2) : '0.00');
                    }
                });
            }
        })
        .catch(error => console.error('Error cargando estadísticas:', error));
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
    
    // Establecer filtro en "todos" al cargar la sección
    const filtroSelect = document.getElementById('filtroEstatus');
    if (filtroSelect) {
        filtroSelect.value = 'todos';
    }
    filtroActualEstatus = 'todos';
    
    cargarPedidos();
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
    const activeNav = document.getElementById(activeId);
    if (activeNav) {
        activeNav.classList.add('active');
    }
}

// ===== GESTIÓN DE CATÁLOGO =====
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

// ===== MODALES DE CATÁLOGO =====
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
    
    // Usar modal personalizado en lugar de alert
    const modalConfirmar = document.getElementById('modalConfirmar');
    const mensajeConfirmar = document.getElementById('mensajeConfirmar');
    
    if (modalConfirmar && mensajeConfirmar) {
        mensajeConfirmar.textContent = `¿Estás seguro de eliminar el producto "${nombre}"? Esta acción no se puede deshacer.`;
        modalConfirmar.showModal();
    }
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
// ===== SISTEMA DE ALERTAS =====
function mostrarAlerta(mensaje, tipo) {
    const modal = document.getElementById('warning');
    const mensajeEl = document.getElementById('mensaje');
    
    if (modal && mensajeEl) {
        mensajeEl.textContent = mensaje;
        mensajeEl.className = tipo;
        
        modal.showModal();
        
        setTimeout(() => {
            modal.close();
        }, 3000);
    }
}

// ===== GESTIÓN DE PEDIDOS =====
function cargarPedidos() {
    const tbody = document.getElementById('tablaPedidosBody');
    if (!tbody) {
        console.error('❌ No se encontró tablaPedidosBody');
        return;
    }
    
    tbody.innerHTML = '<tr><td colspan="7" class="loading"><div class="spinner"></div>Cargando pedidos...</td></tr>';
    
    fetch('../PHP/admin_pedidos.php?action=listar')
        .then(response => response.json())
        .then(data => {
            console.log('📦 Respuesta del servidor:', data);
            
            if(data.success) {
                pedidosData = data.pedidos;
                console.log('✅ Pedidos cargados:', pedidosData.length);
                mostrarPedidos(pedidosData);
                actualizarContadorPedidos(pedidosData);
            } else {
                console.error('❌ Error en respuesta:', data.message);
                tbody.innerHTML = `<tr><td colspan="7" class="error">Error al cargar pedidos: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('❌ Error de conexión:', error);
            tbody.innerHTML = '<tr><td colspan="7" class="error">Error de conexión: ' + error.message + '</td></tr>';
        });
}

function mostrarPedidos(pedidos) {
    const tbody = document.getElementById('tablaPedidosBody');
    if (!tbody) return;
    
    console.log('🔄 Mostrando pedidos, total:', pedidos.length);
    
    // Filtrar por estatus si hay filtro activo
    const filtroSelect = document.getElementById('filtroEstatus');
    const filtro = filtroSelect ? filtroSelect.value : 'todos';
    console.log('🔍 Filtro actual:', filtro);
    
    if (filtro !== 'todos') {
        pedidos = pedidos.filter(p => p.estatus.toLowerCase() === filtro);
        console.log('📊 Pedidos después del filtro:', pedidos.length);
    }
    
    if(pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty">No hay pedidos con este estatus</td></tr>';
        return;
    }
    
    tbody.innerHTML = pedidos.map(pedido => {
        const estatusClass = 'estatus-' + pedido.estatus.toLowerCase();
        const isCatalogo = (pedido.id_tipoPedido == 1);
        const tipoClass = isCatalogo ? 'pedido-tipo-catalogo' : 'pedido-tipo-personalizada';
        const tipoTexto = isCatalogo ? '📚 Catálogo' : '🎨 Personalizada';
        
        let estatusIcon = '';
        switch(pedido.estatus.toLowerCase()) {
            case 'pendiente': estatusIcon = '⏳'; break;
            case 'visto': estatusIcon = '👀'; break;
            case 'aprobado': estatusIcon = '✅'; break;
            case 'declinado': estatusIcon = '❌'; break;
            case 'proceso': estatusIcon = '🔨'; break;
            case 'terminado': estatusIcon = '🎉'; break;
            case 'entregado': estatusIcon = '📦'; break;
        }
        
        return `
        <tr data-id="${pedido.id}">
            <td><strong>#${String(pedido.id).padStart(5, '0')}</strong></td>
            <td>
                <div class="cliente-info">
                    ${pedido.cliente_nombre || 'N/A'} ${pedido.paterno || ''} ${pedido.materno || ''}
                    <span class="cliente-correo">${pedido.cliente_correo || 'N/A'}</span>
                </div>
            </td>
            <td>
                <span class="pedido-tipo-badge ${tipoClass}">
                    ${tipoTexto}
                </span>
            </td>
            <td class="nombre-col">${pedido.producto_nombre || 'N/A'}</td>
            <td>
                ${pedido.fecha}<br>
                <small style="color: var(--text-secondary);">${pedido.hora}</small>
            </td>
            <td>
                <span class="pedido-estatus ${estatusClass}">
                    ${estatusIcon} ${pedido.estatus}
                </span>
            </td>
            <td class="acciones-col">
                <button class="btn-icon btn-editar" onclick="verDetallePedido('${pedido.id}')" title="Ver detalle">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
                <button class="btn-icon" onclick="cambiarEstatus('${pedido.id}', '${pedido.estatus}')" 
                        title="Cambiar estatus"
                        style="background: #e3f2fd; color: #1976d2;">
                    <span class="material-symbols-outlined">sync_alt</span>
                </button>
            </td>
        </tr>
        `;
    }).join('');
    
    console.log('✅ Tabla actualizada');
}

function filtrarPedidos() {
    const filtroSelect = document.getElementById('filtroEstatus');
    if (filtroSelect) {
        filtroActualEstatus = filtroSelect.value;
        console.log('🔄 Filtrando por:', filtroActualEstatus);
        mostrarPedidos(pedidosData);
    }
}

function buscarEnPedidos() {
    const searchInput = document.getElementById('buscarPedido');
    if (!searchInput) return;
    
    const filtro = searchInput.value.toLowerCase();
    const filas = document.querySelectorAll('#tablaPedidosBody tr');
    
    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
}

function verDetallePedido(id) {
    console.log('👁️ Viendo detalle del pedido:', id);
    
    fetch(`../PHP/admin_pedidos.php?action=detalle&id=${id}`)
        .then(response => response.json())
        .then(data => {
            console.log('📋 Detalle recibido:', data);
            
            if(data.success) {
                mostrarDetallePedido(data.pedido);
            } else {
                mostrarAlerta('Error al cargar detalle del pedido: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            mostrarAlerta('Error de conexión', 'error');
        });
}

function mostrarDetallePedido(pedido) {
    const modal = document.getElementById('modalDetallePedido');
    const content = document.getElementById('detallePedidoContent');
    
    if (!modal || !content) return;
    
    document.getElementById('modalPedidoTitulo').textContent = 
        `Pedido #${String(pedido.id).padStart(5, '0')} - ${pedido.tipo === 'catalogo' ? 'Catálogo' : 'Personalizada'}`;
    
    let detalleHTML = `
        <div class="detalle-section">
            <h3>📋 Información del Pedido</h3>
            <div class="detalle-grid">
                <div class="detalle-item">
                    <span class="detalle-label">ID del Pedido</span>
                    <span class="detalle-value">#${String(pedido.id).padStart(5, '0')}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Fecha</span>
                    <span class="detalle-value">${pedido.fecha} ${pedido.hora}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Tipo</span>
                    <span class="detalle-value">${pedido.tipo === 'catalogo' ? 'Catálogo' : 'Personalizada'}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Estatus</span>
                    <span class="detalle-value pedido-estatus estatus-${pedido.estatus.toLowerCase()}">
                        ${pedido.estatus}
                    </span>
                </div>
            </div>
        </div>

        <div class="detalle-section">
            <h3>👤 Información del Cliente</h3>
            <div class="detalle-grid">
                <div class="detalle-item">
                    <span class="detalle-label">Nombre</span>
                    <span class="detalle-value">${pedido.cliente.nombre} ${pedido.cliente.paterno} ${pedido.cliente.materno}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Correo</span>
                    <span class="detalle-value">${pedido.cliente.correo}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Teléfono</span>
                    <span class="detalle-value">${pedido.cliente.tel}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Usuario</span>
                    <span class="detalle-value">${pedido.cliente.usuario}</span>
                </div>
            </div>
        </div>

        <div class="detalle-section">
            <h3>📦 Detalles del Producto</h3>
    `;
    
    if (pedido.tipo === 'catalogo') {
        detalleHTML += `
            <div class="producto-preview">
                <img src="${pedido.producto.img}" alt="${pedido.producto.nombre}">
            </div>
            <div class="detalle-grid">
                <div class="detalle-item">
                    <span class="detalle-label">Nombre</span>
                    <span class="detalle-value">${pedido.producto.nombre}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Precio</span>
                    <span class="detalle-value" style="color: var(--primary); font-weight: bold; font-size: 1.5rem;">
                        $${parseFloat(pedido.producto.precio).toFixed(2)}
                    </span>
                </div>
            </div>
            <div class="detalle-item" style="margin-top: 1rem;">
                <span class="detalle-label">Descripción</span>
                <span class="detalle-value">${pedido.producto.descripcion}</span>
            </div>
        `;
    } else {
        detalleHTML += `
            ${pedido.producto.portada ? `
            <div class="producto-preview">
                <img src="${pedido.producto.portada}" alt="Diseño personalizado">
            </div>
            ` : '<p style="text-align: center; color: var(--text-secondary);">Sin imagen de portada</p>'}
            
            <div class="detalle-grid">
                <div class="detalle-item">
                    <span class="detalle-label">Tamaño</span>
                    <span class="detalle-value">${pedido.producto.tam || 'No especificado'}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Tipo de Encuadernación</span>
                    <span class="detalle-value">${pedido.producto.tipo_encuadernacion || 'No especificado'}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Tipo de Papel</span>
                    <span class="detalle-value">${pedido.producto.tipo_papel || 'No especificado'}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Color</span>
                    <span class="detalle-value">
                        <span style="display: inline-block; width: 30px; height: 30px; background: ${pedido.producto.color}; border: 2px solid var(--marron-texto); vertical-align: middle;"></span>
                        ${pedido.producto.color}
                    </span>
                </div>
            </div>
            ${pedido.producto.descripcion ? `
            <div class="detalle-item" style="margin-top: 1rem;">
                <span class="detalle-label">Descripción del cliente</span>
                <span class="detalle-value">${pedido.producto.descripcion}</span>
            </div>
            ` : ''}
        `;
    }
    
    detalleHTML += '</div>';
    
    content.innerHTML = detalleHTML;
    modal.showModal();
}

function cerrarModalDetalle() {
    const modal = document.getElementById('modalDetallePedido');
    if (modal) {
        modal.close();
    }
}

function cambiarEstatus(id, estatusActual) {
    document.getElementById('pedidoIdEstatus').value = id;
    document.getElementById('nuevoEstatus').value = estatusActual.toLowerCase();
    document.getElementById('mensajeAdmin').value = '';
    
    document.getElementById('modalCambiarEstatus').showModal();
}

function cerrarModalEstatus() {
    const modal = document.getElementById('modalCambiarEstatus');
    if (modal) {
        modal.close();
    }
}

function actualizarContadorPedidos(pedidos) {
    const pendientes = pedidos.filter(p => p.estatus.toLowerCase() === 'pendiente').length;
    
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        const text = card.textContent;
        if (text.includes('Pedidos Activos')) {
            const h3 = card.querySelector('.stat-info h3');
            if (h3) h3.textContent = pendientes;
        }
    });
}

// ===== CERRAR SESIÓN =====
function closeSesion() {
    window.location.href = '../PHP/cerrar_sesion.php';
}

console.log('✅ logicaAdmin.js cargado correctamente');