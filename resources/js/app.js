if (!window.__fnbAudioInit) {
    window.__fnbAudioInit = true;

    const FnbAudio = (() => {
    let audioContext = null;
    let gainNode = null;
    let unlocked = false;

    const ensureContext = () => {
        if (audioContext) return audioContext;
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return null;
        audioContext = new Ctx();
        gainNode = audioContext.createGain();
        gainNode.gain.value = 0.08;
        gainNode.connect(audioContext.destination);
        return audioContext;
    };

    const unlock = async () => {
        if (unlocked) return;
        const ctx = ensureContext();
        if (!ctx) return;
        try {
            if (ctx.state === "suspended") {
                await ctx.resume();
            }
            unlocked = ctx.state === "running";
        } catch (_) {
            unlocked = false;
        }
    };

    const unlockOnGesture = () => {
        const handler = () => {
            unlock();
            window.removeEventListener("pointerdown", handler, true);
            window.removeEventListener("keydown", handler, true);
            window.removeEventListener("touchstart", handler, true);
        };
        window.addEventListener("pointerdown", handler, true);
        window.addEventListener("keydown", handler, true);
        window.addEventListener("touchstart", handler, true);
    };

    const tone = (frequency, durationMs, volume = 1) => {
        const ctx = ensureContext();
        if (!ctx || !gainNode) return;
        if (ctx.state !== "running") return;

        const oscillator = ctx.createOscillator();
        const gain = ctx.createGain();

        oscillator.type = "sine";
        oscillator.frequency.value = frequency;

        const now = ctx.currentTime;
        const duration = Math.max(0.01, durationMs / 1000);

        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(Math.max(0.0001, 0.2 * volume), now + 0.01);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + duration);

        oscillator.connect(gain);
        gain.connect(gainNode);

        oscillator.start(now);
        oscillator.stop(now + duration + 0.02);
    };

    const pattern = (steps) => {
        const ctx = ensureContext();
        if (!ctx || ctx.state !== "running") return;
        let offset = 0;
        for (const step of steps) {
            const { f, d, v, gap = 0 } = step;
            window.setTimeout(() => tone(f, d, v ?? 1), offset);
            offset += d + gap;
        }
    };

    const play = (name) => {
        const ctx = ensureContext();
        if (!ctx) return;
        if (ctx.state !== "running") return;

        switch (name) {
            case "tap":
                pattern([{ f: 880, d: 30, v: 0.7 }]);
                break;
            case "success":
                pattern([
                    { f: 523.25, d: 70, v: 1, gap: 20 },
                    { f: 659.25, d: 90, v: 1 },
                ]);
                break;
            case "warning":
                pattern([
                    { f: 440, d: 90, v: 1, gap: 30 },
                    { f: 330, d: 120, v: 1 },
                ]);
                break;
            case "error":
                pattern([
                    { f: 220, d: 120, v: 1, gap: 30 },
                    { f: 196, d: 160, v: 1 },
                ]);
                break;
            case "order":
                pattern([
                    { f: 880, d: 80, v: 1, gap: 30 },
                    { f: 660, d: 80, v: 1, gap: 30 },
                    { f: 880, d: 110, v: 1 },
                ]);
                break;
            default:
                pattern([{ f: 660, d: 40, v: 0.6 }]);
                break;
        }
    };

    unlockOnGesture();

    return {
        unlock,
        play,
    };
})();

window.addEventListener("sound", (e) => {
    const detail = e?.detail;
    const name = typeof detail === "string" ? detail : detail?.name;
    if (!name) return;
    FnbAudio.play(name);
});

window.addEventListener("notify", (e) => {
    const detail = e?.detail;
    const type = typeof detail === "object" && detail ? detail.type : null;
    if (type === "success") FnbAudio.play("success");
    else if (type === "warning") FnbAudio.play("warning");
    else if (type === "error") FnbAudio.play("error");
});
}
