<?php

namespace Tests\Feature;

use App\Models\{DeviceCatalog, DeviceDeclaration, DeviceDeclarationCatalog, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceCatalogFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_availability_is_visible_and_forbidden_names_are_hidden(): void
    {
        $tenant = User::factory()->create();
        $available = DeviceCatalog::create([
            'name' => 'Electric fan',
            'available' => true,
            'sort_order' => 1,
        ]);
        $unavailable = DeviceCatalog::create([
            'name' => 'Rice cooker',
            'note' => 'Shared with roommates',
            'available' => false,
            'reason' => 'Temporarily unavailable',
            'sort_order' => 2,
        ]);
        DeviceCatalog::create([
            'name' => 'Phone charging device',
            'available' => true,
        ]);

        $this->actingAs($tenant)->get(route('tenant.wifi-devices'))
            ->assertOk()
            ->assertSee('Electric fan')
            ->assertSee('● Available')
            ->assertSee('Rice cooker')
            ->assertSee('Shared with roommates')
            ->assertSee('✕ Not available')
            ->assertSee('Temporarily unavailable')
            ->assertDontSee('Phone charging device')
            ->assertDontSee('charging');

        $this->assertTrue($available->available);
        $this->assertFalse($unavailable->available);
    }

    public function test_available_catalog_device_is_declared_and_linked_but_unavailable_is_rejected(): void
    {
        $tenant = User::factory()->create();
        $available = DeviceCatalog::create(['name' => 'Electric fan', 'available' => true]);
        $unavailable = DeviceCatalog::create([
            'name' => 'Rice cooker',
            'available' => false,
            'reason' => 'Temporarily unavailable',
        ]);

        $this->actingAs($tenant)->from(route('tenant.wifi-devices'))
            ->post(route('tenant.device-declarations.store'), [
                'catalog_device_id' => $unavailable->id,
                'note' => 'Please review',
            ])
            ->assertRedirect(route('tenant.wifi-devices'))
            ->assertSessionHasErrors('catalog_device_id');
        $this->assertDatabaseMissing('device_declarations', ['user_id' => $tenant->id]);

        $this->post(route('tenant.device-declarations.store'), [
            'catalog_device_id' => $available->id,
            'note' => 'Please review',
        ])->assertRedirect(route('tenant.wifi-devices'));

        $declaration = DeviceDeclaration::where('user_id', $tenant->id)->firstOrFail();
        $this->assertSame('Electric fan', $declaration->name);
        $this->assertSame('pending', $declaration->status);
        $this->assertSame($available->id, $declaration->catalogDevice->id);
        $this->assertDatabaseHas('device_declaration_catalog', [
            'device_declaration_id' => $declaration->id,
            'device_catalog_id' => $available->id,
        ]);

        $this->get(route('tenant.wifi-devices'))
            ->assertOk()
            ->assertSee('Electric fan')
            ->assertSee('Pending review')
            ->assertSee('Please review');
    }

    public function test_other_declarations_without_a_pivot_still_render_and_declaration_ownership_is_scoped(): void
    {
        $tenant = User::factory()->create();
        $otherTenant = User::factory()->create();
        $legacyOther = DeviceDeclaration::create([
            'user_id' => $tenant->id,
            'name' => 'My own non-catalog device',
            'note' => 'Still works',
            'status' => 'pending',
        ]);
        $privateDeclaration = DeviceDeclaration::create([
            'user_id' => $otherTenant->id,
            'name' => 'Other tenant private device',
            'status' => 'pending',
        ]);

        $this->actingAs($tenant)->get(route('tenant.wifi-devices'))
            ->assertOk()
            ->assertSee('My own non-catalog device')
            ->assertSee('Still works')
            ->assertDontSee('Other tenant private device');

        $this->actingAs($tenant)->delete(route('tenant.device-declarations.destroy', $privateDeclaration))
            ->assertNotFound();
        $this->assertDatabaseHas('device_declarations', ['id' => $legacyOther->id]);
        $this->assertDatabaseHas('device_declarations', ['id' => $privateDeclaration->id]);
        $this->assertDatabaseMissing('device_declaration_catalog', [
            'device_declaration_id' => $legacyOther->id,
        ]);
    }

    public function test_only_admins_can_manage_catalog_and_referenced_catalog_devices_cannot_be_deleted(): void
    {
        $tenant = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $catalog = DeviceCatalog::create(['name' => 'Rice cooker', 'available' => true]);

        $this->actingAs($tenant)->post(route('admin.device-catalog.store'), ['name' => 'New device'])
            ->assertForbidden();
        $this->patch(route('admin.device-catalog.update', $catalog), ['available' => '0', 'reason' => 'Not now'])
            ->assertForbidden();
        $this->delete(route('admin.device-catalog.destroy', $catalog))
            ->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.device-catalog.update', $catalog), [
            'available' => '0',
            'reason' => 'Not now',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('device_catalog', [
            'id' => $catalog->id,
            'available' => 0,
            'reason' => 'Not now',
        ]);
        $this->patch(route('admin.device-catalog.update', $catalog), [
            'available' => '1',
            'reason' => 'This reason should clear',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('device_catalog', [
            'id' => $catalog->id,
            'available' => 1,
            'reason' => null,
        ]);

        $this->post(route('admin.device-catalog.store'), [
            'name' => 'New catalog device',
            'note' => 'A useful device',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('device_catalog', [
            'name' => 'New catalog device',
            'note' => 'A useful device',
            'available' => 1,
        ]);

        $declaration = DeviceDeclaration::create([
            'user_id' => $tenant->id,
            'name' => $catalog->name,
            'status' => 'pending',
        ]);
        DeviceDeclarationCatalog::create([
            'device_declaration_id' => $declaration->id,
            'device_catalog_id' => $catalog->id,
        ]);
        $this->delete(route('admin.device-catalog.destroy', $catalog))
            ->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('device_catalog', ['id' => $catalog->id]);
        $this->assertDatabaseHas('device_declaration_catalog', [
            'device_declaration_id' => $declaration->id,
            'device_catalog_id' => $catalog->id,
        ]);

        $unusedCatalog = DeviceCatalog::create(['name' => 'Unused device', 'available' => true]);
        $this->delete(route('admin.device-catalog.destroy', $unusedCatalog))
            ->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseMissing('device_catalog', ['id' => $unusedCatalog->id]);
    }

    public function test_catalog_names_containing_charging_are_rejected_by_admin_and_tenant(): void
    {
        $tenant = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.device-catalog.store'), [
            'name' => 'Phone charging',
        ])->assertSessionHasErrors('name');

        $this->actingAs($tenant)->post(route('tenant.device-declarations.store'), [
            'name' => 'Laptop charging',
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('device_catalog', ['name' => 'Phone charging']);
        $this->assertDatabaseMissing('device_declarations', ['name' => 'Laptop charging']);
    }

    public function test_tenant_catalog_shows_finite_unit_counts_and_unlimited_devices_without_counts(): void
    {
        $tenant = User::factory()->create();
        $limited = DeviceCatalog::create([
            'name' => 'Limited fan',
            'available' => true,
            'total_units' => 3,
        ]);
        $unlimited = DeviceCatalog::create([
            'name' => 'Unlimited lamp',
            'available' => true,
            'total_units' => null,
        ]);

        foreach (['pending', 'approved', 'rejected'] as $status) {
            $declaration = DeviceDeclaration::create([
                'user_id' => $tenant->id,
                'name' => 'Declared '.$status,
                'status' => $status,
            ]);
            DeviceDeclarationCatalog::create([
                'device_declaration_id' => $declaration->id,
                'device_catalog_id' => $limited->id,
            ]);
        }

        $response = $this->actingAs($tenant)->get(route('tenant.wifi-devices'));
        $response
            ->assertOk()
            ->assertSee('Limited fan')
            ->assertSee('● 1 available')
            ->assertSee('✕ 2 in use')
            ->assertSee('Unlimited lamp')
            ->assertDontSee('● 3 available');
        $this->assertMatchesRegularExpression(
            '/<strong>Unlimited lamp<\/strong>\s*<span class="device-available">● Available<\/span>/',
            $response->getContent()
        );
    }

    public function test_catalog_inventory_is_enforced_and_cancelled_or_rejected_declarations_free_units(): void
    {
        $firstTenant = User::factory()->create();
        $secondTenant = User::factory()->create();
        $thirdTenant = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $catalog = DeviceCatalog::create([
            'name' => 'One-unit device',
            'available' => true,
            'total_units' => 1,
        ]);

        $this->actingAs($firstTenant)->post(route('tenant.device-declarations.store'), [
            'catalog_device_id' => $catalog->id,
        ])->assertRedirect(route('tenant.wifi-devices'));

        $response = $this->actingAs($secondTenant)->from(route('tenant.wifi-devices'))
            ->post(route('tenant.device-declarations.store'), [
                'catalog_device_id' => $catalog->id,
            ])
            ->assertRedirect(route('tenant.wifi-devices'))
            ->assertSessionHasErrors('catalog_device_id');
        $this->assertSame(
            'No units left for this device',
            $response->getSession()->get('errors')->getBag('default')->first('catalog_device_id')
        );

        $firstDeclaration = DeviceDeclaration::where('user_id', $firstTenant->id)->firstOrFail();
        $this->actingAs($firstTenant)->delete(route('tenant.device-declarations.destroy', $firstDeclaration))
            ->assertRedirect(route('tenant.wifi-devices'));

        $this->actingAs($secondTenant)->post(route('tenant.device-declarations.store'), [
            'catalog_device_id' => $catalog->id,
        ])->assertRedirect(route('tenant.wifi-devices'));
        $secondDeclaration = DeviceDeclaration::where('user_id', $secondTenant->id)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.device-declarations.reject', $secondDeclaration), [
            'reason' => 'Not approved',
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($thirdTenant)->post(route('tenant.device-declarations.store'), [
            'catalog_device_id' => $catalog->id,
        ])->assertRedirect(route('tenant.wifi-devices'));
        $this->assertDatabaseHas('device_declarations', [
            'user_id' => $thirdTenant->id,
            'name' => 'One-unit device',
            'status' => 'pending',
        ]);
    }

    public function test_only_admins_can_change_unit_limits_and_disabling_a_device_shows_zero_available_with_reason(): void
    {
        $tenant = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $catalog = DeviceCatalog::create([
            'name' => 'Limited cooker',
            'available' => true,
            'total_units' => 4,
        ]);

        $this->actingAs($tenant)->patch(route('admin.device-catalog.update', $catalog), [
            'available' => '1',
            'total_units' => 2,
        ])->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.device-catalog.update', $catalog), [
            'available' => '1',
            'total_units' => 2,
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('device_catalog', ['id' => $catalog->id, 'total_units' => 2]);

        $this->patch(route('admin.device-catalog.update', $catalog), [
            'available' => '0',
            'reason' => 'Temporarily disabled',
            'total_units' => 2,
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($tenant)->get(route('tenant.wifi-devices'))
            ->assertOk()
            ->assertSee('✕ None available (0 of 2 in use)')
            ->assertSee('Temporarily disabled');
    }
}
