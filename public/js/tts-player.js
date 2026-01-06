document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('playTtsEvent', event => {
        const data = event.detail;
        console.log('Event playTtsEvent diterima:', data);

        const queueNumber = data.nomor_antrean;
        if (!queueNumber) return;

        const textToSpeak = `Nomor antrean ${queueNumber}, dipersilakan menuju loket.`;
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(textToSpeak);
        utterance.lang = 'id-ID';
        utterance.rate = 0.95;
        window.speechSynthesis.speak(utterance);
    });

    console.log('TTS Player siap mendengarkan event Livewire (nomor antrean).');
});
