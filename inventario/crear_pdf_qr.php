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
$pdf->AddPage();

// =================================
// Configuración y Diseño de Etiquetas
// =================================

$columna = 0;
$fila = 0;

$anchoEtiqueta = 45;
$anchoUtilTexto = 41; 
$margenEntreColumnas = 5;
$margenEntreFilas = 4; // Espacio vertical pequeño y compacto entre filas

// Márgenes de la hoja A4
$margenIzqInicial = 10;
$margenSupInicial = 10;
$limiteInferiorA4 = 282; // Límite seguro antes de saltar de página en A4 (297mm - márgenes)

// Variables de control de altura por fila
$yActualFila = $margenSupInicial;
$maxAltoFilaActual = 0;

// =================================
// Recorrer materiales
// =================================

foreach ($materialesSeleccionados as $id_material) {

    // =============================
    // Consulta PDO Unificada (Incluyendo id_viejo)
    // =============================
    $sql = "
    SELECT
        i.id_material,
        i.id_viejo,
        c.id_categoria,
        c.nombre,
        CASE i.id_categoria
            WHEN 1 THEN l.nombre
            WHEN 2 THEN md.tipo
            WHEN 3 THEN mg.tipo
            WHEN 4 THEN im.tipo
            WHEN 5 THEN md.tipo
            WHEN 6 THEN mdt.tipo
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
    // Obtenemos el id viejo (si es null o 0, mostramos un guion o vacío)
    $idViejo = (!empty($material['id_viejo'])) ? $material['id_viejo'] : '-';

    // =============================
    // Ajustar tamaño de letra según longitud
    // =============================
    $tamanoLetra = 9;
    if (strlen($nombre) > 25) {
        $tamanoLetra = 8;
    }
    if (strlen($nombre) > 45) {
        $tamanoLetra = 7;
    }

    // =============================
    // Precálculo de altura de esta etiqueta específica (ahora con 2 líneas de info abajo)
    // =============================
    $pdf->SetFont('helvetica', 'B', $tamanoLetra);
    $altoNombreReal = $pdf->getStringHeight($anchoUtilTexto, $nombre);

    $espacioSuperior = 3;
    $espacioEntreTextoQR = 3;
    $tamanoQR = 20; 
    $espacioEntreQRInfo = 3;
    $altoInfo = 4;      // Altura para la primera línea (ID prefijado y categoría)
    $altoInfoViejo = 3.5; // Altura para la segunda línea (ID viejo)
    $espacioInferior = 3;

    $altoEtiqueta = $espacioSuperior + $altoNombreReal + $espacioEntreTextoQR + $tamanoQR + $espacioEntreQRInfo + $altoInfo + $altoInfoViejo + $espacioInferior;

    // Actualizar la altura máxima de la fila actual si esta etiqueta es más alta que las anteriores de su misma fila
    if ($altoEtiqueta > $maxAltoFilaActual) {
        $maxAltoFilaActual = $altoEtiqueta;
    }

    // =============================
    // Control de salto de página inteligente
    // =============================
    if ($yActualFila + $altoEtiqueta > $limiteInferiorA4) {
        $pdf->AddPage();
        $columna = 0;
        $fila = 0;
        $yActualFila = $margenSupInicial;
        $maxAltoFilaActual = $altoEtiqueta; 
    }

    // =============================
    // Calcular posición X e Y definitiva
    // =============================
    $x = $margenIzqInicial + ($columna * ($anchoEtiqueta + $margenEntreColumnas));
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
        4,
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
    $pdf->SetFont('helvetica', '', 7);

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
    $pdf->SetFont('helvetica', '', 6); // Letra más chica para el id viejo

    $pdf->Cell(
        $anchoUtilTexto,
        $altoInfoViejo,
        $idViejo,
        0,
        0,
        'C'
    );

    // =============================
    // Gestión de Columnas y Filas Dinámicas
    // =============================
    $columna++;

    if ($columna == 4) {
        $columna = 0;
        $fila++;
        $yActualFila += $maxAltoFilaActual + $margenEntreFilas;
        $maxAltoFilaActual = 0; 
    }
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