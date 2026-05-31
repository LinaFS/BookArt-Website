<?php
require_once __DIR__ . '/../../core/conexionBDD.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: /inicio-sesion'); exit;
}

$idPedido = $_GET['id'] ?? '';
if (empty($idPedido)) {
    header('Location: /mis-pedidos'); exit;
}

// Obtener ID del usuario con prepared statement
$usuario = $_SESSION['usuario'];
$stmt = mysqli_prepare($conexion, "SELECT id_cuenta FROM cuenta WHERE correo = ? OR usuario = ?");
mysqli_stmt_bind_param($stmt, 'ss', $usuario, $usuario);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$row) { header('Location: /mis-pedidos'); exit; }
$usuarioId = $row['id_cuenta'];

// Obtener datos del pedido
$stmt = mysqli_prepare($conexion,
    "SELECT p.*, per.*
     FROM pedidos p
     INNER JOIN personalizada per ON p.idPersonalizada = per.id_personalizada
     WHERE p.idPedido = ? AND p.IdCuenta = ? AND p.idTipoPedido = 2"
);
mysqli_stmt_bind_param($stmt, 'ii', $idPedido, $usuarioId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: /mis-pedidos'); exit;
}

$pedido = mysqli_fetch_assoc($result);
mysqli_close($conexion);

if (!in_array(strtolower($pedido['estatus']), ['pendiente', 'visto'])) {
    header('Location: /mis-pedidos'); exit;
}

$title   = 'Editar Pedido - BookArt';
$extraJs = ['funcionModal.js'];
?>
<!DOCTYPE html>
<html lang="es">
<head><?php require __DIR__ . '/../partials/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="personalized-hero">
    <h1>✏️ Editar tu Pedido</h1>
    <p>Realiza los cambios necesarios a tu libreta personalizada</p>
</section>

