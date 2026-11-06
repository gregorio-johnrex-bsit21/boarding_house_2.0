@extends('layouts.admin')

@section('content')
<<<<<<< HEAD

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 style="color: #4A3629; font-weight: 800; margin: 0; font-size: 1.75rem; letter-spacing: -0.5px;">Room Management</h1>
</div>

<div id="roomsGridContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;">
    @foreach($rooms as $room)
    <div class="card" style="background: #4A3629; border-radius: 20px; padding: 25px; box-shadow: 0 12px 30px rgba(74, 54, 41, 0.15); text-align: center; position: relative; display: flex; flex-direction: column; justify-content: space-between; min-height: 210px; box-sizing: border-box;">
        
        <div style="position: absolute; top: 15px; right: 15px;">
            <span style="font-size: 0.7rem; font-weight: 800; padding: 5px 12px; border-radius: 30px; letter-spacing: 0.5px;
                {{ $room->room_type === 'Premium' ? 'background: rgba(255, 212, 59, 0.18); color: #ffd43b; border: 1px solid rgba(255, 212, 59, 0.25);' : 'background: rgba(255, 255, 255, 0.12); color: #f1f2f6; border: 1px solid rgba(255, 255, 255, 0.15);' }}">
                {{ strtoupper($room->room_type ?? 'Normal') }}
            </span>
        </div>

        <div style="margin-top: 20px;">
            <h3 style="margin: 0 0 6px 0; color: #ffffff; font-size: 1.4rem; font-weight: 800; letter-spacing: -0.3px;">Room {{ $room->room_number }}</h3>
            
            <p style="margin: 0 0 15px 0; font-size: 0.85rem; font-weight: 700; letter-spacing: 0.2px;
                color: {{ $room->status === 'Available' ? '#2ed573' : ($room->status === 'Occupied' ? '#ff6b81' : '#ffa502') }};">
                <i class="fa-solid {{ $room->status === 'Available' ? 'fa-circle-dot' : ($room->status === 'Occupied' ? 'fa-user-lock' : 'fa-screwdriver-wrench') }}" style="font-size: 0.75rem; margin-right: 5px;"></i>
                {{ $room->status === 'Available' ? 'Vacant' : ($room->status === 'Occupied' ? 'Occupied' : 'Maintenance') }}
            </p>
        </div>
        
        <div style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div style="text-align: left;">
                <span style="display: block; font-size: 0.65rem; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Monthly Rate</span>
                <span style="color: #ffffff; font-weight: 800; font-size: 1.05rem;">₱{{ number_format($room->price, 0) }}</span>
            </div>
            
            <button onclick="openEditModal({{ json_encode($room) }})" style="border: none; background: rgba(255,255,255,0.1); color: #ffffff; width: 34px; height: 34px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                <i class="fa-solid fa-pen-to-square" style="font-size: 0.9rem;"></i>
            </button>
        </div>
=======
@if(session('success'))
    <div style="background-color: #e8f5e9; border: 1px solid #00b894; color: #00b894; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div style="background-color: #fff5f5; border: 1px solid #ff7675; color: #ff7675; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; font-size: 0.9rem;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 style="color: var(--primary); font-weight: 800; margin: 0;">Room Management</h1>
    <button onclick="toggleRoomModal(true)" class="btn-primary" style="background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-door-open"></i> Add New Room
    </button>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;">
    @foreach($rooms as $room)
    <div class="card" style="background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); text-align: center; position: relative;">
        <h3 style="margin: 0 0 10px 0; color: var(--primary); font-size: 1.3rem; font-weight: 800;">Room {{ $room->room_number }}</h3>
        
        <p style="margin: 0 0 15px 0; font-size: 0.85rem; font-weight: 700; 
            color: {{ $room->status == 'Available' ? '#00b894' : '#ff7675' }};">
            {{ $room->status }}
        </p>
        
        <p style="margin: 0 0 20px 0; color: var(--secondary); font-weight: 700; font-size: 0.95rem;">
            ₱{{ number_format($room->price) }} / month
        </p>
        
        <button style="border: none; background: none; color: var(--secondary); cursor: pointer; font-size: 1rem;">
            <i class="fa-solid fa-pen-to-square"></i>
        </button>
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
    </div>
    @endforeach

    <div onclick="toggleRoomModal(true)" style="background: #f4f3f0; border: 2px dashed rgba(74, 54, 41, 0.3); border-radius: 20px; padding: 25px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 210px; cursor: pointer; transition: all 0.2s; box-sizing: border-box;" onmouseover="this.style.background='#eae8e3'; this.style.borderColor='rgba(74, 54, 41, 0.5)';" onmouseout="this.style.background='#f4f3f0'; this.style.borderColor='rgba(74, 54, 41, 0.3)';">
        <div style="background: rgba(74, 54, 41, 0.08); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
            <i class="fa-solid fa-plus" style="color: #4A3629; font-size: 1.3rem;"></i>
        </div>
        <span style="color: #4A3629; font-weight: 800; font-size: 0.95rem; letter-spacing: -0.2px;">Add New Room</span>
    </div>
