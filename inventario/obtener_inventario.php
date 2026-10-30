<?php
// obtener_inventario.php
require_once '../conexion.php';

// Obtener la lista única de géneros directamente desde la columna 'genero' de la tabla libros
$stmt_generos = db()->query("SELECT DISTINCT genero FROM libros WHERE genero IS NOT NULL AND genero != '' ORDER BY genero");
$lista_generos = $stmt_generos->fetchAll(PDO::FETCH_COLUMN);

// Obtener la lista de estados de materiales para los filtros individuales
$stmt_estados_filtro = db()->query("SELECT id_estado_material, desc_estado_material FROM estado_material");
$lista_estados_filtro = $stmt_estados_filtro->fetchAll();

// Definición de filtros comunes de estado para reutilizar en cada categoría
$filtro_estado_comun = [
    'name' => 'id_estado_material', 
    'type' => 'select_estado', 
    'placeholder' => 'Filtrar por estado...', 
    'sql' => 'i.id_estado_material = ?', 
    'options' => $lista_estados_filtro
];

// Matriz de configuración central
$categorias_config = [
    'libros' => [
        'titulo' => 'Catálogo de Libros',
        'columnas' => ['Código', 'Título', 'Autor', 'Género', 'Estado'],
        'campos_select' => [
            'l.nombre AS col1',
            'l.autor AS col2',
            'l.genero AS col3'
        ],
        'joins' => 'INNER JOIN libros l ON i.id_material = l.id_material',
        'search_fields' => ['i.id_material', 'l.nombre', 'l.autor', 'l.genero'],
        'filtros' => [
            ['name' => 'autor', 'type' => 'text', 'placeholder' => 'Filtrar por autor...', 'sql' => 'l.autor LIKE ?'],
            ['name' => 'genero', 'type' => 'select', 'placeholder' => 'Todos los géneros...', 'sql' => 'l.genero = ?', 'options' => $lista_generos],
            $filtro_estado_comun
        ]
    ],
    'instrumentos_medicion' => [
        'titulo' => 'Instrumentos de Medición',
        'columnas' => ['Código', 'Tipo', 'Longitud (cm)', '-', 'Estado'],
        'campos_select' => [
            'im.tipo AS col1',
            'im.longitud_cm AS col2',
            "'' AS col3"
        ],
        'joins' => 'INNER JOIN instrumentos_medicion im ON i.id_material = im.id_material',
        'search_fields' => ['i.id_material', 'im.tipo'],
        'filtros' => [
            ['name' => 'tipo', 'type' => 'text', 'placeholder' => 'Filtrar por tipo...', 'sql' => 'im.tipo LIKE ?'],
            $filtro_estado_comun
        ]
    ],
    'materiales_didacticos' => [
        'titulo' => 'Materiales Didácticos',
        'columnas' => ['Código', 'Tipo', 'Temática', 'Piezas', 'Estado'],
        'campos_select' => [
            'md.tipo AS col1',
            'md.tematica AS col2',
            'md.piezas AS col3'
        ],
        'joins' => 'INNER JOIN materiales_didacticos md ON i.id_material = md.id_material',
        'search_fields' => ['i.id_material', 'md.tipo', 'md.tematica'],
        'filtros' => [
            ['name' => 'tematica', 'type' => 'text', 'placeholder' => 'Filtrar por temática...', 'sql' => 'md.tematica LIKE ?'],
            $filtro_estado_comun
        ]
    ],
    'materiales_geograficos' => [
        'titulo' => 'Materiales Geográficos',
        'columnas' => ['Código', 'Tipo', 'Región', 'Escala', 'Estado'],
        'campos_select' => [
            'mg.tipo AS col1',
            'mg.region AS col2',
            'mg.escala AS col3'
        ],
        'joins' => 'INNER JOIN materiales_geograficos mg ON i.id_material = mg.id_material',
        'search_fields' => ['i.id_material', 'mg.tipo', 'mg.region'],
        'filtros' => [
            ['name' => 'region', 'type' => 'text', 'placeholder' => 'Filtrar por región...', 'sql' => 'mg.region LIKE ?'],
            $filtro_estado_comun
        ]
    ],
    'materiales_dibujo_tecnico' => [
        'titulo' => 'Materiales de Dibujo Técnico',
        'columnas' => ['Código', 'Tipo', 'Descripción', '-', 'Estado'],
        'campos_select' => [
            'mdt.tipo AS col1',
            'mdt.descripcion AS col2',
            "'' AS col3"
        ],
        'joins' => 'INNER JOIN materiales_dibujo_tecnico mdt ON i.id_material = mdt.id_material',
        'search_fields' => ['i.id_material', 'mdt.tipo', 'mdt.descripcion'],
        'filtros' => [
            ['name' => 'descripcion', 'type' => 'text', 'placeholder' => 'Filtrar por descripción...', 'sql' => 'mdt.descripcion LIKE ?'],
            $filtro_estado_comun
        ]
    ]
];

// Categoría activa por URL
$cat_key = $_GET['cat'] ?? 'libros';
if (!isset($categorias_config[$cat_key])) {
    $cat_key = 'libros';
}
$config = $categorias_config[$cat_key];

// Construcción dinámica de la consulta SQL base
$sql_base = "FROM inventario i
             {$config['joins']}
             INNER JOIN estado_material em ON i.id_estado_material = em.id_estado_material
             WHERE 1=1";

$params = [];

// Buscador masivo global (texto)
$busqueda = trim($_GET['q'] ?? '');
if (!empty($busqueda)) {
    $busqueda_sql = [];
    foreach ($config['search_fields'] as $field) {
        $busqueda_sql[] = "$field LIKE ?";
        $params[] = "%$busqueda%";
    }
    $sql_base .= " AND (" . implode(" OR ", $busqueda_sql) . ")";
}

// Filtro por categoría general
$id_categoria_filtro = trim($_GET['id_categoria_filtro'] ?? '');
if (!empty($id_categoria_filtro)) {
    $sql_base .= " AND i.id_categoria = ?";
    $params[] = $id_categoria_filtro;
}

// Filtros específicos (dinámicos según tipo text, select o select_estado)
foreach ($config['filtros'] as $filtro) {
    $valor = trim($_GET[$filtro['name']] ?? '');
    if (!empty($valor)) {
        $sql_base .= " AND " . $filtro['sql'];
        if (isset($filtro['type']) && ($filtro['type'] === 'select' || $filtro['type'] === 'select_estado')) {
            $params[] = $valor;
        } else {
            $params[] = "%$valor%";
        }
    }
}

// --- CONFIGURACIÓN DE PAGINACIÓN ---
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// 1. Obtener el total de registros con los filtros actuales
$stmt_total = db()->prepare("SELECT COUNT(i.id_material) " . $sql_base);
$stmt_total->execute($params);
$total_registros = $stmt_total->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);
if ($total_paginas < 1) $total_paginas = 1;
if ($pagina_actual > $total_paginas) $pagina_actual = $total_paginas;

// 2. Consulta final para obtener los registros limitados por página
$sql_final = "SELECT i.id_material, {$config['campos_select'][0]}, {$config['campos_select'][1]}, {$config['campos_select'][2]}, em.desc_estado_material AS estado " 
             . $sql_base 
             . " LIMIT $registros_por_pagina OFFSET $offset";

$stmt = db()->prepare($sql_final);
$stmt->execute($params);
$resultados = $stmt->fetchAll();
?>