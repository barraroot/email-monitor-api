<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_mail_events',
            'view_auth_events',
            'view_metrics',
            'manage_mailboxes',
            'manage_cpanel_accounts',
            'manage_ingest_clients',
            'manage_users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'view_mail_events',
            'view_auth_events',
            'view_metrics',
        ]);

        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->syncPermissions([
            'view_mail_events',
            'view_auth_events',
            'view_metrics',
            'manage_mailboxes',
            'manage_cpanel_accounts',
            'manage_ingest_clients',
        ]);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());
    }
}
