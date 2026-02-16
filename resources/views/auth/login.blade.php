<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login Escolar</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.ico') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

  <!-- Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- CSS -->
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>
    window.LOGIN_URL = "{{ route('login.ajax') }}";
  </script>
</head>

<body>
  <div class="wrap">
    <div class="card">

      <!-- LEFT -->
      <section class="side">
        <div class="brand">
          <div class="logoBox">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
          </div>
          <div class="brandTxt">
            <b>Universidad Popular de Tehuacán</b>
            <span>Sistema de control escolar</span>
          </div>
        </div>

        <div class="hero">
          <h1>Hola 👋<br/>Bienvenid@</h1>
          <p>Es un placer tenerte de vuelta</p>
        </div>
      </section>

      <!-- RIGHT -->
      <section class="formPane">
        <div class="title">
          <div>
            <h2>Iniciar sesión</h2>
            <p>Ingresa tus credenciales para continuar.</p>
          </div>

          <button class="miniTheme" id="toggleTheme" type="button" title="Cambiar tema">
            <i class="fa-solid fa-circle-half-stroke"></i>
          </button>
        </div>

        <form class="form" id="loginForm" method="POST" action="#" novalidate>
  @csrf

  <!-- USER -->
  <div class="field" id="fgUser">
    <label>Correo / Usuario</label>
    <div class="input">
      <i class="fa-regular fa-user icon"></i>
      <input
        type="text"
        name="user"
        id="user"
        placeholder="Ej. admin@school.local"
        autocomplete="username"
      >
      <button type="button" class="toggle-pass" id="clearUser" title="Limpiar" style="display:none;">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="hint" id="hintUser">Debe ser correo válido o usuario (mín. 3 caracteres).</div>
    <div class="error" id="errUser"></div>
    <div class="ok" id="okUser">Perfecto ✅</div>
  </div>

  <!-- PASS -->
  <div class="field" id="fgPass">
    <label>Contraseña</label>
    <div class="input">
      <i class="fa-solid fa-lock icon"></i>
      <input
        type="password"
        name="pass"
        id="pass"
        placeholder="••••••••"
        autocomplete="current-password"
      >
      <button type="button" class="toggle-pass" id="togglePass" title="Ver/ocultar">
        <i class="fa-regular fa-eye"></i>
      </button>
    </div>

    <div class="hint" id="hintPass">Mínimo 6 caracteres (recomendado 8+).</div>
    <div class="error" id="errPass"></div>
    <div class="ok" id="okPass">Se ve bien ✅</div>
  </div>

  <div class="row">
    <label class="check">
      <input type="checkbox" name="remember" id="remember">
      <span>Recordarme</span>
    </label>

    <a class="link" href="#" id="forgot">¿Olvidaste tu contraseña?</a>
  </div>

  <button class="btn" type="submit">
    <i class="fa-solid fa-right-to-bracket"></i>
    Entrar al sistema
  </button>
</form>

      </section>

    </div>
  </div>

  <!-- JS -->
  <script src="{{ asset('js/login.js') }}"></script>

  @if (session('auth_error'))
  <script>
    Swal.fire({ icon:'error', title:'Acceso denegado', text:@json(session('auth_error')), confirmButtonText:'OK' });
  </script>
  @endif
</body>
</html>
