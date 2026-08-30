<?php
session_start();
require_once __DIR__ . '/app/Models/Usuario.php';
require_once __DIR__ . '/app/Helpers/texto.php';

// Esta página muestra datos personales: si no hay sesión, no se puede entrar.
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario = (new Usuario())->buscarPorId((int) $_SESSION['usuario_id']);
if (!$usuario) {
    // La sesión apunta a un usuario que ya no existe (por ejemplo, si un
    // admin lo borró mientras estaba logueado). Se cierra la sesión y listo.
    header('Location: logout.php');
    exit;
}

$iniciales = obtenerIniciales($usuario['nombre_completo']);

$etiquetasGenero = [
    'masculino' => 'Masculino',
    'femenino'  => 'Femenino',
    'otro'      => 'Otro / Prefiero no decirlo',
];
$generoActual = $usuario['genero'] ?: 'otro';
$generoTexto  = $etiquetasGenero[$generoActual] ?? $etiquetasGenero['otro'];

// Nombre del mes de registro, en español (ej: "junio 2026")
$meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
          7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
$fechaRegistro = new DateTime($usuario['fecha_registro']);
$miembroDesde  = $meses[(int) $fechaRegistro->format('n')] . ' ' . $fechaRegistro->format('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- HEAD: contiene informacion para el navegador; no se muestra como contenido principal de la pagina. -->
  <!-- charset define la codificacion para que tildes y eñes se lean correctamente. -->
  <meta charset="UTF-8" />
  <!-- viewport adapta el ancho de la pagina a celulares, tablets y PC. -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- title es el texto que aparece en la pestaña del navegador. -->
  <title>Perfil — SGDM</title>
  <!-- styles.css guarda todos los estilos visuales del sitio. -->
  <link rel="stylesheet" href="styles.css?v=25" />
  <!-- Font Awesome aporta los iconos usados en botones, tarjetas y menus. -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <!-- BODY: contiene todo lo visible de la pagina: navegacion, contenido principal y pie. -->

  <!-- NAVBAR: menu principal para moverse entre las pantallas del mockup. -->
  <nav class="nav">
    <a href="index.php" class="nav-logo">
      <img src="Imagenes/Logo_Pagina.png" alt="Logo_Página" class="nav-logo-img"/>
      <span class="nav-logo-text">PrimeCup</span>
    </a>
    <div class="nav-botones">
      <button class="theme-toggle" id="themeToggle" aria-label="Cambiar a modo oscuro">
        <i class="fa-solid fa-moon"></i>
      </button>
      <button class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
    <?php $mostrarComoFunciona = false; include __DIR__ . '/app/Views/partials/nav_links.php'; ?>
  </nav>

  <main class="perfil-main">
    <!-- MAIN: contenido central y especifico de esta pagina. -->

    <!-- =====================
       CABECERA DEL PERFIL: muestra avatar, nombre, usuario y datos generales.
       Tambien contiene accesos para editar perfil o ir a configuracion.
       ===================== -->
    <section class="perfil-header">

      <!-- AVATAR: circulo con iniciales del usuario y opcion visual para foto. -->
      <div class="perfil-avatar-wrap">
        <div class="perfil-avatar" id="perfilAvatarCirculo"><?= htmlspecialchars($iniciales) ?></div>
        <label class="btn-foto" title="Cambiar foto" aria-label="Cambiar foto de perfil">
          <i class="fa-solid fa-camera" aria-hidden="true"></i>
          <input type="file" id="perfilFotoInput" name="avatar" accept="image/*" style="display:none;" />
        </label>
      </div>

      <!-- INFO: datos principales del usuario mostrados en su perfil. -->
      <div class="perfil-info">
        <h1 class="perfil-nombre"><?= htmlspecialchars($usuario['nombre_completo']) ?></h1>
        <span class="perfil-usuario">@<?= htmlspecialchars($usuario['nombre_usuario']) ?></span>
        <!-- "Sobre mí" todavía es un texto fijo: la tabla usuarios no tiene
             columna para esto todavía. Se agrega en una próxima entrega. -->
        <p class="perfil-sobre">Apasionado por los torneos de fútbol y ajedrez. Siempre compitiendo.</p>
        <div class="perfil-meta">
          <span><i class="fa-solid fa-calendar"></i> Miembro desde <?= htmlspecialchars($miembroDesde) ?></span>
          <span><i class="fa-solid fa-venus-mars"></i> <?= htmlspecialchars($generoTexto) ?></span>
        </div>
      </div>

      <!-- ACCIONES: enlaces para editar el perfil o abrir configuracion. -->
      <div class="perfil-acciones">
        <a href="#editar-perfil" class="btn btn-editar">
          <i class="fa-solid fa-pen"></i> Editar perfil
        </a>
        <a href="configuracion.php" class="btn btn-editar">
          <i class="fa-solid fa-gear"></i> Configuración
        </a>
      </div>

    </section>

    <!-- FORMULARIO DE EDICION: campos para modificar datos visibles del perfil. -->
    <section class="perfil-edicion" id="editar-perfil">
      <div class="seccion-header">
        <h2 class="seccion-titulo"><i class="fa-solid fa-pen"></i> Editar perfil</h2>
      </div>
      <form class="editar-form" id="editarPerfilForm">
        <div class="editar-fila">
          <div class="form-group">
            <label class="form-label-p" for="perfilNombreInput">Nombre completo</label>
            <input type="text" id="perfilNombreInput" name="nombre" class="form-input-p" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>"
              pattern="[A-Za-záéíóúÁÉÍÓÚñÑ]+ [A-Za-záéíóúÁÉÍÓÚñÑ].*"
              title="Ingresá tu nombre y apellido" required />
          </div>
          <div class="form-group">
            <label class="form-label-p" for="perfilUsuarioInput">Nombre de usuario</label>
            <input type="text" id="perfilUsuarioInput" name="usuario" class="form-input-p" value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>"
              pattern="[a-zA-Z0-9_]+"
              title="Solo letras, números y guion bajo" required />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label-p">Género</label>
          <div class="radio-inline">
            <label class="radio-opcion">
              <input type="radio" name="genero" value="masculino" <?= $generoActual === 'masculino' ? 'checked' : '' ?> />
              <span>Masculino</span>
            </label>
            <label class="radio-opcion">
              <input type="radio" name="genero" value="femenino" <?= $generoActual === 'femenino' ? 'checked' : '' ?> />
              <span>Femenino</span>
            </label>
            <label class="radio-opcion">
              <input type="radio" name="genero" value="otro" <?= $generoActual === 'otro' ? 'checked' : '' ?> />
              <span>Otro / Prefiero no decirlo</span>
            </label>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label-p" for="perfilBioInput">Sobre mí</label>
          <textarea id="perfilBioInput" name="bio" class="form-input-p form-textarea" rows="3" maxlength="200"
            placeholder="Contá algo sobre vos...">Apasionado por los torneos de fútbol y ajedrez. Siempre compitiendo.</textarea>
          <span class="form-hint-p" id="perfilBioContador">200 caracteres restantes.</span>
        </div>
        <div class="editar-acciones">
          <button type="submit" class="btn-primary">Guardar cambios</button>
          <a href="perfil.php" class="btn">Cancelar</a>
        </div>
      </form>
    </section>

    <!-- =====================
       ESTADISTICAS: resumen visual con numeros importantes del sistema.
       Ayuda a mostrar actividad en el mockup.
       ===================== -->
    <section class="perfil-stats">
      <div class="pstat">
        <div class="pstat-num" data-target="8">8</div>
        <div class="pstat-label">Torneos jugados</div>
      </div>
      <div class="pstat">
        <div class="pstat-num" data-target="3">3</div>
        <div class="pstat-label">Torneos activos</div>
      </div>
      <div class="pstat">
        <div class="pstat-num" data-target="2">2</div>
        <div class="pstat-label">Torneos ganados</div>
      </div>
      <div class="pstat">
        <div class="pstat-num" data-target="74" data-formato="%">74%</div>
        <div class="pstat-label">Victorias</div>
      </div>
    </section>

    <!-- =====================
       TORNEOS ACTIVOS: tarjetas de torneos destacados o en curso.
       Cada tarjeta funciona como acceso al detalle del torneo.
       ===================== -->
    <section class="perfil-seccion">
      <div class="seccion-header">
        <h2 class="seccion-titulo"><i class="fa-solid fa-trophy"></i> Torneos en los que participa</h2>
        <a href="busqueda.php" class="section-link">Ver todos <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="carrusel-wrap">
        <button class="carrusel-flecha" data-carrusel-dir="-1" aria-label="Ver torneos anteriores">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="tarjetas-carrusel">

          <div class="card">
            <div class="card-sport"><i class="fa-solid fa-futbol"></i> Fútbol</div>
            <div class="card-name">Mundialito 2026</div>
            <div class="card-meta">
              <div class="card-row"><i class="fa-solid fa-users"></i> 16 equipos</div>
              <div class="card-row"><i class="fa-solid fa-calendar"></i> Inicia 15 jun</div>
              <div class="card-row"><i class="fa-solid fa-chart-bar"></i> Liga</div>
            </div>
            <span class="badge estado-verde">Inscripciones abiertas</span>
          </div>

          <div class="card">
            <div class="card-sport"><i class="fa-solid fa-chess"></i> Ajedrez</div>
            <div class="card-name">Torneo de ajedrez</div>
            <div class="card-meta">
              <div class="card-row"><i class="fa-solid fa-users"></i> 128 participantes</div>
              <div class="card-row"><i class="fa-solid fa-calendar"></i> En curso</div>
              <div class="card-row"><i class="fa-solid fa-chart-bar"></i> Sistema suizo</div>
            </div>
            <span class="badge estado-naranja">En curso — ronda 3</span>
          </div>

          <div class="card">
            <div class="card-sport"><i class="fa-solid fa-gamepad"></i> Videojuegos</div>
            <div class="card-name">CS:2 Gaming Cup</div>
            <div class="card-meta">
              <div class="card-row"><i class="fa-solid fa-users"></i> 16 equipos</div>
              <div class="card-row"><i class="fa-solid fa-calendar"></i> 20 jun</div>
              <div class="card-row"><i class="fa-solid fa-chart-bar"></i> Eliminación directa</div>
            </div>
            <span class="badge estado-verde">Inscripciones abiertas</span>
          </div>

        </div>
        <button class="carrusel-flecha" data-carrusel-dir="1" aria-label="Ver más torneos">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </section>

    <!-- =====================
       HISTORIAL: tabla con torneos ya jugados y resultados obtenidos.
       Sirve para mostrar antecedentes del usuario dentro del sistema.
       ===================== -->
    <section class="perfil-seccion">
      <div class="seccion-header">
        <h2 class="seccion-titulo"><i class="fa-solid fa-clock-rotate-left"></i> Historial de resultados</h2>
      </div>
      <div class="historial-tabla">
        <div class="historial-header">
          <span>Torneo</span>
          <span>Formato</span>
          <span>Posición</span>
          <span>Resultado</span>
        </div>

        <div class="historial-fila">
          <div class="historial-torneo">
            <span class="historial-nombre">Liga Escolar 2025</span>
            <span class="historial-deporte"><i class="fa-solid fa-futbol"></i> Fútbol</span>
          </div>
          <span class="historial-formato">Liga</span>
          <span class="historial-pos">1°</span>
          <span class="badge estado-verde">Campeón</span>
        </div>

        <div class="historial-fila">
          <div class="historial-torneo">
            <span class="historial-nombre">Open Ajedrez UTU</span>
            <span class="historial-deporte"><i class="fa-solid fa-chess"></i> Ajedrez</span>
          </div>
          <span class="historial-formato">Sistema suizo</span>
          <span class="historial-pos">3°</span>
          <span class="badge estado-naranja">Semifinal</span>
        </div>

        <div class="historial-fila">
          <div class="historial-torneo">
            <span class="historial-nombre">Gaming Cup 2025</span>
            <span class="historial-deporte"><i class="fa-solid fa-gamepad"></i> Videojuegos</span>
          </div>
          <span class="historial-formato">Eliminación directa</span>
          <span class="historial-pos">2°</span>
          <span class="badge estado-naranja">Finalista</span>
        </div>

        <div class="historial-fila">
          <div class="historial-torneo">
            <span class="historial-nombre">Torneo Relámpago</span>
            <span class="historial-deporte"><i class="fa-solid fa-futbol"></i> Fútbol</span>
          </div>
          <span class="historial-formato">Eliminación directa</span>
          <span class="historial-pos">5°</span>
          <span class="badge estado-rojo">Eliminado</span>
        </div>

        <div class="historial-fila">
          <div class="historial-torneo">
            <span class="historial-nombre">Copa Otoño 2025</span>
            <span class="historial-deporte"><i class="fa-solid fa-futbol"></i> Fútbol</span>
          </div>
          <span class="historial-formato">Liga</span>
          <span class="historial-pos">1°</span>
          <span class="badge estado-verde">Campeón</span>
        </div>

      </div>
    </section>

  </main>

  <!-- FOOTER: informacion final comun del sistema. -->
  <footer class="footer">
    <div class="footer-links">
      <a href="como-funciona.php">Cómo funciona</a>
      <a href="crear-torneo.php">Crear torneo</a>
      <a href="#">Términos</a>
      <a href="#">Privacidad</a>
    </div>
    <div class="footer-brand">
      <img src="Imagenes/Logo_Pagina.png" alt="Logo PrimeCup" class="footer-logo">
      <span>&copy; 2026 CeiboTech</span>
    </div>
  </footer>
<script src="script.js?v=25"></script>
</body>
</html>
