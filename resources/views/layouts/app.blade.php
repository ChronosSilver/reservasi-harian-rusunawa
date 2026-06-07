<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Rusunawa Universitas Tanjungpura</title>
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body>

    <!-- Header / Navbar -->
    <header class="navbar">
        <div class="nav-container nav-wrapper">
            <a href="/" class="logo">
                <span class="logo-text">Rusunawa <span class="highlight">Untan</span></span>
            </a>
            
            <nav class="nav-links">
                <a href="/#beranda" class="nav-item {{ request()->is('/') ? 'active' : '' }}">Beranda</a>
                <a href="/#kamar" class="nav-item">Katalog Kamar</a>
                @auth
                    <a href="{{ route('reservations.index') }}" class="nav-item {{ request()->routeIs(['reservations.index', 'reservations.create']) ? 'active' : '' }}">Reservasi</a>
                    <a href="{{ route('reservations.history') }}" class="nav-item {{ request()->routeIs('reservations.history') ? 'active' : '' }}">Riwayat</a>
                @endauth
            </nav>
            
            <div class="nav-actions">
                @auth
                    <div class="profile-dropdown">
                        <button class="profile-btn" id="profileBtn" aria-haspopup="true" aria-expanded="false">
                            <div class="avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                            <span class="profile-name">{{ explode(' ', Auth::user()->name)[0] }}</span>
                            <svg class="dropdown-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <a href="#" class="dropdown-item">Detail Profil</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="/logout" class="dropdown-form">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="/register" class="btn btn-outline">Daftar</a>
                    <a href="/login" class="btn btn-primary">Login <span class="arrow">&rarr;</span></a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-brand">
                <h3>Rusunawa Untan</h3>
                <p>Asrama Karakter Kewirausahaan Universitas Tanjungpura.</p>
            </div>
            <div class="footer-links">
                <h4>Tautan Cepat</h4>
                <ul>
                    <li><a href="/">Beranda</a></li>
                    <li><a href="#kamar">Tipe Kamar</a></li>
                    <li><a href="/login">Login Penyewa</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Kontak</h4>
                <p>Jalan Prof. Dr. H. Hadari Nawawi</p>
                <p>Pontianak, Kalimantan Barat</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Booking Rusunawa Universitas Tanjungpura. All rights reserved.</p>
        </div>
    </footer>

    <script src="{{ asset('js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
