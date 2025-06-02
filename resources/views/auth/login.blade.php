<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <form method="POST" action="{{ route('login') }}" class="bg-white p-6 rounded shadow-md w-96">
        @csrf
        <h2 class="text-xl font-semibold mb-4">Login</h2>

        @if ($errors->any())
            <div class="text-red-500 text-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <label class="block text-sm font-medium">Email</label>
        <input type="email" name="email" required class="w-full border p-2 rounded mb-3">

        <label class="block text-sm font-medium">Password</label>
        <input type="password" name="password" required class="w-full border p-2 rounded mb-3">

        <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Login</button>
    </form>
</body>
</html>
