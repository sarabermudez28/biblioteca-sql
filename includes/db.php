<?php
/**
 * Capa de acceso a datos — Biblioteca Central.
 * Toda la información se consulta y modifica en la base de datos mediante
 * sentencias SQL (PDO + prepared statements). No se usa la sesión para
 * almacenar datos de la aplicación, solo para el estado de autenticación.
 */

require_once __DIR__ . '/../config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

/* ==================== Lecturas ==================== */

function obtener_metricas(): array {
    $pdo = db();

    $totalMateriales = (int) $pdo->query("SELECT COUNT(*) FROM materiales")->fetchColumn();
    $disponibles     = (int) $pdo->query("SELECT COUNT(*) FROM materiales WHERE estado = 'disponible'")->fetchColumn();
    $rotos           = (int) $pdo->query("SELECT COUNT(*) FROM materiales WHERE estado = 'roto'")->fetchColumn();
    $robados         = (int) $pdo->query("SELECT COUNT(*) FROM materiales WHERE estado = 'robado'")->fetchColumn();

    $prestadosHoy = (int) $pdo->query(
        "SELECT COUNT(*) FROM prestamos WHERE DATE(fecha_prestamo) = CURDATE()"
    )->fetchColumn();

    $devueltosHoy = (int) $pdo->query(
        "SELECT COUNT(*) FROM prestamos WHERE estado = 'devuelto' AND DATE(fecha_devolucion) = CURDATE()"
    )->fetchColumn();

    $prestamosActivos = (int) $pdo->query(
        "SELECT COUNT(*) FROM prestamos WHERE estado = 'activo'"
    )->fetchColumn();

    $atrasados = (int) $pdo->query(
        "SELECT COUNT(*) FROM prestamos WHERE estado = 'activo' AND fecha_devolucion < NOW()"
    )->fetchColumn();

    return [
        "prestadosHoy" => $prestadosHoy,
        "devueltosHoy" => $devueltosHoy,
        "prestamosActivos" => $prestamosActivos,
        "atrasados" => $atrasados,
        "robados" => $robados,
        "rotos" => $rotos,
        "disponibles" => $disponibles,
        "totalMateriales" => $totalMateriales,
    ];
}

function obtener_estadistica_anual(?int $anio = null): array {
    $anio = $anio ?? (int) date('Y');
    $stmt = db()->prepare(
        "SELECT mes_num, mes_nombre, prestamos, robos, rotos
         FROM estadistica_anual
         WHERE anio = :anio
         ORDER BY mes_num ASC"
    );
    $stmt->execute(['anio' => $anio]);
    return $stmt->fetchAll();
}

