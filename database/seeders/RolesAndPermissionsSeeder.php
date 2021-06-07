<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            [
                "name" => "admin",
                "permissions" => [
                    ''
                ],
            ],

            [
                "name" => "user",
                "permissions" => [
                    ''
                ],
            ],

        ];
        $permissions = [
            'test-view-all',
            'test-view',
            'test-store',
            'test-update',
            'test-destroy',
            'test-bulk-store',
            'test-bulk-destroy'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name'], 'guard_name' => 'web'])
                ->givePermissionTo($role['permissions']);
        }
    }
}
