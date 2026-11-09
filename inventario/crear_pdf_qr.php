<?php

ob_start();

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

lb_requerir_login();

// =================================
// Verificar materiales seleccionados
// =================================

if (!isset($_POST['materiales']) || empty($_POST['materiales'])) {
    header("Location: generar_qr.php?error=No selecciono materiales");
    exit;
}

$materialesSeleccionados = $_POST['materiales'];

// =================================
// Tamaños de etiqueta disponibles
// (A4: 210mm de ancho, se acomodan en filas según el ancho de cada etiqueta)
// =================================

$tamanos = [
    // La clave es el lado del QR en cm (ej: '4x4' = QR de 4 cm x 4 cm)
    '2x2' => [
        'ancho'        => 34,   // ancho de la etiqueta (mm)
        'qr'           => 20,   // lado del QR (mm)
        'fuente_nombre'=> [7, 6, 5],  // normal, nombre largo, nombre muy largo
        'fuente_info'  => 5.5,
        'fuente_viejo' => 5,
        'alto_info'    => 3,
        'alto_viejo'   => 2.8,
        'alto_linea'   => 3,
    ],
    '3x3' => [
        'ancho'        => 42,
        'qr'           => 30,
        'fuente_nombre'=> [9, 8, 7],
        'fuente_info'  => 7,
        'fuente_viejo' => 6,
        'alto_info'    => 4,
        'alto_viejo'   => 3.5,
        'alto_linea'   => 4,
    ],
    '4x4' => [
        'ancho'        => 52,
        'qr'           => 40,
        'fuente_nombre'=> [10, 9, 7],
        'fuente_info'  => 7.5,
        'fuente_viejo' => 6.5,
        'alto_info'    => 4.5,
        'alto_viejo'   => 4,
        'alto_linea'   => 4.5,
    ],
    '5x5' => [
        'ancho'        => 60,
        'qr'           => 50,
        'fuente_nombre'=> [11, 10, 8],
        'fuente_info'  => 8,
        'fuente_viejo' => 7,
        'alto_info'    => 5,
        'alto_viejo'   => 4.5,
        'alto_linea'   => 5,
    ],
];

// =================================
// Tamaño RECOMENDADO por categoría (id_categoria => tamaño)
// Si cambiás algo acá, cambialo también en generar_qr.php
// (así coincide con lo que se muestra en la tabla).
// =================================
$tamanoRecomendadoPorCategoria = [
    1 => '2x2',    // Libros
    3 => '4x4',   // Materiales geográficos (mapas, globos)
    4 => '2x2',    // Instrumentos de medición
    5 => '4x4',   // Dibujo técnico (tableros)
    6 => '4x4',   // Dibujo técnico
    7 => '3x3',     // Materiales didácticos
];
$tamanoPorDefecto = '3x3'; // categorías que no figuren arriba

// 'auto' = cada etiqueta usa el recomendado de su categoría.
// Cualquier otro valor válido fuerza ese tamaño para todas.
$modoTamano = $_POST['tamano'] ?? 'auto';
if ($modoTamano !== 'auto' && !isset($tamanos[$modoTamano])) {
    $modoTamano = 'auto';
}

// =================================
// Crear PDF
// =================================

$pdf = new TCPDF(
    'P',
    'mm',
    'A4',
    true,
    'UTF-8',
    false
);

$pdf->SetCreator('Sistema Inventario');
$pdf->SetTitle('Etiquetas QR Inventario');

// Desactivar cabecera y pie de página automáticos de TCPDF para las etiquetas
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(false); // el salto de página lo controlamos nosotros
$pdf->AddPage();

// =================================
// Configuración de la grilla
// =================================

$margenEntreColumnas = 5;
$margenEntreFilas = 4;

// Márgenes de la hoja A4
$margenIzqInicial = 10;
$margenSupInicial = 10;
$limiteDerecho = 205.01; // A4 = 210mm, dejamos 5mm de margen derecho
$limiteInferiorA4 = 282; // Límite seguro antes de saltar de página

// Posición actual (las etiquetas se acomodan de izquierda a derecha y bajan de fila cuando no entran)
$xActual = $margenIzqInicial;
$yActualFila = $margenSupInicial;
$maxAltoFilaActual = 0;

// =================================
// Recorrer materiales
// =================================

