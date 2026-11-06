<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - IPK Boarding House</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            /* Your 60-30-10 Color Rule */
            --primary-brown: #3d2b1f; /* Primary 60% */
            --bg-cream: #F5F5DC;      /* Secondary 30% */
            --accent-tan: #D7CCC8;    /* Accent 10% */
            --glass-white: rgba(255, 255, 255, 0.55);
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Animated Live Background */
        .live-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(45deg, #F5F5DC, #D7CCC8, #EFEBE9);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Sidebar - Cozy Habitat Style */
        .sidebar {
            width: 260px;
            background-color: var(--primary-brown);
            height: 100vh;
            color: white;
            padding: 30px 20px;
            position: fixed;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            z-index: 100;
            box-sizing: border-box;
        }

        .logo { 
            font-size: 1.4rem; 
            font-weight: 800; 
            margin-bottom: 40px; 
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 14px 18px;
            margin-bottom: 8px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-item i { width: 20px; text-align: center; }

        .nav-item:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-item.active {
            background-color: var(--accent-tan);
            color: var(--primary-brown);
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .main-content { 
            margin-left: 260px; 
            padding: 40px; 
            min-height: 100vh;
            width: calc(100% - 260px);
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="live-bg"></div>

    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-house-chimney-user"></i>
            <span>IPK RESIDENT</span>
        </div>
        
        <a href="{{ route('client.dashboard') }}" class="nav-item {{ Route::is('client.dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        
        <a href="{{ route('client.payment') }}" class="nav-item {{ Route::is('client.payment') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i> Online Payment
        </a>

        <a href="{{ route('client.history') }}" class="nav-item {{ Route::is('client.history') ? 'active' : '' }}">
            <i class="fas fa-clock-rotate-left"></i> Billing History
        </a>

        <a href="{{ route('client.settings') }}" class="nav-item {{ Route::is('client.settings') ? 'active' : '' }}">
            <i class="fas fa-sliders-h"></i> Portal Settings
        </a>

        <div style="position: absolute; bottom: 30px; width: 220px; left: 20px; box-sizing: border-box;">
            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
            
            <form id="logout-form" action="{{ route('client.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>

            <a href="#" class="nav-item" style="color: #ff7675; background: rgba(255,118,117,0.05);"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-power-off"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        @yield('content')
    </div>
</body>
</html>