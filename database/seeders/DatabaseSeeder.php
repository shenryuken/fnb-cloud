<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Category;
use App\Models\Product;
use App\Models\Permission;
use App\Models\Role;
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
        // 0. Seed default roles and permissions first
        $this->call(DefaultRolesSeeder::class);

        // 1. Create Landlord (System Admin) - No Tenant
        $superAdminRole = Role::withoutGlobalScopes()->where('slug', 'superadmin')->whereNull('tenant_id')->first();
        
        $systemAdmin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@fnbcloud.com',
            'password' => Hash::make('password'),
            'api_token' => Str::random(80),
            'tenant_id' => null,
        ]);
        
        if ($superAdminRole) {
            $systemAdmin->roles()->attach($superAdminRole);
        }

        // 2. Create Test Tenant 1: The Burger House
        $burgerHouse = Tenant::create([
            'name' => 'The Burger House',
            'slug' => 'burger-house',
            'domain' => 'burger-house.fnbcloud.test',
            'address' => '123 Burger St, Food City',
            'phone' => '+1 234 567 890',
        ]);

        // Admin for Burger House - assign Owner role
        $ownerRole = Role::withoutGlobalScopes()->where('slug', 'owner')->whereNull('tenant_id')->first();
        
        $burgerAdmin = User::create([
            'name' => 'Burger Admin',
            'email' => 'admin@burgerhouse.com',
            'password' => Hash::make('password'),
            'api_token' => Str::random(80),
            'tenant_id' => $burgerHouse->id,
        ]);
        
        if ($ownerRole) {
            $burgerAdmin->roles()->attach($ownerRole);
        }

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

        // Admin for Sushi Central - assign Owner role
        $sushiAdmin = User::create([
            'name' => 'Sushi Admin',
            'email' => 'admin@sushicentral.com',
            'password' => Hash::make('password'),
            'api_token' => Str::random(80),
            'tenant_id' => $sushiCentral->id,
        ]);
        
        if ($ownerRole) {
            $sushiAdmin->roles()->attach($ownerRole);
        }

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
    }
}