</div>

<div id="addRoomModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="background: white; border-radius: 20px; padding: 35px; width: 100%; max-width: 450px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); border: 1px solid rgba(0,0,0,0.05); position: relative; margin: 20px;">
<<<<<<< HEAD
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: #4A3629; font-weight: 800; margin: 0; font-size: 1.4rem;">Add New Room Entry</h2>
=======
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: var(--primary); font-weight: 800; margin: 0; font-size: 1.4rem;">Add New Room Entry</h2>
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
            <button onclick="toggleRoomModal(false)" style="background: none; border: none; color: #636e72; font-size: 1.2rem; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('admin.rooms.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            <div>
<<<<<<< HEAD
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #57606f; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px;">Room Number / Name</label>
                <input type="text" name="room_number" required placeholder="e.g. 104 or Room 302" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #57606f; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px;">Room Classification Class</label>
                <select name="room_type" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box; background-color: white; color: #2f3542;">
                    <option value="Normal" selected>Normal Layout Class</option>
                    <option value="Premium">Premium Deluxe Layout</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #57606f; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px;">Monthly Rental Rate (₱)</label>
                <input type="number" name="price" required placeholder="e.g. 3500" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            </div>
=======
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Room Number / Name</label>
                <input type="text" name="room_number" required placeholder="e.g. 104 or Room 302" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Monthly Rental Rate (₱)</label>
                <input type="number" name="price" required placeholder="e.g. 3500" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            </div>

>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
            <div style="display: flex; gap: 12px; margin-top: 10px;">
                <button type="button" onclick="toggleRoomModal(false)" style="flex: 1; background: #f1f2f6; color: #2f3542; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
                    Cancel
                </button>
<<<<<<< HEAD
                <button type="submit" style="flex: 1; background: #4A3629; color: white; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
=======
                <button type="submit" class="btn-primary" style="flex: 1; background: var(--primary); color: white; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
                    Save Room
                </button>
            </div>
        </form>
    </div>
</div>

<<<<<<< HEAD
<div id="editRoomModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="background: white; border-radius: 20px; padding: 35px; width: 100%; max-width: 450px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); border: 1px solid rgba(0,0,0,0.05); position: relative; margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: #4A3629; font-weight: 800; margin: 0; font-size: 1.4rem;">Modify Room Details</h2>
            <button onclick="toggleEditModal(false)" style="background: none; border: none; color: #636e72; font-size: 1.2rem; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editRoomForm" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            @method('PUT')
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #57606f; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px;">Room Number / Name</label>
                <input type="text" id="edit_room_number" name="room_number" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #57606f; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px;">Room Classification Class</label>
                <select id="edit_room_type" name="room_type" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box; background-color: white; color: #2f3542;">
                    <option value="Normal">Normal Layout Class</option>
                    <option value="Premium">Premium Deluxe Layout</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #57606f; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px;">Monthly Rental Rate (₱)</label>
                <input type="number" id="edit_price" name="price" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #57606f; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px;">Operational Status</label>
                <select id="edit_status" name="status" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box; background-color: white; color: #2f3542;">
                    <option value="Available">Available</option>
                    <option value="Occupied">Occupied</option>
                    <option value="Maintenance">Maintenance</option>
                </select>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 10px;">
                <button type="button" onclick="toggleEditModal(false)" style="flex: 1; background: #f1f2f6; color: #2f3542; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="flex: 1; background: #4A3629; color: white; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
                    Apply Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleRoomModal(show) {
    document.getElementById('addRoomModal').style.display = show ? 'flex' : 'none';
}

function toggleEditModal(show) {
    document.getElementById('editRoomModal').style.display = show ? 'flex' : 'none';
}

