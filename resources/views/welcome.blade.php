@extends('layouts.app')

@section('content')
<section id="beranda" class="hero">
    <div class="container hero-content">
        <h1>Temukan Kenyamanan<br>Tinggal di <span class="highlight-text">Rusunawa Untan</span></h1>
        <p>Asrama mahasiswa dengan fasilitas lengkap, aman, dan harga terjangkau untuk menunjang kegiatan akademik dan karakter kewirausahaan Anda.</p>
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
            <h2>Pilih Kamar Sesuai Kebutuhan Anda</h2>
            <p>Kami menyediakan dua tipe kamar yang dirancang khusus untuk kenyamanan belajar dan istirahat Anda.</p>
        </div>

        <div class="room-grid">
            @foreach($roomTypes as $index => $type)
                <div class="room-card animate-on-scroll" style="transition-delay: {{ $index * 150 }}ms;">
                    <div class="room-image-wrapper">
                        @if($type->name === 'AC')
                            <img src="{{ asset('images/rooms/ac.png') }}" alt="Kamar AC" class="room-image">
                            <div class="room-badge">Rekomendasi</div>
                        @else
                            <img src="{{ asset('images/rooms/kipas.png') }}" alt="Kamar Kipas" class="room-image">
                        @endif
                    </div>
                    
                    <div class="room-content">
                        <h3>Kamar {{ $type->name }}</h3>
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
@endsection
