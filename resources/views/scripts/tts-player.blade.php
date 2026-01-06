<script>
    document.addEventListener('livewire:initialized', () => {
        console.log('TTS Player Diinisialisasi.');

        // Fungsi TTS dengan opsi repeat
        const playSound = (textParts, repeat = 1) => {
            if (!('speechSynthesis' in window)) {
                console.error('API Speech Synthesis tidak didukung oleh browser ini.');
                return;
            }

            // Batalkan suara yang sedang berjalan
            window.speechSynthesis.cancel();

            let count = 0;

            const speakSequence = () => {
                if (count >= repeat) return;

                let idx = 0;

                const speakNext = () => {
                    if (idx >= textParts.length) {
                        count++;
                        if (count < repeat) {
                            setTimeout(speakSequence, 1000); // jeda 1 detik antar repeat
                        }
                        return;
                    }

                    const utterance = new SpeechSynthesisUtterance(textParts[idx]);
                    utterance.lang = 'id-ID';
                    utterance.rate = 1.5; // lebih cepat
                    utterance.pitch = 1;

                    utterance.onend = () => {
                        idx++;
                        setTimeout(speakNext, 500); // jeda 0.5 detik antar bagian
                    };

                    window.speechSynthesis.speak(utterance);
                };

                speakNext();
            };

            speakSequence();
        };

        // Format angka → dibaca digit per digit, dengan jeda
        const formatNomorAntrean = (nomor) => {
            if (!nomor) return ['nol'];

            // Pecah jadi array: ["1", "5"] dst
            return nomor.toString().split('').map(digit => digit === '0' ? 'nol' : digit);
        };

        // Listener Livewire Event
        window.addEventListener('playTtsEvent', event => {
            console.log('Event "playTtsEvent" diterima:', event.detail);

            if (event.detail.nomor_antrean) {
                const digits = formatNomorAntrean(event.detail.nomor_antrean);

                // Buat sequence ucapan:
                const textParts = [
                    "Nomor antrean", 
                    ...digits, // disebut pelan satu per satu
                    "telah selesai."
                ];

                // Atur repeat = 2 kalau mau dipanggil 2 kali
                playSound(textParts, 2);
            } else {
                console.warn('Event "playTtsEvent" diterima tanpa nomor antrean.');
            }
        });
    });
</script>
