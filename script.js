/* ══════════════════════════════════════════════
   SPLASH SCREEN
   ══════════════════════════════════════════════ */
window.addEventListener('DOMContentLoaded', () => {
    const splash = document.getElementById('splashScreen');
    setTimeout(createSplashHearts, 400);
    setTimeout(() => {
        if (splash) {
            splash.style.opacity = '0';
            splash.style.transform = 'scale(1.05)';
            setTimeout(() => splash.style.display = 'none', 700);
        }
    }, 2500);

    // Restore theme
    const saved = localStorage.getItem('inikah-theme');
    if (saved === 'dark') {
        document.body.classList.add('dark-mode');
        document.getElementById('themeToggle').classList.add('active');
        updateToggleIcon(true);
    } else {
        updateToggleIcon(false);
    }
});

function createSplashHearts() {
    const splash = document.getElementById('splashScreen');
    const icons = ['favorite', 'favorite_border'];
    for (let i = 0; i < 10; i++) {
        setTimeout(() => {
            if (!splash || splash.style.display === 'none') return;
            const h = document.createElement('span');
            h.className = 'material-icons-outlined splash-heart';
            h.innerText = icons[Math.floor(Math.random() * icons.length)];
            h.style.left = `${20 + Math.random() * 60}%`;
            h.style.fontSize = `${16 + Math.random() * 14}px`;
            splash.appendChild(h);
            setTimeout(() => h.remove(), 2000);
        }, i * 130);
    }
}

/* ══════════════════════════════════════════════
   THEME TOGGLE
   ══════════════════════════════════════════════ */
const toggleTrack = document.getElementById('themeToggle');
const toggleIcon = document.getElementById('toggleIcon');

function updateToggleIcon(isDark) {
    toggleIcon.textContent = isDark ? 'dark_mode' : 'light_mode';
}

updateToggleIcon(document.body.classList.contains('dark-mode'));

toggleTrack.addEventListener('click', (e) => {
    e.stopPropagation();
    const isDark = document.body.classList.toggle('dark-mode');
    toggleTrack.classList.toggle('active');
    updateToggleIcon(isDark);
    localStorage.setItem('inikah-theme', isDark ? 'dark' : 'light');
    document.body.style.transition = 'none';
    requestAnimationFrame(() => { document.body.style.transition = ''; });
});

toggleTrack.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleTrack.click();
    }
});

/* ══════════════════════════════════════════════
   ACCORDION
   ══════════════════════════════════════════════ */
function toggleCard(subId, chevId) {
    const sub = document.getElementById(subId);
    const chev = document.getElementById(chevId);
    if (!sub || !chev) return;
    const isOpen = sub.classList.contains('open');
    sub.classList.toggle('open');
    chev.classList.toggle('open');
    const inner = sub.querySelector('.submenu-inner');
    if (inner && !isOpen) inner.classList.add('has-border');
}

function toggleBooks(e) {
    e.stopPropagation();
    const shelf = document.getElementById('innerBookshelf');
    const chev = document.getElementById('bookChevron');
    if (shelf && chev) {
        shelf.classList.toggle('open');
        chev.classList.toggle('open');
    }
}

/* ══════════════════════════════════════════════
   MODAL IFRAME
   ══════════════════════════════════════════════ */
window.bukaModalFrame = function(url, judul) {
    const m = document.getElementById('appModal');
    const f = document.getElementById('appModalFrame');
    const t = document.getElementById('appModalTitle');
    if (m && f && t) {
        t.innerText = judul;
        f.src = url;
        m.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
};

window.tutupModalFrame = function() {
    const m = document.getElementById('appModal');
    const f = document.getElementById('appModalFrame');
    if (m && f) {
        m.classList.remove('show');
        document.body.style.overflow = '';
        setTimeout(() => { f.src = ''; }, 400);
    }
};

/* ══════════════════════════════════════════════
   MODAL SERTIFIKAT
   ══════════════════════════════════════════════ */
window.bukaModalSertifikat = function() {
    const m = document.getElementById('searchModal');
    if (m) {
        m.classList.add('show');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('searchInput').focus(), 300);
    }
};

window.tutupModalSertifikat = function() {
    const m = document.getElementById('searchModal');
    if (m) {
        m.classList.remove('show');
        document.body.style.overflow = '';
        document.getElementById('searchInput').value = '';
        document.getElementById('resultArea').innerHTML = '';
    }
};

/* ══════════════════════════════════════════════
   MODAL BLANGKO
   ══════════════════════════════════════════════ */
window.bukaModalBlanko = function() {
    const m = document.getElementById('blankoModal');
    if (m) {
        m.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
};

window.tutupModalBlanko = function() {
    const m = document.getElementById('blankoModal');
    if (m) {
        m.classList.remove('show');
        document.body.style.overflow = '';
    }
};

/* ══════════════════════════════════════════════
   CLOSE MODAL ON BACKDROP
   ══════════════════════════════════════════════ */
document.querySelectorAll('.modal-overlay').forEach(overlay => {
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

/* ══════════════════════════════════════════════
   CARI SERTIFIKAT
   ══════════════════════════════════════════════ */
function prosesCariSertifikat() {
    const input = document.getElementById('searchInput').value.trim();
    const loading = document.getElementById('loading');
    const resultArea = document.getElementById('resultArea');

    if (!input) {
        alert('Silakan masukkan nama lengkap!');
        return;
    }

    loading.classList.remove('d-none');
    resultArea.innerHTML = '';

    const urlWebApp = 'https://script.google.com/macros/s/AKfycbzSsypF03AF6k9b9N2s_rmQsk6-kLVuErjwGSPQdX3fC6zBhiUsCuMMwkpTnoRBq450Dg/exec?q=' +
        encodeURIComponent(input);

    fetch(urlWebApp)
        .then(r => r.json())
        .then(data => {
            loading.classList.add('d-none');
            if (data.length === 0) {
                resultArea.innerHTML = `
                    <div class="empty-state">
                        <span class="material-icons-outlined empty-icon">search_off</span>
                        <p>Sertifikat belum terbit atau nama tidak sesuai.<br>
                           <small>Pastikan nama sesuai dengan formulir Post-Test.</small></p>
                    </div>`;
            } else {
                let html = '<div class="result-list">';
                data.forEach(item => {
                    html += `
                        <a href="${item.link}" target="_blank" class="result-item">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div class="result-badge">E-CERT</div>
                                <div>
                                    <p style="font-size:0.87rem;font-weight:700;margin:0;text-transform:uppercase;">${item.nama}</p>
                                    <p style="font-size:0.7rem;color:var(--muted);margin:0;">Sertifikat siap diunduh</p>
                                </div>
                            </div>
                            <span class="material-icons-outlined" style="font-size:22px;color:var(--emerald);">file_download</span>
                        </a>`;
                });
                html += '</div>';
                resultArea.innerHTML = html;
            }
        })
        .catch(err => {
            loading.classList.add('d-none');
            alert('Gagal memuat data. Periksa kembali koneksi internet Anda.');
            console.error(err);
        });
}

document.getElementById('searchInput').addEventListener('keypress', e => {
    if (e.key === 'Enter') prosesCariSertifikat();
});
