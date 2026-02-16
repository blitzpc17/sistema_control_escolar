<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>@yield('title','CMS Escolar')</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <!-- jQuery (primero) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"/>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

  <!-- Select2 (como el template original) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- CSS del CMS (debe traer MODAL + Select2 skin + DataTables skin) -->
  <link rel="stylesheet" href="{{ asset('css/cms.css') }}">

  @stack('styles')
</head>

<body data-theme="{{ session('cms_theme','light') }}">
  <div class="overlay" id="overlay"></div>

  <div class="app">
    {{-- Sidebar --}}
    @include('layouts.partials.sidebar')

    <main class="main">
      {{-- Topbar --}}
      <header class="topbar">
        <div class="topbar-left">
          <button class="hamburger" id="btnHamburger" title="Menú"><i class="fa-solid fa-bars"></i></button>

          <div class="crumbs">
            <b>@yield('crumbTitle','Dashboard')</b>
            <span>@yield('crumbSub','Inicio')</span>
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
                <b>{{ auth()->user()->name }}</b>
                <span>{{ auth()->user()->role->name ?? '' }}</span>
              </div>
              <i class="fa-solid fa-chevron-down chev"></i>
            </div>

            <div class="menu" id="menu">
              <a href="#" id="menuTheme"><i class="fa-solid fa-circle-half-stroke"></i> Cambiar tema</a>

              <a href="#" id="menuLogout"
                 onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
              </a>

              <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
              </form>
            </div>
          </div>
        </div>
      </header>

      <section class="content">
        @yield('content')
      </section>
    </main>
  </div>

  <!-- JS del CMS (drawer/theme/dropdown + helpers modales) -->
  <script src="{{ asset('js/cms.js') }}"></script>

  <!-- Alerts de sesión -->
  @if(session('ok'))
    <script>Swal.fire({icon:'success', title:'OK', text:@json(session('ok')), confirmButtonText:'OK'});</script>
  @endif
  @if(session('err'))
    <script>Swal.fire({icon:'error', title:'Error', text:@json(session('err')), confirmButtonText:'OK'});</script>
  @endif

  @stack('scripts')
</body>
</html>
