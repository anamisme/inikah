// SPLASH SCREEN & HEARTS EFFECT
window.addEventListener('DOMContentLoaded', () => {
    const splash = document.getElementById('splashScreen');
    setTimeout(() => { createSplashHearts(); }, 500);

    setTimeout(() => {
        if (splash) {
            splash.style.opacity = '0';
            splash.style.transform = 'scale(1.06)';
            setTimeout(() => splash.style.display = 'none', 800);
        }
    }, 2600);

    // Restore theme preference
    const saved = localStorage.getItem('inikah-theme');
    if (saved === 'dark') {
        document.body.classList.add('dark-mode');
        updateToggleIcon(true);
    }
});

// THEME TOGGLE
const themeToggle = document.getElementById('themeToggle');
const toggleIcon = document.getElementById('toggleIcon');

function updateToggleIcon(isDark) {
    toggleIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
}

themeToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    const isDark = document.body.classList.toggle('dark-mode');
    updateToggleIcon(isDark);
    localStorage.setItem('inikah-theme', isDark ? 'dark' : 'light');
});

themeToggle.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        themeToggle.click();
    }
});

function createSplashHearts() {
    const splash = document.getElementById('splashScreen');
    const icons = ['favorite', 'favorite_border'];
    for (let i = 0; i < 10; i++) {
        setTimeout(() => {
            if(!splash || splash.style.display === 'none') return;
            const heart = document.createElement('span');
            heart.className = 'material-icons-outlined splash-heart';
            heart.innerText = icons[Math.floor(Math.random() * icons.length)];
            heart.style.left = `${Math.floor(Math.random() * 60) + 20}%`;
            heart.style.fontSize = `${Math.floor(Math.random() * 12) + 16}px`;
            splash.appendChild(heart);
            setTimeout(() => heart.remove(), 2000);
        }, i * 140);
    }
}

// ACCORDION NAVIGATION COLLAPSIBLE
function toggleMainMenu(idSubmenu, idChevron) {
    const submenu = document.getElementById(idSubmenu);
    const chevron = document.getElementById(idChevron);
    if (submenu && chevron) {
        const card = submenu.closest('.ios-main-card');
        submenu.classList.toggle('active');
        chevron.classList.toggle('rotated');
        if (card) card.classList.toggle('expanded');
    }
}

function toggleBookShelf(event) {
    event.stopPropagation();
    const shelf = document.getElementById('innerBookshelf');
    const chev  = document.getElementById('bookChevron');
    if (shelf && chev) {
        shelf.classList.toggle('show');
        chev.style.transform = shelf.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
    }
}

// UNIVERSAL FLOATING MODAL SYSTEM
window.bukaModalFrame = function(url, judul) {
    const modal = document.getElementById('appModal');
    const frame = document.getElementById('appModalFrame');
    const title = document.getElementById('appModalTitle');
    
    if (modal && frame && title) {
        title.innerText = judul;
        frame.src = url;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; 
    }
}

window.tutupModalFrame = function() {
    const modal = document.getElementById('appModal');
    const frame = document.getElementById('appModalFrame');
    
    if (modal && frame) {
        modal.classList.remove('show');
        document.body.style.overflow = ''; 
        setTimeout(() => { frame.src = ""; }, 400); 
    }
}

// SERTIFIKAT FLOATING MODAL CONTROLLER
window.bukaModalSertifikat = function() {
    const modal = document.getElementById('searchModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            document.getElementById('searchInput').focus();
        }, 300);
    }
}

window.tutupModalSertifikat = function() {
    const modal = document.getElementById('searchModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        document.getElementById('searchInput').value = '';
        document.getElementById('resultArea').innerHTML = '';
    }
}

