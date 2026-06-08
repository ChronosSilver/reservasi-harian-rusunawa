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
    <link rel="stylesheet" href="{{ asset('css/tenant.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body>

    <!-- Header / Navbar -->
    <header class="navbar">
        <div class="nav-container nav-wrapper">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo Rusunawa Untan" class="logo-img">
                <span class="logo-text">Rusunawa <span class="highlight">Untan</span></span>
            </a>
            
            <nav class="nav-links">
                <a href="/#beranda" class="nav-item {{ request()->is('/') ? 'active' : '' }}">Beranda</a>
                <a href="/#kamar" class="nav-item">Katalog Kamar</a>
                <a href="/#kontak" class="nav-item">Hubungi Kami</a>
                @auth
                    <a href="{{ route('reservations.index') }}" class="nav-item {{ request()->routeIs(['reservations.index', 'reservations.create']) ? 'active' : '' }}">Reservasi</a>
                    <a href="{{ route('reservations.history') }}" class="nav-item {{ request()->routeIs('reservations.history') ? 'active' : '' }}">Riwayat</a>
                @endauth
            </nav>
            
            <div class="nav-actions">
                @auth
                    <div class="profile-dropdown">
                        <button class="profile-btn" id="profileBtn" aria-haspopup="true" aria-expanded="false">
                            @php
                                $navNames = explode(' ', Auth::user()->name);
                                $navInitials = '';
                                foreach (array_slice($navNames, 0, 2) as $navNamePart) {
                                    $navInitials .= strtoupper(substr($navNamePart, 0, 1));
                                }
                            @endphp
                            <div class="avatar">{{ $navInitials }}</div>
                            <span class="profile-name">{{ explode(' ', Auth::user()->name)[0] }}</span>
                            <svg class="dropdown-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <a href="{{ route('profile.index') }}" class="dropdown-item dropdown-item-flex">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-icon-left"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Detail Profil
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="/logout" class="dropdown-form">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger dropdown-item-flex-full">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-icon-left"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Logout
                                </button>
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
            <div class="footer-copyright">
                &copy; <strong>Universitas Tanjungpura</strong>
            </div>
            <div class="footer-social">
                <a href="https://www.youtube.com/channel/UCPJspK3WqL8-Dgvy2JGCnoA" class="social-icon" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="https://api.whatsapp.com/send?phone=6289520352407&text=Halo Min" class="social-icon" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                <a href="https://instagram.com/rusunawa.untan?igshid=YmMyMTA2M2Y=" class="social-icon" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@rusunawauntan" class="social-icon" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
                <a href="https://www.facebook.com/profile.php?id=100084108862928" class="social-icon" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            </div>
        </div>
    </footer>

    <!-- Scroll Top Button -->
    <a href="#" class="scroll-top" title="Kembali ke atas"><i class="fas fa-arrow-up"></i></a>

    <script src="{{ asset('js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
