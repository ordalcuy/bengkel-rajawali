import hotkeys from 'hotkeys-js';

let hasInitializedForThisPage = false;

// Penanda bahwa modul telah dimuat
console.log('📦 shortcuts.js dimuat');
window.__shortcutsLoaded = true;

// Utility throttle agar init tidak dipanggil terlalu sering saat DOM berubah cepat
let lastInitAt = 0;
function tryInitShortcuts(reason = 'unknown') {
    const now = Date.now();
    if (now - lastInitAt < 200) return; // throttle 200ms
    lastInitAt = now;
    hasInitializedForThisPage = false; // izinkan init lagi
    console.log(`⏩ Memicu initShortcuts karena: ${reason}`);
    initShortcuts();
}

function initShortcuts() {
    // Hindari inisialisasi berulang dalam siklus event yang sama
    if (hasInitializedForThisPage) {
        return;
    }
    hasInitializedForThisPage = true;
    console.log('🎯 Inisialisasi shortcuts keyboard...');

    // Pastikan tidak terjadi duplikasi binding ketika halaman Filament
    // melakukan re-render dengan membersihkan binding yang kita gunakan.
    try {
        hotkeys.unbind('/');
        hotkeys.unbind('alt+n');
        hotkeys.unbind('alt+r');
        hotkeys.unbind('alt+s');
        hotkeys.unbind('esc');
        hotkeys.unbind('alt+p');
        hotkeys.unbind('alt+c');
    } catch (e) {
        // abaikan jika library belum siap
    }

    /**
     * Helper function untuk mencegah shortcut aktif saat sedang mengetik di dalam input, textarea, dll.
     */
    const withInputGuard = (callback) => {
        return (event, handler) => {
            const element = event.target || event.srcElement;
            const isInput =
                element.tagName === 'INPUT' ||
                element.tagName === 'TEXTAREA' ||
                element.isContentEditable;
            if (!isInput) {
                event.preventDefault();
                callback(event, handler);
            }
        };
    };

    const navigateTo = (target) => {
        // Jika diberikan path langsung atau URL penuh, arahkan langsung
        if (typeof target === 'string' && (target.startsWith('/') || target.startsWith('http'))) {
            window.location.href = target;
            return;
        }

        // Jika diberikan selector, cari elemen anchor terlebih dahulu
        const element = document.querySelector(target);
        if (element && element.href) {
            window.location.href = element.href;
        }
    };

    const clickElement = (selector) => {
        const element = document.querySelector(selector);
        if (element) {
            element.click();
        }
    };

    const focusElement = (selector) => {
        const element = document.querySelector(selector);
        if (element) {
            element.focus();
        }
    };

    //======================================================================
    // 1. HALAMAN DAFTAR ANTREN & RIWAYAT ANTREN
    //======================================================================
    if (window.location.pathname.includes('/antreans') || window.location.pathname.includes('/riwayat-antreans')) {
        hotkeys('/', withInputGuard((event) => {
            event.preventDefault();
            focusElement('input[type="search"]');
        }));

        hotkeys('alt+n', withInputGuard(() => {
            if (window.location.pathname.includes('/riwayat-antreans')) {
                clickElement('table tbody tr:first-child a[title="Antre Lagi"]');
            } else {
                navigateTo('a[href$="/antreans/create"]');
            }
        }));

        hotkeys('alt+r', withInputGuard(() => {
            if (window.Livewire) {
                Livewire.dispatch('refreshAntreanList');
                console.log('Daftar antrean diperbarui...');
            }
        }));
    }

    //======================================================================
    // 2. HALAMAN FORM "BUAT / EDIT"
    //======================================================================
    if (window.location.pathname.includes('/create') || window.location.pathname.includes('/edit')) {
        hotkeys('alt+s', (event) => {
            event.preventDefault();
            clickElement('button[type="submit"]');
        });

        hotkeys('esc', withInputGuard(() => {
            clickElement('a.fi-btn-color-gray');
        }));

        hotkeys('alt+p', (event) => {
            event.preventDefault();
            focusElement('input[name="data.nomor_plat"], input[name="data.new_nomor_plat"]');
        });

        hotkeys('alt+c', (event) => {
            event.preventDefault();
            focusElement('input[name="data.nama_pengunjung"], input[name="data.nama_pengunjung_plat"]');
        });
    }

    if (window.location.pathname.includes('/riwayat-antreans')) {
        hotkeys('esc', withInputGuard(() => {
            navigateTo('/admin');
        }));
    }

    console.log('✅ Shortcuts keyboard siap.');
}

// Pemicu inisialisasi pada berbagai event yang relevan (Filament + Livewire + DOM)
window.addEventListener('filament::pageRendered', () => {
    tryInitShortcuts('filament::pageRendered');
});

// Beberapa proyek/versi menggunakan nama event berbeda (fallback)
window.addEventListener('filament::page-rendered', () => {
    tryInitShortcuts('filament::page-rendered');
});

document.addEventListener('livewire:navigated', () => {
    tryInitShortcuts('livewire:navigated');
});

document.addEventListener('livewire:initialized', () => {
    tryInitShortcuts('livewire:initialized');
});

// Fallback ketika halaman pertama kali dimuat
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(() => tryInitShortcuts('document ready'), 0);
} else {
    document.addEventListener('DOMContentLoaded', () => tryInitShortcuts('DOMContentLoaded'));
}

// MutationObserver untuk menangkap pergantian konten SPA (Filament/Livewire)
try {
    const observer = new MutationObserver((mutations) => {
        for (const m of mutations) {
            if (m.type === 'childList' && (m.addedNodes?.length || m.removedNodes?.length)) {
                tryInitShortcuts('MutationObserver childList');
                break;
            }
        }
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
    console.log('👀 MutationObserver aktif untuk shortcuts');
} catch (e) {
    console.warn('MutationObserver tidak aktif:', e);
}
