@extends('layouts.app')

@section('title','Permisos')
@section('crumbTitle','Permisos')
@section('crumbSub','Seguridad / Permisos por rol')

@section('content')
<div class="card">
  <div class="section-head">
    <div class="title">
      <b>Permisos por rol</b>
      <span>Define qué módulos puede VER cada rol</span>
    </div>

    <div class="actions" style="gap:10px;">
      <form method="GET" action="{{ route('permissions.index') }}" style="display:flex; gap:10px; align-items:center;">
        <select name="role_id" onchange="this.form.submit()" style="min-width:260px;">
          @foreach($roles as $r)
            <option value="{{ $r->id }}" {{ (int)$roleId === (int)$r->id ? 'selected' : '' }}>
              {{ $r->name }}
            </option>
          @endforeach
        </select>
        <noscript><button class="btn" type="submit">Cargar</button></noscript>
      </form>
    </div>
  </div>

  <div style="height:10px"></div>

  <form method="POST" action="{{ route('permissions.store') }}" id="frmPerms">
    @csrf
    <input type="hidden" name="role_id" value="{{ $roleId }}">

    <div class="table-wrap" style="padding:14px;">
      <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
        <button class="btn" type="button" id="btnAll"><i class="fa-regular fa-square-check"></i> Marcar todo</button>
        <button class="btn" type="button" id="btnNone"><i class="fa-solid fa-ban"></i> Quitar todo</button>
        <button class="btn primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
      </div>

      <div style="overflow:auto;">
        <table class="display" style="width:100%;">
          <thead>
          <tr>
            <th style="min-width:340px;">Módulo</th>
            <th style="width:110px;">Ver</th>
          </tr>
          </thead>
          <tbody>
          @foreach($tree as $node)
            @php
              $m = $node['module'];
              $p = $node['perm'];
              $pCanView = (int)($p->can_view ?? 0) === 1;
            @endphp

            {{-- PADRE --}}
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <i class="fa-solid {{ $m->icon ?? 'fa-layer-group' }}" style="color:var(--accent)"></i>
                  <b>{{ $m->name }}</b>
                  <span style="color:var(--muted);font-size:12px;">
                    {{ $m->route ? '('.$m->route.')' : '' }}
                  </span>
                </div>
              </td>
              <td style="text-align:center;">
                <input type="checkbox"
                       class="chkView"
                       name="permissions[{{ $m->id }}][can_view]"
                       {{ $pCanView ? 'checked' : '' }}>
              </td>
            </tr>

            {{-- HIJOS --}}
            @foreach(($node['children'] ?? []) as $child)
              @php
                $cm = $child['module'];
                $cp = $child['perm'];
                $cCanView = (int)($cp->can_view ?? 0) === 1;
              @endphp

              <tr>
                <td style="padding-left:46px;">
                  <div style="display:flex;align-items:center;gap:10px;">
                    <i class="fa-regular {{ $cm->icon ?? 'fa-rectangle-list' }}" style="color:var(--accent)"></i>
                    <span>{{ $cm->name }}</span>
                    <span style="color:var(--muted);font-size:12px;">
                      {{ $cm->route ? '('.$cm->route.')' : '' }}
                    </span>
                  </div>
                </td>
                <td style="text-align:center;">
                  <input type="checkbox"
                         class="chkView"
                         name="permissions[{{ $cm->id }}][can_view]"
                         {{ $cCanView ? 'checked' : '' }}>
                </td>
              </tr>
            @endforeach

          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const $frm = $('#frmPerms');
  $('#btnAll').on('click', () => $frm.find('.chkView').prop('checked', true));
  $('#btnNone').on('click', () => $frm.find('.chkView').prop('checked', false));
})();
</script>
@endpush