// BLANGKO FLOATING MODAL CONTROLLER
window.bukaModalBlanko = function() {
    const modal = document.getElementById('blankoModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

window.tutupModalBlanko = function() {
    const modal = document.getElementById('blankoModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// CLOSE MODAL ON BACKDROP CLICK
document.querySelectorAll('.app-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target !== overlay) return;
        overlay.classList.remove('show');
        document.body.style.overflow = '';
        const frame = overlay.querySelector('iframe');
        if (frame) setTimeout(() => { frame.src = ''; }, 400);
        const inp = overlay.querySelector('#searchInput');
        const ra = overlay.querySelector('#resultArea');
        if (inp) inp.value = '';
        if (ra) ra.innerHTML = '';
    });
});

// CERTIFICATE DATABASE QUERY EXECUTION
function prosesCariSertifikat() {
    const input      = document.getElementById('searchInput').value.trim();
    const loading    = document.getElementById('loading');
    const resultArea = document.getElementById('resultArea');

    if (!input) return alert('Silakan masukkan nama lengkap!');

    loading.classList.remove('d-none');
    resultArea.innerHTML = '';

    const urlWebApp = "https://script.google.com/macros/s/AKfycbzSsypF03AF6k9b9N2s_rmQsk6-kLVuErjwGSPQdX3fC6zBhiUsCuMMwkpTnoRBq450Dg/exec?q=" + encodeURIComponent(input);

    fetch(urlWebApp)
        .then(r => r.json())
        .then(data => {
            loading.classList.add('d-none');
            if (data.length === 0) {
                resultArea.innerHTML = `
                    <div class="text-center text-muted p-4 page-view">
                        <span class="material-icons-outlined" style="font-size:48px;color:#cbd5e1;">search_off</span>
                        <p class="mt-2" style="font-size:0.9rem;">Sertifikat belum terbit atau nama salah.<br><small>Pastikan nama sesuai dengan form Post-Test.</small></p>
                    </div>`;
            } else {
                let html = `<div class="ios-list-group page-view">`;
                data.forEach(item => {
                    // Sanitasi output untuk mencegah XSS
                    const safeNama = document.createElement('span');
                    safeNama.textContent = item.nama || '';
                    const safeLink = (item.link || '').replace(/[^a-zA-Z0-9\-._~:/?#\[\]@!$&'()*+,;=%]/g, '');
                    html += `
                        <a href="${safeLink}" target="_blank" rel="noopener noreferrer" class="ios-list-item">
                            <div class="ios-list-left">
                                <div class="ios-list-badge">E-CERT</div>
                                <div class="ios-list-title-box">
                                    <span class="ios-list-main-title" style="text-transform:uppercase;">${safeNama.innerHTML}</span>
                                    <span style="font-size:0.75rem;color:var(--muted);">Sertifikat Siap Diunduh</span>
                                </div>
                            </div>
                            <span class="material-icons-outlined" style="color:var(--green-mid);">file_download</span>
                        </a>`;
                });
                html += `</div>`;
                resultArea.innerHTML = html;
            }
        })
        .catch(err => {
            loading.classList.add('d-none');
            alert('Gagal memuat data. Periksa kembali koneksi internet Anda.');
            console.error(err);
        });
}

document.getElementById("searchInput").addEventListener("keypress", e => {
    if (e.key === "Enter") prosesCariSertifikat();
});


// ════════════════════════════════
// NOTIFIKASI SYSTEM
// ════════════════════════════════
const NOTIF_SCRIPT_URL = 'https://script.google.com/macros/s/AKfycbx4TWRNB8tmdmob5nD0uxL8Vnq0fJt832u4wktfZUEpUfZ75GwNYNLbEy3iO6Xy3EwgQQ/exec';

window.bukaModalNotif = function() {
    const modal = document.getElementById('notifModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        markAllRead();
    }
}

window.tutupModalNotif = function() {
    const modal = document.getElementById('notifModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function loadNotifications() {
    fetch(NOTIF_SCRIPT_URL + '?action=get')
        .then(r => r.json())
        .then(data => {
            if (!data || data.length === 0) return;
            renderNotifList(data);
            updateBadge(data);
        })
        .catch(err => console.log('Notif fetch skipped:', err.message));
}

function renderNotifList(data) {
    const container = document.getElementById('notifList');
    const readIds = JSON.parse(localStorage.getItem('inikah-notif-read') || '[]');

    if (data.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted p-4">
                <span class="material-icons-outlined" style="font-size:48px;color:#cbd5e1;">notifications_off</span>
                <p class="mt-2" style="font-size:0.9rem;">Belum ada notifikasi.</p>
            </div>`;
        return;
    }

    let html = '';
    data.forEach(item => {
        const isUnread = !readIds.includes(item.id);
        const safeTitle = document.createElement('span');
        safeTitle.textContent = item.judul || '';
        const safeMsg = document.createElement('span');
        safeMsg.textContent = item.pesan || '';
        html += `
            <div class="notif-item ${isUnread ? 'unread' : ''}">
                <div class="notif-item-title">${safeTitle.innerHTML}</div>
                <div class="notif-item-msg">${safeMsg.innerHTML}</div>
                <div class="notif-item-date">${item.tanggal || ''}</div>
            </div>`;
    });
    container.innerHTML = html;
}

function updateBadge(data) {
    const badge = document.getElementById('notifBadge');
    const readIds = JSON.parse(localStorage.getItem('inikah-notif-read') || '[]');
    const unreadCount = data.filter(item => !readIds.includes(item.id)).length;

    if (unreadCount > 0) {
        badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

function markAllRead() {
    fetch(NOTIF_SCRIPT_URL + '?action=get')
        .then(r => r.json())
        .then(data => {
            if (!data || data.length === 0) return;
            const allIds = data.map(item => item.id);
            localStorage.setItem('inikah-notif-read', JSON.stringify(allIds));
            document.getElementById('notifBadge').style.display = 'none';
            renderNotifList(data);
        })
        .catch(() => {});
}

// Load notifikasi saat halaman siap
setTimeout(loadNotifications, 3000);
