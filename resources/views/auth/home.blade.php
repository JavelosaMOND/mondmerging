@auth
    <h1>Welcome, {{ Auth::user()->name }}!</h1>

    <form method="POST" action="/logout">
        @csrf
        <button type="submit">Logout</button>
    </form>
@endauth

@guest
    <h1>You are not logged in.</h1>
    <a href="/login">Login</a> or <a href="/register">Register</a>
@endguest
