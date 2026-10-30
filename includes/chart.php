<?php
/**
 * Genera un gráfico de líneas/área en SVG puro (sin JavaScript) a partir
 * de los datos de estadistica_anual. Reemplaza al antiguo Chart.js.
 */
function lb_grafico_anual_svg(array $datos): string {
    if (empty($datos)) {
        return '<p class="text-muted" style="padding:2rem 0;text-align:center">Sin datos para este año todavía.</p>';
    }

    $w = 700; $h = 260;
    $padL = 34; $padR = 12; $padT = 12; $padB = 28;
    $plotW = $w - $padL - $padR;
    $plotH = $h - $padT - $padB;

    $maxVal = 1;
    foreach ($datos as $d) {
        $maxVal = max($maxVal, (int) $d['prestamos'], (int) $d['robos'], (int) $d['rotos']);
    }
    $maxVal = (int) ceil($maxVal / 20) * 20;
    if ($maxVal < 20) $maxVal = 20;

    $n = count($datos);
    $stepX = $n > 1 ? $plotW / ($n - 1) : 0;

    $puntoX = fn($i) => $padL + $i * $stepX;
    $puntoY = fn($v) => $padT + $plotH - ($v / $maxVal) * $plotH;

    $seriePuntos = function (string $campo) use ($datos, $puntoX, $puntoY) {
        $pts = [];
        foreach (array_values($datos) as $i => $d) {
            $pts[] = $puntoX($i) . ',' . round($puntoY((int) $d[$campo]), 1);
        }
        return implode(' ', $pts);
    };

    $ptsPrestamos = $seriePuntos('prestamos');
    $ptsRotos = $seriePuntos('rotos');
    $ptsRobos = $seriePuntos('robos');

    // Área bajo la curva de préstamos
    $areaPath = "M{$padL}," . round($puntoY(0), 1) . " L" . str_replace(' ', ' L', $ptsPrestamos)
        . " L" . round($puntoX($n - 1), 1) . "," . round($puntoY(0), 1) . " Z";

    // Líneas de grilla horizontales (0%, 25%, 50%, 75%, 100%)
    $grid = '';
    for ($g = 0; $g <= 4; $g++) {
        $y = $padT + ($plotH / 4) * $g;
        $grid .= "<line x1=\"{$padL}\" y1=\"{$y}\" x2=\"" . ($w - $padR) . "\" y2=\"{$y}\" class=\"chart-grid-line\"/>";
    }

    // Etiquetas de mes en el eje X
    $labels = '';
    foreach (array_values($datos) as $i => $d) {
        $x = round($puntoX($i), 1);
        $labels .= "<text x=\"{$x}\" y=\"" . ($h - 6) . "\" class=\"chart-axis-label\" text-anchor=\"middle\">" . htmlspecialchars($d['mes_nombre']) . "</text>";
    }

    return <<<SVG
    <svg viewBox="0 0 {$w} {$h}" class="annual-chart-svg" role="img" aria-label="Gráfico de estadística anual">
      <g>{$grid}</g>
      <path d="{$areaPath}" class="chart-area-prestamos"/>
      <polyline points="{$ptsPrestamos}" class="chart-line-prestamos"/>
      <polyline points="{$ptsRotos}" class="chart-line-rotos"/>
      <polyline points="{$ptsRobos}" class="chart-line-robos"/>
      {$labels}
    </svg>
    SVG;
}
