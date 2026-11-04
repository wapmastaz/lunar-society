// PayPal Smart Buttons, loaded only on pages that render a checkout.
let sdkPromise = null;

function loadPayPalSdk() {
    if (sdkPromise) return sdkPromise;

    const clientId = document.querySelector('meta[name="paypal-client-id"]')?.content;
    if (!clientId) return Promise.reject(new Error('PayPal is not configured yet.'));

    sdkPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(clientId)}&currency=USD&intent=capture&components=buttons`;
        script.onload = () => resolve(window.paypal);
        script.onerror = () => reject(new Error('Could not load PayPal. Check your connection and try again.'));
        document.head.appendChild(script);
    });

    return sdkPromise;
}

async function postJson(url) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
    });

    return { ok: response.ok, data: await response.json().catch(() => ({})) };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('paypalButtons', (createUrl, captureUrl) => ({
        error: null,
        processing: false,

        async init() {
            try {
                const paypal = await loadPayPalSdk();

                await paypal.Buttons({
                    style: { layout: 'vertical', shape: 'pill', label: 'pay' },

                    createOrder: async () => {
                        this.error = null;
                        const { ok, data } = await postJson(createUrl);
                        if (!ok || !data.id) throw new Error(data.error ?? 'Could not start the payment.');
                        return data.id;
                    },

                    onApprove: async (_data, actions) => {
                        this.processing = true;
                        const { data } = await postJson(captureUrl);

                        if (data.restart) {
                            this.processing = false;
                            return actions.restart();
                        }

                        if (data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }

                        this.processing = false;
                        this.error = data.error ?? 'Something went wrong. You have not been charged.';
                    },

                    onError: (err) => {
                        this.processing = false;
                        this.error = err?.message ?? 'PayPal reported an error. Please try again.';
                    },
                }).render(this.$refs.buttons);
            } catch (err) {
                this.error = err.message;
            }
        },
    }));
});

// Scroll reveal: adds .is-visible to [data-reveal] elements as they enter the
// viewport. The layout's head script hides them first (html.reveal-ready) and
// unhides everything if this file never runs.
function initReveal() {
    window.__revealInit = true;

    const targets = () => document.querySelectorAll('[data-reveal]:not(.is-visible)');

    if (!('IntersectionObserver' in window)) {
        targets().forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.12 },
    );

    const observeAll = () => targets().forEach((el) => observer.observe(el));
    observeAll();

    // Livewire can add new [data-reveal] elements after an update.
    document.addEventListener('livewire:init', () => {
        window.Livewire.hook('morphed', () => requestAnimationFrame(observeAll));
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReveal);
} else {
    initReveal();
}
