<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · BoardersHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-sm">
        <h1 class="text-xl font-bold text-slate-800">BoardersHub</h1>
        <p class="mb-4 text-sm text-slate-500">Sign in to continue</p>

        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 p-3 text-sm text-rose-600">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs text-slate-500">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs text-slate-500">Password</label>
                <input type="password" name="password" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-semibold text-white">
                Log in
            </button>
        </form>
    </div>
</body>
</html>