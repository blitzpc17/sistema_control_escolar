<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>@yield('title','CMS Escolar')</title>

  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <!-- jQuery (si lo quieres, útil para DataTables/Select2) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"/>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

  <!-- Select2 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- CSS -->
  <link rel="stylesheet" href="{{ asset('css/cms.css') }}">

  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body data-theme="light">
  <div class="overlay" id="overlay"></div>

  <div class="app">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
      <div class="brand">
        <img class="logo-img" src="{{ asset('images/logo.png') }}" alt="Logo"
          onerror="this.style.display='none'; this.insertAdjacentHTML('afterend','<div class=&quot;logo-fallback&quot; style=&quot;width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;font-weight:800;box-shadow:var(--shadow2)&quot;>EDU</div>');">
        <div class="meta">
          <b>UPT</b>
          <span>Sistema de Control Escolar</span>
        </div>
      </div>

      <div class="side-scroll">
        <div class="nav-group-title">Navegación</div>

        <!--
          ✅ RECOMENDACIÓN: más adelante lo pintas dinámico desde DB (modules + role_modules).
          Por ahora dejo el menú fijo igual a tu template.
        -->
        <nav class="nav" id="nav">
          <a href="{{ url('/dashboard') }}" data-page="dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}" title="Dashboard">
            <div class="left">
              <i class="fa-solid fa-grid-2"></i>
              <span>Dashboard</span>
            </div>
          </a>

          <button type="button" class="nav-toggle" title="Catálogos" data-parent="catalogs">
            <div class="left">
              <i class="fa-solid fa-list"></i>
              <span>Catálogos</span>
            </div>
            <i class="fa-solid fa-chevron-right caret"></i>
          </button>
          <div class="submenu" data-parent="catalogs">
            <a href="{{ url('/students') }}" data-parent="catalogs"><div class="left"><i class="fa-solid fa-user-graduate"></i><span>Estudiantes</span></div></a>
            <a href="{{ url('/services') }}" data-parent="catalogs"><div class="left"><i class="fa-solid fa-tags"></i><span>Servicios</span></div></a>
            <a href="{{ url('/payment-methods') }}" data-parent="catalogs"><div class="left"><i class="fa-solid fa-credit-card"></i><span>Formas de pago</span></div></a>
          </div>

          <button type="button" class="nav-toggle" title="Pagos" data-parent="payments_root">
            <div class="left">
              <i class="fa-solid fa-file-invoice-dollar"></i>
              <span>Pagos</span>
            </div>
            <i class="fa-solid fa-chevron-right caret"></i>
          </button>
          <div class="submenu" data-parent="payments_root">
            <a href="{{ url('/payments') }}" data-parent="payments_root"><div class="left"><i class="fa-solid fa-cash-register"></i><span>Cobros</span></div></a>
            <a href="{{ url('/payment-requests') }}" data-parent="payments_root"><div class="left"><i class="fa-solid fa-pen"></i><span>Solicitudes</span></div></a>
          </div>

          <button type="button" class="nav-toggle" title="Seguridad" data-parent="security">
            <div class="left">
              <i class="fa-solid fa-lock"></i>
              <span>Seguridad</span>
            </div>
            <i class="fa-solid fa-chevron-right caret"></i>
          </button>
          <div class="submenu" data-parent="security">
            <a href="{{ url('/users') }}" data-parent="security"><div class="left"><i class="fa-solid fa-users"></i><span>Usuarios</span></div></a>
            <a href="{{ url('/roles') }}" data-parent="security"><div class="left"><i class="fa-solid fa-user-shield"></i><span>Roles</span></div></a>
            <a href="{{ url('/modules') }}" data-parent="security"><div class="left"><i class="fa-solid fa-sitemap"></i><span>Módulos</span></div></a>
            <a href="{{ url('/permissions') }}" data-parent="security"><div class="left"><i class="fa-solid fa-key"></i><span>Permisos</span></div></a>
          </div>

          <button type="button" class="nav-toggle" title="Auditoría" data-parent="audit">
            <div class="left">
              <i class="fa-solid fa-clipboard"></i>
              <span>Auditoría</span>
            </div>
            <i class="fa-solid fa-chevron-right caret"></i>
          </button>
          <div class="submenu" data-parent="audit">
            <a href="{{ url('/audit-logs') }}" data-parent="audit"><div class="left"><i class="fa-solid fa-clock"></i><span>Bitácora</span></div></a>
          </div>
        </nav>
      </div>

      <div class="side-footer">
        <div class="pill">
          <div class="userbox">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}</div>
            <div class="userinfo">
              <b>{{ auth()->user()->name ?? 'Usuario' }}</b>
              <span>{{ optional(auth()->user()->role)->name ?? 'rol' }}</span>
            </div>
          </div>

          <button class="iconbtn" id="btnTheme" title="Cambiar tema" type="button">
            <i class="fa-solid fa-moon"></i>
          </button>
        </div>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
      <!-- TOPBAR -->
      <header class="topbar">
        <div class="topbar-left">
          <button class="hamburger" id="btnHamburger" title="Menú" type="button">
            <i class="fa-solid fa-bars"></i>
          </button>

          <div class="crumbs">
            <b id="crumbTitle">@yield('crumb_title','Dashboard')</b>
            <span id="crumbSub">@yield('crumb_sub','Inicio / Resumen')</span>
          </div>
        </div>

        <div class="topbar-right">
          <div class="search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Buscar alumno, pago, usuario...">
          </div>

          <div class="dropdown" id="userDropdown">
            <div class="dropbtn" id="dropbtn">
              <div class="mini">{{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}</div>
              <div class="dropmeta">
                <b>{{ auth()->user()->name ?? 'Usuario' }}</b>
                <span>{{ optional(auth()->user()->role)->name ?? 'rol' }}</span>
              </div>
              <i class="fa-solid fa-chevron-down chev"></i>
            </div>
            <div class="menu" id="menu">
              <a href="#" id="menuTheme"><i class="fa-solid fa-circle-half-stroke"></i> Cambiar tema</a>

              <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                @csrf
                <button type="submit" class="menu-btn">
                  <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                </button>
              </form>
            </div>
          </div>
        </div>
      </header>

      <!-- CONTENT -->
      <section class="content">
        @yield('content')
      </section>
    </main>
  </div>

  <script src="{{ asset('js/cms.js') }}"></script>

  @if (session('ok'))
  <script>
    Swal.fire({ icon:'success', title:'Listo', text:@json(session('ok')), confirmButtonText:'OK' });
  </script>
  @endif

  @if ($errors->any())
  <script>
    Swal.fire({
      icon:'error',
      title:'Revisa la información',
      html: `{!! implode('<br>', $errors->all()) !!}`,
      confirmButtonText:'OK'
    });
  </script>
  @endif
</body>
</html>
