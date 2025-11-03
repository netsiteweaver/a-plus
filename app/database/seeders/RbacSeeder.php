<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    /**
     * Seed the application's default roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('permission.defaults.guard', 'web');

        $permissionNames = [
            'catalog.view',
            'catalog.manage',
            'orders.place',
            'orders.view',
            'orders.manage',
            'customers.view',
            'customers.manage',
            'promotions.manage',
            'content.manage',
            'recommendations.manage',
            'settings.manage',
        ];

        $permissions = collect($permissionNames)->mapWithKeys(function (string $name) use ($guard) {
            return [$name => Permission::findOrCreate($name, $guard)];
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'customer' => [
                'catalog.view',
                'orders.place',
            ],
            'staff' => [
                'catalog.view',
                'orders.view',
                'orders.manage',
                'customers.view',
            ],
            'manager' => [
                'catalog.view',
                'catalog.manage',
                'orders.view',
                'orders.manage',
                'customers.view',
                'customers.manage',
                'promotions.manage',
                'recommendations.manage',
            ],
            'admin' => $permissionNames,
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->syncPermissions($permissions->only($rolePermissions)->values());
        }
    }
}
