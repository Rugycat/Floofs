<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Floofs</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header>
        <div class="logo">Floofs</div>
        <nav>
            <ul class="nav-links">
                <li><a href="#" data-page="users">Users</a></li>
                <li><a href="#" data-page="pets">Pets</a></li>
                <li><a href="#" data-page="health-records">Health Records</a></li>
            </ul>
            <div class="hamburger"><i class="fa fa-bars"></i></div>
        </nav>
    </header>

    <main id="app-content">
        @yield('content')
    </main>

    <footer>
        <p>&copy; 2025 Floofs App</p>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
