<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Login - IPK Boardinghouse</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-dark: #3E2723;   /* Deep Espresso Brown */
            --accent-coffee: #5D4037;  /* Warm Coffee Accent */
            --light-brown: #A1887F;    /* Soft Light Brown Gradient Anchor */
            --warm-stone: #EFEBE9;     /* Creamy Stone Light Brown Anchor */
            --custom-gold: #d97706;    /* Button/Icon Highlight */
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }

        body { 
            margin: 0; 
            display: flex; 
            height: 100vh; 
            width: 100vw;
            overflow: hidden;
            align-items: center;
            justify-content: center;
            background: linear-gradient(-45deg, var(--primary-dark), var(--accent-coffee), var(--light-brown), var(--warm-stone));
            background-size: 400% 400%;
            animation: brownGradientShift 15s ease infinite;
        }

        @keyframes brownGradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-card { 
            width: 400px; 
            background: rgba(255, 255, 255, 0.12); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 50px 40px; 
            border-radius: 24px; 
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25); 
            border: 1px solid rgba(255, 255, 255, 0.18);
            text-align: center;
            animation: cardFadeUp 0.8s ease-out;
        }

        @keyframes cardFadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-title { 
            color: #ffffff; 
            font-size: 2.2rem; 
            font-weight: 800; 
            margin-bottom: 6px; 
            letter-spacing: -1px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 40px;
        }

        .form-group { 
            position: relative;
            margin-bottom: 20px; 
        }

        .form-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input { 
            width: 100%; 
            padding: 15px 16px 15px 48px; 
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15); 
            border-radius: 14px; 
            outline: none; 
            font-size: 0.95rem;
            color: #ffffff;
            transition: var(--transition); 
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-group input:focus { 
            background: rgba(255, 255, 255, 0.15);
            border-color: #ffffff; 
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1); 
        }

        .form-group input:focus + i {
            color: #ffffff;
        }

        .btn-submit { 
            width: 100%; 
            padding: 15px; 
            background: var(--primary-dark); 
            color: #ffffff; 
            border: none; 
            border-radius: 14px; 
            font-weight: 700; 
            font-size: 1rem;
            cursor: pointer; 
            transition: var(--transition); 
            margin-top: 12px; 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-submit:hover { 
            background: var(--accent-coffee); 
            transform: translateY(-2px); 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-submit i {
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .btn-submit:hover i {
            transform: translateX(4px);
        }

        .error-container {
            background: rgba(239, 83, 80, 0.2); 
            border: 1px solid rgba(239, 83, 80, 0.4); 
            color: #ff9494; 
            padding: 12px 16px; 
            border-radius: 14px; 
            font-size: 0.85rem; 
            font-weight: 600; 
            margin-bottom: 24px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-title">IPK Boardinghouse</div>
        <div class="brand-subtitle">Resident Portal Console</div>
        
        <form action="{{ route('client.login.submit') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="error-container">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="form-group">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" required autocomplete="off">
                <i class="fa-solid fa-envelope"></i>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fa-solid fa-lock"></i>
            </div>
            
            <button type="submit" class="btn-submit">
                <span>Sign In</span> <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>

</body>
</html>