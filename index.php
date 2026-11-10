<?php
include 'conexion.php';

$mensaje_exito = "";

// ===== GUARDADO AJAX: TEST VOCACIONAL Y ENCUESTA DE SATISFACCIÓN =====
if (isset($_POST['accion']) && in_array($_POST['accion'], ['guardar_vocacional', 'guardar_encuesta'], true)) {
    header('Content-Type: application/json; charset=utf-8');

    $ok = false;

    if ($_POST['accion'] === 'guardar_vocacional') {
        // Tabla: test_vocacion (nombre_aspirante, resultado_carrera, puntos_automotriz, puntos_renovables)
        // El test general ya no usa puntos por carrera: resultado_carrera guarda las 3 áreas principales con su puntaje
        // y las columnas de puntos se dejan en 0 (ver nota de ALTER TABLE para guardar los 6 puntajes).
        $nombre = trim(mb_substr($_POST['nombre_aspirante'] ?? '', 0, 150));
        $carrera = trim(mb_substr(strip_tags($_POST['resultado_carrera'] ?? ''), 0, 100));
        $pa = 0;
        $pr = 0;

        $stmt = $conexion->prepare("INSERT INTO test_vocacion (nombre_aspirante, resultado_carrera, puntos_automotriz, puntos_renovables) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssii", $nombre, $carrera, $pa, $pr);
            $ok = $stmt->execute();
            $stmt->close();
        }
    } else {
        // Tabla: respuestas_encuesta (puntaje_total, porcentaje)
        $total = 0;
        for ($i = 1; $i <= 10; $i++) {
            $v = (int)($_POST["p$i"] ?? 0);
            $total += ($v >= 1 && $v <= 3) ? $v : 1;
        }
        $porcentaje = round(($total / 30) * 100, 2);

        $stmt = $conexion->prepare("INSERT INTO respuestas_encuesta (puntaje_total, porcentaje) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("id", $total, $porcentaje);
            $ok = $stmt->execute();
            $stmt->close();
        }
    }

    echo json_encode(['ok' => $ok]);
    exit;
}

