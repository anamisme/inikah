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
    // PDF files: open in new tab (better mobile support for multi-page)
    if (url.toLowerCase().endsWith('.pdf')) {
        window.open(url, '_blank');
        return;
    }
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
        setTimeout(() => { document.getElementById('searchInput').focus(); }, 300);
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
    if (modal) { modal.classList.add('show'); document.body.style.overflow = 'hidden'; }
}

window.tutupModalBlanko = function() {
    const modal = document.getElementById('blankoModal');
    if (modal) { modal.classList.remove('show'); document.body.style.overflow = ''; }
}

// JADWAL MODAL CONTROLLER
window.bukaModalJadwal = function() {
    const modal = document.getElementById('jadwalModal');
    if (modal) { modal.classList.add('show'); document.body.style.overflow = 'hidden'; loadJadwalModal(); }
}

window.tutupModalJadwal = function() {
    const modal = document.getElementById('jadwalModal');
    if (modal) { modal.classList.remove('show'); document.body.style.overflow = ''; }
}

function loadJadwalModal() {
    const container = document.getElementById('jadwalModalContent');
    container.innerHTML = '<div class="text-center text-muted p-4"><div class="spinner-border spinner-border-sm text-success me-2" role="status"></div> Memuat data jadwal...</div>';
    fetch('api/jadwal-api.php?action=getJadwal')
        .then(r => r.json())
        .then(data => {
            if (!data || data.length === 0) {
                container.innerHTML = '<div class="text-center text-muted p-4"><span class="material-icons-outlined" style="font-size:48px;color:#cbd5e1;">event_busy</span><p class="mt-2" style="font-size:0.9rem;">Belum ada data jadwal akad.</p></div>';
                return;
            }
            window._jadwalData = data;
            renderJadwalList(data, container);
        })
        .catch(() => { container.innerHTML = '<div class="text-center p-4" style="color:#ef4444;font-size:0.9rem;">Gagal memuat data.</div>'; });
}

function renderJadwalList(data, container) {
    let html = '<div style="margin-bottom:12px;display:flex;gap:8px;flex-wrap:wrap;">';
    html += '<input type="text" id="jadwalSearchInput" placeholder="🔍 Cari nama..." style="flex:1;min-width:140px;padding:12px 16px;border-radius:12px;border:1.5px solid rgba(15,118,110,0.08);font-size:0.85rem;outline:none;background:#fff;">';
    html += '<input type="date" id="jadwalDateFilter" style="padding:12px 16px;border-radius:12px;border:1.5px solid rgba(15,118,110,0.08);font-size:0.85rem;outline:none;background:#fff;">';
    html += '</div>';
    html += '<div id="jadwalListContainer">';
    html += buildJadwalItems(data);
    html += '</div>';
    container.innerHTML = html;

    document.getElementById('jadwalSearchInput').addEventListener('input', filterJadwal);
    document.getElementById('jadwalDateFilter').addEventListener('change', filterJadwal);
}

function filterJadwal() {
    const query = (document.getElementById('jadwalSearchInput').value || '').toLowerCase().trim();
    const dateVal = document.getElementById('jadwalDateFilter').value || '';
    const filtered = window._jadwalData.filter(item => {
        const pria = (item.nama_pria || '').toLowerCase();
        const wanita = (item.nama_wanita || '').toLowerCase();
        const matchName = !query || pria.includes(query) || wanita.includes(query);
        const matchDate = !dateVal || item.tanggal_akad === dateVal;
        return matchName && matchDate;
    });
    document.getElementById('jadwalListContainer').innerHTML = buildJadwalItems(filtered);
}

function buildJadwalItems(data) {
    if (!data || data.length === 0) {
        return '<div style="text-align:center;padding:20px;color:#64748b;font-size:0.85rem;">Tidak ada data yang cocok.</div>';
    }
    let html = '<div style="display:flex;flex-direction:column;gap:10px;">';
    data.forEach(item => {
        html += '<div style="background:#fff;border-radius:14px;padding:14px 16px;border:1px solid rgba(15,118,110,0.08);">';
        html += '<p style="font-size:0.9rem;font-weight:700;margin-bottom:4px;">' + _esc(item.nama_pria) + ' & ' + _esc(item.nama_wanita) + '</p>';
        html += '<p style="font-size:0.78rem;color:#64748b;">📅 ' + _esc(item.tanggal_akad) + ' · ⏰ ' + _esc(item.waktu || '') + '</p>';
        html += '<p style="font-size:0.78rem;color:#64748b;">📍 ' + _esc(item.desa || '') + '</p>';
        html += '</div>';
    });
    html += '</div>';
    return html;
}

