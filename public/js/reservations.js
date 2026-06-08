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

    // Ketersediaan Real-time
    const checkInInput = document.getElementById('check_in_date');
    const availabilityInfo = document.getElementById('availability_info');
    const btnConfirm = document.querySelector('.btn-confirm');

    function checkAvailability() {
        const typeId = typeSelect.value;
        const checkIn = checkInInput.value;
        const duration = durationInput.value;

        if (!typeId || !checkIn || !duration) {
            if(availabilityInfo) availabilityInfo.style.display = 'none';
            return;
        }

        const csrfToken = document.querySelector('input[name="_token"]');
        
        fetch('/api/check-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.value : ''
            },
            body: JSON.stringify({
                room_type_id: typeId,
                check_in_date: checkIn,
                duration_days: duration
            })
        })
        .then(response => response.json())
        .then(data => {
            if(availabilityInfo) {
                availabilityInfo.style.display = 'block';
                if (data.available > 0) {
                    availabilityInfo.style.color = '#10b981'; // Emerald
                    availabilityInfo.innerHTML = `<svg style="display:inline; vertical-align:middle; margin-right:4px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg> Tersedia: ${data.available} Kamar`;
                    if (btnConfirm) {
                        btnConfirm.disabled = false;
                        btnConfirm.style.opacity = '1';
                        btnConfirm.style.cursor = 'pointer';
                    }
                } else {
                    availabilityInfo.style.color = '#ef4444'; // Red
                    availabilityInfo.innerHTML = `<svg style="display:inline; vertical-align:middle; margin-right:4px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Maaf, Kamar Penuh / Tidak Tersedia`;
                    if (btnConfirm) {
                        btnConfirm.disabled = true;
                        btnConfirm.style.opacity = '0.5';
                        btnConfirm.style.cursor = 'not-allowed';
                    }
                }
            }
        })
        .catch(error => console.error('Error fetching availability:', error));
    }

    typeSelect.addEventListener('change', checkAvailability);
    if(checkInInput) checkInInput.addEventListener('change', checkAvailability);
    durationInput.addEventListener('input', checkAvailability);

    // Hitung saat pertama load
    calculatePrice();
    checkAvailability();
});
