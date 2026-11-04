<?php

namespace Tests\Feature;

use App\Models\{Bed, DeviceDeclaration, Payment, Room, UtilityBill, User};
use App\Support\Billing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceBillingFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function assignedTenant(string $meshRoom, int $bedNo = 1): array
    {
        $room = Room::create([
            'mesh_room' => $meshRoom,
            'number' => (string) $bedNo,
            'capacity' => 3,
            'monthly_rate' => '1500.00',
        ]);
        $bed = Bed::create([
            'room_id' => $room->id,
            'bed_no' => $bedNo,
            'bunk' => 'A',
            'label' => 'Bed '.$bedNo.' · Top',
            'mesh_id' => $meshRoom.'_Bed'.$bedNo.'_A',
            'status' => 'occupied',
            'monthly_rate' => '1500.00',
        ]);
        $tenant = User::factory()->create([
            'bed_id' => $bed->id,
            'move_in_date' => now()->subMonth()->toDateString(),
        ]);

        return [$tenant, $bed];
    }

    private function declaration(User $tenant, array $attributes = []): DeviceDeclaration
    {
        return DeviceDeclaration::create(array_merge([
            'user_id' => $tenant->id,
            'name' => 'Desk appliance',
            'note' => null,
            'status' => 'pending',
        ], $attributes));
    }

    public function test_tenant_declarations_are_owner_scoped_and_pending_requests_can_be_cancelled(): void
    {
        [$tenant] = $this->assignedTenant('R1');
        [$otherTenant] = $this->assignedTenant('R2');
        $otherDeclaration = $this->declaration($otherTenant, ['name' => 'Private device']);

        $this->actingAs($tenant)->post(route('tenant.device-declarations.store'), [
            'name' => 'Personal device',
            'note' => 'Near the desk',
        ])->assertRedirect(route('tenant.wifi-devices'));

        $ownDeclaration = DeviceDeclaration::where('user_id', $tenant->id)->firstOrFail();
        $this->assertSame('pending', $ownDeclaration->status);
        $this->assertSame('Near the desk', $ownDeclaration->note);

        $this->actingAs($tenant)->get(route('tenant.wifi-devices'))
            ->assertOk()
            ->assertSee('Personal device')
            ->assertSee('Pending review')
            ->assertDontSee('Private device');

        $this->delete(route('tenant.device-declarations.destroy', $otherDeclaration))
            ->assertNotFound();
        $this->assertDatabaseHas('device_declarations', ['id' => $otherDeclaration->id]);

        $this->actingAs($tenant)->delete(route('tenant.device-declarations.destroy', $ownDeclaration))
            ->assertRedirect(route('tenant.wifi-devices'));
        $this->assertDatabaseMissing('device_declarations', ['id' => $ownDeclaration->id]);
    }

    public function test_tenant_cannot_cancel_approved_declarations(): void
    {
        [$tenant] = $this->assignedTenant('R3');
        $approved = $this->declaration($tenant, [
            'status' => 'approved',
            'monthly_fee' => '12.50',
            'reviewed_by' => User::factory()->admin()->create()->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($tenant)->delete(route('tenant.device-declarations.destroy', $approved))
            ->assertNotFound();
        $this->assertDatabaseHas('device_declarations', ['id' => $approved->id]);
    }

    public function test_only_admins_can_review_devices_or_save_electricity_bills(): void
    {
        [$tenant] = $this->assignedTenant('R4');
        $declaration = $this->declaration($tenant);
        $nonAdmin = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($nonAdmin)
            ->post(route('admin.device-declarations.approve', $declaration), ['fee' => '10.00'])
            ->assertForbidden();
        $this->post(route('admin.device-declarations.reject', $declaration), ['reason' => 'No'])
            ->assertForbidden();
        $this->post(route('admin.electricity-bills.store'), [
            'period' => now()->format('Y-m'),
            'total_amount' => '1000.00',
        ])->assertForbidden();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Device requests')
            ->assertDontSee('Electricity monthly total');

        $this->post(route('admin.device-declarations.approve', $declaration), ['fee' => '25.75'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('device_declarations', [
            'id' => $declaration->id,
            'status' => 'approved',
            'monthly_fee' => '25.75',
            'reviewed_by' => $admin->id,
        ]);
        $this->assertSame('1525.75', Billing::cycle($tenant->fresh())['amount']);

        $rejectedDeclaration = $this->declaration($tenant, ['name' => 'Rejected by admin']);
        $this->post(route('admin.device-declarations.reject', $rejectedDeclaration), ['reason' => 'Not permitted'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('device_declarations', [
            'id' => $rejectedDeclaration->id,
            'status' => 'rejected',
            'rejection_reason' => 'Not permitted',
            'reviewed_by' => $admin->id,
        ]);

        $this->post(route('admin.electricity-bills.store'), [
            'period' => now()->format('Y-m'),
            'total_amount' => '1000.00',
        ])->assertRedirect(route('admin.dashboard'));
        $this->post(route('admin.electricity-bills.store'), [
            'period' => now()->format('Y-m'),
            'total_amount' => '1100.00',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertSame(
            '1100.00',
            UtilityBill::query()->whereDate('period', now()->startOfMonth()->toDateString())
                ->where('type', 'electricity')->firstOrFail()->total_amount
        );
        $this->assertSame(1, UtilityBill::where('type', 'electricity')->count());
    }

    public function test_electricity_split_assigns_remainder_cents_to_lowest_user_id_and_missing_bill_is_zero(): void
    {
        [$first] = $this->assignedTenant('R5');
        [$second] = $this->assignedTenant('R6');
        [$third] = $this->assignedTenant('R7');
        $period = now()->startOfMonth();
        $shares = Billing::electricityShares($period);

        $this->assertSame([], $shares);
        $this->assertSame('0.00', Billing::cycle($first, $period)['electricity_share']);

        UtilityBill::create([
            'period' => $period->toDateString(),
            'type' => 'electricity',
            'total_amount' => '1000.00',
        ]);
        $shares = Billing::electricityShares($period);

        $this->assertSame('333.34', $shares[$first->id]);
        $this->assertSame('333.33', $shares[$second->id]);
        $this->assertSame('333.33', $shares[$third->id]);
        $this->assertSame(100000, array_sum(array_map([Billing::class, 'amountToCents'], $shares)));
        $this->assertSame('1500.00', Billing::cycle($first, $period)['amount']);
        $this->assertSame('0.00', Billing::cycle($first, $period)['electricity_share']);
        $this->assertNotContains('Electricity', array_column(Billing::cycle($first, $period)['lines'], 'label'));
    }

    public function test_only_approved_device_fees_are_billed_for_review_month_and_later_cycles(): void
    {
        [$tenant] = $this->assignedTenant('R8');
        $admin = User::factory()->admin()->create();
        $period = now()->startOfMonth();
        $this->declaration($tenant, [
            'name' => 'Approved device',
            'status' => 'approved',
            'monthly_fee' => '45.25',
            'reviewed_by' => $admin->id,
            'reviewed_at' => $period->copy()->addDays(10),
        ]);
        $this->declaration($tenant, [
            'name' => 'Pending device',
            'status' => 'pending',
            'monthly_fee' => '99.00',
        ]);
        $this->declaration($tenant, [
            'name' => 'Rejected device',
            'status' => 'rejected',
            'monthly_fee' => null,
            'reviewed_by' => $admin->id,
            'reviewed_at' => $period->copy()->addDays(8),
        ]);

        $cycle = Billing::cycle($tenant, $period);
        $this->assertSame('1545.25', $cycle['amount']);
        $this->assertSame('45.25', $cycle['device_total']);
        $this->assertSame(1, collect($cycle['lines'])->where('type', 'device')->count());

        $previousCycle = Billing::cycle($tenant, $period->copy()->subMonth());
        $this->assertSame('1500.00', $previousCycle['amount']);
    }

    public function test_same_remaining_balance_is_shown_on_home_billing_and_utilities(): void
    {
        [$tenant, $bed] = $this->assignedTenant('R9');
        $this->assignedTenant('R10');
        $period = now()->startOfMonth();
        UtilityBill::create([
            'period' => $period->toDateString(),
            'type' => 'electricity',
            'total_amount' => '1000.00',
        ]);
        $this->declaration($tenant, [
            'status' => 'approved',
            'monthly_fee' => '50.00',
            'reviewed_by' => User::factory()->admin()->create()->id,
            'reviewed_at' => $period->copy()->addDay(),
        ]);
        Payment::create([
            'user_id' => $tenant->id,
            'bed_id' => $bed->id,
            'period' => $period->toDateString(),
            'amount' => '500.00',
            'payment_method' => 'cash',
            'status' => 'verified',
        ]);

        $this->actingAs($tenant)->get(route('tenant.home'))
            ->assertOk()
            ->assertSee('₱1,550.00', false);
        $this->get(route('tenant.billing'))
            ->assertOk()
            ->assertSee('₱1,550.00', false);
        $this->get(route('tenant.wifi-devices'))
            ->assertOk()
            ->assertSee('₱1,050.00', false)
            ->assertSee('₱50.00', false);
    }
}