// PETUGAS MODAL CONTROLLER
window.bukaModalPetugas = function() {
    const modal = document.getElementById('petugasModal');
    if (modal) { modal.classList.add('show'); document.body.style.overflow = 'hidden'; loadPetugasModal(); }
}

window.tutupModalPetugas = function() {
    const modal = document.getElementById('petugasModal');
    if (modal) { modal.classList.remove('show'); document.body.style.overflow = ''; }
}

function loadPetugasModal() {
    const container = document.getElementById('petugasModalContent');
    container.innerHTML = '<div class="text-center text-muted p-4"><div class="spinner-border spinner-border-sm text-success me-2" role="status"></div> Memuat data petugas...</div>';
    fetch('api/jadwal-api.php?action=getPetugas')
        .then(r => r.json())
        .then(data => {
            if (!data || data.length === 0) {
                container.innerHTML = '<div class="text-center text-muted p-4"><span class="material-icons-outlined" style="font-size:48px;color:#cbd5e1;">person_off</span><p class="mt-2" style="font-size:0.9rem;">Belum ada data petugas.</p></div>';
                return;
            }
            window._petugasData = data;
            renderPetugasList(data, container);
        })
        .catch(() => { container.innerHTML = '<div class="text-center p-4" style="color:#ef4444;font-size:0.9rem;">Gagal memuat data.</div>'; });
}

function renderPetugasList(data, container) {
    let html = '<div style="margin-bottom:12px;display:flex;gap:8px;flex-wrap:wrap;">';
    html += '<input type="text" id="petugasSearchInput" placeholder="🔍 Cari nama..." style="flex:1;min-width:140px;padding:12px 16px;border-radius:12px;border:1.5px solid rgba(15,118,110,0.08);font-size:0.85rem;outline:none;background:#fff;">';
    html += '<input type="date" id="petugasDateFilter" style="padding:12px 16px;border-radius:12px;border:1.5px solid rgba(15,118,110,0.08);font-size:0.85rem;outline:none;background:#fff;">';
    html += '</div>';
    html += '<div id="petugasListContainer">';
    html += buildPetugasItems(data);
    html += '</div>';
    container.innerHTML = html;

    document.getElementById('petugasSearchInput').addEventListener('input', filterPetugas);
    document.getElementById('petugasDateFilter').addEventListener('change', filterPetugas);
}

function filterPetugas() {
    const query = (document.getElementById('petugasSearchInput').value || '').toLowerCase().trim();
    const dateVal = document.getElementById('petugasDateFilter').value || '';
    const filtered = window._petugasData.filter(item => {
        const petugas = (item.nama_petugas || '').toLowerCase();
        const pria = (item.nama_pria || '').toLowerCase();
        const wanita = (item.nama_wanita || '').toLowerCase();
        const matchName = !query || petugas.includes(query) || pria.includes(query) || wanita.includes(query);
        const matchDate = !dateVal || item.tanggal === dateVal;
        return matchName && matchDate;
    });
    document.getElementById('petugasListContainer').innerHTML = buildPetugasItems(filtered);
}

function buildPetugasItems(data) {
    if (!data || data.length === 0) {
        return '<div style="text-align:center;padding:20px;color:#64748b;font-size:0.85rem;">Tidak ada data yang cocok.</div>';
    }
    let html = '<div style="display:flex;flex-direction:column;gap:12px;">';
    data.forEach(item => {
        html += '<div style="background:#fff;border-radius:14px;padding:14px 16px;border:1px solid rgba(15,118,110,0.08);display:flex;align-items:center;gap:12px;">';
        if (item.foto) {
            html += '<img src="' + _esc(item.foto) + '" onclick="openFotoLightbox(this.src)" style="width:56px;height:56px;border-radius:12px;object-fit:cover;border:1px solid rgba(15,118,110,0.08);flex-shrink:0;cursor:pointer;" alt="Foto">';
        } else {
            html += '<div style="width:56px;height:56px;border-radius:12px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><span class="material-icons-outlined" style="color:#94a3b8;">person</span></div>';
        }
        html += '<div style="flex:1;min-width:0;">';
        html += '<p style="font-size:0.9rem;font-weight:700;margin-bottom:3px;">' + _esc(item.nama_petugas) + '</p>';
        html += '<p style="font-size:0.78rem;color:#64748b;">📅 ' + _esc(item.tanggal) + ' · ⏰ ' + _esc(item.waktu || '') + '</p>';
        html += '<p style="font-size:0.78rem;color:#64748b;">👤 ' + _esc(item.nama_pria) + ' & ' + _esc(item.nama_wanita) + '</p>';
        html += '</div></div>';
    });
    html += '</div>';
    return html;
}

