<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: Inicio_sesion.php?origen=MisPedidos");
    exit();
}

require("PHP/conexionBDD.php");

// Obtener ID del pedido
$idPedido = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : '';

if (empty($idPedido)) {
    header("Location: MisPedidos.php");
    exit();
}

// Obtener ID del usuario
$usuario = $_SESSION["usuario"];
$queryId = "SELECT id_cuenta FROM cuenta WHERE correo = '$usuario' OR usuario = '$usuario'";
$executeQuery = mysqli_query($conexion, $queryId);
$usuarioIdArray = mysqli_fetch_assoc($executeQuery);
$usuarioId = $usuarioIdArray['id_cuenta'];

// Obtener datos del pedido
$queryPedido = "SELECT p.*, per.* 
                FROM pedidos p
                INNER JOIN personalizada per ON p.idPersonalizada = per.id_personalizada
                WHERE p.idPedido = '$idPedido' 
                AND p.IdCuenta = '$usuarioId' 
                AND p.idTipoPedido = 2";

$resultPedido = mysqli_query($conexion, $queryPedido);

if (!$resultPedido || mysqli_num_rows($resultPedido) == 0) {
    header("Location: MisPedidos.php?mensaje=" . urlencode("Pedido no encontrado o no puedes editarlo") . "&modal=true");
    exit();
}

$pedido = mysqli_fetch_assoc($resultPedido);

