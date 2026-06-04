<?php
// Requiere sesión activa (cualquier usuario)
function requireAuth(): void {
    if (!isset($_SESSION['usuario'])) {
        response(401, false, 'No autenticado.');
    }
}

// Requiere usuario normal — bloquea admins (permiso_id = 1)
function requireUser(): void {
    requireAuth();
    if ((int)($_SESSION['permiso'] ?? 0) === 1) {
        response(403, false, 'Los administradores no pueden realizar pedidos.');
    }
}

// Requiere administrador
function requireAdmin(): void {
    requireAuth();
    if ((int)($_SESSION['permiso'] ?? 0) !== 1) {
        response(403, false, 'No tienes permisos de administrador.');
    }
}