<section class="personalized-form-section">
    <div class="personalized-form-container">
        <h2 class="form-section-title">Modificar Diseño</h2>
        <p class="form-section-subtitle">Pedido #<?= str_pad($idPedido, 5, '0', STR_PAD_LEFT) ?></p>

        <dialog id="warning">
            <p id="mensaje"></p>
            <div class="btnModal"><button id="btnAcept">Aceptar</button></div>
        </dialog>

        <form action="/api/pedidos?action=editar" method="post" enctype="multipart/form-data">
            <input type="hidden" name="idPedido" value="<?= (int)$idPedido ?>">
            <input type="hidden" name="idPersonalizada" value="<?= (int)$pedido['id_personalizada'] ?>">

            <div class="options-section">
                <h3 class="options-section-title">Tipo de Encuadernación</h3>
                <input type="hidden" id="opcFinal" name="opcFinal" value="<?= htmlspecialchars($pedido['tipo_encuadernacion']) ?>">
                <div class="binding-options-grid">
                    <label class="binding-option">
                        <input type="radio" name="opcion" value="encuadernacionClasica"
                               <?= $pedido['tipo_encuadernacion'] == 'encuadernacionClasica' ? 'checked' : '' ?>>
                        <div class="binding-content">
                            <div class="binding-image"><img src="/wwwroot/img/libretaEncuadernada.jpg" alt="Clásica"></div>
                            <h4 class="binding-title">Encuadernación Clásica</h4>
                        </div>
                    </label>
                    <label class="binding-option">
                        <input type="radio" name="opcion" value="diseñoPiel"
                               <?= $pedido['tipo_encuadernacion'] == 'diseñoPiel' ? 'checked' : '' ?>>
                        <div class="binding-content">
                            <div class="binding-image"><img src="/wwwroot/img/libretaPiel.jpg" alt="Piel"></div>
                            <h4 class="binding-title">Diseño en Piel</h4>
                        </div>
                    </label>
                    <label class="binding-option">
                        <input type="radio" name="opcion" value="diseñoEngargolado"
                               <?= $pedido['tipo_encuadernacion'] == 'diseñoEngargolado' ? 'checked' : '' ?>>
                        <div class="binding-content">
                            <div class="binding-image"><img src="/wwwroot/img/libretaEngargolada.jpg" alt="Engargolado"></div>
                            <h4 class="binding-title">Diseño Engargolado</h4>
                        </div>
                    </label>
                </div>
            </div>

            <div class="customization-form">
                <div class="form-row">
                    <div class="form-field">
                        <label for="tam">Tamaño de tu libreta</label>
                        <select name="tamaño" id="tam" required>
                            <option value="Chica 11cm x 12cm"   <?= $pedido['tam'] == 'Chica 11cm x 12cm'   ? 'selected' : '' ?>>Chica (11cm x 12cm)</option>
                            <option value="Mediana 14cm x 23cm" <?= $pedido['tam'] == 'Mediana 14cm x 23cm' ? 'selected' : '' ?>>Mediana (14cm x 23cm)</option>
                            <option value="Grande 18cm x 23cm"  <?= $pedido['tam'] == 'Grande 18cm x 23cm'  ? 'selected' : '' ?>>Grande (18cm x 23cm)</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="tipoPap">Tipo de papel</label>
                        <select name="tipoPapel" id="tipoPap" required>
                            <option value="Ahuesado"  <?= $pedido['tipo_papel'] == 'Ahuesado'  ? 'selected' : '' ?>>Ahuesado</option>
                            <option value="Capuchino" <?= $pedido['tipo_papel'] == 'Capuchino' ? 'selected' : '' ?>>Capuchino (Reciclado)</option>
                            <option value="Blanco"    <?= $pedido['tipo_papel'] == 'Blanco'    ? 'selected' : '' ?>>Blanco</option>
                        </select>
                    </div>
                </div>

                <div class="form-field" style="margin-top:2rem;">
                    <label for="color">Color de detalles</label>
                    <div class="color-picker-wrapper">
                        <input type="color" name="color" id="color" class="color-picker-preview" value="<?= htmlspecialchars($pedido['color']) ?>">
                        <input type="text" class="color-picker-input" value="<?= htmlspecialchars($pedido['color']) ?>" readonly>
                    </div>
                </div>

                <?php if (!empty($pedido['portada'])): ?>
                <div class="form-field" style="margin-top:2rem;">
                    <label>Portada actual</label>
                    <div class="image-preview" style="display:block;">
                        <img src="<?= htmlspecialchars($pedido['portada']) ?>" alt="Portada actual">
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-field" style="margin-top:2rem;">
                    <label for="portada">Cambiar portada (opcional)</label>
                    <div class="file-upload-wrapper">
                        <label for="portada" class="file-upload-label">
                            <span class="file-upload-icon">📁</span>
                            <span class="file-upload-text">Subir nueva portada</span>
                        </label>
                        <input type="file" name="portada" id="portada" class="file-upload-input" accept="image/*">
                    </div>
                </div>

                <div class="form-field" style="margin-top:2rem;">
                    <label for="desc">Descripción</label>
                    <textarea name="descripcion" id="desc" required><?= htmlspecialchars($pedido['descripcion']) ?></textarea>
                </div>

                <div class="submit-section">
                    <button type="submit" class="btn-submit-custom">
                        <span class="material-symbols-outlined">save</span>
                        Guardar Cambios
                    </button>
                    <a href="/mis-pedidos" class="btn-submit-custom" style="background:var(--marron-texto);text-decoration:none;display:inline-flex;">
                        <span class="material-symbols-outlined">cancel</span>
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
document.querySelectorAll('.binding-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('opcFinal').value = this.value;
    });
});

document.getElementById('color').addEventListener('input', function(e) {
    document.querySelector('.color-picker-input').value = e.target.value;
});

document.getElementById('portada').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.querySelector('.image-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.className = 'image-preview';
                document.getElementById('portada').parentElement.after(preview);
            }
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Nueva portada">';
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Enviar formulario vía fetch
document.querySelector('form[action="/api/pedidos?action=editar"]')
    ?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('[type="submit"]');
        if (btn) btn.disabled = true;
        try {
            const res  = await fetch('/api/pedidos?action=editar', { method: 'POST', body: new FormData(this) });
            const data = await res.json();
            if (data.success) {
                window.location.href = data.redirect || '/mis-pedidos';
            } else {
                const dialog = document.getElementById('warning');
                const msg    = document.getElementById('mensaje');
                if (dialog && msg) { msg.textContent = '❌ ' + data.message; dialog.showModal(); }
            }
        } catch {
            const dialog = document.getElementById('warning');
            const msg    = document.getElementById('mensaje');
            if (dialog && msg) { msg.textContent = '❌ Error de conexión.'; dialog.showModal(); }
        } finally {
            if (btn) btn.disabled = false;
        }
    });
</script>
</body>
</html>
