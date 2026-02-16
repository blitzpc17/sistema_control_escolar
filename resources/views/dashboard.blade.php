@extends('layouts.cms')

@section('title','Dashboard')
@section('crumb_title','Dashboard')
@section('crumb_sub','Inicio / Resumen')

@section('content')
  <div class="grid cards">
    <div class="card col-3">
      <div class="stat">
        <div>
          <h3>Alumnos activos</h3>
          <p>Total en el sistema</p>
          <div class="num">1,248</div>
        </div>
        <span class="badge"><i class="fa-solid fa-arrow-trend-up"></i> +3.2%</span>
      </div>
    </div>

    <div class="card col-3">
      <div class="stat">
        <div>
          <h3>Pagos hoy</h3>
          <p>Registros del día</p>
          <div class="num">37</div>
        </div>
        <span class="badge"><i class="fa-solid fa-bolt"></i> En tiempo</span>
      </div>
    </div>

    <div class="card col-3">
      <div class="stat">
        <div>
          <h3>Servicios</h3>
          <p>Catálogo activo</p>
          <div class="num">18</div>
        </div>
        <span class="badge"><i class="fa-solid fa-layer-group"></i> OK</span>
      </div>
    </div>

    <div class="card col-3">
      <div class="stat">
        <div>
          <h3>Usuarios</h3>
          <p>Con acceso</p>
          <div class="num">12</div>
        </div>
        <span class="badge"><i class="fa-solid fa-shield"></i> Roles</span>
      </div>
    </div>

    <div class="card col-12">
      <div class="section-head">
        <div class="title">
          <b>Accesos rápidos</b>
          <span>Acciones frecuentes del sistema</span>
        </div>
        <div class="actions">
          <button class="btn primary" id="btnQuickDemo1" type="button">
            <i class="fa-solid fa-plus"></i> Acción demo
          </button>
          <button class="btn" id="btnQuickDemo2" type="button">
            <i class="fa-solid fa-download"></i> Exportar demo
          </button>
        </div>
      </div>
    </div>
  </div>
@endsection
