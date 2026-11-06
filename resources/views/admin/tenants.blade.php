@extends('layouts.admin')

@section('content')
<<<<<<< HEAD
=======
@if(session('success'))
    <div style="background-color: #e8f5e9; border: 1px solid #00b894; color: #00b894; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 style="color: var(--primary); font-weight: 800; margin: 0;">Tenant Directory</h1>
    <a href="{{ route('admin.create-tenant') }}" class="btn-primary">
        <i class="fa-solid fa-user-plus"></i> Register Tenant
    </a>
</div>

<div class="card" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #f5f5f4; color: var(--secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 15px 10px;">Tenant Name</th>
                <th style="padding: 15px 10px;">Assigned Room</th>
                <th style="padding: 15px 10px;">Contact Number</th>
                <th style="padding: 15px 10px;">Lease Status</th>
                <th style="padding: 15px 10px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="tenant-table-body">
            @foreach($tenants as $tenant)
            <tr style="border-bottom: 1px solid #f5f5f4; font-size: 0.95rem; color: #2d3436; transition: background 0.2s;">
<<<<<<< HEAD
                <td style="padding: 15px 10px; font-weight: 600; color: var(--primary);">{{ $tenant->name }}</td>
                <td style="padding: 15px 10px;">
                    <span style="font-weight: 700; color: var(--secondary);">Room {{ $tenant->room->room_number ?? 'Unassigned' }}</span>
                </td>
                <td style="padding: 15px 10px; color: #636e72;">{{ $tenant->phone }}</td>
=======
                <td style="padding: 15px 10px; font-weight: 600; color: var(--primary);">{{ $tenant['name'] }}</td>
                <td style="padding: 15px 10px;">
                    <span style="font-weight: 700; color: var(--secondary);">
                        Room {{ $tenant['room_id'] ?? ($tenant['room'] ?? 'N/A') }}
                    </span>
                </td>
                <td style="padding: 15px 10px; color: #636e72;">{{ $tenant['phone'] }}</td>
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
                <td style="padding: 15px 10px;">
                    <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;
                        background: {{ $tenant->status == 'Active' ? '#e3fcef' : '#fff5f5' }};
                        color: {{ $tenant->status == 'Active' ? '#00b894' : '#ff7675' }};">
                        {{ $tenant->status }}
                    </span>
                </td>
                <td style="padding: 15px 10px;">
                    <div style="display: flex; justify-content: center; align-items: center; gap: 15px;">
                        <a href="{{ route('admin.tenants.edit', $tenant->id) }}" style="color: var(--secondary); text-decoration: none; font-size: 1.1rem;" title="Edit Tenant">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <form action="{{ route('admin.tenants.destroy', $tenant->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this tenant?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="border: none; background: none; color: #ff7675; cursor: pointer; font-size: 1.1rem;" title="Delete Tenant">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
    // Set up the interval
    setInterval(function() {
        const url = "{{ route('admin.tenants.data') }}?t=" + new Date().getTime();
        const csrfToken = "{{ csrf_token() }}"; // Capture the token
        
        fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('tenant-table-body');
            tbody.innerHTML = ''; 

            data.tenants.forEach(tenant => {
                const statusColor = tenant.status === 'Active' ? '#00b894' : '#ff7675';
                const statusBg = tenant.status === 'Active' ? '#e3fcef' : '#fff5f5';
                const roomDisplay = tenant.room ? tenant.room.room_number : 'Unassigned';

                // We recreate the delete form exactly as it appears in the static PHP version
                const row = `
                    <tr style="border-bottom: 1px solid #f5f5f4; font-size: 0.95rem; color: #2d3436;">
                        <td style="padding: 15px 10px; font-weight: 600; color: var(--primary);">${tenant.name}</td>
                        <td style="padding: 15px 10px;">
                            <span style="font-weight: 700; color: var(--secondary);">Room ${roomDisplay}</span>
                        </td>
                        <td style="padding: 15px 10px; color: #636e72;">${tenant.phone}</td>
                        <td style="padding: 15px 10px;">
                            <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; background: ${statusBg}; color: ${statusColor};">
                                ${tenant.status}
                            </span>
                        </td>
                        <td style="padding: 15px 10px;">
                            <div style="display: flex; justify-content: center; align-items: center; gap: 15px;">
                                <a href="/admin/tenants/${tenant.id}/edit" style="color: var(--secondary); text-decoration: none; font-size: 1.1rem;">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="/admin/tenants/${tenant.id}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this tenant?');">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" style="border: none; background: none; color: #ff7675; cursor: pointer; font-size: 1.1rem;" title="Delete Tenant">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        })
        .catch(error => console.error('Silent Refresh Error:', error));
    }, 3000);
</script>
@endpush

@endsection