function obtener_eventos(int $limite = 8): array {
    $stmt = db()->prepare("SELECT * FROM eventos ORDER BY hora DESC LIMIT :lim");
    $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtener_socios(): array {
    return db()->query("SELECT id, nombre, email FROM socios ORDER BY nombre ASC")->fetchAll();
}

function obtener_materiales_disponibles(): array {
    return db()->query(
        "SELECT id, titulo, autor, tipo, codigo FROM materiales WHERE estado = 'disponible' ORDER BY titulo ASC"
    )->fetchAll();
}

function obtener_materiales(string $busqueda = ''): array {
    $busqueda = trim($busqueda);
    if ($busqueda === '') {
        $stmt = db()->prepare("SELECT * FROM materiales ORDER BY titulo ASC LIMIT 200");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    $like = '%' . $busqueda . '%';
    $stmt = db()->prepare(
        "SELECT * FROM materiales
         WHERE titulo LIKE :q OR autor LIKE :q OR codigo LIKE :q
         ORDER BY titulo ASC
         LIMIT 200"
    );
    $stmt->execute(['q' => $like]);
    return $stmt->fetchAll();
}

/** Préstamos con datos de material y socio, y el estado "atrasado" calculado en la propia consulta SQL. */
function obtener_prestamos(string $filtro = 'todos'): array {
    $sql = "
        SELECT
            p.id,
            p.fecha_prestamo,
            p.fecha_devolucion,
            p.estado AS estado_bd,
            m.titulo AS material_titulo,
            s.nombre AS socio_nombre,
            CASE
                WHEN p.estado = 'activo' AND p.fecha_devolucion < NOW() THEN 'atrasado'
                ELSE p.estado
            END AS estado
        FROM prestamos p
        INNER JOIN materiales m ON m.id = p.material_id
        INNER JOIN socios s ON s.id = p.socio_id
    ";

    $where = "";
    if ($filtro === 'activo') {
        $where = "WHERE p.estado = 'activo' AND p.fecha_devolucion >= NOW()";
    } elseif ($filtro === 'atrasado') {
        $where = "WHERE p.estado = 'activo' AND p.fecha_devolucion < NOW()";
    } elseif ($filtro === 'devuelto') {
        $where = "WHERE p.estado = 'devuelto'";
    }

    $sql .= " $where ORDER BY p.fecha_prestamo DESC";
    return db()->query($sql)->fetchAll();
}

/** Conteos por estado para las pestañas de la vista de préstamos. */
function contar_prestamos_por_estado(): array {
    $pdo = db();
    return [
        "todos" => (int) $pdo->query("SELECT COUNT(*) FROM prestamos")->fetchColumn(),
        "activo" => (int) $pdo->query(
            "SELECT COUNT(*) FROM prestamos WHERE estado = 'activo' AND fecha_devolucion >= NOW()"
        )->fetchColumn(),
        "atrasado" => (int) $pdo->query(
            "SELECT COUNT(*) FROM prestamos WHERE estado = 'activo' AND fecha_devolucion < NOW()"
        )->fetchColumn(),
        "devuelto" => (int) $pdo->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'devuelto'")->fetchColumn(),
    ];
}

/* ==================== Escrituras ==================== */

function lb_nuevo_evento(string $tipo, string $descripcion): void {
    $stmt = db()->prepare("INSERT INTO eventos (tipo, descripcion, hora) VALUES (:tipo, :descripcion, NOW())");
    $stmt->execute(['tipo' => $tipo, 'descripcion' => $descripcion]);
}

function registrar_prestamo(int $materialId, int $socioId): array {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT titulo, estado FROM materiales WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $materialId]);
        $material = $stmt->fetch();

        if (!$material || $material['estado'] !== 'disponible') {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'El material seleccionado ya no está disponible.'];
        }

        $stmt = $pdo->prepare("SELECT nombre FROM socios WHERE id = :id");
        $stmt->execute(['id' => $socioId]);
        $socio = $stmt->fetch();
        if (!$socio) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'Selecciona un socio válido.'];
        }

        $pdo->prepare("UPDATE materiales SET estado = 'prestado' WHERE id = :id")
            ->execute(['id' => $materialId]);

        $pdo->prepare(
            "INSERT INTO prestamos (material_id, socio_id, fecha_prestamo, fecha_devolucion, estado)
             VALUES (:material_id, :socio_id, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), 'activo')"
        )->execute(['material_id' => $materialId, 'socio_id' => $socioId]);

        lb_nuevo_evento('prestamo', "{$socio['nombre']} solicitó \"{$material['titulo']}\"");

        $pdo->commit();
        return ['ok' => true, 'message' => "Préstamo registrado: \"{$material['titulo']}\" a {$socio['nombre']}"];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'No se pudo registrar el préstamo.'];
    }
}

function devolver_prestamo(int $prestamoId): array {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "SELECT p.id, p.material_id, p.estado, m.titulo AS material_titulo, s.nombre AS socio_nombre
             FROM prestamos p
             INNER JOIN materiales m ON m.id = p.material_id
             INNER JOIN socios s ON s.id = p.socio_id
             WHERE p.id = :id FOR UPDATE"
        );
        $stmt->execute(['id' => $prestamoId]);
        $prestamo = $stmt->fetch();

        if (!$prestamo || $prestamo['estado'] === 'devuelto') {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'El préstamo no existe o ya fue devuelto.'];
        }

        $pdo->prepare("UPDATE prestamos SET estado = 'devuelto' WHERE id = :id")
            ->execute(['id' => $prestamoId]);

        $pdo->prepare("UPDATE materiales SET estado = 'disponible' WHERE id = :id")
            ->execute(['id' => $prestamo['material_id']]);

        lb_nuevo_evento('devolucion', "{$prestamo['socio_nombre']} devolvió \"{$prestamo['material_titulo']}\"");

        $pdo->commit();
        return ['ok' => true, 'message' => "\"{$prestamo['material_titulo']}\" marcado como devuelto"];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'No se pudo registrar la devolución.'];
    }
}

function marcar_material(int $materialId, string $estado): array {
    if (!in_array($estado, ['roto', 'robado'], true)) {
        return ['ok' => false, 'error' => 'Estado inválido.'];
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT titulo, estado FROM materiales WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $materialId]);
        $material = $stmt->fetch();

        if (!$material || in_array($material['estado'], ['roto', 'robado'], true)) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'El material no existe o ya está fuera de circulación.'];
        }

        $pdo->prepare("UPDATE materiales SET estado = :estado WHERE id = :id")
            ->execute(['estado' => $estado, 'id' => $materialId]);

        $pdo->prepare("UPDATE prestamos SET estado = 'devuelto' WHERE material_id = :id AND estado = 'activo'")
            ->execute(['id' => $materialId]);

        if ($estado === 'robado') {
            lb_nuevo_evento('robado', "Reportado robo de \"{$material['titulo']}\"");
            $mensaje = "\"{$material['titulo']}\" reportado como robado";
        } else {
            lb_nuevo_evento('roto', "Material dañado: \"{$material['titulo']}\"");
            $mensaje = "\"{$material['titulo']}\" reportado como dañado";
        }

        $pdo->commit();
        return ['ok' => true, 'message' => $mensaje];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'No se pudo actualizar el material.'];
    }
}
