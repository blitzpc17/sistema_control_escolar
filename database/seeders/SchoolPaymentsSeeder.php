<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class SchoolPaymentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::transaction(function () use ($now) {

            // ============================================================
            // 1) ROLES
            // ============================================================
            $roles = [
                ['name' => 'admin',      'description' => 'Administrador del sistema', 'is_active' => true],
                ['name' => 'supervisor', 'description' => 'Autoriza cancelaciones/modificaciones', 'is_active' => true],
                ['name' => 'cajero',     'description' => 'Genera cobros a estudiantes', 'is_active' => true],
            ];

            foreach ($roles as $r) {
                DB::table('roles')->updateOrInsert(
                    ['name' => $r['name']],
                    [
                        'description' => $r['description'],
                        'is_active'   => $r['is_active'],
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]
                );
            }

            $roleByName = DB::table('roles')->select('id','name')->get()->keyBy('name');

            // ============================================================
            // 2) MÓDULOS
            // ============================================================
            $modules = [
                // Padres
                ['key'=>'dashboard',       'name'=>'Dashboard',        'route'=>'/dashboard',        'icon'=>'fa-home',        'parent_key'=>null,          'sort_order'=>1,  'is_menu'=>true, 'is_active'=>true],
                ['key'=>'catalogs',        'name'=>'Catálogos',        'route'=>null,               'icon'=>'fa-list',        'parent_key'=>null,          'sort_order'=>2,  'is_menu'=>true, 'is_active'=>true],
                ['key'=>'payments_root',   'name'=>'Pagos',            'route'=>null,               'icon'=>'fa-cash',        'parent_key'=>null,          'sort_order'=>3,  'is_menu'=>true, 'is_active'=>true],
                ['key'=>'security',        'name'=>'Seguridad',        'route'=>null,               'icon'=>'fa-lock',        'parent_key'=>null,          'sort_order'=>4,  'is_menu'=>true, 'is_active'=>true],
                ['key'=>'audit',           'name'=>'Auditoría',        'route'=>null,               'icon'=>'fa-clipboard',   'parent_key'=>null,          'sort_order'=>5,  'is_menu'=>true, 'is_active'=>true],

                // Hijos de Catálogos
                ['key'=>'students',        'name'=>'Estudiantes',      'route'=>'/students',        'icon'=>'fa-user-graduate','parent_key'=>'catalogs',     'sort_order'=>10, 'is_menu'=>true, 'is_active'=>true],
                ['key'=>'services',        'name'=>'Servicios',        'route'=>'/services',        'icon'=>'fa-tags',        'parent_key'=>'catalogs',     'sort_order'=>11, 'is_menu'=>true, 'is_active'=>true],
                ['key'=>'payment_methods', 'name'=>'Formas de pago',   'route'=>'/payment-methods', 'icon'=>'fa-credit-card', 'parent_key'=>'catalogs',     'sort_order'=>12, 'is_menu'=>true, 'is_active'=>true],

                // Hijos de Pagos
                ['key'=>'payments',        'name'=>'Cobros',           'route'=>'/payments',        'icon'=>'fa-receipt',     'parent_key'=>'payments_root','sort_order'=>20, 'is_menu'=>true, 'is_active'=>true],
                ['key'=>'change_requests', 'name'=>'Solicitudes',      'route'=>'/payment-requests','icon'=>'fa-pen',         'parent_key'=>'payments_root','sort_order'=>21, 'is_menu'=>true, 'is_active'=>true],

                // Hijos de Seguridad
                ['key'=>'users',           'name'=>'Usuarios',         'route'=>'/users',           'icon'=>'fa-users',       'parent_key'=>'security',     'sort_order'=>30, 'is_menu'=>true, 'is_active'=>true],
                ['key'=>'roles',           'name'=>'Roles',            'route'=>'/roles',           'icon'=>'fa-user-shield', 'parent_key'=>'security',     'sort_order'=>31, 'is_menu'=>true, 'is_active'=>true],
                ['key'=>'modules',         'name'=>'Módulos',          'route'=>'/modules',         'icon'=>'fa-sitemap',     'parent_key'=>'security',     'sort_order'=>32, 'is_menu'=>true, 'is_active'=>true],
                ['key'=>'permissions',     'name'=>'Permisos',         'route'=>'/permissions',     'icon'=>'fa-key',         'parent_key'=>'security',     'sort_order'=>33, 'is_menu'=>true, 'is_active'=>true],

                // Hijos de Auditoría
                ['key'=>'audit_logs',      'name'=>'Bitácora',         'route'=>'/audit-logs',      'icon'=>'fa-clock',       'parent_key'=>'audit',        'sort_order'=>40, 'is_menu'=>true, 'is_active'=>true],
            ];

            // 2.1 Inserta/actualiza módulos con parent_id=null
            foreach ($modules as $m) {
                DB::table('modules')->updateOrInsert(
                    ['key' => $m['key']],
                    [
                        'name'       => $m['name'],
                        'route'      => $m['route'],
                        'icon'       => $m['icon'],
                        'parent_id'  => null,
                        'sort_order' => $m['sort_order'],
                        'is_menu'    => $m['is_menu'],
                        'is_active'  => $m['is_active'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            // 2.2 Resuelve parent_id por parent_key
            $moduleByKey = DB::table('modules')->select('id','key')->get()->keyBy('key');

            foreach ($modules as $m) {
                if (!empty($m['parent_key'])) {
                    $parentId = $moduleByKey[$m['parent_key']]->id ?? null;
                    DB::table('modules')
                        ->where('key', $m['key'])
                        ->update(['parent_id' => $parentId, 'updated_at' => $now]);
                }
            }

            // Refresca por si cambió
            $moduleByKey = DB::table('modules')->select('id','key')->get()->keyBy('key');

            // ============================================================
            // 3) ROLE_MODULES (visibilidad por rol)
            // ============================================================
            $insertRoleModule = function (int $roleId, int $moduleId) use ($now) {
                // role_modules en tu DDL original NO tenía updated_at, por eso lo omito
                DB::table('role_modules')->updateOrInsert(
                    ['role_id' => $roleId, 'module_id' => $moduleId],
                    ['can_view' => true, 'created_at' => $now]
                );
            };

            // Admin -> todos los módulos existentes
            $adminRoleId = $roleByName['admin']->id ?? null;
            if ($adminRoleId) {
                foreach ($moduleByKey as $m) {
                    $insertRoleModule($adminRoleId, $m->id);
                }
            }

            // Supervisor
            $supervisorRoleId = $roleByName['supervisor']->id ?? null;
            if ($supervisorRoleId) {
                $keys = [
                    'dashboard','payments_root','payments','change_requests',
                    'catalogs','students','services','payment_methods',
                    'audit','audit_logs',
                ];
                foreach ($keys as $k) {
                    if (isset($moduleByKey[$k])) $insertRoleModule($supervisorRoleId, $moduleByKey[$k]->id);
                }
            }

            // Cajero
            $cajeroRoleId = $roleByName['cajero']->id ?? null;
            if ($cajeroRoleId) {
                $keys = [
                    'dashboard','payments_root','payments',
                    'catalogs','students','services','payment_methods',
                ];
                foreach ($keys as $k) {
                    if (isset($moduleByKey[$k])) $insertRoleModule($cajeroRoleId, $moduleByKey[$k]->id);
                }
            }

            // ============================================================
            // 4) FORMAS DE PAGO
            // ============================================================
            $methods = [
                ['key'=>'CASH',     'name'=>'Efectivo',      'is_active'=>true],
                ['key'=>'CARD',     'name'=>'Tarjeta',       'is_active'=>true],
                ['key'=>'TRANSFER', 'name'=>'Transferencia', 'is_active'=>true],
                ['key'=>'CHECK',    'name'=>'Cheque',        'is_active'=>true],
            ];

            foreach ($methods as $m) {
                DB::table('payment_methods')->updateOrInsert(
                    ['key' => $m['key']],
                    [
                        'name'       => $m['name'],
                        'is_active'  => $m['is_active'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            // ============================================================
            // 5) SERVICIOS
            // ============================================================
            $services = [
                ['code'=>'INSCRIPCION', 'name'=>'Inscripción',           'price_type'=>'FIXED', 'fixed_price'=>1200.00, 'is_active'=>true],
                ['code'=>'COLEGIATURA', 'name'=>'Colegiatura mensual',   'price_type'=>'OPEN',  'fixed_price'=>null,    'is_active'=>true],
                ['code'=>'EXAMEN',      'name'=>'Examen extraordinario', 'price_type'=>'FIXED', 'fixed_price'=>250.00,  'is_active'=>true],
                ['code'=>'CREDENCIAL',  'name'=>'Reposición credencial', 'price_type'=>'FIXED', 'fixed_price'=>80.00,   'is_active'=>true],
            ];

            foreach ($services as $s) {
                DB::table('services')->updateOrInsert(
                    ['code' => $s['code']],
                    [
                        'name'        => $s['name'],
                        'description' => null,
                        'price_type'  => $s['price_type'],
                        'fixed_price' => $s['fixed_price'],
                        'is_active'   => $s['is_active'],
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]
                );
            }

            // ============================================================
            // 6) USUARIO ADMIN + PERMISOS (FULL)
            // ============================================================
            if ($adminRoleId) {
                DB::table('users')->updateOrInsert(
                    ['email' => 'admin@school.local'],
                    [
                        'role_id'     => $adminRoleId,
                        'name'        => 'Administrador',
                        'username'    => 'admin',
                        'password'    => Hash::make('Admin123*'), // cámbiala al desplegar
                        'is_active'   => true,
                        'remember_token' => null,
                        'deleted_at'  => null,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]
                );

                $adminUserId = DB::table('users')->where('email', 'admin@school.local')->value('id');

                // Permisos por usuario (admin full)
                foreach ($moduleByKey as $m) {
                    DB::table('user_permissions')->updateOrInsert(
                        ['user_id' => $adminUserId, 'module_id' => $m->id],
                        [
                            'can_view'   => true,
                            'can_create' => true,
                            'can_update' => true,
                            'can_delete' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }

        }); // transaction
    }
}