// FOTO LIGHTBOX - popup foto ukuran asli
function openFotoLightbox(src) {
    // Hapus lightbox lama jika ada
    const existing = document.getElementById('fotoLightbox');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'fotoLightbox';
    overlay.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;padding:20px;cursor:zoom-out;opacity:0;transition:opacity 0.3s ease;';
    
    const img = document.createElement('img');
    img.src = src;
    img.style.cssText = 'max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.5);object-fit:contain;transform:scale(0.9);transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1);';
    
    overlay.appendChild(img);
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';

    // Animate in
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        img.style.transform = 'scale(1)';
    });

    // Tutup lightbox
    overlay.addEventListener('click', () => {
        overlay.style.opacity = '0';
        img.style.transform = 'scale(0.9)';
        setTimeout(() => {
            overlay.remove();
            document.body.style.overflow = '';
        }, 300);
    });
}
window.openFotoLightbox = openFotoLightbox;

// HTML escape helper
function _esc(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// CLOSE MODAL ON BACKDROP CLICK
window.addEventListener('DOMContentLoaded', function() {
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
});

// CERTIFICATE DATABASE QUERY EXECUTION
function prosesCariSertifikat() {
    const input      = document.getElementById('searchInput').value.trim();
    const loading    = document.getElementById('loading');
    const resultArea = document.getElementById('resultArea');

    if (!input) return alert('Silakan masukkan nama lengkap!');

    loading.classList.remove('d-none');
    resultArea.innerHTML = '';

    fetch("api/sertifikat.php?q=" + encodeURIComponent(input))
        .then(r => r.json())
        .then(data => {
            loading.classList.add('d-none');
            if (data.length === 0) {
                resultArea.innerHTML = '<div class="text-center text-muted p-4 page-view"><span class="material-icons-outlined" style="font-size:48px;color:#cbd5e1;">search_off</span><p class="mt-2" style="font-size:0.9rem;">Sertifikat belum terbit atau nama salah.<br><small>Pastikan nama sesuai dengan form Post-Test.</small></p></div>';
            } else {
                let html = '<div class="ios-list-group page-view">';
                data.forEach(item => {
                    const safeNama = document.createElement('span');
                    safeNama.textContent = item.nama || '';
                    const safeLink = (item.link || '').replace(/[^a-zA-Z0-9\-._~:/?#\[\]@!$&'()*+,;=%]/g, '');
                    html += '<a href="' + safeLink + '" target="_blank" rel="noopener noreferrer" class="ios-list-item"><div class="ios-list-left"><div class="ios-list-badge">E-CERT</div><div class="ios-list-title-box"><span class="ios-list-main-title" style="text-transform:uppercase;">' + safeNama.innerHTML + '</span><span style="font-size:0.75rem;color:var(--muted);">Sertifikat Siap Diunduh</span></div></div><span class="material-icons-outlined" style="color:var(--green-mid);">file_download</span></a>';
                });
                html += '</div>';
                resultArea.innerHTML = html;
            }
        })
        .catch(err => {
            loading.classList.add('d-none');
            alert('Gagal memuat data. Periksa kembali koneksi internet Anda.');
        });
}

document.getElementById("searchInput").addEventListener("keypress", e => {
    if (e.key === "Enter") prosesCariSertifikat();
});


// ════════════════════════════════
// NOTIFIKASI SYSTEM
// ════════════════════════════════
const NOTIF_SCRIPT_URL = 'api/notifikasi.php';

window.bukaModalNotif = function() {
    const modal = document.getElementById('notifModal');
    if (modal) { modal.classList.add('show'); document.body.style.overflow = 'hidden'; }
}

window.tutupModalNotif = function() {
    const modal = document.getElementById('notifModal');
    if (modal) { modal.classList.remove('show'); document.body.style.overflow = ''; }
}

function loadNotifications() {
    fetch(NOTIF_SCRIPT_URL + '?action=get')
        .then(r => r.json())
        .then(data => {
            if (!data || data.length === 0) { document.getElementById('notifBadge').style.display = 'none'; return; }
            renderNotifList(data);
            updateBadge(data);
        })
        .catch(err => console.log('Notif fetch skipped:', err.message));
}

function renderNotifList(data) {
    const container = document.getElementById('notifList');
    if (!data || data.length === 0) {
        container.innerHTML = '<div class="text-center text-muted p-4"><span class="material-icons-outlined" style="font-size:48px;color:#cbd5e1;">notifications_off</span><p class="mt-2" style="font-size:0.9rem;">Belum ada notifikasi.</p></div>';
        return;
    }
    let html = '';
    data.forEach(item => {
        const safeTitle = document.createElement('span');
        safeTitle.textContent = item.judul || '';
        const safeMsg = document.createElement('span');
        safeMsg.textContent = item.pesan || '';
        let tgl = item.tanggal || '';
        if (tgl && tgl.includes('T')) {
            const d = new Date(tgl);
            tgl = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
        html += '<div class="notif-item unread"><div class="notif-item-title">' + safeTitle.innerHTML + '</div><div class="notif-item-msg">' + safeMsg.innerHTML + '</div><div class="notif-item-date">' + tgl + '</div></div>';
    });
    container.innerHTML = html;
}

function updateBadge(data) {
    const badge = document.getElementById('notifBadge');
    const count = data.length;
    if (count > 0) { badge.textContent = count > 9 ? '9+' : count; badge.style.display = 'flex'; }
    else { badge.style.display = 'none'; }
}

setTimeout(loadNotifications, 3000);

// ════════════════════════════════
// BANNER CAROUSEL SYSTEM
// ════════════════════════════════
const BANNER_COLORS = [
    'linear-gradient(145deg, #064e3b, #0f766e)',
    'linear-gradient(145deg, #0369a1, #0c4a6e)',
    'linear-gradient(145deg, #7c3aed, #4c1d95)',
    'linear-gradient(145deg, #be185d, #831843)',
    'linear-gradient(145deg, #b45309, #78350f)',
    'linear-gradient(145deg, #1d4ed8, #1e3a8a)'
];

let bannerCurrent = 0;
let bannerTotal = 0;
let bannerInterval = null;

function loadBanners() {
    fetch(NOTIF_SCRIPT_URL + '?action=getBanners')
        .then(r => r.json())
        .then(data => {
            if (!data || data.length === 0) { document.getElementById('bannerCarousel').style.display = 'none'; return; }
            renderBanners(data);
        })
        .catch(() => {});
}

function renderBanners(data) {
    const carousel = document.getElementById('bannerCarousel');
    const track = document.getElementById('bannerTrack');
    const dotsContainer = document.getElementById('bannerDots');
    bannerTotal = data.length;
    if (bannerTotal === 0) { carousel.style.display = 'none'; return; }
    carousel.style.display = 'block';
    let trackHtml = '';
    let dotsHtml = '';
    data.forEach((item, i) => {
        const bg = item.gambar ? "background-image:url('" + item.gambar + "');background-size:cover;background-position:center;" : "background:" + (item.warna || BANNER_COLORS[i % BANNER_COLORS.length]) + ";";
        const safeTitle = document.createElement('span');
        safeTitle.textContent = item.judul || '';
        const safeTag = document.createElement('span');
        safeTag.textContent = item.tag || 'INFO';
        const link = item.link ? "onclick=\"window.open('" + item.link.replace(/'/g, "\\'") + "', '_blank')\"" : '';
        trackHtml += '<div class="banner-slide"><div class="banner-card" style="' + bg + '" ' + link + '><div class="banner-card-content"><div class="banner-tag">' + safeTag.innerHTML + '</div><div class="banner-title">' + safeTitle.innerHTML + '</div></div></div></div>';
        dotsHtml += '<span class="banner-dot ' + (i === 0 ? 'active' : '') + '" onclick="goToBanner(' + i + ')"></span>';
    });
    track.innerHTML = trackHtml;
    dotsContainer.innerHTML = dotsHtml;
    if (bannerTotal > 1) {
        bannerInterval = setInterval(() => { bannerCurrent = (bannerCurrent + 1) % bannerTotal; goToBanner(bannerCurrent); }, 4000);
    }
    let startX = 0;
    track.addEventListener('touchstart', e => { startX = e.changedTouches[0].screenX; }, { passive: true });
    track.addEventListener('touchend', e => {
        const diff = startX - e.changedTouches[0].screenX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) bannerCurrent = (bannerCurrent + 1) % bannerTotal;
            else bannerCurrent = (bannerCurrent - 1 + bannerTotal) % bannerTotal;
            goToBanner(bannerCurrent);
            resetBannerInterval();
        }
    }, { passive: true });
}

function goToBanner(index) {
    bannerCurrent = index;
    const track = document.getElementById('bannerTrack');
    const dots = document.querySelectorAll('.banner-dot');
    track.style.transform = 'translateX(-' + (index * 100) + '%)';
    dots.forEach((d, i) => d.classList.toggle('active', i === index));
}

function resetBannerInterval() {
    if (bannerInterval) clearInterval(bannerInterval);
    if (bannerTotal > 1) {
        bannerInterval = setInterval(() => { bannerCurrent = (bannerCurrent + 1) % bannerTotal; goToBanner(bannerCurrent); }, 4000);
    }
}

setTimeout(loadBanners, 1500);


// ════════════════════════════════
// ELASTIC OVERSCROLL EFFECT
// ════════════════════════════════
(function() {
    var startY = 0, currentY = 0, isDragging = false, isOverscrolling = false, elasticOffset = 0, velocity = 0, lastY = 0, lastTime = 0, animFrame = null;
    var getCards = function() { return document.querySelectorAll('.ios-main-card'); };

    function applyElastic(offset) {
        var cards = getCards();
        cards.forEach(function(card, i) {
            var individual = offset * (1 - i * 0.06);
            card.style.transform = 'translateY(' + individual + 'px)';
            card.style.transition = 'none';
        });
    }

    function releaseElastic() {
        var cards = getCards();
        cards.forEach(function(card, i) {
            setTimeout(function() {
                card.style.transition = 'transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                card.style.transform = 'translateY(0px)';
                setTimeout(function() { card.style.transition = ''; card.style.transform = ''; }, 700);
            }, i * 40);
        });
    }

    function isAtTop() { return window.scrollY <= 0; }
    function isAtBottom() { return (window.innerHeight + window.scrollY) >= document.body.scrollHeight - 2; }

    window.addEventListener('touchstart', function(e) {
        startY = e.touches[0].clientY; lastY = startY; lastTime = Date.now();
        isDragging = true; isOverscrolling = false; velocity = 0;
        if (animFrame) cancelAnimationFrame(animFrame);
    }, { passive: true });

    window.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        currentY = e.touches[0].clientY;
        var diff = currentY - startY;
        var now = Date.now();
        velocity = (currentY - lastY) / (now - lastTime || 1);
        lastY = currentY; lastTime = now;
        if (isAtTop() && diff > 0) { isOverscrolling = true; elasticOffset = Math.pow(diff, 0.7); applyElastic(elasticOffset); }
        else if (isAtBottom() && diff < 0) { isOverscrolling = true; elasticOffset = -Math.pow(Math.abs(diff), 0.7); applyElastic(elasticOffset); }
        else if (isOverscrolling) { elasticOffset = diff > 0 ? Math.pow(Math.abs(diff), 0.7) : -Math.pow(Math.abs(diff), 0.7); applyElastic(elasticOffset); }
    }, { passive: true });

    window.addEventListener('touchend', function() {
        isDragging = false;
        if (isOverscrolling) {
            isOverscrolling = false;
            if (Math.abs(velocity) > 0.5) {
                var bounce = velocity * 30;
                var cards = getCards();
                cards.forEach(function(card, i) {
                    card.style.transition = 'transform 0.2s ease-out';
                    card.style.transform = 'translateY(' + (elasticOffset + bounce * (1 - i * 0.06)) + 'px)';
                });
                setTimeout(releaseElastic, 180);
            } else { releaseElastic(); }
            elasticOffset = 0;
        }
    }, { passive: true });

    var wheelTimeout = null, wheelAccum = 0;
    window.addEventListener('wheel', function(e) {
        var atTop = isAtTop() && e.deltaY < 0;
        var atBottom = isAtBottom() && e.deltaY > 0;
        if (atTop || atBottom) {
            wheelAccum += e.deltaY * 0.3;
            wheelAccum = Math.max(-80, Math.min(80, wheelAccum));
            applyElastic(-wheelAccum);
            if (wheelTimeout) clearTimeout(wheelTimeout);
            wheelTimeout = setTimeout(function() { releaseElastic(); wheelAccum = 0; }, 150);
        }
    }, { passive: true });
})();
