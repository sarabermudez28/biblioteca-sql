<?php
/**
 * Íconos SVG simples e independientes (sin librerías externas ni JavaScript).
 * Todos usan currentColor para heredar el color del texto que los envuelve.
 */
function lb_icon(string $nombre, string $clase = ''): string {
    $attrs = 'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" '
        . 'stroke-linecap="round" stroke-linejoin="round" class="icon ' . htmlspecialchars($clase) . '" aria-hidden="true"';

    $paths = [
        'book-marked' => '<path d="M5 4a2 2 0 0 1 2-2h10a1 1 0 0 1 1 1v17l-3-2-3 2-3-2-3 2V4Z"/><path d="M9 8h6"/>',
        'layout-dashboard' => '<rect x="3.5" y="3.5" width="7" height="7" rx="1"/><rect x="13.5" y="3.5" width="7" height="4.5" rx="1"/><rect x="13.5" y="10.5" width="7" height="10" rx="1"/><rect x="3.5" y="13" width="7" height="7.5" rx="1"/>',
        'library-big' => '<rect x="3.5" y="3.5" width="17" height="3.5" rx="1"/><rect x="3.5" y="9.5" width="17" height="11" rx="1"/><path d="M8 13v4M12 13v4M16 13v4"/>',
        'shield-alert' => '<path d="M12 3 4.5 6v6c0 4.6 3.1 8.2 7.5 9 4.4-.8 7.5-4.4 7.5-9V6L12 3Z"/><path d="M12 8v5"/><circle cx="12" cy="16.3" r=".4" fill="currentColor" stroke="none"/>',
        'log-out' => '<path d="M9 21H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'book-up' => '<path d="M4 5.5C4 4.7 4.7 4 5.5 4H11v14H5.5A1.5 1.5 0 0 0 4 19.5v-14Z"/><path d="M20 5.5c0-.8-.7-1.5-1.5-1.5H13v14h5.5a1.5 1.5 0 0 1 1.5 1.5v-14Z"/><path d="M12 2v6M9.5 5.5 12 3l2.5 2.5"/>',
        'wrench' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.8 2.8-2-2 2.8-2.8Z"/>',
        'check-circle-2' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.3 2.3L16 9.6"/>',
        'arrow-left-right' => '<path d="m17 8-4-4v3H3v2h10v3l4-4Z"/><path d="M7 16l4 4v-3h10v-2H11v-3l-4 4Z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'library' => '<path d="M4 20V6a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v14"/><path d="M10 20V5.4a1 1 0 0 1 1.2-1l3 .8a1 1 0 0 1 .8 1v13.8"/><path d="M16 20V8.5a1 1 0 0 1 1.2-1l2.6.7A1 1 0 0 1 20.5 9v11"/><path d="M3 20h18"/>',
        'book-open' => '<path d="M12 6.5C10.5 5 8 4 4.5 4V18c3.5 0 6 1 7.5 2.5C13.5 19 16 18 19.5 18V4C16 4 13.5 5 12 6.5Z"/><path d="M12 6.5V20.5"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'rotate-ccw' => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/>',
        'lock' => '<rect x="4.5" y="10.5" width="15" height="10" rx="1.5"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m20 20-4.6-4.6"/>',
        'triangle-alert' => '<path d="M12 3.5 22 20H2L12 3.5Z"/><path d="M12 9.5v4.5"/><circle cx="12" cy="17" r=".4" fill="currentColor" stroke="none"/>',
        'circle-x' => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><circle cx="12" cy="8" r=".4" fill="currentColor" stroke="none"/>',
        'bar-chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
    ];

    $inner = $paths[$nombre] ?? $paths['info'];
    return "<svg {$attrs}>{$inner}</svg>";
}
