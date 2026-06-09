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

/* =========================================
   Fungsi untuk Halaman Index Reservasi
   ========================================= */

function toggleActionDropdown(id) {
    document.querySelectorAll('.dropdown-menu').forEach(el => {
        if (el.id !== id && el.id !== 'dropdownMenu') {
            el.classList.remove('show');
        }
    });
    const el = document.getElementById(id);
    if (el) el.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    document.querySelectorAll('.dropdown-menu').forEach(el => {
        if (el.id !== 'dropdownMenu') {
            el.classList.remove('show');
        }
    });
});

function openPaymentDetailModal(data) {
    const body = document.getElementById('payment-detail-body');
    if (!body) return;
    
    if (!data) {
        body.innerHTML = '<div class="alert alert-warning">Belum ada data pembayaran untuk reservasi ini.</div>';
    } else {
        let statusBadge = '';
        if (data.status === 'PAID' || data.status === 'VERIFIED') statusBadge = 'badge-confirmed';
        else if (data.status === 'PENDING') statusBadge = 'badge-pending';
        else if (data.status === 'REJECTED') statusBadge = 'badge-rejected';
        else statusBadge = 'badge-completed';

        body.innerHTML = `
            <div class="modal-status-row">
                <span class="modal-detail-label">Status Pembayaran</span>
                <span class="badge ${statusBadge}">${data.status}</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Tanggal Terakhir</span>
                <span class="modal-detail-value">${data.date}</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Metode Pembayaran</span>
                <span class="modal-detail-value">${data.method}</span>
            </div>
            ${data.method === 'Transfer Bank' ? `
            <div class="modal-detail-row">
                <span class="modal-detail-label">Rekening Tujuan</span>
                <span class="modal-detail-value">${data.bank}</span>
            </div>` : ''}
            
            ${data.proof ? `
            <div style="margin-top: 15px; padding-bottom: 15px; border-bottom: 1px dashed var(--border-color); text-align: center;">
                <p class="modal-detail-label" style="text-align: left; margin-bottom: 10px;">Foto Bukti Transfer</p>
                <a href="${data.proof}" target="_blank" style="display: block; overflow: hidden; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <img src="${data.proof}" alt="Bukti Transfer" style="width: 100%; max-height: 250px; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                </a>
                <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 8px; margin-bottom: 0;">Klik gambar untuk melihat ukuran penuh</p>
            </div>` : ''}

            <div class="modal-total-row">
                <span class="modal-total-label">Total Dibayar</span>
                <span class="modal-total-value">${data.amount}</span>
            </div>
        `;
    }
    const modal = document.getElementById('paymentDetailModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closePaymentDetailModal() {
    const modal = document.getElementById('paymentDetailModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function openReservationModal(data) {
    document.getElementById('modal-ticket').innerText = data.ticket;
    document.getElementById('modal-created-at').innerText = data.createdAt;
    document.getElementById('modal-room').innerText = data.room;
    document.getElementById('modal-checkin').innerText = data.checkIn;
    document.getElementById('modal-checkout').innerText = data.checkOut;
    document.getElementById('modal-duration').innerText = data.duration;
    document.getElementById('modal-payment').innerText = data.payment;
    document.getElementById('modal-total').innerText = data.total;
    
    const statusEl = document.getElementById('modal-status');
    statusEl.innerText = data.status;
    statusEl.className = 'badge ' + data.statusClass;
    
    document.getElementById('reservationModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeReservationModal() {
    const modal = document.getElementById('reservationModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function openCancelModal(id, ticket) {
    document.getElementById('cancel-ticket-code').innerText = ticket;
    const form = document.getElementById('cancelForm');
    form.action = `/reservations/${id}/cancel`;
    
    document.getElementById('cancelModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeCancelModal() {
    const modal = document.getElementById('cancelModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('cancelForm').reset();
    }
}

// Close on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeReservationModal();
        closeCancelModal();
        closePaymentDetailModal();
        closeHistoryDetailModal(); // Added history modal close
    }
});

/* =========================================
   Fungsi untuk Halaman Riwayat Reservasi
   ========================================= */

function openHistoryDetailModal(data) {
    const histTicket = document.getElementById('hist-ticket');
    if (!histTicket) return; // Prevent error if not on history page

    histTicket.innerText = data.ticket;
    document.getElementById('hist-room').innerText = data.room;
    document.getElementById('hist-checkin').innerText = data.checkIn;
    document.getElementById('hist-checkout').innerText = data.checkOut;
    document.getElementById('hist-duration').innerText = data.duration;
    document.getElementById('hist-total').innerText = data.total;
    
    const statusEl = document.getElementById('hist-status');
    statusEl.innerText = data.status;
    statusEl.className = 'badge ' + data.statusClass;
    
    const payContainer = document.getElementById('hist-payment-container');
    if (!data.payment) {
        payContainer.innerHTML = '<div class="alert alert-warning">Belum ada data pembayaran untuk reservasi ini.</div>';
    } else {
        let pStatusBadge = '';
        if (data.payment.status === 'PAID' || data.payment.status === 'VERIFIED') pStatusBadge = 'badge-confirmed';
        else if (data.payment.status === 'PENDING') pStatusBadge = 'badge-pending';
        else if (data.payment.status === 'REJECTED') pStatusBadge = 'badge-rejected';
        else if (data.payment.status === 'REFUNDED') pStatusBadge = 'badge-completed';
        else pStatusBadge = 'badge-completed';

        let html = `
            <div class="modal-status-row">
                <span class="modal-detail-label">Status Pembayaran</span>
                <span class="badge ${pStatusBadge}">${data.payment.status}</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Tanggal Transaksi</span>
                <span class="modal-detail-value">${data.payment.date}</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Total Dibayar</span>
                <span class="modal-detail-value" style="font-weight:700; color:var(--primary);">${data.payment.amount}</span>
            </div>
        `;
        
        if (data.payment.proof) {
            html += `
            <div style="margin-top: 15px; margin-bottom: 15px;">
                <span class="modal-detail-label">Bukti Transfer Penyewa:</span>
                <a href="${data.payment.proof}" target="_blank" class="proof-box">
                    <img src="${data.payment.proof}" alt="Bukti Transfer" class="proof-img">
                </a>
                <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 5px;">Klik gambar untuk memperbesar</p>
            </div>
            `;
        }
        
        if (data.payment.refund_proof) {
            html += `
            <div style="margin-top: 15px; margin-bottom: 15px; padding-top: 15px; border-top: 1px dashed #cbd5e1;">
                <span class="modal-detail-label" style="color: #059669;">Bukti Pengembalian Dana (Refund) dari Admin:</span>
                <a href="${data.payment.refund_proof}" target="_blank" class="proof-box">
                    <img src="${data.payment.refund_proof}" alt="Bukti Refund" class="proof-img">
                </a>
                <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 5px;">Klik gambar untuk memperbesar</p>
            </div>
            `;
        }

        payContainer.innerHTML = html;
    }

    document.getElementById('historyDetailModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeHistoryDetailModal() {
    const modal = document.getElementById('historyDetailModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/* =========================================
   Event Listeners (Refactored from inline)
   ========================================= */
document.addEventListener('DOMContentLoaded', function() {
    // 1. Dropdown Trigger
    const dropdownTriggers = document.querySelectorAll('.js-dropdown-trigger');
    dropdownTriggers.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const targetId = this.getAttribute('data-target');
            toggleActionDropdown(targetId);
        });
    });

    // 2. Stop Propagation
    const stopPropElements = document.querySelectorAll('.js-stop-prop');
    stopPropElements.forEach(el => {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // 3. Payment Detail Trigger
    const paymentDetailTriggers = document.querySelectorAll('.js-payment-detail-trigger');
    paymentDetailTriggers.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const targetId = this.getAttribute('data-target');
            toggleActionDropdown(targetId);
            const paymentData = JSON.parse(this.getAttribute('data-payment'));
            openPaymentDetailModal(paymentData);
        });
    });

    // 4. Cancel Trigger
    const cancelTriggers = document.querySelectorAll('.js-cancel-trigger');
    cancelTriggers.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const targetId = this.getAttribute('data-target');
            toggleActionDropdown(targetId);
            const id = this.getAttribute('data-id');
            const ticket = this.getAttribute('data-ticket');
            openCancelModal(id, ticket);
        });
    });

    // 5. Close Cancel Modal
    const closeCancelBtns = document.querySelectorAll('.js-close-cancel-modal');
    closeCancelBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            closeCancelModal();
        });
    });

    // 6. Reservation Card Click
    const reservationCards = document.querySelectorAll('.js-reservation-card');
    reservationCards.forEach(card => {
        card.addEventListener('click', function() {
            const data = JSON.parse(this.getAttribute('data-reservation'));
            openReservationModal(data);
        });
    });

    // 7. History Row Click
    const historyRows = document.querySelectorAll('.js-history-row');
    historyRows.forEach(row => {
        row.addEventListener('click', function() {
            const data = JSON.parse(this.getAttribute('data-history'));
            openHistoryDetailModal(data);
        });
    });

    // 8. Close History Modal
    const closeHistoryBtns = document.querySelectorAll('.js-close-history-modal');
    closeHistoryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            closeHistoryDetailModal();
        });
    });
});
