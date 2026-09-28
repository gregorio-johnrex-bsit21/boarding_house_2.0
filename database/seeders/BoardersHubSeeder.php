<?php
namespace Database\Seeders;

use App\Models\{Bed, Room, User};
use Illuminate\Database\Seeder;

class BoardersHubSeeder extends Seeder
{
    public function run(): void
    {
        // 3D model room  =>  real room number (matched by position on your floor plan)
        $map = [1=>17, 2=>16, 3=>15, 4=>14, 5=>13, 6=>3, 7=>2, 8=>1, 9=>4,
                10=>5, 11=>6, 12=>12, 13=>11, 14=>10, 15=>9, 16=>8, 17=>7];

        foreach ($map as $mesh => $number) {
            $beds = in_array($mesh, [11, 16]) ? 2 : 3;   // model rooms with 2 beds
            $room = Room::updateOrCreate(['mesh_room' => "R$mesh"], [
                'number' => (string) $number, 'capacity' => $beds * 2, 'monthly_rate' => 1500, // change in Settings
            ]);
            for ($b = 1; $b <= $beds; $b++) {
                foreach (['A' => 'Top', 'B' => 'Bottom'] as $k => $name) {
                    Bed::updateOrCreate(['mesh_id' => "R{$mesh}_Bed{$b}_{$k}"], [
                        'room_id' => $room->id, 'bed_no' => $b, 'bunk' => $k, 'label' => "Bed $b · $name",
                    ]);
                }
            }
        }

        User::updateOrCreate(['email' => 'admin@boardershub.test'], [
            'name' => 'Landlord', 'password' => 'password', 'role' => 'admin',
        ]);

        // Demo tenant on Room 9, Bed 1 (top) — delete this block in production
        $bed = Bed::where('mesh_id', 'R15_Bed1_A')->first();
        $t = User::updateOrCreate(['email' => 'tenant@boardershub.test'], [
            'name' => 'Demo Boarder', 'password' => 'password', 'role' => 'tenant',
            'phone' => '09171234567', 'bed_id' => $bed->id, 'move_in_date' => now()->subMonths(2), 'due_day' => 5,
        ]);
        $bed->update(['status' => 'occupied']);
    }
}