// Verificar que el pedido esté en estado editable
if (!in_array(strtolower($pedido['estatus']), ['pendiente', 'visto'])) {
    header("Location: MisPedidos.php?mensaje=" . urlencode("Este pedido ya no puede ser editado") . "&modal=true");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido - BookArt</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />    <script src="../JavaScript/funcionModal.js"></script></head>

<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <a href="index.php">
                    <img class="logo" src="../img/Logo/BookArt Positivo_B-N.png" alt="BookArt Logo">
                </a>
                <h1 class="site-title">BookArt</h1>
            </div>
            
            <nav class="main-nav" id="mainNav">
                <ul class="nav-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="Productos.php">Productos</a></li>
                    <li><a href="Contacto.php">Contacto</a></li>
                </ul>
                <a href="MisPedidos.php" class="btn-session">Volver a Pedidos</a>
            </nav>
        </div>
    </header>

    <section class="personalized-hero">
        <h1>✏️ Editar tu Pedido</h1>
        <p>Realiza los cambios necesarios a tu libreta personalizada</p>
    </section>

    <section class="personalized-form-section">
        <div class="personalized-form-container">
            <h2 class="form-section-title">Modificar Diseño</h2>
            <p class="form-section-subtitle">
                Pedido #<?php echo str_pad($idPedido, 5, '0', STR_PAD_LEFT); ?>
            </p>

            <dialog id="warning">
                <p id="mensaje"></p>
                <div class="btnModal">
                    <button id="btnAcept">Aceptar</button>
                </div>
            </dialog>

            <form action="../PHP/actualizar_pedido.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="idPedido" value="<?php echo $idPedido; ?>">
                <input type="hidden" name="idPersonalizada" value="<?php echo $pedido['id_personalizada']; ?>">
                
                <div class="options-section">
                    <h3 class="options-section-title">Tipo de Encuadernación</h3>
                    
                    <input type="hidden" id="opcFinal" name="opcFinal" value="<?php echo htmlspecialchars($pedido['tipo_encuadernacion']); ?>">

                    <div class="binding-options-grid">
                        <label class="binding-option">
                            <input type="radio" name="opcion" value="encuadernacionClasica" 
                                   <?php echo ($pedido['tipo_encuadernacion'] == 'encuadernacionClasica') ? 'checked' : ''; ?>>
                            <div class="binding-content">
                                <div class="binding-image">
                                    <img src="../img/libretaEncuadernada.jpg" alt="Encuadernación Clásica">
                                </div>
                                <h4 class="binding-title">Encuadernación Clásica</h4>
                            </div>
                        </label>

                        <label class="binding-option">
                            <input type="radio" name="opcion" value="diseñoPiel"
                                   <?php echo ($pedido['tipo_encuadernacion'] == 'diseñoPiel') ? 'checked' : ''; ?>>
                            <div class="binding-content">
                                <div class="binding-image">
                                    <img src="../img/libretaPiel.jpg" alt="Diseño en Piel">
                                </div>
                                <h4 class="binding-title">Diseño en Piel</h4>
                            </div>
                        </label>

                        <label class="binding-option">
                            <input type="radio" name="opcion" value="diseñoEngargolado"
                                   <?php echo ($pedido['tipo_encuadernacion'] == 'diseñoEngargolado') ? 'checked' : ''; ?>>
                            <div class="binding-content">
                                <div class="binding-image">
                                    <img src="../img/libretaEngargolada.jpg" alt="Diseño Engargolado">
                                </div>
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
                                <option value="Chica 11cm x 12cm" <?php echo ($pedido['tam'] == 'Chica 11cm x 12cm') ? 'selected' : ''; ?>>Chica (11cm x 12cm)</option>
                                <option value="Mediana 14cm x 23cm" <?php echo ($pedido['tam'] == 'Mediana 14cm x 23cm') ? 'selected' : ''; ?>>Mediana (14cm x 23cm)</option>
                                <option value="Grande 18cm x 23cm" <?php echo ($pedido['tam'] == 'Grande 18cm x 23cm') ? 'selected' : ''; ?>>Grande (18cm x 23cm)</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="tipoPap">Tipo de papel</label>
                            <select name="tipoPapel" id="tipoPap" required>
                                <option value="Ahuesado" <?php echo ($pedido['tipo_papel'] == 'Ahuesado') ? 'selected' : ''; ?>>Ahuesado</option>
                                <option value="Capuchino" <?php echo ($pedido['tipo_papel'] == 'Capuchino') ? 'selected' : ''; ?>>Capuchino (Reciclado)</option>
                                <option value="Blanco" <?php echo ($pedido['tipo_papel'] == 'Blanco') ? 'selected' : ''; ?>>Blanco</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-field" style="margin-top: 2rem;">
                        <label for="color">Color de detalles</label>
                        <div class="color-picker-wrapper">
                            <input type="color" name="color" id="color" class="color-picker-preview" value="<?php echo htmlspecialchars($pedido['color']); ?>">
                            <input type="text" class="color-picker-input" value="<?php echo htmlspecialchars($pedido['color']); ?>" readonly>
                        </div>
                    </div>

                    <?php if (!empty($pedido['portada']) && file_exists($pedido['portada'])): ?>
                    <div class="form-field" style="margin-top: 2rem;">
                        <label>Portada actual</label>
                        <div class="image-preview" style="display: block;">
                            <img src="<?php echo htmlspecialchars($pedido['portada']); ?>" alt="Portada actual">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-field" style="margin-top: 2rem;">
                        <label for="portada">Cambiar portada (opcional)</label>
                        <div class="file-upload-wrapper">
                            <label for="portada" class="file-upload-label">
                                <span class="file-upload-icon">📁</span>
                                <span class="file-upload-text">Subir nueva portada</span>
                            </label>
                            <input type="file" name="portada" id="portada" class="file-upload-input" accept="image/*">
                        </div>
                    </div>

                    <div class="form-field" style="margin-top: 2rem;">
                        <label for="desc">Descripción</label>
                        <textarea name="descripcion" id="desc" required><?php echo htmlspecialchars($pedido['descripcion']); ?></textarea>
                    </div>

                    <div class="submit-section">
                        <button type="submit" class="btn-submit-custom">
                            <span class="material-symbols-outlined">save</span>
                            Guardar Cambios
                        </button>
                        <a href="MisPedidos.php" class="btn-submit-custom" style="background: var(--marron-texto); text-decoration: none; display: inline-flex;">
                            <span class="material-symbols-outlined">cancel</span>
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <img src="../img/Logo/BookArt Negativo_B-N.png" alt="BookArt Logo" class="footer-logo">
            <p class="footer-text">
                Encuadernaciones artesanales hechas con pasión<br>
                Cada libreta es única, como tus ideas
            </p>
            <div class="footer-bottom">
                <p>&copy; 2024 BookArt Encuadernaciones. Hecho con ❤️ y dedicación</p>
            </div>
        </div>
    </footer>

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
    </script>
</body>
</html>