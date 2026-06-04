<?php
require_once dirname(__DIR__, 2) . '/core/conexionBDD.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = trim($_GET['action'] ?? '');

match ($action) {
    'login'          => login($conexion, $method),
    'register'       => register($conexion, $method),
    'logout'         => logout($method),
    'reset-password' => resetPassword($conexion, $method),
    default          => response(404, false, "Acción '$action' no encontrada.")
};

// ── LOGIN ────────────────────────────────────────────────────────────────────
function login($conexion, $method): void {
    if ($method !== 'POST') { response(405, false, 'Método no permitido.'); return; }

    $usuario = trim($_POST['sesionUsuario'] ?? '');
    $contra  = trim($_POST['sesionContra']  ?? '');

    if (empty($usuario) || empty($contra)) {
        response(422, false, 'Usuario y contraseña son obligatorios.');
        return;
    }

    $hash = hash('sha512', $contra);
    // ORDER BY: correo exacto primero, luego usuario. LIMIT 1 evita ambigüedad con contraseñas iguales.
    $sql  = "SELECT id_cuenta, usuario, correo, permiso_id
             FROM cuenta
             WHERE (correo = ? OR usuario = ?) AND contrasenia = ?
             ORDER BY (correo = ?) DESC
             LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, 'ssss', $usuario, $usuario, $hash, $usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        response(401, false, 'Usuario o contraseña incorrectos.');
        return;
    }

    $_SESSION['usuario']   = $row['usuario'] ?: $row['correo'];
    $_SESSION['correo']    = $row['correo'];
    $_SESSION['permiso']   = $row['permiso_id'];
    $_SESSION['id_cuenta'] = $row['id_cuenta'];

    $redirect = $row['permiso_id'] == 1 ? '/administrador' : '/catalogo';
    response(200, true, 'Sesión iniciada.', ['redirect' => $redirect, 'permiso' => (int)$row['permiso_id']]);
}

// ── REGISTER ─────────────────────────────────────────────────────────────────
function register($conexion, $method): void {
    if ($method !== 'POST') { response(405, false, 'Método no permitido.'); return; }

    $campos = ['nombre','paterno','materno','tel','Dia','Mes','anio','usuario','correo','contrasena'];
    $data   = [];
    foreach ($campos as $campo) {
        $val = trim($_POST[$campo] ?? '');
        if (empty($val)) { response(422, false, "El campo '$campo' es obligatorio."); return; }
        $data[$campo] = $val;
    }

    if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
        response(422, false, 'El correo no es válido.');
        return;
    }

    // Verificar correo duplicado
    $stmt = mysqli_prepare($conexion, "SELECT 1 FROM cuenta WHERE correo = ?");
    mysqli_stmt_bind_param($stmt, 's', $data['correo']);
    mysqli_stmt_execute($stmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        mysqli_stmt_close($stmt);
        response(409, false, 'Este correo ya está registrado.');
        return;
    }
    mysqli_stmt_close($stmt);

    // Verificar usuario duplicado
    $stmt = mysqli_prepare($conexion, "SELECT 1 FROM cuenta WHERE usuario = ?");
    mysqli_stmt_bind_param($stmt, 's', $data['usuario']);
    mysqli_stmt_execute($stmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        mysqli_stmt_close($stmt);
        response(409, false, 'Este nombre de usuario ya está registrado.');
        return;
    }
    mysqli_stmt_close($stmt);

    // Insertar en usuario
    $stmt = mysqli_prepare($conexion,
        "INSERT INTO usuario (nombre, paterno, materno, tel, dia, mes, anio)
         VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssssss',
        $data['nombre'], $data['paterno'], $data['materno'],
        $data['tel'], $data['Dia'], $data['Mes'], $data['anio']);
    mysqli_stmt_execute($stmt);
    $idUsuario = mysqli_insert_id($conexion);
    mysqli_stmt_close($stmt);

    // Insertar en cuenta
    $hash    = hash('sha512', $data['contrasena']);
    $permiso = 2;
    $stmt    = mysqli_prepare($conexion,
        "INSERT INTO cuenta (usuario, correo, contrasenia, permiso_id, usuario_id)
         VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssii',
        $data['usuario'], $data['correo'], $hash, $permiso, $idUsuario);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        // Revertir usuario si falla cuenta
        $stmt = mysqli_prepare($conexion, "DELETE FROM usuario WHERE id_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        response(500, false, 'Error al crear la cuenta.');
        return;
    }

    response(201, true, '¡Cuenta creada exitosamente!');
}

// ── LOGOUT ───────────────────────────────────────────────────────────────────
function logout($method): void {
    if ($method !== 'POST') { response(405, false, 'Método no permitido.'); return; }
    $_SESSION = [];
    session_destroy();
    response(200, true, 'Sesión cerrada.', ['redirect' => '/inicio-sesion']);
}

// ── RESET PASSWORD ───────────────────────────────────────────────────────────
function resetPassword($conexion, $method): void {
    if ($method !== 'POST') { response(405, false, 'Método no permitido.'); return; }

    $correo = trim($_POST['correoRecupera'] ?? '');
    $nueva  = trim($_POST['recuperaContra'] ?? '');

    if (empty($correo) || empty($nueva)) {
        response(422, false, 'Correo y nueva contraseña son obligatorios.');
        return;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        response(422, false, 'El correo no es válido.');
        return;
    }

    $stmt = mysqli_prepare($conexion, "SELECT 1 FROM cuenta WHERE correo = ?");
    mysqli_stmt_bind_param($stmt, 's', $correo);
    mysqli_stmt_execute($stmt);
    $existe = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
    mysqli_stmt_close($stmt);

    // Respuesta genérica para no revelar si el correo existe
    if (!$existe) {
        response(200, true, 'Si el correo existe, la contraseña fue actualizada.');
        return;
    }

    $hash = hash('sha512', $nueva);
    $stmt = mysqli_prepare($conexion, "UPDATE cuenta SET contrasenia = ? WHERE correo = ?");
    mysqli_stmt_bind_param($stmt, 'ss', $hash, $correo);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        response(200, true, 'Si el correo existe, la contraseña fue actualizada.');
    } else {
        response(500, false, 'Error al actualizar la contraseña.');
    }
}
