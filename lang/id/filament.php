<?php

return [
    'actions' => [
        'attach' => 'Lampirkan',
        'attach_another' => 'Lampirkan Lainnya',
        'cancel' => 'Batal',
        'close' => 'Tutup',
        'create' => 'Buat',
        'delete' => 'Hapus',
        'detach' => 'Lepas',
        'edit' => 'Edit',
        'replicate' => 'Duplikasi',
        'save' => 'Simpan',
        'view' => 'Lihat',
    ],
    'forms' => [
        'actions' => [
            'create' => 'Buat',
            'create_another' => 'Buat & Buat Lagi',
            'save' => 'Simpan',
            'save_and_close' => 'Simpan & Tutup',
        ],
        'fields' => [
            'is_required' => 'Wajib diisi',
            'is_searchable' => 'Dapat dicari',
        ],
        'placeholders' => [
            'select' => 'Pilih opsi...',
            'search' => 'Cari...',
        ],
        'validation' => [
            'required' => 'Field ini wajib diisi.',
            'email' => 'Field ini harus berupa alamat email yang valid.',
            'url' => 'Field ini harus berupa URL yang valid.',
            'integer' => 'Field ini harus berupa angka.',
            'numeric' => 'Field ini harus berupa angka.',
            'min' => 'Field ini harus minimal :min karakter.',
            'max' => 'Field ini tidak boleh lebih dari :max karakter.',
            'unique' => 'Nilai ini sudah digunakan.',
        ],
    ],
    'modals' => [
        'actions' => [
            'cancel' => 'Batal',
            'confirm' => 'Konfirmasi',
            'submit' => 'Kirim',
        ],
        'delete' => [
            'heading' => 'Konfirmasi Hapus',
            'description' => 'Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.',
            'actions' => [
                'cancel' => 'Batal',
                'delete' => 'Ya, Hapus',
            ],
        ],
    ],
    'navigation' => [
        'account' => 'Akun',
        'dashboard' => 'Dashboard',
        'logout' => 'Keluar',
        'profile' => 'Profil',
    ],
    'pages' => [
        'actions' => [
            'create' => 'Buat',
            'edit' => 'Edit',
            'view' => 'Lihat',
        ],
    ],
    'tables' => [
        'actions' => [
            'delete' => 'Hapus',
            'edit' => 'Edit',
            'view' => 'Lihat',
        ],
        'bulk_actions' => [
            'delete' => 'Hapus Terpilih',
        ],
        'columns' => [
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diperbarui Pada',
        ],
        'empty' => [
            'heading' => 'Tidak ada data',
            'description' => 'Belum ada data yang tersedia.',
        ],
        'filters' => [
            'actions' => [
                'reset' => 'Reset',
            ],
        ],
        'pagination' => [
            'actions' => [
                'next' => 'Selanjutnya',
                'previous' => 'Sebelumnya',
            ],
            'label' => 'Navigasi halaman',
            'overview' => 'Menampilkan :first hingga :last dari :total hasil',
        ],
        'search' => [
            'label' => 'Cari',
            'placeholder' => 'Cari...',
        ],
        'sorting' => [
            'label' => 'Urutkan',
        ],
    ],
    'widgets' => [
        'stats' => [
            'overview' => 'Ringkasan Statistik',
        ],
    ],
];