foreach ($materialesSeleccionados as $id_material) {

    // =============================
    // Consulta PDO Unificada (Incluyendo id_viejo)
    // El CASE usa el mismo mapeo de categorías que generar_qr.php
    // =============================
    $sql = "
    SELECT
        i.id_material,
        i.id_viejo,
        c.id_categoria,
        c.nombre,
        CASE i.id_categoria
            WHEN 1 THEN l.nombre
            WHEN 3 THEN mg.tipo
            WHEN 4 THEN im.tipo
            WHEN 5 THEN mdt.tipo
            WHEN 6 THEN mdt.tipo
            WHEN 7 THEN md.tipo
            ELSE 'Material Genérico'
        END AS nombre_material
    FROM inventario i
    INNER JOIN categorias c ON c.id_categoria = i.id_categoria
    LEFT JOIN libros l ON i.id_material = l.id_material
    LEFT JOIN instrumentos_medicion im ON i.id_material = im.id_material
    LEFT JOIN materiales_didacticos md ON i.id_material = md.id_material
    LEFT JOIN materiales_geograficos mg ON i.id_material = mg.id_material
    LEFT JOIN materiales_dibujo_tecnico mdt ON i.id_material = mdt.id_material
    WHERE i.id_material = :id
    ";

    $stmt = db()->prepare($sql);
    $stmt->execute([':id' => $id_material]);
    $material = $stmt->fetch();

    if (!$material) {
        continue;
    }

    $categoria = $material['nombre'] ?? 'Sin categoria';
    $nombre = $material['nombre_material'] ?? 'Sin nombre';
    if (trim((string)$nombre) === '') {
        $nombre = 'Sin nombre';
    }
    // Obtenemos el id viejo (si es null o 0, mostramos un guion)
    $idViejo = (!empty($material['id_viejo'])) ? $material['id_viejo'] : '-';

    // =============================
    // Elegir tamaño de ESTA etiqueta
    // =============================
    if ($modoTamano === 'auto') {
        $claveTamano = $tamanoRecomendadoPorCategoria[(int)$material['id_categoria']] ?? $tamanoPorDefecto;
    } else {
        $claveTamano = $modoTamano;
    }
    $cfg = $tamanos[$claveTamano];

    $anchoEtiqueta = $cfg['ancho'];
    $anchoUtilTexto = $anchoEtiqueta - 4;

    // =============================
    // Ajustar tamaño de letra según longitud (proporcional al ancho de la etiqueta)
    // =============================
    $factorLargo = $anchoEtiqueta / 45;
    $tamanoLetra = $cfg['fuente_nombre'][0];
    if (strlen($nombre) > 25 * $factorLargo) {
        $tamanoLetra = $cfg['fuente_nombre'][1];
    }
    if (strlen($nombre) > 45 * $factorLargo) {
        $tamanoLetra = $cfg['fuente_nombre'][2];
    }

    // =============================
    // Precálculo de altura de esta etiqueta específica
    // =============================
    $pdf->SetFont('helvetica', 'B', $tamanoLetra);
    $altoNombreReal = $pdf->getStringHeight($anchoUtilTexto, $nombre);

    $espacioSuperior = 3;
    $espacioEntreTextoQR = 3;
    $tamanoQR = $cfg['qr'];
    $espacioEntreQRInfo = 3;
    $altoInfo = $cfg['alto_info'];       // Línea 1 (ID y categoría)
    $altoInfoViejo = $cfg['alto_viejo']; // Línea 2 (ID viejo)
    $espacioInferior = 3;

    $altoEtiqueta = $espacioSuperior + $altoNombreReal + $espacioEntreTextoQR + $tamanoQR + $espacioEntreQRInfo + $altoInfo + $altoInfoViejo + $espacioInferior;

    // =============================
    // Si no entra en el ancho de la hoja, pasar a la fila siguiente
    // =============================
    if ($xActual > $margenIzqInicial && ($xActual + $anchoEtiqueta) > $limiteDerecho) {
        $xActual = $margenIzqInicial;
        $yActualFila += $maxAltoFilaActual + $margenEntreFilas;
        $maxAltoFilaActual = 0;
    }

    // =============================
    // Control de salto de página
    // =============================
    if ($yActualFila + $altoEtiqueta > $limiteInferiorA4) {
        $pdf->AddPage();
        $xActual = $margenIzqInicial;
        $yActualFila = $margenSupInicial;
        $maxAltoFilaActual = 0;
    }

    // Altura máxima de la fila actual (para saber cuánto bajar en la próxima fila)
    if ($altoEtiqueta > $maxAltoFilaActual) {
        $maxAltoFilaActual = $altoEtiqueta;
    }

    // =============================
    // Posición X e Y definitiva
    // =============================
    $x = $xActual;
    $y = $yActualFila;

    // =============================
    // Borde de la etiqueta
    // =============================
    $pdf->Rect($x, $y, $anchoEtiqueta, $altoEtiqueta);

    // =============================
    // Nombre material
    // =============================
    $pdf->SetXY($x + 2, $y + $espacioSuperior);
    $pdf->SetFont('helvetica', 'B', $tamanoLetra);
    $pdf->MultiCell(
        $anchoUtilTexto,
        $cfg['alto_linea'],
        $nombre,
        0,
        'C'
    );

    // =============================
    // QR (Posicionado dinámicamente debajo del texto)
    // =============================
    $yDespuesTexto = $pdf->GetY();
    $yQR = $yDespuesTexto + $espacioEntreTextoQR;
    $xQR = $x + (($anchoEtiqueta - $tamanoQR) / 2);

    $pdf->write2DBarcode(
        (string)$material['id_material'],
        'QRCODE,L',
        $xQR,
        $yQR,
        $tamanoQR,
        $tamanoQR
    );

    // =============================
    // ID y categoría (Línea 1)
    // =============================
    $yInfo = $yQR + $tamanoQR + $espacioEntreQRInfo;
    $pdf->SetXY($x + 2, $yInfo);
    $pdf->SetFont('helvetica', '', $cfg['fuente_info']);

    $info = "ID: " . $material['id_material'] . " | Cat: " . $categoria;

    $pdf->Cell(
        $anchoUtilTexto,
        $altoInfo,
        $info,
        0,
        0,
        'C'
    );

    // =============================
    // ID viejo sin la palabra "ID" (Línea 2 - Letra más chica)
    // =============================
    $yInfoViejo = $yInfo + $altoInfo;
    $pdf->SetXY($x + 2, $yInfoViejo);
    $pdf->SetFont('helvetica', '', $cfg['fuente_viejo']);

    $pdf->Cell(
        $anchoUtilTexto,
        $altoInfoViejo,
        $idViejo,
        0,
        0,
        'C'
    );

    // =============================
    // Avanzar a la posición de la próxima etiqueta
    // =============================
    $xActual += $anchoEtiqueta + $margenEntreColumnas;
}

// Limpiar buffer
ob_end_clean();

// =================================
// Mostrar PDF
// =================================
$pdf->Output(
    'etiquetas_qr.pdf',
    'I'
);
?>