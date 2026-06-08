@extends('layouts.app')

@section('content')
<section id="beranda" class="hero">
    <div class="container hero-content">
        <h1>Reservasi Hunian Harian<br><span class="highlight-text">Rusunawa Putri Untan</span></h1>
        <p>Fasilitas penginapan harian yang aman, nyaman, dan strategis khusus untuk mahasiswi dan tamu perempuan di lingkungan Universitas Tanjungpura.</p>
        <a href="#kamar" class="btn btn-primary btn-large">
            Lihat Kamar 
            <svg class="arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
    </div>
</section>

<!-- Catalog Section -->
<section id="kamar" class="catalog-section">
    <div class="container">
        <div class="section-header text-center animate-on-scroll">
            <h2>Pilih Kamar Harian Anda</h2>
            <p>Kami menyediakan dua tipe kamar harian yang dirancang khusus untuk kenyamanan istirahat Anda selama berada di Pontianak.</p>
        </div>

        <div class="room-grid">
            @foreach($roomTypes as $index => $type)
                <div class="room-card animate-on-scroll" style="transition-delay: {{ $index * 150 }}ms;">
                    <div class="room-image-wrapper">
                        @if($type->name === 'AC')
                            <img src="{{ asset('images/rooms/ac.jpg') }}" alt="Kamar AC" class="room-image" style="object-fit: cover;">
                            <div class="room-badge">Rekomendasi</div>
                        @else
                            <img src="{{ asset('images/rooms/kipas.jpg') }}" alt="Kamar Kipas" class="room-image" style="object-fit: cover;">
                        @endif
                    </div>
                    
                    <div class="room-content">
                        <div class="room-title-bar">
                            <h3>Kamar {{ $type->name }}</h3>
                            <span class="room-available-badge">
                                <svg class="svg-icon-inline" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                {{ $type->rooms_count }} Unit Tersedia
                            </span>
                        </div>
                        <div class="room-price">
                            <span class="currency">Rp</span>
                            <span class="amount">{{ number_format($type->base_price, 0, ',', '.') }}</span>
                            <span class="period">/ hari</span>
                        </div>

                        <div class="room-features">
                            <h4>Fasilitas Termasuk:</h4>
                            <ul>
                                <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Tempat Tidur & Kasur Nyaman</li>
                                <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Meja & Kursi Belajar</li>
                                <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Lemari Pakaian</li>
                                <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Kamar Mandi Dalam</li>
                                <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> {{ $type->name === 'AC' ? 'Pendingin Ruangan (AC)' : 'Kipas Angin (Efisien)' }}</li>
                            </ul>
                        </div>

                        <div class="room-footer">
                            <p class="extra-fee"><small>* Tambahan Rp {{ number_format($type->extra_person_fee, 0, ',', '.') }}/hari jika kamar diisi 3 orang.</small></p>
                            <a href="{{ route('reservations.create', ['type_id' => $type->id]) }}" class="btn btn-primary btn-block mt-15">Pesan Sekarang</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="kontak" class="contact-section">
    <div class="container">
        <div class="section-header text-center animate-on-scroll">
            <h2>Hubungi Kami</h2>
            <p>Punya pertanyaan atau butuh bantuan? Tim Rusunawa Untan siap membantu Anda.</p>
        </div>

        <div class="contact-grid">
            <div class="contact-card animate-on-scroll" style="transition-delay: 100ms;">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Lokasi</h3>
                <p>Jl. Prof. Dr. H. Hadari Nawawi, Kel. Bansir Laut, Kec. Pontianak Tenggara, Kota Pontianak, Kalimantan Barat 78112</p>
            </div>
            
            <div class="contact-card animate-on-scroll" style="transition-delay: 200ms;">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email</h3>
                <p><a href="mailto:sekretariat@rusunawa.untan.ac.id">sekretariat@rusunawa.untan.ac.id</a></p>
            </div>

            <div class="contact-card animate-on-scroll" style="transition-delay: 300ms;">
                <div class="contact-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h3>Call / WhatsApp</h3>
                <p><a href="https://wa.me/6289520352407" target="_blank">+62 895-2035-2407</a></p>
            </div>
        </div>
    </div>
</section>

@endsection
