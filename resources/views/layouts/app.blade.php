<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Boarding House System') }}</title>
    
    <!-- Using CDN instead of Vite -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200 p-4 shadow-sm">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="font-bold text-xl text-blue-600">
                    <a href="{{ route('admin.dashboard') }}">BH Admin</a>
                </div>
                <div class="flex space-x-6 text-sm font-medium">
                    <a href="{{ route('admin.tenants') }}" class="hover:text-blue-600">Tenants</a>
                    <a href="{{ route('admin.rooms') }}" class="hover:text-blue-600">Rooms</a>[cite: 2]
                    <a href="{{ route('admin.payments') }}" class="hover:text-blue-600">Payments</a>[cite: 2]
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>