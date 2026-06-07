document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('room_type_id');
    const guestSelect = document.getElementById('guest_count');
    const durationInput = document.getElementById('duration_days');
    
    const summaryBase = document.getElementById('summary_base');
    const summaryExtra = document.getElementById('summary_extra');
    const summaryTotal = document.getElementById('summary_total');
    const calcDays = document.getElementById('calc_days');
    const extraRow = document.getElementById('extra_fee_row');

    if (!typeSelect || !guestSelect || !durationInput) return; // Mencegah error jika dipanggil di halaman lain

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    function calculatePrice() {
        const option = typeSelect.options[typeSelect.selectedIndex];
        if (!option || !option.value) {
            summaryBase.innerText = 'Rp 0';
            summaryExtra.innerText = 'Rp 0';
            summaryTotal.innerText = 'Rp 0';
            return;
        }

        const basePrice = parseFloat(option.getAttribute('data-price')) || 0;
        const extraFee = parseFloat(option.getAttribute('data-extra')) || 0;
        const days = parseInt(durationInput.value) || 1;
        const guests = parseInt(guestSelect.value) || 1;

        calcDays.innerText = days;

        // Harga Dasar = Harga per hari * jumlah hari
        const totalBase = basePrice * days;
        summaryBase.innerText = formatRupiah(totalBase);

        // Biaya Ekstra = Biaya ekstra per hari * jumlah hari (Jika tamu 3 orang)
        let totalExtra = 0;
        if (guests === 3) {
            totalExtra = extraFee * days;
            extraRow.style.display = 'flex';
        } else {
            extraRow.style.display = 'none';
        }
        summaryExtra.innerText = formatRupiah(totalExtra);

        // Total
        const total = totalBase + totalExtra;
        summaryTotal.innerText = formatRupiah(total);
    }

    typeSelect.addEventListener('change', calculatePrice);
    guestSelect.addEventListener('change', calculatePrice);
    durationInput.addEventListener('input', calculatePrice);

    // Hitung saat pertama load
    calculatePrice();
});