function openEditModal(room) {
    const form = document.getElementById('editRoomForm');
    form.action = `/admin/rooms/${room.id}`;
    
    document.getElementById('edit_room_number').value = room.room_number;
    document.getElementById('edit_room_type').value = room.room_type || 'Normal';
    document.getElementById('edit_price').value = Math.round(room.price);
    document.getElementById('edit_status').value = room.status;
    
    toggleEditModal(true);
}

// 10-Second High-Fidelity Background Dynamic Refresh Sync
setInterval(function() {
    fetch("{{ route('admin.rooms.data') }}")
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('roomsGridContainer');
            let contentHTML = '';

            data.rooms.forEach(room => {
                const isPremium = room.room_type === 'Premium';
                const badgeStyle = isPremium ? 'background: rgba(255, 212, 59, 0.18); color: #ffd43b; border: 1px solid rgba(255, 212, 59, 0.25);' : 'background: rgba(255, 255, 255, 0.12); color: #f1f2f6; border: 1px solid rgba(255, 255, 255, 0.15);';
                
                let statusColor = '#2ed573';
                let statusIcon = 'fa-circle-dot';
                let statusText = 'Vacant';

                if (room.status === 'Occupied') {
                    statusColor = '#ff6b81';
                    statusIcon = 'fa-user-lock';
                    statusText = 'Occupied';
                } else if (room.status === 'Maintenance') {
                    statusColor = '#ffa502';
                    statusIcon = 'fa-screwdriver-wrench';
                    statusText = 'Maintenance';
                }

                const formattedPrice = parseFloat(room.price.replace(/[^\d.]/g, '')).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

                const sanitizedRoomObj = JSON.stringify({
                    id: room.id,
                    room_number: room.room_number,
                    room_type: room.room_type,
                    price: parseFloat(room.price.replace(/[^\d.]/g, '')),
                    status: room.status
                }).replace(/"/g, '&quot;');

                contentHTML += `
                <div class="card" style="background: #4A3629; border-radius: 20px; padding: 25px; box-shadow: 0 12px 30px rgba(74, 54, 41, 0.15); text-align: center; position: relative; display: flex; flex-direction: column; justify-content: space-between; min-height: 210px; box-sizing: border-box;">
                    <div style="position: absolute; top: 15px; right: 15px;">
                        <span style="font-size: 0.7rem; font-weight: 800; padding: 5px 12px; border-radius: 30px; letter-spacing: 0.5px; ${badgeStyle}">
                            ${room.room_type.toUpperCase()}
                        </span>
                    </div>

                    <div style="margin-top: 20px;">
                        <h3 style="margin: 0 0 6px 0; color: #ffffff; font-size: 1.4rem; font-weight: 800; letter-spacing: -0.3px;">Room ${room.room_number}</h3>
                        <p style="margin: 0 0 15px 0; font-size: 0.85rem; font-weight: 700; letter-spacing: 0.2px; color: ${statusColor};">
                            <i class="fa-solid ${statusIcon}" style="font-size: 0.75rem; margin-right: 5px;"></i>
                            ${statusText}
                        </p>
                    </div>
                    
                    <div style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="text-align: left;">
                            <span style="display: block; font-size: 0.65rem; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Monthly Rate</span>
                            <span style="color: #ffffff; font-weight: 800; font-size: 1.05rem;">₱${formattedPrice}</span>
                        </div>
                        <button onclick="openEditModal(${sanitizedRoomObj})" style="border: none; background: rgba(255,255,255,0.1); color: #ffffff; width: 34px; height: 34px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-pen-to-square" style="font-size: 0.9rem;"></i>
                        </button>
                    </div>
                </div>`;
            });

            contentHTML += `
            <div onclick="toggleRoomModal(true)" style="background: #f4f3f0; border: 2px dashed rgba(74, 54, 41, 0.3); border-radius: 20px; padding: 25px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 210px; cursor: pointer; box-sizing: border-box;">
                <div style="background: rgba(74, 54, 41, 0.08); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                    <i class="fa-solid fa-plus" style="color: #4A3629; font-size: 1.3rem;"></i>
                </div>
                <span style="color: #4A3629; font-weight: 800; font-size: 0.95rem; letter-spacing: -0.2px;">Add New Room</span>
            </div>`;

            container.innerHTML = contentHTML;
        })
        .catch(error => console.error('Silent room channel heartbeat failed to sync:', error));
}, 10000);
=======
<script>
function toggleRoomModal(show) {
    const modal = document.getElementById('addRoomModal');
    if (show) {
        modal.style.display = 'flex';
    } else {
        modal.style.display = 'none';
    }
}
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
</script>
@endsection