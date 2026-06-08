/**
 * Profile Page Scripts
 */

document.addEventListener('DOMContentLoaded', function() {
    // Identity Modal Click Outside
    const idModal = document.getElementById('identityModal');
    if(idModal) {
        idModal.addEventListener('click', function(e) {
            if (e.target === this) closeIdentityModal();
        });
    }

    // Password Modal Click Outside
    const pwdModal = document.getElementById('passwordModal');
    if(pwdModal) {
        pwdModal.addEventListener('click', function(e) {
            if (e.target === this) closePasswordModal();
        });
    }

    // Auto open modals based on hidden data attributes (Refactored from inline blade scripts)
    const pageData = document.getElementById('profilePageData');
    if (pageData) {
        if (pageData.getAttribute('data-require-identity') === 'true') {
            openIdentityModal();
        }
        if (pageData.getAttribute('data-password-error') === 'true') {
            openPasswordModal();
        }
    }
});

function openIdentityModal() {
    const modal = document.getElementById('identityModal');
    if(modal) modal.classList.add('show');
}

function closeIdentityModal() {
    const modal = document.getElementById('identityModal');
    if(modal) {
        modal.classList.remove('show');
        // Fokuskan pada isian identitas
        setTimeout(() => {
            const idNumber = document.getElementById('identity_number');
            if(idNumber) {
                idNumber.focus();
                idNumber.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 300);
    }
}

function openPasswordModal() {
    const modal = document.getElementById('passwordModal');
    if(modal) modal.classList.add('show');
}

function closePasswordModal() {
    const modal = document.getElementById('passwordModal');
    if(!modal) return;
    
    modal.classList.remove('show');
    
    // Bersihkan formulir setelah animasi selesai (300ms)
    setTimeout(() => {
        // Kosongkan input
        const currentPwd = document.getElementById('current_pwd');
        const newPwd = document.getElementById('new_pwd');
        const newPwdConf = document.getElementById('new_pwd_conf');
        
        if(currentPwd) currentPwd.value = '';
        if(newPwd) newPwd.value = '';
        if(newPwdConf) newPwdConf.value = '';
        
        // Kembalikan mata ke mode tertutup (password)
        ['current_pwd', 'new_pwd', 'new_pwd_conf'].forEach(id => {
            const input = document.getElementById(id);
            if (input && input.type === 'text') {
                input.type = 'password';
                const btn = input.nextElementSibling;
                if(btn) {
                    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
                }
            }
        });

        // Hilangkan kotak merah peringatan error jika ada
        const errorAlert = document.querySelector('#passwordModal .profile-alert-danger');
        if (errorAlert) {
            errorAlert.style.display = 'none';
        }
    }, 300);
}

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    }
}
