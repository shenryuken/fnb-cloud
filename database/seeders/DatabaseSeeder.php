<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Category;
use App\Models\Product;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Create Default Permissions
        $permissions = [
            ['name' => 'Access POS', 'slug' => 'pos.access'],
            ['name' => 'Manage Orders', 'slug' => 'orders.manage'],
            ['name' => 'Access KDS', 'slug' => 'kds.access'],
            ['name' => 'Manage Menu', 'slug' => 'menu.manage'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // 1. Create Landlord (System Admin) - No Tenant
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@fnbcloud.com',
            'password' => Hash::make('password'),
            'api_token' => Str::random(80),
            'tenant_id' => null,
        ]);

        // 2. Create Test Tenant 1: The Burger House
        $burgerHouse = Tenant::create([
            'name' => 'The Burger House',
            'slug' => 'burger-house',
            'domain' => 'burger-house.fnbcloud.test',
            'address' => '123 Burger St, Food City',
            'phone' => '+1 234 567 890',
        ]);

        // Admin for Burger House
        User::create([
            'name' => 'Burger Admin',
            'email' => 'admin@burgerhouse.com',
            'password' => Hash::make('password'),
            'api_token' => Str::random(80),
            'tenant_id' => $burgerHouse->id,
        ]);

        // Bind tenant_id for seeder so the BelongsToTenant trait works automatically
        app()->instance('tenant_id', $burgerHouse->id);

        $burgers = Category::create([
            'name' => 'Burgers',
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $burgers->id,
            'name' => 'Classic Cheeseburger',
            'price' => 12.99,
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $burgers->id,
            'name' => 'Bacon Deluxe',
            'price' => 14.99,
            'sort_order' => 2,
        ]);

        // 3. Create Test Tenant 2: Sushi Central
        $sushiCentral = Tenant::create([
            'name' => 'Sushi Central',
            'slug' => 'sushi-central',
            'domain' => 'sushi-central.fnbcloud.test',
        ]);

        // Admin for Sushi Central
        User::create([
            'name' => 'Sushi Admin',
            'email' => 'admin@sushicentral.com',
            'password' => Hash::make('password'),
            'api_token' => Str::random(80),
            'tenant_id' => $sushiCentral->id,
        ]);

        // Bind tenant_id for the second tenant
        app()->instance('tenant_id', $sushiCentral->id);

        $sushi = Category::create([
            'name' => 'Sushi Rolls',
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $sushi->id,
            'name' => 'California Roll',
            'price' => 8.50,
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $sushi->id,
            'name' => 'Dragon Roll',
            'price' => 15.00,
            'sort_order' => 2,
        ]);

        // Seed default roles and permissions for all tenants + landlord
        $this->call(DefaultRolesSeeder::class);
    }
}
