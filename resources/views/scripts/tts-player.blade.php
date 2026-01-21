<script>
    document.addEventListener('livewire:initialized', () => {
        console.log('TTS Player Diinisialisasi.');

        // Mapping digit ke kata Indonesia
        const digitWords = {
            '0': 'nol',
            '1': 'satu',
            '2': 'dua',
            '3': 'tiga',
            '4': 'empat',
            '5': 'lima',
            '6': 'enam',
            '7': 'tujuh',
            '8': 'delapan',
            '9': 'sembilan'
        };

        // Format nomor antrean menjadi kata-kata (contoh: "15" → "satu lima")
        const formatNomorAntrean = (nomor) => {
            if (!nomor) return 'nol';
            return nomor.toString().split('').map(digit => digitWords[digit] || digit).join(' ');
        };

        // Fungsi TTS dengan opsi repeat - satu kalimat utuh
        const playSound = (text, repeat = 1) => {
            if (!('speechSynthesis' in window)) {
                console.error('API Speech Synthesis tidak didukung oleh browser ini.');
                return;
            }

            // Batalkan suara yang sedang berjalan
            window.speechSynthesis.cancel();

            let count = 0;

            const speakOnce = () => {
                if (count >= repeat) return;

                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = 0.9; // sedikit lebih lambat agar jelas terdengar
                utterance.pitch = 1;

                utterance.onend = () => {
                    count++;
                    if (count < repeat) {
                        setTimeout(speakOnce, 1500); // jeda 1.5 detik antar pengulangan
                    }
                };

                window.speechSynthesis.speak(utterance);
            };

            speakOnce();
        };

        // Listener Livewire Event
        window.addEventListener('playTtsEvent', event => {
            console.log('Event "playTtsEvent" diterima:', event.detail);

            if (event.detail.nomor_antrean) {
                const nomorKata = formatNomorAntrean(event.detail.nomor_antrean);

                // Gabungkan menjadi satu kalimat utuh
                const fullText = `Nomor antrean ${nomorKata}, telah selesai.`;

                // Diulang 2 kali
                playSound(fullText, 2);
            } else {
                console.warn('Event "playTtsEvent" diterima tanpa nomor antrean.');
            }
        });
    });
</script>
