{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title','Usuarios')
@section('crumbTitle','Usuarios')
@section('crumbSub','Seguridad / Usuarios')

@section('content')
<div class="card">
  <div class="section-head">
    <div class="title">
      <b>Usuarios</b>
      <span>Alta/edición y baja lógica</span>
    </div>
    <div class="actions">
      <button class="btn primary" id="btnNew"><i class="fa-solid fa-plus"></i> Nuevo</button>
    </div>
  </div>

  <div style="height:10px"></div>

  <div class="table-wrap">
    <table id="tblUsers" class="display" style="width:100%">
      <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Usuario</th>
        <th>Email</th>
        <th>Rol</th>
        <th>Activo</th>
        <th>Acciones</th>
      </tr>
      </thead>
      <tbody>
      @foreach($users as $u)
        @php
          $payload = [
            'id'        => $u->id,
            'name'      => $u->name,
            'username'  => $u->username,
            'email'     => $u->email,
            'role_id'   => $u->role_id,
            'is_active' => $u->is_active ? 1 : 0,
          ];
        @endphp

        <tr data-json="{{ e(json_encode($payload)) }}">
          <td>{{ $u->id }}</td>
          <td>{{ $u->name }}</td>
          <td>{{ $u->username }}</td>
          <td>{{ $u->email }}</td>
          <td>{{ $u->role->name ?? '' }}</td>
          <td>{{ $u->is_active ? 'Sí' : 'No' }}</td>
          <td style="white-space:nowrap;">
            <button class="btn outline btnEdit" style="padding:8px 10px;border-radius:12px" title="Editar">
              <i class="fa-regular fa-pen-to-square"></i>
            </button>

            <form id="del-user-{{ $u->id }}" action="{{ route('users.destroy',$u->id) }}" method="POST" style="display:inline;">
              @csrf
              @method('DELETE')
              <button type="button" class="btn outline danger" style="padding:8px 10px;border-radius:12px"
                onclick="confirmBaja('del-user-{{ $u->id }}','¿Dar de baja usuario?','{{ $u->name }}')">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL --}}
<div class="modal" id="mdlUser">
  <div class="backdrop" data-close="1"></div>
  <div class="dialog">
    <div class="dialog-head">
      <b id="mdlTitle">Nuevo usuario</b>
      <button class="close" type="button" data-close="1"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form id="frmUser" method="POST" action="{{ route('users.store') }}" class="form" autocomplete="off">
      @csrf
      <input type="hidden" id="u_id" value="">

      <div class="field col-6">
        <label>Nombre</label>
        <input type="text" name="name" id="u_name" required>
      </div>

      <div class="field col-6">
        <label>Rol</label>
        <select name="role_id" id="u_role" required>
          @foreach(\App\Models\Role::where('is_active',true)->orderBy('name')->get() as $r)
            <option value="{{ $r->id }}">{{ $r->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="field col-6">
        <label>Username</label>
        <input type="text" name="username" id="u_username">
      </div>

      <div class="field col-6">
        <label>Email</label>
        <input type="email" name="email" id="u_email">
      </div>

      <div class="field col-6">
        <label>Password <span style="color:var(--muted)">(solo al crear o si cambias)</span></label>
        <input type="password" name="password" id="u_password" minlength="6">
      </div>

      <div class="field col-6">
        <label>Activo</label>
        <select name="is_active" id="u_active">
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>

      <div class="form-footer">
        <button type="button" class="btn" data-close="1"><i class="fa-solid fa-ban"></i> Cancelar</button>
        <button type="submit" class="btn primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const dt = $('#tblUsers').DataTable({
    pageLength: 10,
    language: {
      search: "Buscar:",
      lengthMenu: "Mostrar _MENU_",
      info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
      paginate: { previous: "Anterior", next: "Siguiente" },
      zeroRecords: "No se encontraron registros",
      infoEmpty: "Sin registros"
    }
  });

  function openModal(){ $('#mdlUser').addClass('show'); $('body').css('overflow','hidden'); }
  function closeModal(){ $('#mdlUser').removeClass('show'); $('body').css('overflow','auto'); }

  // cerrar (backdrop + X + botones con data-close)
  $('#mdlUser').on('click','[data-close="1"]', closeModal);

  $('#btnNew').on('click', function(){
    $('#mdlTitle').text('Nuevo usuario');
    $('#frmUser').attr('action', @json(route('users.store')));
    $('#frmUser').find('input[name="_method"]').remove();
    $('#u_password').prop('required', true);

    $('#u_name,#u_username,#u_email,#u_password').val('');
    $('#u_role').val($('#u_role option:first').val());
    $('#u_active').val('1');
    openModal();
  });

  $('#tblUsers').on('click','.btnEdit', function(){
    const row = $(this).closest('tr');
    const data = JSON.parse(row.attr('data-json'));

    $('#mdlTitle').text('Editar usuario #' + data.id);
    $('#frmUser').attr('action', @json(url('/users')) + '/' + data.id);

    if(!$('#frmUser input[name="_method"]').length){
      $('#frmUser').append('<input type="hidden" name="_method" value="PUT">');
    }

    // en edición, password NO obligatorio (solo si cambias)
    $('#u_password').prop('required', false).val('');

    $('#u_name').val(data.name || '');
    $('#u_username').val(data.username || '');
    $('#u_email').val(data.email || '');
    $('#u_role').val(String(data.role_id || ''));
    $('#u_active').val(String(data.is_active ?? 1));
    openModal();
  });

})();
</script>
@endpush