// C - INSERTAR ASPIRANTE DESDE EL PORTAL
if (isset($_POST['registrar_aspirante'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $carrera = $_POST['carrera'];
    $folio = 'UNP-2026-' . rand(1000, 9999);
    $curp = strtoupper(substr(trim($_POST['curp'] ?? ''), 0, 18));

    if ($curp === '') {
        $mensaje_exito = "Por favor escribe tu CURP.";
    } else {
    $stmt = $conexion->prepare("INSERT INTO aspirantes (folio, nombre, correo, curp, carrera) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssss", $folio, $nombre, $correo, $curp, $carrera);
        if ($stmt->execute()) {
            $mensaje_exito = "¡Registro exitoso! Tu folio asignado es: " . $folio;
        } else {
            $mensaje_exito = "Error al registrar en la base de datos.";
        }
        $stmt->close();
    }
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universidad UNP - Portal Académico Oficial</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        unp: {
                            navy: '#1b365d',
                            navyLight: '#2c4d7e',
                            burgundy: '#8b1e23',
                            burgundyHover: '#6f161a',
                            gold: '#d97706',
                            bgLight: '#f8fafc'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .page-section { display: none; }
        .page-section.active { display: block; animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 bg-slate-50 antialiased selection:bg-unp-burgundy selection:text-white">

    <?php if (!empty($mensaje_exito)): ?>
    <div class="bg-emerald-600 text-white text-center py-3 px-4 font-bold text-sm z-50">
        <?= $mensaje_exito ?>
    </div>
    <?php endif; ?>

    <!-- TOP BAR DE INFORMACIÓN INSTITUCIONAL -->
    <div class="bg-unp-navy text-slate-200 text-xs py-2 px-4 border-b border-slate-700">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center space-x-4">
                <span class="flex items-center"><i class="fa-solid fa-graduation-cap text-amber-400 mr-1.5"></i> Convocatoria UNP 2026 Abierta</span>
                <span class="hidden md:inline"><i class="fa-solid fa-phone mr-1 text-slate-400"></i> +52 (55) 8000-UNP1</span>
                <span class="hidden lg:inline"><i class="fa-solid fa-envelope mr-1 text-slate-400"></i> admisiones@unp.edu.mx</span>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-xs text-slate-300">Síguedos en redes:</span>
                <a href="#redes-sociales" onclick="navigateTo('redes-sociales')" class="hover:text-amber-400 transition"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#redes-sociales" onclick="navigateTo('redes-sociales')" class="hover:text-amber-400 transition"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#redes-sociales" onclick="navigateTo('redes-sociales')" class="hover:text-amber-400 transition"><i class="fa-brands fa-instagram"></i></a>
                <a href="#redes-sociales" onclick="navigateTo('redes-sociales')" class="hover:text-amber-400 transition"><i class="fa-brands fa-linkedin-in"></i></a>
                <a href="#redes-sociales" onclick="navigateTo('redes-sociales')" class="hover:text-amber-400 transition"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <!-- NAVEGACIÓN PRINCIPAL CON MENÚS DESPLEGABLES INDEPENDIENTES -->
    <header class="bg-white shadow-md sticky top-0 z-40 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <!-- Logo Oficial UNP Vectorial -->
                <div class="flex items-center cursor-pointer" onclick="navigateTo('mision-vision')">
                    <div class="mr-3 flex-shrink-0">
                        <svg class="w-14 h-14" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="100" cy="100" r="96" fill="#1b365d"/>
                            <circle cx="100" cy="100" r="88" fill="#f8fafc"/>
                            <circle cx="100" cy="100" r="80" fill="none" stroke="#1b365d" stroke-width="3"/>
                            <circle cx="100" cy="100" r="68" fill="none" stroke="#8b1e23" stroke-width="22"/>
                            
                            <path id="textPathTop" d="M 38,100 A 62,62 0 0,1 162,100" fill="none"/>
                            <path id="textPathBottom" d="M 162,100 A 62,62 0 0,1 38,100" fill="none"/>
                            
                            <text font-family="Inter, sans-serif" font-weight="800" font-size="13" fill="#ffffff" letter-spacing="2">
                                <textPath href="#textPathTop" startOffset="50%" text-anchor="middle">UNIVERSIDAD UNP</textPath>
                            </text>
                            <text font-family="Inter, sans-serif" font-weight="800" font-size="13" fill="#ffffff" letter-spacing="2">
                                <textPath href="#textPathBottom" startOffset="50%" text-anchor="middle">UNIVERSIDAD UNP</textPath>
                            </text>

                            <polygon points="32,100 35,93 42,93 36,88 38,81 32,85 26,81 28,88 22,93 29,93" fill="#1b365d"/>
                            <polygon points="168,100 171,93 178,93 172,88 174,81 168,85 162,81 164,88 158,93 165,93" fill="#1b365d"/>

                            <circle cx="100" cy="100" r="56" fill="#f8fafc" stroke="#1b365d" stroke-width="2"/>

                            <path d="M 100,68 L 130,78 L 100,88 L 70,78 Z" fill="#1b365d"/>
                            <path d="M 82,82 L 82,93 C 82,97 118,97 118,93 L 118,82" fill="none" stroke="#1b365d" stroke-width="2"/>
                            <line x1="122" y1="79" x2="128" y2="92" stroke="#1b365d" stroke-width="2"/>
                            <circle cx="128" cy="93" r="2.5" fill="#8b1e23"/>

                            <path d="M 75,108 C 88,103 98,106 100,110 C 102,106 112,103 125,108 L 125,128 C 112,123 102,126 100,130 C 98,126 88,123 75,128 Z" fill="#1b365d"/>
                            <path d="M 100,110 L 100,130" stroke="#ffffff" stroke-width="2"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-black text-unp-navy tracking-tight block">UNIVERSIDAD UNP</span>
                        <span class="text-[10px] font-semibold text-unp-burgundy tracking-widest block uppercase">Excelencia & Innovación</span>
                    </div>
                </div>

                <!-- Menú Desktop con Dropdowns Separados -->
                <nav class="hidden lg:flex items-center space-x-1">
                    <button onclick="navigateTo('mision-vision')" id="nav-mision-vision" class="nav-btn px-3 py-2 rounded-lg font-medium text-slate-700 hover:text-unp-navy hover:bg-slate-100 transition text-sm">
                        <i class="fa-solid fa-house mr-1 text-unp-burgundy"></i> Inicio
                    </button>

                    <div class="relative group">
                        <button class="px-3 py-2 rounded-lg font-medium text-slate-700 hover:text-unp-navy hover:bg-slate-100 transition inline-flex items-center text-sm">
                            <i class="fa-solid fa-graduation-cap mr-1.5 text-unp-navy"></i>
                            <span>Planes de Estudio</span>
                            <i class="fa-solid fa-chevron-down ml-1.5 text-xs text-slate-400 group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute left-0 w-72 bg-white rounded-xl shadow-xl py-2 hidden group-hover:block border border-slate-100 z-50">
                            <a href="#" onclick="navigateTo('plan-automotriz'); return false;" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-unp-navy transition">
                                <i class="fa-solid fa-car mr-2 text-unp-burgundy"></i> Ing. en Diseño Automotriz
                            </a>
                            <a href="#" onclick="navigateTo('plan-renovables'); return false;" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition border-t border-slate-100">
                                <i class="fa-solid fa-leaf mr-2 text-emerald-600"></i> Ing. en Energías Renovables
                            </a>
                        </div>
                    </div>

                    <div class="relative group">
                        <button class="px-3 py-2 rounded-lg font-medium text-slate-700 hover:text-unp-navy hover:bg-slate-100 transition inline-flex items-center text-sm">
                            <i class="fa-solid fa-user-gear mr-1.5 text-unp-burgundy"></i>
                            <span>Perfil & Trabajo</span>
                            <i class="fa-solid fa-chevron-down ml-1.5 text-xs text-slate-400 group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute left-0 w-72 bg-white rounded-xl shadow-xl py-2 hidden group-hover:block border border-slate-100 z-50">
                            <a href="#" onclick="navigateTo('perfil-egreso'); return false;" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-unp-navy transition">
                                <i class="fa-solid fa-id-card-clip mr-2 text-unp-navy"></i> Perfil del Egresado
                            </a>
                            <a href="#" onclick="navigateTo('campo-laboral'); return false;" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-unp-burgundy transition border-t border-slate-100">
                                <i class="fa-solid fa-building-user mr-2 text-unp-burgundy"></i> Campo Laboral & Compromiso
                            </a>
                        </div>
                    </div>

                    <div class="relative group">
                        <button class="px-3 py-2 rounded-lg font-medium text-slate-700 hover:text-unp-navy hover:bg-slate-100 transition inline-flex items-center text-sm">
                            <i class="fa-solid fa-clipboard-check mr-1.5 text-unp-navy"></i>
                            <span>Admisión & Requisitos</span>
                            <i class="fa-solid fa-chevron-down ml-1.5 text-xs text-slate-400 group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute left-0 w-72 bg-white rounded-xl shadow-xl py-2 hidden group-hover:block border border-slate-100 z-50">
                            <a href="#" onclick="navigateTo('requisitos'); return false;" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-unp-navy transition">
                                <i class="fa-solid fa-list-check mr-2 text-unp-navy"></i> Requisitos de Ingreso
                            </a>
                            <a href="#" onclick="navigateTo('costos'); return false;" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-unp-burgundy transition border-t border-slate-100">
                                <i class="fa-solid fa-sack-dollar mr-2 text-amber-600"></i> Costos, Aranceles y Becas
                            </a>
                        </div>
                    </div>

                    <div class="relative group">
                        <button class="px-3 py-2 rounded-lg font-medium text-slate-700 hover:text-unp-navy hover:bg-slate-100 transition inline-flex items-center text-sm">
                            <i class="fa-solid fa-photo-film mr-1.5 text-amber-600"></i>
                            <span>Comunidad</span>
                            <i class="fa-solid fa-chevron-down ml-1.5 text-xs text-slate-400 group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute left-0 w-72 bg-white rounded-xl shadow-xl py-2 hidden group-hover:block border border-slate-100 z-50">
                            <a href="#" onclick="navigateTo('testimonios'); return false;" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-unp-navy transition">
                                <i class="fa-solid fa-circle-play mr-2 text-amber-600"></i> Testimonios & Spots UNP
                            </a>
                            <a href="#" onclick="navigateTo('redes-sociales'); return false;" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-unp-burgundy transition border-t border-slate-100">
                                <i class="fa-solid fa-share-nodes mr-2 text-unp-burgundy"></i> Redes Sociales Oficiales
                            </a>
                        </div>
                    </div>

                    <button onclick="navigateTo('test-vocacional')" id="nav-test-vocacional" class="nav-btn px-3 py-2 rounded-lg font-medium text-slate-700 hover:text-unp-navy hover:bg-slate-100 transition text-sm">
                        <i class="fa-solid fa-compass mr-1.5 text-amber-600"></i> Test Vocacional
                    </button>

                    <button onclick="navigateTo('satisfaccion')" id="nav-satisfaccion" class="nav-btn px-3 py-2 rounded-lg font-medium text-slate-700 hover:text-unp-navy hover:bg-slate-100 transition text-sm">
                        <i class="fa-solid fa-square-poll-vertical mr-1.5 text-unp-burgundy"></i> Encuesta
                    </button>

                    <button onclick="toggleAuthModal()" class="ml-2 bg-unp-burgundy hover:bg-unp-burgundyHover text-white font-bold px-4 py-2 rounded-xl shadow border border-red-900 transition flex items-center text-xs">
                        <i class="fa-solid fa-right-to-bracket mr-2 text-amber-300"></i>
                        <span>Portal Aspirantes</span>
                    </button>
                </nav>

                <!-- Botón Menú Mobile -->
                <div class="flex items-center lg:hidden">
                    <button onclick="toggleMobileMenu()" class="p-2 rounded-lg text-slate-600 hover:text-unp-navy hover:bg-slate-100 focus:outline-none">
                        <i class="fa-solid fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menú Mobile Completo -->
        <div id="mobile-menu" class="hidden lg:hidden bg-white border-b border-slate-200 px-4 pt-2 pb-6 space-y-2 text-xs">
            <button onclick="navigateTo('mision-vision'); toggleMobileMenu();" class="block w-full text-left px-3 py-2 rounded-md font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-house mr-2 text-unp-burgundy"></i> Inicio / Misión y Visión
            </button>
            <div class="pl-3 space-y-1 border-l-2 border-slate-200 my-1">
                <div class="font-bold text-slate-400 text-[10px] uppercase tracking-wider py-1">Planes de Estudio</div>
                <button onclick="navigateTo('plan-automotriz'); toggleMobileMenu();" class="block w-full text-left px-2 py-1 text-slate-600 hover:text-unp-navy">• Ing. en Diseño Automotriz</button>
                <button onclick="navigateTo('plan-renovables'); toggleMobileMenu();" class="block w-full text-left px-2 py-1 text-slate-600 hover:text-emerald-700">• Ing. en Energías Renovables</button>
            </div>
            <div class="pl-3 space-y-1 border-l-2 border-slate-200 my-1">
                <div class="font-bold text-slate-400 text-[10px] uppercase tracking-wider py-1">Perfil y Trabajo</div>
                <button onclick="navigateTo('perfil-egreso'); toggleMobileMenu();" class="block w-full text-left px-2 py-1 text-slate-600 hover:text-unp-navy">• Perfil de Egreso</button>
                <button onclick="navigateTo('campo-laboral'); toggleMobileMenu();" class="block w-full text-left px-2 py-1 text-slate-600 hover:text-unp-burgundy">• Campo Laboral & Impacto Social</button>
            </div>
            <div class="pl-3 space-y-1 border-l-2 border-slate-200 my-1">
                <div class="font-bold text-slate-400 text-[10px] uppercase tracking-wider py-1">Admisión y Costos</div>
                <button onclick="navigateTo('requisitos'); toggleMobileMenu();" class="block w-full text-left px-2 py-1 text-slate-600 hover:text-unp-navy">• Requisitos de Ingreso</button>
                <button onclick="navigateTo('costos'); toggleMobileMenu();" class="block w-full text-left px-2 py-1 text-slate-600 hover:text-amber-600">• Costos, Aranceles y Becas</button>
            </div>
            <div class="pl-3 space-y-1 border-l-2 border-slate-200 my-1">
                <div class="font-bold text-slate-400 text-[10px] uppercase tracking-wider py-1">Comunidad</div>
                <button onclick="navigateTo('testimonios'); toggleMobileMenu();" class="block w-full text-left px-2 py-1 text-slate-600 hover:text-amber-600">• Testimonios & Spot</button>
                <button onclick="navigateTo('redes-sociales'); toggleMobileMenu();" class="block w-full text-left px-2 py-1 text-slate-600 hover:text-unp-burgundy">• Redes Sociales</button>
            </div>
            <button onclick="navigateTo('test-vocacional'); toggleMobileMenu();" class="block w-full text-left px-3 py-2 rounded-md font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-compass mr-2 text-amber-600"></i> Test Vocacional
            </button>
            <button onclick="navigateTo('satisfaccion'); toggleMobileMenu();" class="block w-full text-left px-3 py-2 rounded-md font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-square-poll-vertical mr-2 text-unp-burgundy"></i> Encuesta de Satisfacción (10 Preguntas)
            </button>
            <button onclick="toggleAuthModal(); toggleMobileMenu();" class="w-full text-center bg-unp-burgundy text-white font-bold py-2.5 rounded-xl shadow mt-2">
                <i class="fa-solid fa-right-to-bracket mr-2 text-amber-300"></i> Acceso Portal Aspirantes
            </button>
        </div>
    </header>

    <!-- CONTENEDOR PRINCIPAL CON HOJAS DEDICADAS E INDEPENDIENTES (SPA) -->
    <main class="flex-grow">

        <!-- HOJA 1: INICIO Y FILOSOFÍA INSTITUCIONAL -->
        <section id="mision-vision" class="page-section active max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-gradient-to-r from-unp-navy via-unp-navyLight to-slate-900 text-white rounded-3xl p-8 md:p-12 mb-10 shadow-2xl relative overflow-hidden">
                <div class="relative z-10 max-w-3xl">
                    <span class="bg-unp-burgundy text-white text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider border border-red-400">
                        Universidad UNP • Ciclo Lectivo 2026
                    </span>
                    <h1 class="text-3xl md:text-5xl font-black mt-4 mb-4 leading-tight">
                        Líderes en Innovación Automotriz y Sustentabilidad Energética
                    </h1>
                    <p class="text-slate-200 text-sm md:text-base leading-relaxed">
                        Bienvenido a la <strong>Universidad UNP</strong>, un referente académico especializado en la formación técnica de alto nivel, la investigación aplicada y el desarrollo con responsabilidad ambiental.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <button onclick="navigateTo('plan-automotriz')" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-5 py-2.5 rounded-xl shadow transition text-xs">
                            <i class="fa-solid fa-car mr-1.5"></i> Plan Design Automotriz
                        </button>
                        <button onclick="navigateTo('plan-renovables')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl shadow transition text-xs">
                            <i class="fa-solid fa-leaf mr-1.5"></i> Plan Energías Renovables
                        </button>
                        <button onclick="navigateTo('requisitos')" class="bg-white/10 hover:bg-white/20 text-white border border-white/30 font-semibold px-5 py-2.5 rounded-xl transition text-xs">
                            <i class="fa-solid fa-clipboard-check mr-1.5"></i> Admisión & Ficha
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Misión -->
                <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="w-12 h-12 bg-unp-navy text-white rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <span class="text-xs font-bold text-unp-navy uppercase tracking-widest block mb-1">Identidad UNP</span>
                    <h2 class="text-2xl font-extrabold text-slate-900 mb-3">Nuestra Misión</h2>
                    <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                        Formar ingenieros integrales en las especialidades de <strong>Diseño Automotriz</strong> e <strong>Energías Renovables</strong>, dotados de sólidos principios éticos, rigor científico y sentido humano, capaces de liderar la transición hacia la movilidad eléctrica y el aprovechamiento de recursos limpios en beneficio directo de la comunidad.
                    </p>
                </div>

                <!-- Visión -->
                <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="w-12 h-12 bg-unp-burgundy text-white rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <span class="text-xs font-bold text-unp-burgundy uppercase tracking-widest block mb-1">Prospectiva 2030</span>
                    <h2 class="text-2xl font-extrabold text-slate-900 mb-3">Nuestra Visión</h2>
                    <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                        Ser la universidad tecnológica de vanguardia nacional e internacional reconocida por la calidad técnica de sus egresados, la transferencia de tecnología limpia a la industria manufacturera y el impacto sustentable tangible en sectores vulnerables de la sociedad.
                    </p>
                </div>
            </div>
        </section>

        <!-- HOJA 2: PLAN DE ESTUDIOS - INGENIERÍA EN DISEÑO AUTOMOTRIZ (DEDICADA) -->
        <section id="plan-automotriz" class="page-section max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-unp-navy text-white rounded-2xl p-8 mb-8 shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="bg-amber-500 text-slate-950 font-bold text-xs px-3 py-1 rounded-full uppercase">Ingeniería Especializada</span>
                    <h1 class="text-3xl font-extrabold mt-2">Plan de Estudios: Ingeniería en Diseño Automotriz</h1>
                    <p class="text-slate-300 text-xs md:text-sm mt-1">Duración: 8 Semestres | Sistema SATCA | Modalidad Presencial Teórico-Práctica</p>
                </div>
                <button onclick="navigateTo('plan-renovables')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold px-4 py-2 rounded-xl text-xs border border-slate-700 self-start md:self-auto">
                    Ver Ing. en Energías Renovables <i class="fa-solid fa-arrow-right ml-1"></i>
                </button>
            </div>

            <!-- Tabla Resumen Créditos Automotriz -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm mb-8">
                <h2 class="text-lg font-bold text-unp-navy mb-4 flex items-center">
                    <i class="fa-solid fa-table-list mr-2 text-unp-burgundy"></i> Desglose Curricular y Créditos SATCA
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold uppercase border-b border-slate-200">
                                <th class="p-3">Área de Conocimiento</th>
                                <th class="p-3 text-center">Asignaturas</th>
                                <th class="p-3 text-center">Horas Teóricas</th>
                                <th class="p-3 text-center">Horas Prácticas / Taller</th>
                                <th class="p-3 text-center">Créditos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Ciencias Básicas e Ingeniería Térmica</td>
                                <td class="p-3 text-center">10</td>
                                <td class="p-3 text-center">320 hrs</td>
                                <td class="p-3 text-center">160 hrs</td>
                                <td class="p-3 text-center font-bold">60</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Diseño Assistido CAD/CAM/CAE y Aerodinámica</td>
                                <td class="p-3 text-center">14</td>
                                <td class="p-3 text-center">350 hrs</td>
                                <td class="p-3 text-center">450 hrs</td>
                                <td class="p-3 text-center font-bold">115</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Electromovilidad, Baterías y Autonomía</td>
                                <td class="p-3 text-center">12</td>
                                <td class="p-3 text-center">300 hrs</td>
                                <td class="p-3 text-center">380 hrs</td>
                                <td class="p-3 text-center font-bold">95</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Manufactura Avanzada y Gestión Industrial</td>
                                <td class="p-3 text-center">8</td>
                                <td class="p-3 text-center">182 hrs</td>
                                <td class="p-3 text-center">130 hrs</td>
                                <td class="p-3 text-center font-bold">50</td>
                            </tr>
                            <tr class="bg-red-50 font-bold text-unp-burgundy">
                                <td class="p-3">TOTAL GENERAL DEL PROGRAMA</td>
                                <td class="p-3 text-center">44 Materias</td>
                                <td class="p-3 text-center">1,152 hrs</td>
                                <td class="p-3 text-center">1,120 hrs</td>
                                <td class="p-3 text-center text-unp-navy">320 SATCA</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Listado por Semestres Automotriz -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="bg-unp-navy text-white text-[10px] font-bold px-2.5 py-1 rounded">Semestre I</span>
                    <ul class="mt-3 space-y-2 text-xs text-slate-700">
                        <li class="border-b pb-1"><strong>Cálculo Vectorial</strong></li>
                        <li class="border-b pb-1"><strong>Álgebra Lineal Aplicada</strong></li>
                        <li class="border-b pb-1"><strong>Física y Estática</strong></li>
                        <li class="border-b pb-1"><strong>Dibujo Técnico y Modelado CAD I</strong></li>
                    </ul>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="bg-unp-navy text-white text-[10px] font-bold px-2.5 py-1 rounded">Semestre II</span>
                    <ul class="mt-3 space-y-2 text-xs text-slate-700">
                        <li class="border-b pb-1"><strong>Mecánica de Materiales</strong></li>
                        <li class="border-b pb-1"><strong>Termodinámica Técnica</strong></li>
                        <li class="border-b pb-1"><strong>Modelado 3D Avanzado (Catia/SolidWorks)</strong></li>
                        <li class="border-b pb-1"><strong>Sistemas Eléctricos Automotrices</strong></li>
                    </ul>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="bg-unp-navy text-white text-[10px] font-bold px-2.5 py-1 rounded">Semestre III</span>
                    <ul class="mt-3 space-y-2 text-xs text-slate-700">
                        <li class="border-b pb-1"><strong>Dinámica Vehicular y Chasis</strong></li>
                        <li class="border-b pb-1"><strong>Sistemas de Propulsión Eléctrica</strong></li>
                        <li class="border-b pb-1"><strong>Simulación Aerodinámica (CFD)</strong></li>
                        <li class="border-b pb-1"><strong>Electrónica de Potencia Automotriz</strong></li>
                    </ul>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="bg-unp-navy text-white text-[10px] font-bold px-2.5 py-1 rounded">Semestre IV</span>
                    <ul class="mt-3 space-y-2 text-xs text-slate-700">
                        <li class="border-b pb-1"><strong>Diseño de Carrocerías y Estructuras</strong></li>
                        <li class="border-b pb-1"><strong>Sensores y Conducción Autónoma</strong></li>
                        <li class="border-b pb-1"><strong>Manufactura Robótica de Monoplazas</strong></li>
                        <li class="border-b pb-1"><strong>Proyecto Integrador de Diseño UNP</strong></li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- HOJA 3: PLAN DE ESTUDIOS - INGENIERÍA EN ENERGÍAS RENOVABLES (DEDICADA) -->
        <section id="plan-renovables" class="page-section max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-emerald-900 text-white rounded-2xl p-8 mb-8 shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="bg-amber-400 text-slate-950 font-bold text-xs px-3 py-1 rounded-full uppercase">Ingeniería Sustentable</span>
                    <h1 class="text-3xl font-extrabold mt-2">Plan de Estudios: Ingeniería en Energías Renovables</h1>
                    <p class="text-slate-200 text-xs md:text-sm mt-1">Duración: 8 Semestres | Sistema SATCA | Prácticas en Redes Eléctricas y Plantas Solares</p>
                </div>
                <button onclick="navigateTo('plan-automotriz')" class="bg-emerald-950 hover:bg-black text-slate-200 font-semibold px-4 py-2 rounded-xl text-xs border border-emerald-800 self-start md:self-auto">
                    Ver Ing. en Diseño Automotriz <i class="fa-solid fa-arrow-right ml-1"></i>
                </button>
            </div>

            <!-- Tabla Resumen Créditos Renovables -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm mb-8">
                <h2 class="text-lg font-bold text-emerald-900 mb-4 flex items-center">
                    <i class="fa-solid fa-table-list mr-2 text-emerald-600"></i> Desglose Curricular y Créditos SATCA
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold uppercase border-b border-slate-200">
                                <th class="p-3">Área de Conocimiento</th>
                                <th class="p-3 text-center">Asignaturas</th>
                                <th class="p-3 text-center">Horas Teóricas</th>
                                <th class="p-3 text-center">Horas Prácticas / Taller</th>
                                <th class="p-3 text-center">Créditos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Termodinámica y Transferencia de Calor</td>
                                <td class="p-3 text-center">10</td>
                                <td class="p-3 text-center">320 hrs</td>
                                <td class="p-3 text-center">160 hrs</td>
                                <td class="p-3 text-center font-bold">65</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Energía Solar Fotovoltaica, Térmica y Radiación</td>
                                <td class="p-3 text-center">12</td>
                                <td class="p-3 text-center">288 hrs</td>
                                <td class="p-3 text-center">384 hrs</td>
                                <td class="p-3 text-center font-bold">100</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Energía Eólica, Hidráulica, Biomasa y Geotermia</td>
                                <td class="p-3 text-center">12</td>
                                <td class="p-3 text-center">288 hrs</td>
                                <td class="p-3 text-center">320 hrs</td>
                                <td class="p-3 text-center font-bold">95</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Redes Eléctricas Inteligentes (Smart Grids) e Hidrógeno</td>
                                <td class="p-3 text-center">10</td>
                                <td class="p-3 text-center">256 hrs</td>
                                <td class="p-3 text-center">256 hrs</td>
                                <td class="p-3 text-center font-bold">60</td>
                            </tr>
                            <tr class="bg-emerald-50 font-bold text-emerald-900">
                                <td class="p-3">TOTAL GENERAL DEL PROGRAMA</td>
                                <td class="p-3 text-center">44 Materias</td>
                                <td class="p-3 text-center">1,152 hrs</td>
                                <td class="p-3 text-center">1,120 hrs</td>
                                <td class="p-3 text-center text-unp-navy">320 SATCA</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Listado por Semestres Renovables -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="bg-emerald-800 text-white text-[10px] font-bold px-2.5 py-1 rounded">Semestre I</span>
                    <ul class="mt-3 space-y-2 text-xs text-slate-700">
                        <li class="border-b pb-1"><strong>Cálculo Multivariable</strong></li>
                        <li class="border-b pb-1"><strong>Química de Materiales Energéticos</strong></li>
                        <li class="border-b pb-1"><strong>Física y Electromagnetismo</strong></li>
                        <li class="border-b pb-1"><strong>Ecología y Desarrollo Sustentable</strong></li>
                    </ul>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="bg-emerald-800 text-white text-[10px] font-bold px-2.5 py-1 rounded">Semestre II</span>
                    <ul class="mt-3 space-y-2 text-xs text-slate-700">
                        <li class="border-b pb-1"><strong>Circuitos Eléctricos Avanzados</strong></li>
                        <li class="border-b pb-1"><strong>Sistemas Fotovoltaicos Conectados a Red</strong></li>
                        <li class="border-b pb-1"><strong>Mecánica de Fluidos e Hidráulica</strong></li>
                        <li class="border-b pb-1"><strong>Evaluación del Recurso Eólico y Solar</strong></li>
                    </ul>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="bg-emerald-800 text-white text-[10px] font-bold px-2.5 py-1 rounded">Semestre III</span>
                    <ul class="mt-3 space-y-2 text-xs text-slate-700">
                        <li class="border-b pb-1"><strong>Diseño e Instalación de Aerogeneradores</strong></li>
                        <li class="border-b pb-1"><strong>Almacenamiento en Baterías e Hidrógeno Verde</strong></li>
                        <li class="border-b pb-1"><strong>Microredes Aisladas Rurales</strong></li>
                        <li class="border-b pb-1"><strong>Legislación y Mercados Energéticos</strong></li>
                    </ul>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="bg-emerald-800 text-white text-[10px] font-bold px-2.5 py-1 rounded">Semestre IV</span>
                    <ul class="mt-3 space-y-2 text-xs text-slate-700">
                        <li class="border-b pb-1"><strong>Auditoría y Eficiencia Energética ISO 50001</strong></li>
                        <li class="border-b pb-1"><strong>Mantenimiento de Plantas Fotovoltaicas</strong></li>
                        <li class="border-b pb-1"><strong>Evaluación de Impacto Ambiental</strong></li>
                        <li class="border-b pb-1"><strong>Proyecto Integrador Renovable UNP</strong></li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- HOJA 4: PERFIL DE EGRESO (DEDICADA) -->
        <section id="perfil-egreso" class="page-section max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="text-center max-w-3xl mx-auto mb-10">
                <span class="text-unp-burgundy font-bold uppercase tracking-wider text-xs bg-red-50 px-3 py-1 rounded-full">Competencias Profesionales</span>
                <h1 class="text-3xl font-extrabold text-unp-navy mt-2">Perfil de Egreso UNP</h1>
                <p class="text-slate-600 mt-2 text-xs md:text-sm">El egresado de la Universidad UNP es un especialista de alto impacto tecnológico orientado a la solución de problemas reales.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 bg-unp-navy text-white rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <h2 class="text-xl font-bold text-unp-navy mb-3">Egresado: Ing. en Diseño Automotriz</h2>
                    <ul class="space-y-3 text-xs text-slate-700">
                        <li class="flex items-start"><i class="fa-solid fa-check text-unp-burgundy mt-0.5 mr-2"></i> Domina software especializado CAD/CAM/CAE (CATIA, ANSYS, SolidWorks) para modelado de plataformas vehiculares.</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-unp-burgundy mt-0.5 mr-2"></i> Diseña e integra trenes motrices eléctricos, bancos de baterías y sistemas de gestión térmica (BMS).</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-unp-burgundy mt-0.5 mr-2"></i> Aplica normativas internacionales de seguridad vial y pruebas de choque (NCAP/SAE).</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-unp-burgundy mt-0.5 mr-2"></i> Coordina líneas de ensamblaje automatizadas con celdas de manufactura robótica.</li>
                    </ul>
                </div>

                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 bg-emerald-700 text-white rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-leaf"></i>
                    </div>
                    <h2 class="text-xl font-bold text-emerald-900 mb-3">Egresado: Ing. en Energías Renovables</h2>
                    <ul class="space-y-3 text-xs text-slate-700">
                        <li class="flex items-start"><i class="fa-solid fa-check text-emerald-600 mt-0.5 mr-2"></i> Diseña, dimensiona y supervisa parques solares fotovoltaicos y centrales eólicas de mediana y gran escala.</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-emerald-600 mt-0.5 mr-2"></i> Implementa redes eléctricas inteligentes (Smart Grids) e integra esquemas de generación distribuida.</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-emerald-600 mt-0.5 mr-2"></i> Evalúa proyectos de eficiencia energética según la norma ISO 50001 y dicta diagnósticos de carbono neutralidad.</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-emerald-600 mt-0.5 mr-2"></i> Desarrolla soluciones de hidrógeno verde y almacenamiento en baterías para la industria.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- HOJA 5: CAMPO LABORAL Y COMPROMISO SOCIAL (DEDICADA) -->
        <section id="campo-laboral" class="page-section max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="text-center max-w-3xl mx-auto mb-10">
                <span class="text-amber-600 font-bold uppercase tracking-wider text-xs bg-amber-50 px-3 py-1 rounded-full">Mercado e Impacto Social</span>
                <h1 class="text-3xl font-extrabold text-unp-navy mt-2">Campo Laboral & Compromiso Social</h1>
                <p class="text-slate-600 mt-2 text-xs md:text-sm">Datos reales sobre la demanda industrial y nuestro modelo de vinculación comunitaria.</p>
            </div>

            <!-- Bloque de Compromiso Social -->
            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm mb-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-7 space-y-4">
                        <span class="text-unp-burgundy font-bold text-xs uppercase tracking-wider">Compromiso Ético UNP</span>
                        <h2 class="text-2xl font-bold text-unp-navy">Ingeniería con Sentido Humano y Datos Reales</h2>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                            Según reportes de la <strong>Secretaría de Energía (SENER)</strong> y la <strong>Asociación Mexicana de la Industria Automotriz (AMIA)</strong>, la región requiere la incorporación de más de 45,000 técnicos especializados en electrificación vehicular y energías sustentables para el año 2030.
                        </p>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                            La <strong>Universidad UNP</strong> exige que cada estudiante complete un proyecto de titulación con impacto comunitario directo (p. ej., bombas de agua solares para campos agrícolas o conversión eléctrica de vehículos de servicio público local).
                        </p>
                    </div>

                    <div class="lg:col-span-5 bg-unp-navy text-white p-6 rounded-2xl space-y-4">
                        <h3 class="font-bold text-sm text-amber-400 border-b border-slate-700 pb-2">Indicadores Laborales UNP</h3>
                        <div class="space-y-3 text-xs">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span>Tasa de Empleabilidad (6 meses)</span>
                                    <span class="font-bold text-amber-400">95.8%</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-1.5"><div class="bg-amber-400 h-1.5 rounded-full" style="width: 95.8%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span>Salario Promedio de Ingreso</span>
                                    <span class="font-bold text-amber-400">$29,500 MXN</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-1.5"><div class="bg-amber-400 h-1.5 rounded-full" style="width: 90%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sectores clave de inserción -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                    <i class="fa-solid fa-industry text-2xl text-unp-navy mb-2"></i>
                    <h3 class="font-bold text-sm text-slate-800 mb-1">Industria Automotriz OEM</h3>
                    <p class="text-xs text-slate-600">Ensambladoras globales, centros de R&D de autopartes Tier 1 y plantas de baterías.</p>
                </div>
                <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                    <i class="fa-solid fa-bolt text-2xl text-amber-600 mb-2"></i>
                    <h3 class="font-bold text-sm text-slate-800 mb-1">Sector Energético & CFE</h3>
                    <p class="text-xs text-slate-600">Parques fotovoltaicos y eólicos, consultoría ambiental y redes de alta tensión.</p>
                </div>
                <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                    <i class="fa-solid fa-handshake-angle text-2xl text-emerald-700 mb-2"></i>
                    <h3 class="font-bold text-sm text-slate-800 mb-1">Proyectos Comunitarios</h3>
                    <p class="text-xs text-slate-600">ONGs, organismos gubernamentales de electrificación rural y emprendimientos sustentables.</p>
                </div>
            </div>
        </section>

        <!-- HOJA 6: REQUISITOS DE INGRESO (DEDICADA) -->
        <section id="requisitos" class="page-section max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm max-w-4xl mx-auto">
                <div class="text-center border-b border-slate-100 pb-6 mb-6">
                    <span class="bg-blue-50 text-unp-navy text-xs font-bold px-3 py-1 rounded-full uppercase">Admisiones UNP 2026</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-unp-navy mt-2">Requisitos de Ingreso e Instructivo</h1>
                    <p class="text-slate-600 text-xs md:text-sm mt-1">Conoce el procedimiento paso a paso para formar parte de la comunidad UNP.</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <h2 class="text-sm font-bold text-unp-burgundy uppercase tracking-wider mb-3"><i class="fa-solid fa-list-ol mr-2"></i> Proceso de Registro de Admisión</h2>
                        <ol class="list-decimal list-inside space-y-2 text-xs text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <li><strong>Pre-registro en línea:</strong> Inicia sesión en el portal y genera tu ficha de examen.</li>
                            <li><strong>Carga de expedientes:</strong> Adjunta tus documentos escaneados en formato PDF.</li>
                            <li><strong>Examen EXANI-II:</strong> Presenta la prueba de diagnóstico de habilidades matemáticas y físicas.</li>
                            <li><strong>Entrevista Vocacional:</strong> Entrevista breve con la dirección de la carrera seleccionada.</li>
                            <li><strong>Inscripción Definitiva:</strong> Efectúa el pago del primer semestre y recibe tu matrícula oficial.</li>
                        </ol>
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-unp-navy uppercase tracking-wider mb-3"><i class="fa-solid fa-folder-open mr-2"></i> Lista de Documentos Requeridos</h2>
                        <ul class="space-y-2 text-xs text-slate-700">
                            <li class="flex items-center"><i class="fa-solid fa-file-check text-emerald-600 mr-2"></i> Certificado de Bachillerato / Preparatoria concluida (Promedio mínimo 8.0).</li>
                            <li class="flex items-center"><i class="fa-solid fa-file-check text-emerald-600 mr-2"></i> Acta de Nacimiento actualizada.</li>
                            <li class="flex items-center"><i class="fa-solid fa-file-check text-emerald-600 mr-2"></i> Clave Única de Registro de Población (CURP) formato reciente.</li>
                            <li class="flex items-center"><i class="fa-solid fa-file-check text-emerald-600 mr-2"></i> Comprobante de domicilio (no mayor a 3 meses).</li>
                            <li class="flex items-center"><i class="fa-solid fa-file-check text-emerald-600 mr-2"></i> 4 Fotografías tamaño infantil a color en fondo blanco.</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 text-center pt-4 border-t border-slate-100">
                    <button onclick="toggleAuthModal()" class="bg-unp-burgundy hover:bg-unp-burgundyHover text-white font-bold px-6 py-2.5 rounded-xl text-xs shadow transition">
                        <i class="fa-solid fa-user-plus mr-2"></i> Iniciar Solicitud de Ficha de Admisión
                    </button>
                </div>
            </div>
        </section>

        <!-- HOJA 7: COSTOS, ARANCELES Y BECAS (DEDICADA) -->
        <section id="costos" class="page-section max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm max-w-4xl mx-auto">
                <div class="text-center border-b border-slate-100 pb-6 mb-6">
                    <span class="bg-amber-50 text-amber-700 text-xs font-bold px-3 py-1 rounded-full uppercase">Aranceles Transparentes</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-unp-navy mt-2">Tabla de Costos y Programa de Becas</h1>
                    <p class="text-slate-600 text-xs md:text-sm mt-1">Cuotas vigentes para el periodo lectivo 2026 de la Universidad UNP.</p>
                </div>

                <div class="overflow-x-auto mb-8">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-unp-navy text-white font-bold uppercase">
                                <th class="p-3.5 rounded-tl-xl">Concepto Académico</th>
                                <th class="p-3.5">Frecuencia</th>
                                <th class="p-3.5 rounded-tr-xl">Monto Oficial (MXN)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr>
                                <td class="p-3.5 font-semibold text-slate-800">Ficha de Admisión y Examen EXANI-II</td>
                                <td class="p-3.5 text-slate-500">Pago único inicial</td>
                                <td class="p-3.5 font-bold text-unp-navy">$700.00</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-semibold text-slate-800">Inscripción Semestral</td>
                                <td class="p-3.5 text-slate-500">Por semestre</td>
                                <td class="p-3.5 font-bold text-unp-navy">$2,600.00</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-semibold text-slate-800">Colegiatura Mensual (4 pagos por semestre)</td>
                                <td class="p-3.5 text-slate-500">Mensual</td>
                                <td class="p-3.5 font-bold text-unp-navy">$3,200.00</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-semibold text-slate-800">Seguro Estudiantil y Laboratorios CAD/CAM/Solar</td>
                                <td class="p-3.5 text-slate-500">Anual</td>
                                <td class="p-3.5 font-bold text-unp-navy">$500.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-amber-50 p-5 rounded-2xl border border-amber-200">
                    <h3 class="font-bold text-sm text-amber-900 mb-2 flex items-center">
                        <i class="fa-solid fa-award text-amber-600 mr-2"></i> Programa de Becas e Incentivos UNP
                    </h3>
                    <p class="text-xs text-amber-800 leading-relaxed mb-3">
                        La UNP otorga apoyos financieros del 30%, 50% y hasta el 100% de la colegiatura para estudiantes con excelencia académica (promedio igual o mayor a 9.2) o que provengan de zonas vulnerables.
                    </p>
                </div>
            </div>
        </section>

        <!-- HOJA 8: TESTIMONIOS Y SPOTS (DEDICADA) -->
        <section id="testimonios" class="page-section max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-unp-navy text-white rounded-2xl p-8 shadow-xl max-w-4xl mx-auto">
                <div class="text-center mb-8 border-b border-slate-700 pb-6">
                    <span class="bg-amber-400 text-slate-950 font-bold text-xs px-3 py-1 rounded-full uppercase">Multimedia UNP</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-white mt-2">Testimonios & Spots Oficiales</h1>
                    <p class="text-slate-300 text-xs md:text-sm mt-1">Conoce la experiencia real de nuestros estudiantes y las instalaciones de la UNP.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-slate-800 p-4 rounded-xl border border-slate-700">
                        <h3 class="font-bold text-xs mb-3 text-slate-200 flex items-center">
                            <i class="fa-solid fa-film text-amber-400 mr-2"></i> Spot Promocional: "Comunidad UNP 2026"
                        </h3>
                        <div class="aspect-video bg-black rounded-lg overflow-hidden">
                            <video controls class="w-full h-full object-cover rounded-lg" poster="https://placehold.co/800x450/1b365d/ffffff?text=Spot+Promocional+UNP">
                                <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4" type="video/mp4">
                            </video>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-3">
                            <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Hipervínculo: <a href="https://www.youtube.com" target="_blank" class="text-amber-400 underline hover:text-amber-300">Ver en HD en nuestro canal de YouTube</a>
                        </p>
                    </div>

                    <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-xs mb-3 text-slate-200 flex items-center">
                                <i class="fa-solid fa-podcast text-amber-400 mr-2"></i> Testimonio de Egresado (Audio)
                            </h3>
                            <div class="flex items-center space-x-3 bg-slate-900 p-3 rounded-lg border border-slate-700 mb-4">
                                <img src="https://placehold.co/80x80/8b1e23/ffffff?text=UNP" alt="Egresado" class="w-12 h-12 rounded-full border border-amber-400">
                                <div>
                                    <h4 class="font-bold text-xs text-white">Mateo Fernández</h4>
                                    <p class="text-[10px] text-slate-400">Diseñador en Monoplaza Eléctrico UNP</p>
                                </div>
                            </div>
                            <audio controls class="w-full">
                                <source src="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3" type="audio/mpeg">
                            </audio>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-3">
                            <i class="fa-solid fa-download mr-1"></i> Hipervínculo: <a href="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3" target="_blank" class="text-amber-400 underline hover:text-amber-300">Descargar Testimonio MP3</a>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- HOJA 9: REDES SOCIALES OFICIALES (DEDICADA) -->
        <section id="redes-sociales" class="page-section max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm max-w-4xl mx-auto text-center">
                <span class="bg-red-50 text-unp-burgundy text-xs font-bold px-3 py-1 rounded-full uppercase">Canales Verificados</span>
                <h1 class="text-2xl md:text-3xl font-extrabold text-unp-navy mt-2">Redes Sociales Oficiales UNP</h1>
                <p class="text-slate-600 text-xs md:text-sm mt-1 mb-8">Haz clic en cualquier plataforma para acceder directamente a la comunidad digital oficial:</p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <a href="https://facebook.com" target="_blank" class="group block bg-slate-50 p-5 rounded-2xl border border-slate-200 hover:border-unp-navy hover:shadow-lg transition">
                        <img src="https://placehold.co/100x100/1877F2/ffffff?text=FB" alt="Facebook UNP" class="w-14 h-14 mx-auto rounded-xl mb-3 group-hover:scale-105 transition-transform">
                        <span class="text-xs font-bold text-slate-800 block">Facebook</span>
                        <span class="text-[10px] text-slate-500">@UniversidadUNP</span>
                    </a>
                    <a href="https://instagram.com" target="_blank" class="group block bg-slate-50 p-5 rounded-2xl border border-slate-200 hover:border-unp-navy hover:shadow-lg transition">
                        <img src="https://placehold.co/100x100/E4405F/ffffff?text=IG" alt="Instagram UNP" class="w-14 h-14 mx-auto rounded-xl mb-3 group-hover:scale-105 transition-transform">
                        <span class="text-xs font-bold text-slate-800 block">Instagram</span>
                        <span class="text-[10px] text-slate-500">@ComunidadUNP</span>
                    </a>
                    <a href="https://linkedin.com" target="_blank" class="group block bg-slate-50 p-5 rounded-2xl border border-slate-200 hover:border-unp-navy hover:shadow-lg transition">
                        <img src="https://placehold.co/100x100/0A66C2/ffffff?text=IN" alt="LinkedIn UNP" class="w-14 h-14 mx-auto rounded-xl mb-3 group-hover:scale-105 transition-transform">
                        <span class="text-xs font-bold text-slate-800 block">LinkedIn</span>
                        <span class="text-[10px] text-slate-500">Universidad UNP</span>
                    </a>
                    <a href="https://youtube.com" target="_blank" class="group block bg-slate-50 p-5 rounded-2xl border border-slate-200 hover:border-unp-navy hover:shadow-lg transition">
                        <img src="https://placehold.co/100x100/FF0000/ffffff?text=YT" alt="YouTube UNP" class="w-14 h-14 mx-auto rounded-xl mb-3 group-hover:scale-105 transition-transform">
                        <span class="text-xs font-bold text-slate-800 block">YouTube</span>
                        <span class="text-[10px] text-slate-500">UNP TV Oficial</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- HOJA: TEST VOCACIONAL (60 preguntas, general) -->
        <section id="test-vocacional" class="page-section max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
                <div class="text-center mb-8 border-b border-slate-100 pb-6">
                    <span class="bg-amber-50 text-amber-700 text-xs font-bold px-3 py-1 rounded-full uppercase">Orientación Vocacional</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-unp-navy mt-2">Test Vocacional: descubre tu perfil profesional</h1>
                    <p class="text-slate-600 text-xs md:text-sm mt-2 max-w-2xl mx-auto">
                        Responde <strong>60 preguntas</strong> sobre actividades que te gustaría realizar. No hay respuestas correctas ni incorrectas: contesta con sinceridad. Al final conocerás tus áreas de mayor afinidad y carreras que se estudian en México relacionadas con ellas.
                    </p>
                    <!-- Barra de progreso -->
                    <div class="mt-4 max-w-md mx-auto">
                        <div class="flex justify-between text-[11px] text-slate-500 mb-1">
                            <span id="voc-page-text">Parte 1 de 6</span>
                            <span id="voc-progress-text">0 de 60 respondidas</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2"><div id="voc-progress-bar" class="bg-unp-burgundy h-2 rounded-full transition-all duration-300" style="width: 0%"></div></div>
                    </div>
                </div>

                <form id="form-vocacional" onsubmit="procesarVocacional(event)" class="text-xs md:text-sm" novalidate>
                    <!-- Nombre -->
                    <div id="voc-nombre-box" class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6">
                        <label class="block font-semibold text-slate-800 mb-2" for="voc-nombre">Tu nombre completo</label>
                        <input type="text" id="voc-nombre" name="nombre_aspirante" maxlength="150" placeholder="Ej. Ana Lucía Morales" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-unp-navy">
                    </div>

                    <p class="font-semibold text-slate-800 mb-3">¿Qué tanto te gustaría o te gusta realizar cada actividad?</p>

                    <!-- Las 60 preguntas se generan por JavaScript, 10 por página -->
                    <div id="voc-questions" class="space-y-3"></div>

                    <p id="voc-error" class="hidden text-red-700 font-semibold text-xs mt-4 text-center"></p>

                    <div class="pt-6 flex flex-wrap justify-center gap-3">
                        <button type="button" id="voc-btn-prev" onclick="vocCambiarPagina(-1)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-6 py-3 rounded-xl transition text-xs border border-slate-200">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Anterior
                        </button>
                        <button type="button" id="voc-btn-next" onclick="vocCambiarPagina(1)" class="bg-unp-navy hover:bg-unp-navyLight text-white font-bold px-8 py-3 rounded-xl shadow transition text-xs">
                            Siguiente <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                        <button type="submit" id="voc-btn-submit" class="hidden bg-unp-burgundy hover:bg-unp-burgundyHover text-white font-bold px-8 py-3 rounded-xl shadow-lg transition text-xs">
                            <i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Ver mi resultado
                        </button>
                        <button type="button" onclick="reiniciarVocacional()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-6 py-3 rounded-xl transition text-xs border border-slate-200">
                            <i class="fa-solid fa-rotate-left mr-2"></i> Reiniciar
                        </button>
                    </div>
                </form>

                <!-- Resultado -->
                <div id="voc-result" class="hidden mt-8">
                    <div class="text-center mb-5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tus áreas de mayor afinidad</span>
                        <h3 id="voc-result-title" class="text-xl md:text-2xl font-extrabold text-unp-navy mt-1"></h3>
                    </div>

                    <div id="voc-result-cards" class="space-y-4"></div>

                    <div class="max-w-xl mx-auto mt-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <p class="font-semibold text-slate-700 text-xs mb-3">Tu puntaje en cada área</p>
                        <div id="voc-bars" class="space-y-3 text-xs"></div>
                    </div>

                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <button type="button" onclick="toggleAuthModal()" class="bg-unp-burgundy hover:bg-unp-burgundyHover text-white font-bold px-5 py-2.5 rounded-xl text-xs shadow transition">
                            <i class="fa-solid fa-user-plus mr-1.5"></i> Solicitar ficha de admisión
                        </button>
                        <button type="button" onclick="reiniciarVocacional()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-5 py-2.5 rounded-xl transition text-xs border border-slate-200">
                            <i class="fa-solid fa-rotate-left mr-1.5"></i> Repetir el test
                        </button>
                    </div>
                    <p id="voc-save-status" class="text-[11px] mt-4 text-slate-500 text-center"></p>
                    <p class="text-[10px] text-slate-400 mt-2 text-center">Este test es una guía orientativa y no sustituye una asesoría vocacional profesional. Las carreras mencionadas son ejemplos y no corresponden únicamente a la oferta de una institución.</p>
                </div>
            </div>
        </section>

        <!-- HOJA 10: FORMULARIO DE SATISFACCIÓN -->
        <section id="satisfaccion" class="page-section max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
                <div class="text-center mb-8 border-b border-slate-100 pb-6">
                    <span class="bg-red-50 text-unp-burgundy text-xs font-bold px-3 py-1 rounded-full uppercase">Evaluación de Calidad UNP</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-unp-navy mt-2">Formulario de Satisfacción del Aspirante</h1>
                    <p class="text-slate-600 text-xs md:text-sm mt-2">
                        Responde exactamente las <strong>10 preguntas</strong> con opción múltiple (3 opciones cada una).
                    </p>
                </div>

                <form id="form-satisfaccion" onsubmit="procesarEncuesta(event)" class="space-y-6 text-xs md:text-sm">
                    <!-- Pregunta 1 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">1. ¿Qué tan clara es la Misión y Visión de la Universidad UNP?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p1" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Muy clara y con un fuerte compromiso social</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p1" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Moderadamente clara</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p1" value="1" class="text-unp-burgundy"> <span class="ml-2">C) Confusa o insuficiente</span></label>
                        </div>
                    </div>
                    <!-- Pregunta 2 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">2. ¿Cómo evalúas el Plan de Estudios de Ingeniería en Diseño Automotriz?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p2" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Innovador y adaptado a electromovilidad</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p2" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Aceptable</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p2" value="1" class="text-unp-burgundy"> <span class="ml-2">C) Poco actualizado</span></label>
                        </div>
                    </div>
                    <!-- Pregunta 3 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">3. ¿Qué opinas de la propuesta de Ingeniería en Energías Renovables?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p3" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Indispensable para la transición ecológica</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p3" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Interesante</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p3" value="1" class="text-unp-burgundy"> <span class="ml-2">C) Poco relevante</span></label>
                        </div>
                    </div>
                    <!-- Pregunta 4 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">4. ¿La tabla de aranceles y programa de becas es transparente?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p4" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Totalmente clara y justa</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p4" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Parcialmente clara</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p4" value="1" class="text-unp-burgundy"> <span class="ml-2">C) Dificultad para entender las cuotas</span></label>
                        </div>
                    </div>
                    <!-- Pregunta 5 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">5. ¿Qué te parece la separación independiente de cada sección en el sitio?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p5" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Excelente, facilita encontrar la información</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p5" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Aceptable</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p5" value="1" class="text-unp-burgundy"> <span class="ml-2">C) Prefiero todo en una sola página</span></label>
                        </div>
                    </div>
                    <!-- Pregunta 6 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">6. ¿Los recursos multimedia (audio y spot) te aportaron valor?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p6" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Sí, aumentaron mi confianza institucional</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p6" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Neutral</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p6" value="1" class="text-unp-burgundy"> <span class="ml-2">C) Poco útiles</span></label>
                        </div>
                    </div>
                    <!-- Pregunta 7 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">7. ¿Consideras valioso el requisito de proyectos comunitarios para titulación?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p7" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Fundamental para el sentido ético del ingeniero</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p7" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Indiferente</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p7" value="1" class="text-unp-burgundy"> <span class="ml-2">C) Innecesario</span></label>
                        </div>
                    </div>
                    <!-- Pregunta 8 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">8. ¿La navegación en las redes sociales mediante imágenes con hipervínculos es adecuada?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p8" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Muy práctica y directa</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p8" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Regular</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p8" value="1" class="text-unp-burgundy"> <span class="ml-2">C) Difícil de usar</span></label>
                        </div>
                    </div>
                    <!-- Pregunta 9 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">9. ¿El proceso simulado para solicitar ficha de examen de admisión te resultó sencillo?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p9" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Muy ágil e intuitivo</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p9" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Normal</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p9" value="1" class="text-unp-burgundy"> <span class="ml-2">C) Complicado</span></label>
                        </div>
                    </div>
                    <!-- Pregunta 10 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block font-semibold text-slate-800 mb-2">10. ¿Recomendarías a la Universidad UNP a un estudiante interesado en ingeniería?</label>
                        <div class="space-y-1">
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p10" value="3" required class="text-unp-burgundy"> <span class="ml-2">A) Totalmente recomendable</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p10" value="2" class="text-unp-burgundy"> <span class="ml-2">B) Podría recomendarla</span></label>
                            <label class="flex items-center p-2 rounded hover:bg-white cursor-pointer"><input type="radio" name="p10" value="1" class="text-unp-burgundy"> <span class="ml-2">C) No la recomendaría</span></label>
                        </div>
                    </div>

                    <div class="text-center pt-4">
                        <button type="submit" class="bg-unp-burgundy hover:bg-unp-burgundyHover text-white font-bold px-8 py-3 rounded-xl shadow-lg transition text-xs">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Enviar Resultados de Encuesta
                        </button>
                    </div>
                </form>

                <div id="survey-result" class="hidden mt-6 p-6 bg-emerald-50 rounded-2xl border border-emerald-200 text-center">
                    <div class="w-12 h-12 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xl mx-auto mb-2">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h3 class="text-lg font-bold text-emerald-900">¡Encuesta Recibida Exitosamente!</h3>
                    <p class="text-xs text-emerald-700 mt-1" id="survey-score-text">Puntaje total: 30 de 30 puntos.</p>
                    <p id="survey-save-status" class="text-[11px] mt-2 text-slate-500"></p>
                </div>
            </div>
        </section>

    </main>

    <!-- MODAL DE INICIO DE SESIÓN / REGISTRO CONECTADO A BASE DE DATOS -->
    <div id="auth-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200 relative">
            <button onclick="toggleAuthModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-2">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <div class="bg-unp-navy text-white p-6 text-center">
                <i class="fa-solid fa-user-shield text-3xl text-amber-400 mb-2"></i>
                <h3 class="text-xl font-bold">Portal del Aspirante UNP</h3>
                <p class="text-xs text-slate-300">Guarda tus datos directo en la Base de Datos</p>
            </div>

            <!-- Este formulario ahora envía los datos por POST a este mismo archivo PHP -->
            <form method="POST" class="p-6 space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nombre Completo</label>
                    <input type="text" name="nombre" required placeholder="Ej. Ana Lucía Morales" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-unp-navy">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">CURP</label>
                    <input type="text" name="curp" required maxlength="18" placeholder="Ej. MOLA050612MPLRNN09" style="text-transform: uppercase" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-unp-navy">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Carrera Seleccionada</label>
                    <select name="carrera" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-unp-navy">
                        <option value="Ingeniería en Diseño Automotriz">Ingeniería en Diseño Automotriz</option>
                        <option value="Ingeniería en Energías Renovables">Ingeniería en Energías Renovables</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Correo Electrónico</label>
                    <input type="email" name="correo" required placeholder="aspirante@unp.edu.mx" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-unp-navy">
                </div>

                <button type="submit" name="registrar_aspirante" class="w-full bg-unp-burgundy hover:bg-unp-burgundyHover text-white font-bold py-2.5 rounded-xl shadow transition">
                    Guardar Registro en la Nube
                </button>
            </form>
        </div>
    </div>

    <!-- FOOTER INSTITUCIONAL -->
    <footer class="bg-unp-navy text-white border-t border-slate-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-xs">
                <div>
                    <span class="font-black text-lg text-white block mb-2">UNIVERSIDAD UNP</span>
                    <p class="text-slate-300 leading-relaxed">
                        Institución universitaria privada de excelencia enfocada en la innovación automotriz y el desarrollo energético sustentable.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-amber-400 uppercase tracking-wider mb-2">Contacto Directo</h4>
                    <p class="text-slate-300 mb-1"><i class="fa-solid fa-location-dot mr-1"></i> Campus UNP Tecnológico, Edificio Central.</p>
                    <p class="text-slate-300 mb-1"><i class="fa-solid fa-phone mr-1"></i> +52 (55) 8000-UNP1</p>
                    <p class="text-slate-300"><i class="fa-solid fa-envelope mr-1"></i> contacto@unp.edu.mx</p>
                </div>
                <div>
                    <h4 class="font-bold text-amber-400 uppercase tracking-wider mb-2">Enlaces Rápidos</h4>
                    <div class="grid grid-cols-2 gap-1 text-slate-300">
                        <a href="#" onclick="navigateTo('plan-automotriz'); return false;" class="hover:underline">• Plan Automotriz</a>
                        <a href="#" onclick="navigateTo('plan-renovables'); return false;" class="hover:underline">• Plan Renovables</a>
                        <a href="#" onclick="navigateTo('costos'); return false;" class="hover:underline">• Aranceles y Becas</a>
                        <a href="#" onclick="navigateTo('requisitos'); return false;" class="hover:underline">• Admisiones 2026</a>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-4 border-t border-slate-800 text-center text-[10px] text-slate-400">
                © 2026 Universidad UNP. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- LÓGICA JAVASCRIPT SPA -->
    <script>
        function navigateTo(sectionId) {
            document.querySelectorAll('.page-section').forEach(sec => sec.classList.remove('active'));
            const target = document.getElementById(sectionId);
            if (target) {
                target.classList.add('active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('text-unp-navy', 'bg-slate-100', 'font-bold');
            });
            const activeNav = document.getElementById(`nav-${sectionId}`);
            if (activeNav) {
                activeNav.classList.add('text-unp-navy', 'bg-slate-100', 'font-bold');
            }
        }

        function toggleMobileMenu() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        }

        function toggleAuthModal() {
            document.getElementById('auth-modal').classList.toggle('hidden');
        }

        // Envía datos por POST a este mismo archivo y muestra el estado del guardado
        function guardarEnBD(datos, idEstado, mensajeOk) {
            const el = document.getElementById(idEstado);
            el.className = 'text-[11px] mt-4 text-slate-500';
            el.textContent = 'Guardando...';
            fetch(window.location.pathname, { method: 'POST', body: datos })
                .then(r => r.json())
                .then(j => {
                    if (j.ok) {
                        el.className = 'text-[11px] mt-4 text-emerald-700 font-semibold';
                        el.textContent = mensajeOk;
                    } else {
                        throw new Error('db');
                    }
                })
                .catch(() => {
                    el.className = 'text-[11px] mt-4 text-red-700 font-semibold';
                    el.textContent = 'No se pudo guardar en la base de datos.';
                });
        }

        // ===== TEST VOCACIONAL (60 preguntas, modelo de intereses de Holland / RIASEC) =====
        const VOC_AREAS = {
            R: {
                nombre: 'Técnico-Práctica', icono: 'fa-screwdriver-wrench', color: 'bg-orange-600', texto: 'text-orange-700', fondo: 'bg-orange-50 border-orange-200',
                desc: 'Te gusta lo concreto: construir, reparar, operar equipos y trabajar al aire libre o con las manos.',
                carreras: ['Ingeniería Civil', 'Ingeniería Mecánica / Mecatrónica', 'Ingeniería Industrial', 'Ingeniería Automotriz', 'Ingeniería Eléctrica y Electrónica', 'Agronomía y Agroindustrias', 'Medicina Veterinaria y Zootecnia', 'Ingeniería Forestal y Ambiental', 'Ingeniería Petrolera, Geología y Minas', 'Aviación y Ciencias Aeronáuticas', 'Energías Renovables'],
                preguntas: [
                    'Armar, reparar o desarmar aparatos, máquinas o motores.',
                    'Trabajar al aire libre, en campo, en una obra o en un taller.',
                    'Cultivar plantas o cuidar animales.',
                    'Usar herramientas, equipo pesado o maquinaria.',
                    'Construir cosas con mis manos (muebles, maquetas, instalaciones).',
                    'Conducir u operar vehículos, drones o maquinaria especial.',
                    'Encontrar y resolver fallas eléctricas, mecánicas o de plomería.',
                    'Practicar deportes o actividades físicas de aventura.',
                    'Interpretar planos, esquemas o diagramas técnicos.',
                    'Cuidar el medio ambiente trabajando directamente en la naturaleza.'
                ]
            },
            I: {
                nombre: 'Científico-Investigadora', icono: 'fa-flask', color: 'bg-sky-700', texto: 'text-sky-800', fondo: 'bg-sky-50 border-sky-200',
                desc: 'Te mueve la curiosidad: investigar, analizar, experimentar y entender cómo funcionan las cosas.',
                carreras: ['Medicina', 'Biología y Biotecnología', 'Química y Química Farmacéutica', 'Física y Matemáticas', 'Ciencias de la Computación / Sistemas', 'Ciencia de Datos e Inteligencia Artificial', 'Actuaría', 'Nutrición', 'Optometría y Odontología', 'Geografía y Ciencias de la Tierra', 'Ingeniería Biomédica'],
                preguntas: [
                    'Investigar por qué ocurren los fenómenos de la naturaleza.',
                    'Hacer experimentos en un laboratorio.',
                    'Resolver problemas de matemáticas o de lógica complejos.',
                    'Leer artículos o ver documentales de ciencia.',
                    'Analizar datos y descubrir patrones.',
                    'Aprender cómo funciona el cuerpo humano y por qué aparecen las enfermedades.',
                    'Programar o entender cómo funcionan las computadoras y el software.',
                    'Observar y estudiar plantas, animales o microorganismos.',
                    'Plantear hipótesis y comprobar si son ciertas.',
                    'Descubrir soluciones nuevas a problemas científicos o tecnológicos.'
                ]
            },
            A: {
                nombre: 'Artística-Creativa', icono: 'fa-palette', color: 'bg-fuchsia-700', texto: 'text-fuchsia-800', fondo: 'bg-fuchsia-50 border-fuchsia-200',
                desc: 'Tienes imaginación y sensibilidad: disfrutas crear, expresarte y darle forma a ideas originales.',
                carreras: ['Arquitectura', 'Diseño Gráfico y Diseño Industrial', 'Artes Visuales y Plásticas', 'Música y Artes Escénicas', 'Cine, Fotografía y Producción Audiovisual', 'Diseño de Modas y Textil', 'Animación y Diseño de Videojuegos', 'Literatura y Letras', 'Gastronomía', 'Ciencias de la Comunicación', 'Diseño de Interiores'],
                preguntas: [
                    'Dibujar, pintar o diseñar.',
                    'Escribir cuentos, poemas, guiones o canciones.',
                    'Tocar un instrumento, cantar o componer música.',
                    'Tomar fotografías o editar videos.',
                    'Actuar, bailar o presentarme frente a un público.',
                    'Decorar espacios o combinar colores y estilos.',
                    'Inventar ideas originales, fuera de lo común.',
                    'Diseñar ropa, logotipos, páginas web o productos.',
                    'Visitar museos, teatros o exposiciones.',
                    'Expresar mis emociones por medio del arte.'
                ]
            },
            S: {
                nombre: 'Social-Asistencial', icono: 'fa-hand-holding-heart', color: 'bg-emerald-700', texto: 'text-emerald-800', fondo: 'bg-emerald-50 border-emerald-200',
                desc: 'Disfrutas ayudar, enseñar y acompañar a las personas, y trabajar en equipo por el bienestar de otros.',
                carreras: ['Psicología', 'Pedagogía y Ciencias de la Educación', 'Enfermería', 'Trabajo Social', 'Medicina', 'Terapia Física y Rehabilitación', 'Nutrición', 'Antropología y Sociología', 'Educación Especial', 'Odontología', 'Desarrollo Comunitario'],
                preguntas: [
                    'Ayudar a otras personas a resolver sus problemas personales.',
                    'Enseñar o explicar cosas a los demás.',
                    'Cuidar la salud y el bienestar de las personas.',
                    'Ser voluntario en causas sociales.',
                    'Escuchar y aconsejar a mis amigos o familiares.',
                    'Trabajar con niños, adolescentes o adultos mayores.',
                    'Participar en actividades de apoyo a mi comunidad.',
                    'Orientar a otros para que tomen buenas decisiones.',
                    'Defender los derechos de personas o grupos vulnerables.',
                    'Entender cómo piensan y sienten las personas.'
                ]
            },
            E: {
                nombre: 'Liderazgo-Emprendedora', icono: 'fa-bullhorn', color: 'bg-rose-700', texto: 'text-rose-800', fondo: 'bg-rose-50 border-rose-200',
                desc: 'Te gusta dirigir, persuadir, tomar iniciativa y lograr metas ambiciosas, ya sea en negocios, política o gestión.',
                carreras: ['Administración de Empresas', 'Derecho', 'Mercadotecnia y Publicidad', 'Negocios Internacionales y Comercio Exterior', 'Relaciones Internacionales', 'Ciencias Políticas y Administración Pública', 'Turismo y Hotelería', 'Emprendimiento e Innovación', 'Comunicación y Periodismo', 'Recursos Humanos', 'Ingeniería Industrial y de Negocios'],
                preguntas: [
                    'Liderar un equipo o dirigir un proyecto.',
                    'Iniciar mi propio negocio.',
                    'Convencer a otros de aceptar mis ideas (negociar, vender).',
                    'Debatir y defender una postura.',
                    'Organizar eventos o campañas.',
                    'Tomar decisiones importantes asumiendo riesgos.',
                    'Participar en política o en cargos de representación.',
                    'Hacer contactos y relacionarme con personas influyentes.',
                    'Competir y alcanzar metas ambiciosas.',
                    'Hablar en público.'
                ]
            },
            C: {
                nombre: 'Administrativa-Organizativa', icono: 'fa-calculator', color: 'bg-slate-700', texto: 'text-slate-800', fondo: 'bg-slate-50 border-slate-300',
                desc: 'Valoras el orden, la precisión y la planeación: te sientes cómodo con números, reglas y procesos bien definidos.',
                carreras: ['Contaduría Pública', 'Finanzas y Banca', 'Economía', 'Actuaría', 'Administración Pública', 'Auditoría y Fiscal', 'Logística y Cadena de Suministro', 'Informática Administrativa', 'Archivonomía y Gestión Documental', 'Comercio Internacional y Aduanas', 'Estadística'],
                preguntas: [
                    'Mantener mis cosas ordenadas y organizadas.',
                    'Llevar cuentas, presupuestos o registros de gastos.',
                    'Seguir procedimientos y reglas con precisión.',
                    'Trabajar con hojas de cálculo y bases de datos.',
                    'Revisar documentos para detectar errores.',
                    'Planear y cumplir horarios y calendarios.',
                    'Archivar, clasificar y administrar información.',
                    'Trabajar en una oficina con tareas definidas y estables.',
                    'Calcular costos, impuestos o inversiones.',
                    'Verificar que se cumplan normas y leyes.'
                ]
            }
        };
        const VOC_OPCIONES = [
            { v: 2, t: 'Me gusta' },
            { v: 1, t: 'Me da igual' },
            { v: 0, t: 'No me gusta' }
        ];
        const VOC_POR_PAGINA = 10;
        let vocPagina = 0;

        // Preguntas intercaladas (una de cada área por vuelta) para evitar respuestas por inercia
        const VOC_PREGUNTAS = [];
        for (let i = 0; i < 10; i++) {
            Object.keys(VOC_AREAS).forEach(k => VOC_PREGUNTAS.push({ area: k, texto: VOC_AREAS[k].preguntas[i] }));
        }

        function vocConstruirPreguntas() {
            const cont = document.getElementById('voc-questions');
            cont.innerHTML = VOC_PREGUNTAS.map((q, i) => `
                <div class="voc-q bg-slate-50 p-4 rounded-xl border border-slate-200" data-pagina="${Math.floor(i / VOC_POR_PAGINA)}" data-idx="${i}">
                    <p class="font-semibold text-slate-800 mb-2">${i + 1}. ${q.texto}</p>
                    <div class="flex flex-wrap gap-2">
                        ${VOC_OPCIONES.map(o => `
                            <label class="flex items-center px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:border-unp-navy cursor-pointer">
                                <input type="radio" name="q${i}" value="${o.v}" class="text-unp-burgundy" onchange="actualizarProgresoVocacional()">
                                <span class="ml-2">${o.t}</span>
                            </label>`).join('')}
                    </div>
                </div>`).join('');
            vocMostrarPagina();
        }

        function vocMostrarPagina() {
            const totalPaginas = Math.ceil(VOC_PREGUNTAS.length / VOC_POR_PAGINA);
            document.querySelectorAll('.voc-q').forEach(el => {
                el.classList.toggle('hidden', Number(el.dataset.pagina) !== vocPagina);
            });
            document.getElementById('voc-nombre-box').classList.toggle('hidden', vocPagina !== 0);
            document.getElementById('voc-page-text').textContent = `Parte ${vocPagina + 1} de ${totalPaginas}`;
            document.getElementById('voc-btn-prev').classList.toggle('hidden', vocPagina === 0);
            document.getElementById('voc-btn-next').classList.toggle('hidden', vocPagina === totalPaginas - 1);
            document.getElementById('voc-btn-submit').classList.toggle('hidden', vocPagina !== totalPaginas - 1);
            document.getElementById('voc-error').classList.add('hidden');
        }

        function vocPaginaIncompleta(pagina) {
            // Devuelve el elemento de la primera pregunta sin responder en esa página (o null)
            const preguntas = document.querySelectorAll(`.voc-q[data-pagina="${pagina}"]`);
            for (const el of preguntas) {
                if (!document.querySelector(`input[name="q${el.dataset.idx}"]:checked`)) return el;
            }
            return null;
        }

        function vocMostrarError(msg, el) {
            const err = document.getElementById('voc-error');
            err.textContent = msg;
            err.classList.remove('hidden');
            if (el) {
                el.classList.add('border-red-400');
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function vocCambiarPagina(dir) {
            const totalPaginas = Math.ceil(VOC_PREGUNTAS.length / VOC_POR_PAGINA);
            if (dir > 0) {
                if (vocPagina === 0 && !document.getElementById('voc-nombre').value.trim()) {
                    vocMostrarError('Escribe tu nombre para continuar.', document.getElementById('voc-nombre-box'));
                    return;
                }
                const faltante = vocPaginaIncompleta(vocPagina);
                if (faltante) {
                    vocMostrarError('Responde todas las preguntas de esta parte para continuar.', faltante);
                    return;
                }
            }
            vocPagina = Math.max(0, Math.min(totalPaginas - 1, vocPagina + dir));
            vocMostrarPagina();
            document.getElementById('test-vocacional').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function actualizarProgresoVocacional() {
            const n = document.querySelectorAll('#voc-questions input[type="radio"]:checked').length;
            document.getElementById('voc-progress-text').textContent = `${n} de ${VOC_PREGUNTAS.length} respondidas`;
            document.getElementById('voc-progress-bar').style.width = `${(n / VOC_PREGUNTAS.length) * 100}%`;
            // Quita el marcado de error de la pregunta que ya fue contestada
            document.querySelectorAll('.voc-q.border-red-400').forEach(el => {
                if (document.querySelector(`input[name="q${el.dataset.idx}"]:checked`)) el.classList.remove('border-red-400');
            });
        }

        function procesarVocacional(e) {
            e.preventDefault();
            const nombre = document.getElementById('voc-nombre').value.trim();
            if (!nombre) {
                vocPagina = 0; vocMostrarPagina();
                vocMostrarError('Escribe tu nombre para ver tu resultado.', document.getElementById('voc-nombre-box'));
                return;
            }

            // Calcula puntajes por área y verifica que todo esté contestado
            const puntos = { R: 0, I: 0, A: 0, S: 0, E: 0, C: 0 };
            for (let i = 0; i < VOC_PREGUNTAS.length; i++) {
                const marcada = document.querySelector(`input[name="q${i}"]:checked`);
                if (!marcada) {
                    vocPagina = Math.floor(i / VOC_POR_PAGINA); vocMostrarPagina();
                    vocMostrarError('Aún faltan preguntas por responder.', document.querySelector(`.voc-q[data-idx="${i}"]`));
                    return;
                }
                puntos[VOC_PREGUNTAS[i].area] += Number(marcada.value);
            }

            const maxArea = 20; // 10 preguntas x 2 puntos
            const orden = Object.keys(puntos).sort((x, y) => puntos[y] - puntos[x]);
            const top = orden.slice(0, 3);

            document.getElementById('voc-result-title').textContent =
                top.map(k => VOC_AREAS[k].nombre).join(' · ');

            // Tarjetas de las 3 áreas principales
            document.getElementById('voc-result-cards').innerHTML = top.map((k, idx) => {
                const a = VOC_AREAS[k];
                return `
                <div class="p-5 rounded-2xl border ${a.fondo}">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-11 h-11 ${a.color} text-white rounded-full flex items-center justify-center text-lg shrink-0"><i class="fa-solid ${a.icono}"></i></div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">${idx === 0 ? 'Área principal' : (idx === 1 ? 'Segunda área' : 'Tercera área')}</span>
                            <h4 class="font-extrabold text-base ${a.texto}">${a.nombre}</h4>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-3">${a.desc}</p>
                    <p class="text-[11px] font-bold text-slate-700 mb-1">Carreras que podrían interesarte:</p>
                    <div class="flex flex-wrap gap-1.5">
                        ${a.carreras.map(c => `<span class="bg-white border border-slate-200 text-slate-700 text-[11px] px-2 py-1 rounded-full">${c}</span>`).join('')}
                    </div>
                </div>`;
            }).join('');

            // Barras con el puntaje de las 6 áreas
            document.getElementById('voc-bars').innerHTML = orden.map(k => {
                const a = VOC_AREAS[k];
                const pct = Math.round((puntos[k] / maxArea) * 100);
                return `
                <div>
                    <div class="flex justify-between mb-1"><span class="font-semibold text-slate-700">${a.nombre}</span><span class="font-bold ${a.texto}">${pct}%</span></div>
                    <div class="w-full bg-slate-200 rounded-full h-2"><div class="${a.color} h-2 rounded-full transition-all duration-500" style="width: ${pct}%"></div></div>
                </div>`;
            }).join('');

            document.getElementById('voc-result').classList.remove('hidden');
            document.getElementById('voc-result').scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Guardar en la base de datos (tabla test_vocacion)
            const resumen = top.map(k => `${VOC_AREAS[k].nombre} (${puntos[k]})`).join(' | ');
            const datos = new URLSearchParams();
            datos.append('accion', 'guardar_vocacional');
            datos.append('nombre_aspirante', nombre);
            datos.append('resultado_carrera', resumen);
            guardarEnBD(datos, 'voc-save-status', 'Resultado guardado en la base de datos.');
        }

        function reiniciarVocacional() {
            document.getElementById('form-vocacional').reset();
            document.getElementById('voc-result').classList.add('hidden');
            document.querySelectorAll('.voc-q').forEach(el => el.classList.remove('border-red-400'));
            vocPagina = 0;
            vocMostrarPagina();
            actualizarProgresoVocacional();
            document.getElementById('voc-save-status').textContent = '';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.addEventListener('DOMContentLoaded', vocConstruirPreguntas);

        function procesarEncuesta(e) {
            e.preventDefault();
            const formData = new FormData(document.getElementById('form-satisfaccion'));
            let total = 0;
            let count = 0;

            for (let [k, v] of formData.entries()) {
                total += parseInt(v);
                count++;
            }

            if (count < 10) {
                alert('Por favor responde las 10 preguntas antes de enviar.');
                return;
            }

            const pct = Math.round((total / 30) * 100);
            const res = document.getElementById('survey-result');
            document.getElementById('survey-score-text').textContent = `Puntaje obtenido: ${total} de 30 puntos posibles (${pct}% de satisfacción).`;
            res.classList.remove('hidden');
            res.scrollIntoView({ behavior: 'smooth' });

            // Guardar en la base de datos
            const datos = new URLSearchParams();
            datos.append('accion', 'guardar_encuesta');
            for (let [k, v] of formData.entries()) datos.append(k, v);
            guardarEnBD(datos, 'survey-save-status', 'Respuestas guardadas en la base de datos.');
        }

        window.onload = function() {
            navigateTo('mision-vision');
        };
    </script>
</body>
</html>
