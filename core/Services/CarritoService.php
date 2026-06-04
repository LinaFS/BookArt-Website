<?php
/**
 * CarritoService
 *
 * Toda la lógica de negocio del carrito vive aquí.
 * No sabe nada de HTTP, $_POST, $_GET ni JSON.
 * El controller llama a este service y responde al cliente.
 */
class CarritoService {

    public function __construct(private PDO $pdo) {}

    // ── OBTENER ITEMS ────────────────────────────────────────────────────────

    public function obtenerItems(int $usuarioId): array {
        $stmt = $this->pdo->prepare(
            "SELECT p.idPedido, p.idTipoPedido,
                    CASE WHEN p.idTipoPedido = 1 THEN c.nombre  ELSE 'Libreta Personalizada' END AS nombre,
                    CASE WHEN p.idTipoPedido = 1 THEN c.precio  ELSE COALESCE(per.precio, 0) END AS precio,
                    CASE WHEN p.idTipoPedido = 1 THEN c.img     ELSE per.portada              END AS img
             FROM pedidos p
             LEFT JOIN catalogo      c   ON p.idCatalogo     = c.id_producto
             LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada
             WHERE p.IdCuenta = :id AND p.estatus = 'carrito'
             ORDER BY p.fecha DESC, p.hora DESC"
        );
        $stmt->execute([':id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    // ── CONTAR ITEMS ─────────────────────────────────────────────────────────

    public function contarItems(int $usuarioId): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM pedidos WHERE IdCuenta = :id AND estatus = 'carrito'"
        );
        $stmt->execute([':id' => $usuarioId]);
        return (int) $stmt->fetchColumn();
    }

    // ── AGREGAR ──────────────────────────────────────────────────────────────

    public function agregar(int $usuarioId, string $tipo, int $id): array {
        if (!in_array($tipo, ['catalogo', 'personalizada'])) {
            return ['success' => false, 'message' => 'Tipo de producto no válido.'];
        }

        $idTipo  = $tipo === 'catalogo' ? 1 : 2;
        $columna = $tipo === 'catalogo' ? 'idCatalogo' : 'idPersonalizada';

        // Regla de negocio: no duplicados en el carrito
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM pedidos
             WHERE IdCuenta = :uid AND $columna = :pid AND idTipoPedido = :tipo AND estatus = 'carrito'"
        );
        $stmt->execute([':uid' => $usuarioId, ':pid' => $id, ':tipo' => $idTipo]);
        if ($stmt->fetchColumn()) {
            return ['success' => false, 'message' => 'Este producto ya está en tu carrito.'];
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO pedidos (fecha, hora, estatus, $columna, idTipoPedido, IdCuenta)
             VALUES (CURDATE(), CURTIME(), 'carrito', :pid, :tipo, :uid)"
        );
        $ok = $stmt->execute([':pid' => $id, ':tipo' => $idTipo, ':uid' => $usuarioId]);

        return $ok
            ? ['success' => true,  'message' => 'Producto agregado al carrito.']
            : ['success' => false, 'message' => 'Error al agregar al carrito.'];
    }

    // ── ELIMINAR ─────────────────────────────────────────────────────────────

    public function eliminar(int $usuarioId, int $idPedido): array {
        // Verificar ownership y obtener datos para limpiar personalizada si aplica
        $stmt = $this->pdo->prepare(
            "SELECT idPersonalizada, idTipoPedido FROM pedidos
             WHERE idPedido = :id AND IdCuenta = :uid AND estatus = 'carrito'"
        );
        $stmt->execute([':id' => $idPedido, ':uid' => $usuarioId]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['success' => false, 'message' => 'Pedido no encontrado.'];
        }

        $this->pdo->prepare(
            "DELETE FROM pedidos WHERE idPedido = :id AND IdCuenta = :uid AND estatus = 'carrito'"
        )->execute([':id' => $idPedido, ':uid' => $usuarioId]);

        // Si era personalizada: limpiar registro e imagen del filesystem
        if ($row['idTipoPedido'] == 2 && $row['idPersonalizada']) {
            $this->limpiarPersonalizada((int) $row['idPersonalizada']);
        }

        return ['success' => true, 'message' => 'Producto eliminado del carrito.'];
    }

    // ── CHECKOUT ─────────────────────────────────────────────────────────────

    public function checkout(int $usuarioId): array {
        $total = $this->contarItems($usuarioId);

        if ($total === 0) {
            return ['success' => false, 'message' => 'Tu carrito está vacío.'];
        }

        // Regla de negocio: fecha y hora se actualizan al momento del pedido real
        $stmt = $this->pdo->prepare(
            "UPDATE pedidos
             SET estatus = 'pendiente', fecha = CURDATE(), hora = CURTIME()
             WHERE IdCuenta = :id AND estatus = 'carrito'"
        );
        $ok = $stmt->execute([':id' => $usuarioId]);

        return $ok
            ? ['success' => true,  'message' => '¡Pedido realizado! Nos pondremos en contacto pronto.']
            : ['success' => false, 'message' => 'Error al procesar el pedido.'];
    }

    // ── HELPER INTERNO ───────────────────────────────────────────────────────

    private function limpiarPersonalizada(int $idPersonalizada): void {
        $stmt = $this->pdo->prepare(
            "SELECT portada FROM personalizada WHERE id_personalizada = :id"
        );
        $stmt->execute([':id' => $idPersonalizada]);
        $per = $stmt->fetch();

        $this->pdo->prepare(
            "DELETE FROM personalizada WHERE id_personalizada = :id"
        )->execute([':id' => $idPersonalizada]);

        if ($per && !empty($per['portada'])) {
            $fs = dirname(__DIR__, 2) . $per['portada'];
            if (file_exists($fs)) unlink($fs);
        }
    }
}