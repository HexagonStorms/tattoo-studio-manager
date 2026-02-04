<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Waiver permissions
            'view_waiver',
            'view_any_waiver',
            'create_waiver',
            'update_waiver',
            'delete_waiver',
            'delete_any_waiver',
            'restore_waiver',
            'restore_any_waiver',
            'force_delete_waiver',
            'force_delete_any_waiver',
            'replicate_waiver',
            'reorder_waiver',

            // Future resource permissions (Client, Appointment, etc.)
            'view_client',
            'view_any_client',
            'create_client',
            'update_client',
            'delete_client',
            'delete_any_client',
            'restore_client',
            'restore_any_client',
            'force_delete_client',
            'force_delete_any_client',

            'view_appointment',
            'view_any_appointment',
            'create_appointment',
            'update_appointment',
            'delete_appointment',
            'delete_any_appointment',
            'restore_appointment',
            'restore_any_appointment',
            'force_delete_appointment',
            'force_delete_any_appointment',

            'view_inventory',
            'view_any_inventory',
            'create_inventory',
            'update_inventory',
            'delete_inventory',
            'delete_any_inventory',

            // Artist (portfolio) permissions
            'view_artist',
            'view_any_artist',
            'create_artist',
            'update_artist',
            'delete_artist',
            'delete_any_artist',
            'restore_artist',
            'restore_any_artist',
            'force_delete_artist',
            'force_delete_any_artist',
            'replicate_artist',
            'reorder_artist',

            // Staff management permissions
            'view_staff',
            'view_any_staff',
            'create_staff',
            'update_staff',
            'delete_staff',
            'delete_any_staff',

            // Studio settings permissions
            'view_studio_settings',
            'update_studio_settings',

            // Billing permissions
            'view_billing',
            'manage_billing',

            // Role/Permission management (Shield)
            'view_role',
            'view_any_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',

            // Widget permissions
            'widget_StudioStatsWidget',
            'widget_TodaysAppointmentsWidget',
            'widget_PendingActionsWidget',
            'widget_RecentWaiversWidget',
            'widget_QuickActionsWidget',

            // Page permissions
            'page_EditStudioProfile',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles with permissions

        // 1. Super Admin - Platform admin (us), all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Owner - Full studio access (all resources, settings, staff management)
        $ownerPermissions = [
            // All waiver permissions
            'view_waiver', 'view_any_waiver', 'create_waiver', 'update_waiver',
            'delete_waiver', 'delete_any_waiver', 'restore_waiver', 'restore_any_waiver',
            'force_delete_waiver', 'force_delete_any_waiver', 'replicate_waiver', 'reorder_waiver',

            // All client permissions
            'view_client', 'view_any_client', 'create_client', 'update_client',
            'delete_client', 'delete_any_client', 'restore_client', 'restore_any_client',
            'force_delete_client', 'force_delete_any_client',

            // All appointment permissions
            'view_appointment', 'view_any_appointment', 'create_appointment', 'update_appointment',
            'delete_appointment', 'delete_any_appointment', 'restore_appointment', 'restore_any_appointment',
            'force_delete_appointment', 'force_delete_any_appointment',

            // All inventory permissions
            'view_inventory', 'view_any_inventory', 'create_inventory', 'update_inventory',
            'delete_inventory', 'delete_any_inventory',

            // All artist permissions (portfolio management)
            'view_artist', 'view_any_artist', 'create_artist', 'update_artist',
            'delete_artist', 'delete_any_artist', 'restore_artist', 'restore_any_artist',
            'force_delete_artist', 'force_delete_any_artist', 'replicate_artist', 'reorder_artist',

            // Staff management
            'view_staff', 'view_any_staff', 'create_staff', 'update_staff',
            'delete_staff', 'delete_any_staff',

            // Studio settings
            'view_studio_settings', 'update_studio_settings',

            // Billing
            'view_billing', 'manage_billing',

            // All widgets
            'widget_StudioStatsWidget', 'widget_TodaysAppointmentsWidget',
            'widget_PendingActionsWidget', 'widget_RecentWaiversWidget', 'widget_QuickActionsWidget',

            // Pages
            'page_EditStudioProfile',
        ];
        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner->syncPermissions($ownerPermissions);

        // 3. Editor - Manage bookings, clients, waivers (no billing/staff)
        $editorPermissions = [
            // All waiver permissions except force delete
            'view_waiver', 'view_any_waiver', 'create_waiver', 'update_waiver',
            'delete_waiver', 'delete_any_waiver', 'restore_waiver', 'restore_any_waiver',
            'replicate_waiver',

            // All client permissions except force delete
            'view_client', 'view_any_client', 'create_client', 'update_client',
            'delete_client', 'delete_any_client', 'restore_client', 'restore_any_client',

            // All appointment permissions except force delete
            'view_appointment', 'view_any_appointment', 'create_appointment', 'update_appointment',
            'delete_appointment', 'delete_any_appointment', 'restore_appointment', 'restore_any_appointment',

            // Inventory view/update only
            'view_inventory', 'view_any_inventory', 'update_inventory',

            // Artist management (can edit artist profiles)
            'view_artist', 'view_any_artist', 'create_artist', 'update_artist',
            'delete_artist', 'delete_any_artist', 'restore_artist', 'restore_any_artist',

            // View staff only
            'view_staff', 'view_any_staff',

            // View studio settings only
            'view_studio_settings',

            // All widgets except stats
            'widget_TodaysAppointmentsWidget', 'widget_PendingActionsWidget',
            'widget_RecentWaiversWidget', 'widget_QuickActionsWidget',
        ];
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions($editorPermissions);

        // 4. Artist - Own schedule, portfolio, clients only
        $artistPermissions = [
            // View and create waivers (for their clients)
            'view_waiver', 'view_any_waiver', 'create_waiver', 'update_waiver',

            // View and create clients
            'view_client', 'view_any_client', 'create_client', 'update_client',

            // View and manage own appointments
            'view_appointment', 'view_any_appointment', 'create_appointment', 'update_appointment',

            // View inventory only
            'view_inventory', 'view_any_inventory',

            // Own artist profile management
            'view_artist', 'view_any_artist', 'update_artist',

            // Basic widgets
            'widget_TodaysAppointmentsWidget', 'widget_QuickActionsWidget',
        ];
        $artist = Role::firstOrCreate(['name' => 'artist', 'guard_name' => 'web']);
        $artist->syncPermissions($artistPermissions);

        // 5. Apprentice - View-only + limited create (no delete, needs approval concept)
        $apprenticePermissions = [
            // View waivers only
            'view_waiver', 'view_any_waiver',

            // View clients only
            'view_client', 'view_any_client',

            // View appointments and create (needs approval - to be implemented)
            'view_appointment', 'view_any_appointment', 'create_appointment',

            // View inventory only
            'view_inventory', 'view_any_inventory',

            // Limited widgets
            'widget_TodaysAppointmentsWidget',
        ];
        $apprentice = Role::firstOrCreate(['name' => 'apprentice', 'guard_name' => 'web']);
        $apprentice->syncPermissions($apprenticePermissions);

        // Create panel_user role for basic panel access
        $panelUser = Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);
        // panel_user has minimal permissions - just dashboard access
    }
}
