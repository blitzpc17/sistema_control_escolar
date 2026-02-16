(function () {
  function $(id){ return document.getElementById(id); }

  function csrf(){
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  // ======= Tema persistente
  function applyThemeFromStorage(){
    const v = localStorage.getItem('login_theme');
    if(v === 'dark') document.body.classList.add('dark');
    else document.body.classList.remove('dark');
  }

  function toggleTheme(){
    document.body.classList.toggle('dark');
    localStorage.setItem('login_theme', document.body.classList.contains('dark') ? 'dark' : 'light');
  }

  // ======= Validación
  function isEmail(v){
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  }

  function showHint(fgId, hintId){
    const fg = $(fgId);
    if(!fg) return;
    fg.classList.remove('invalid','valid');
    const hint = $(hintId);
    const err = fg.querySelector('.error');
    const ok = fg.querySelector('.ok');

    if(err){ err.style.display='none'; err.textContent=''; }
    if(ok) ok.style.display='none';
    if(hint) hint.style.display='block';
  }

  function setFieldOk(fgId, hintId, okId){
    const fg = $(fgId);
    if(!fg) return;
    fg.classList.remove('invalid');
    fg.classList.add('valid');

    const hint = $(hintId);
    const err = fg.querySelector('.error');
    const ok = $(okId);

    if(hint) hint.style.display='none';
    if(err){ err.style.display='none'; err.textContent=''; }
    if(ok) ok.style.display='block';
  }

  function setFieldErr(fgId, hintId, errId, msg){
    const fg = $(fgId);
    if(!fg) return;
    fg.classList.remove('valid');
    fg.classList.add('invalid');

    const hint = $(hintId);
    const err = $(errId);
    const ok = fg.querySelector('.ok');

    if(hint) hint.style.display='none';
    if(ok) ok.style.display='none';
    if(err){
      err.textContent = msg || 'Campo inválido.';
      err.style.display='block';
    }
  }

  function validateUser(){
    const v = ($('user')?.value || '').trim();
    if(!v){
      setFieldErr('fgUser','hintUser','errUser','El correo/usuario es obligatorio.');
      return false;
    }
    if(isEmail(v) || v.length >= 3){
      setFieldOk('fgUser','hintUser','okUser');
      return true;
    }
    setFieldErr('fgUser','hintUser','errUser','Escribe un correo válido o un usuario de al menos 3 caracteres.');
    return false;
  }

  function validatePass(){
    const v = $('pass')?.value || '';
    if(!v){
      setFieldErr('fgPass','hintPass','errPass','La contraseña es obligatoria.');
      return false;
    }
    if(v.length < 6){
      setFieldErr('fgPass','hintPass','errPass','La contraseña debe tener mínimo 6 caracteres.');
      return false;
    }
    setFieldOk('fgPass','hintPass','okPass');
    return true;
  }

  async function ajaxLogin(){
    const payload = {
      user: ($('user')?.value || '').trim(),
      pass: $('pass')?.value || '',
      remember: $('remember')?.checked ? 1 : 0
    };

    const res = await fetch(window.LOGIN_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
      credentials: 'same-origin',
    });

    const data = await res.json().catch(() => ({}));

    if(!res.ok){
      // Laravel 422 validation o 403/500
      throw data;
    }
    return data;
  }

  document.addEventListener('DOMContentLoaded', function () {
    applyThemeFromStorage();

    // hint inicial
    showHint('fgUser','hintUser');
    showHint('fgPass','hintPass');

    // theme
    $('toggleTheme')?.addEventListener('click', toggleTheme);

    // toggle pass
    $('togglePass')?.addEventListener('click', function () {
      const p = $('pass');
      if(!p) return;
      const isPass = p.getAttribute('type') === 'password';
      p.setAttribute('type', isPass ? 'text' : 'password');
      const i = this.querySelector('i');
      if(i){
        i.classList.toggle('fa-eye', !isPass);
        i.classList.toggle('fa-eye-slash', isPass);
      }
    });

    // clear user
    const userInput = $('user');
    const clearBtn = $('clearUser');

    if(userInput && clearBtn){
      userInput.addEventListener('input', function(){
        clearBtn.style.display = userInput.value.trim().length ? 'grid' : 'none';
        if($('fgUser')?.classList.contains('invalid')) validateUser();
      });

      clearBtn.addEventListener('click', function(){
        userInput.value = '';
        clearBtn.style.display = 'none';
        userInput.focus();
        showHint('fgUser','hintUser');
      });
    }

    // live validation
    userInput?.addEventListener('blur', validateUser);
    $('pass')?.addEventListener('blur', validatePass);

    $('pass')?.addEventListener('input', function(){
      if($('fgPass')?.classList.contains('invalid')) validatePass();
    });

    // ✅ submit AJAX
    $('loginForm')?.addEventListener('submit', async function(e){
      e.preventDefault();

      const okU = validateUser();
      const okP = validatePass();

      if(!okU || !okP){
        Swal.fire({
          icon:'error',
          title:'Revisa tus datos',
          text:'Corrige los campos marcados antes de continuar.',
          confirmButtonText:'Entendido'
        });
        return;
      }

      try{
        const r = await ajaxLogin();

        await Swal.fire({
          icon:'success',
          title:'¡Bienvenido!',
          text: r.message || 'Acceso correcto.',
          confirmButtonText:'Continuar'
        });

        window.location.href = r.redirect || '/dashboard';
      }catch(err){
        const msg = err?.message || 'No se pudo iniciar sesión.';
        Swal.fire({
          icon:'error',
          title:'Acceso denegado',
          text: msg,
          confirmButtonText:'OK'
        });
      }
    });

    // forgot
    $('forgot')?.addEventListener('click', function(e){
      e.preventDefault();
      Swal.fire({
        icon:'info',
        title:'Recuperar contraseña',
        text:'Pide al administrador que restablezca tu contraseña.',
        confirmButtonText:'OK'
      });
    });
  });
})();
