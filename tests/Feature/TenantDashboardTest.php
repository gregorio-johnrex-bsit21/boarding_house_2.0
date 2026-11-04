<?php

namespace Tests\Feature;

use App\Models\{Bed, MaintenanceRequest, Payment, Room, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function assignedTenant(string $meshRoom = 'R15'): array
    {
        $room = Room::create(['mesh_room' => $meshRoom, 'number' => '17', 'capacity' => 2, 'monthly_rate' => 1500]);
        $bed = Bed::create([
            'room_id' => $room->id,
            'bed_no' => 1,
            'bunk' => 'A',
            'label' => 'Bed 1 · Top',
            'mesh_id' => $meshRoom.'_Bed1_A',
            'status' => 'occupied',
            'monthly_rate' => 1500,
        ]);
        $tenant = User::factory()->create(['bed_id' => $bed->id, 'move_in_date' => now()->subMonth()->toDateString()]);

        return [$tenant, $room, $bed];
    }

    public function test_all_tenant_dashboard_pages_render_for_an_assigned_tenant(): void
    {
        [$tenant] = $this->assignedTenant();

        $this->actingAs($tenant)->get('/tenant')->assertOk()->assertSee('Bill charged');
        $this->get('/tenant/billing')->assertOk()->assertSee('Ledger')->assertSee('No payments recorded for this billing period');
        $this->get('/tenant/map')->assertOk()->assertSee('Beds in Room 17');
        $this->get('/tenant/wifi-devices')->assertOk()
            ->assertSee('Utilities & devices')
            ->assertSee('Electricity and water are included in your rent.')
            ->assertDontSee('Property electricity total')
            ->assertDontSee('Electricity share');
        $this->get('/tenant/maintenance')->assertOk()->assertSee('Report issue')->assertSee('No repair requests yet');
        $this->get('/profile')->assertOk()->assertSee('Emergency contact name');
    }

    public function test_only_verified_payments_reduce_the_current_balance(): void
    {
        [$tenant, , $bed] = $this->assignedTenant();
        $period = now()->startOfMonth()->toDateString();

        foreach ([['verified', 500], ['pending', 200], ['rejected', 100]] as [$status, $amount]) {
            Payment::create([
                'user_id' => $tenant->id,
                'bed_id' => $bed->id,
                'period' => $period,
                'amount' => $amount,
                'payment_method' => 'cash',
                'status' => $status,
            ]);
        }

        $this->actingAs($tenant)->get('/tenant/billing')
            ->assertOk()
            ->assertSee('₱500.00')
            ->assertSee('₱1,000.00');
    }

    public function test_home_and_billing_show_the_same_remaining_balance_for_a_verified_payment(): void
    {
        [$tenant, , $bed] = $this->assignedTenant();
        $period = now()->startOfMonth()->toDateString();
        $payment = Payment::create([
            'user_id' => $tenant->id,
            'bed_id' => $bed->id,
            'period' => $period,
            'amount' => 500,
            'payment_method' => 'cash',
            'status' => 'verified',
        ]);

        DB::table('payments')->where('id', $payment->id)->update([
            'period' => $period.' 00:00:00',
        ]);

        $this->actingAs($tenant)->get('/tenant')
            ->assertOk()
            ->assertSee('₱1,000.00', false);

        $this->get('/tenant/billing')
            ->assertOk()
            ->assertSee('₱500.00', false)
            ->assertSee('₱1,000.00', false);
    }

    public function test_billing_page_shows_only_the_signed_in_tenants_payments(): void
    {
        [$tenant, , $tenantBed] = $this->assignedTenant();
        [$otherTenant, , $otherBed] = $this->assignedTenant('R16');
        $period = now()->startOfMonth()->toDateString();

        Payment::create([
            'user_id' => $tenant->id,
            'bed_id' => $tenantBed->id,
            'period' => $period,
            'amount' => 321.45,
            'payment_method' => 'cash',
            'status' => 'verified',
        ]);
        Payment::create([
            'user_id' => $otherTenant->id,
            'bed_id' => $otherBed->id,
            'period' => $period,
            'amount' => 9876.54,
            'payment_method' => 'cash',
            'status' => 'verified',
        ]);

        $this->actingAs($tenant)->get('/tenant/billing')
            ->assertOk()
            ->assertSee('₱321.45', false)
            ->assertDontSee('₱9,876.54', false);
    }

    public function test_home_shows_only_the_signed_in_tenants_period_payments(): void
    {
        [$tenant, , $tenantBed] = $this->assignedTenant();
        [$otherTenant, , $otherBed] = $this->assignedTenant('R16');
        $period = now()->startOfMonth()->toDateString();

        Payment::create([
            'user_id' => $tenant->id,
            'bed_id' => $tenantBed->id,
            'period' => $period,
            'amount' => 321.45,
            'payment_method' => 'cash',
            'status' => 'verified',
        ]);
        Payment::create([
            'user_id' => $otherTenant->id,
            'bed_id' => $otherBed->id,
            'period' => $period,
            'amount' => 9876.54,
            'payment_method' => 'cash',
            'status' => 'verified',
        ]);

        $this->actingAs($tenant)->get('/tenant')
            ->assertOk()
            ->assertSee('₱321.45', false)
            ->assertDontSee('₱9,876.54', false);
    }

    public function test_home_lists_older_open_repairs_without_plotting_them_on_the_timeline(): void
    {
        [$tenant, , $bed] = $this->assignedTenant();
        $repairDate = now()->startOfMonth()->subDay()->setTime(12, 0);

        $repair = MaintenanceRequest::create([
            'tenant_id' => $tenant->id,
            'bed_id' => $bed->id,
            'issue_category' => 'pipes',
            'urgency' => 'normal',
            'description' => 'A previous-period plumbing issue.',
            'status' => 'pending',
        ]);
        $repair->created_at = $repairDate;
        $repair->save();

        $this->actingAs($tenant)->get('/tenant')
            ->assertOk()
            ->assertSee('1 older open repair request from before this period.')
            ->assertDontSee('Repair · Pipes / Plumbing')
            ->assertDontSee('data-d="'.$repairDate->format('M j').'"', false);
    }

    public function test_tenant_can_submit_and_cancel_own_device_declaration(): void
    {
        [$tenant, , $bed] = $this->assignedTenant();
        [$otherTenant] = $this->assignedTenant('R16');

        $this->actingAs($tenant)->post('/tenant/devices', [
            'name' => 'Table fan',
            'notes' => 'Used beside the desk.',
        ])->assertRedirect(route('tenant.wifi-devices'));

        $this->assertDatabaseHas('tenant_device_declarations', [
            'user_id' => $tenant->id,
            'bed_id' => $bed->id,
            'name' => 'Table fan',
            'status' => 'pending',
        ]);

        $declaration = $tenant->deviceDeclarations()->firstOrFail();
        $this->actingAs($otherTenant)->delete(route('tenant.devices.destroy', $declaration->id))
            ->assertNotFound();

        $this->actingAs($tenant)->delete(route('tenant.devices.destroy', $declaration->id))
            ->assertRedirect(route('tenant.wifi-devices'));

        $this->assertDatabaseMissing('tenant_device_declarations', ['id' => $declaration->id]);
    }

    public function test_tenant_notification_preferences_are_saved_to_their_own_account(): void
    {
        [$tenant] = $this->assignedTenant();

        $this->actingAs($tenant)->patch('/tenant/preferences', [
            'notifications' => ['rent' => '1', 'repairs' => '0', 'notices' => '1'],
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame([
            'rent' => true,
            'repairs' => false,
            'notices' => true,
        ], $tenant->fresh()->notification_preferences);
    }

    public function test_roommate_emergency_contact_is_never_visible_on_the_room_page(): void
    {
        [$tenant, $room, $tenantBed] = $this->assignedTenant();
        $roommateBed = Bed::create([
            'room_id' => $room->id,
            'bed_no' => 2,
            'bunk' => 'A',
            'label' => 'Bed 2 · Top',
            'mesh_id' => 'R15_Bed2_A',
            'status' => 'occupied',
            'monthly_rate' => 1500,
        ]);
        $roommate = User::factory()->create([
            'bed_id' => $roommateBed->id,
            'emergency_contact_name' => 'Shared Contact',
            'emergency_contact_phone' => '09171112222',
            'emergency_contact_shared' => true,
        ]);

        $this->actingAs($tenant)->get('/tenant/map')
            ->assertOk()
            ->assertDontSee('Shared Contact')
            ->assertDontSee('09171112222')
            ->assertDontSee('Roommates');

        $roommate->update(['emergency_contact_shared' => false]);
        $this->get('/tenant/map')
            ->assertOk()
            ->assertDontSee('Shared Contact')
            ->assertDontSee('09171112222');
    }

    public function test_tenant_can_submit_a_payment_for_verification(): void
    {
        [$tenant] = $this->assignedTenant();

        $this->actingAs($tenant)->post('/tenant/billing', [
            'amount' => 300,
            'payment_method' => 'cash',
        ])->assertRedirect(route('tenant.billing', ['period' => now()->format('Y-m')]));

        $this->assertDatabaseHas('payments', [
            'user_id' => $tenant->id,
            'amount' => 300,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);
    }

    public function test_gcash_payment_requires_a_reference_number(): void
    {
        [$tenant] = $this->assignedTenant();

        $this->actingAs($tenant)->from('/tenant/billing')->post('/tenant/billing', [
            'amount' => 300,
            'payment_method' => 'gcash',
        ])->assertRedirect('/tenant/billing')->assertSessionHasErrors('reference_number');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_maya_payment_is_accepted_with_a_reference_number(): void
    {
        [$tenant] = $this->assignedTenant();

        $this->actingAs($tenant)->post('/tenant/billing', [
            'amount' => 300,
            'payment_method' => 'maya',
            'reference_number' => 'MAYA-12345',
        ])->assertRedirect(route('tenant.billing', ['period' => now()->format('Y-m')]));

        $this->assertDatabaseHas('payments', [
            'user_id' => $tenant->id,
            'amount' => 300,
            'payment_method' => 'maya',
            'gcash_reference' => 'MAYA-12345',
            'status' => 'pending',
        ]);
    }

    public function test_rejected_payment_resubmission_keeps_its_original_billing_cycle(): void
    {
        [$tenant, , $bed] = $this->assignedTenant();
        $period = now()->subMonthNoOverflow()->startOfMonth();
        $payment = Payment::create([
            'user_id' => $tenant->id,
            'bed_id' => $bed->id,
            'period' => $period->toDateString(),
            'amount' => 300,
            'payment_method' => 'cash',
            'status' => 'rejected',
            'note' => 'Incorrect amount',
        ]);

        $this->actingAs($tenant)->post('/tenant/billing', [
            'amount' => 300,
            'payment_method' => 'cash',
            'resubmit_id' => $payment->id,
        ])->assertRedirect(route('tenant.billing', ['period' => $period->format('Y-m')]));

        $payment->refresh();
        $this->assertSame($period->toDateString(), $payment->period->toDateString());
        $this->assertSame('pending', $payment->status);
    }

    public function test_tenant_can_submit_a_repair_request_and_map_reports_bed_statuses(): void
    {
        [$tenant, , $bed] = $this->assignedTenant();

        $this->actingAs($tenant)->post('/tenant/maintenance', [
            'issue_category' => 'pipes',
            'urgency' => 'urgent',
            'description' => 'The sink is leaking.',
        ])->assertRedirect(route('tenant.maintenance'));

        $this->assertDatabaseHas('maintenance_requests', [
            'tenant_id' => $tenant->id,
            'issue_category' => 'pipes',
            'urgency' => 'urgent',
            'status' => 'pending',
        ]);

        $this->getJson('/map/statuses')->assertOk()->assertJsonPath($bed->mesh_id, 'occupied');
    }

    public function test_repairs_page_shows_a_selected_request_open_list_and_history(): void
    {
        [$tenant, , $bed] = $this->assignedTenant();
        $pending = MaintenanceRequest::create([
            'tenant_id' => $tenant->id,
            'bed_id' => $bed->id,
            'issue_category' => 'pipes',
            'urgency' => 'urgent',
            'description' => 'Kitchen sink is leaking.',
            'status' => 'pending',
        ]);
        $resolved = MaintenanceRequest::create([
            'tenant_id' => $tenant->id,
            'bed_id' => $bed->id,
            'issue_category' => 'electrical',
            'urgency' => 'low',
            'description' => 'Room light was repaired.',
            'status' => 'resolved',
        ]);

        $this->actingAs($tenant)->get('/tenant/maintenance')
            ->assertOk()
            ->assertSee('Selected request')
            ->assertSee('Open requests')
            ->assertSee('History')
            ->assertDontSee('Wi-Fi')
            ->assertSee('data-repair-select="'.$pending->id.'"', false)
            ->assertSee('data-repair-select="'.$resolved->id.'"', false)
            ->assertSee('Kitchen sink is leaking.')
            ->assertSee('Room light was repaired.');
    }

    public function test_tenant_can_update_emergency_contact_information(): void
    {
        [$tenant] = $this->assignedTenant();

        $this->actingAs($tenant)->patch('/profile', [
            'name' => $tenant->name,
            'email' => $tenant->email,
            'phone' => '09171234567',
            'emergency_contact_name' => 'Alex Contact',
            'emergency_contact_phone' => '09179876543',
            'emergency_contact_shared' => '1',
        ])->assertRedirect('/profile')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $tenant->id,
            'phone' => '09171234567',
            'emergency_contact_name' => 'Alex Contact',
            'emergency_contact_phone' => '09179876543',
            'emergency_contact_shared' => 1,
        ]);
    }
}
