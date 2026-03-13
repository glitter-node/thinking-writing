const root = document.getElementById('google-onetap-root');
const status = document.getElementById('google-signin-status');

if (root && status) {
    const endpoint = root.dataset.googleEndpoint;
    const clientId = window.GOOGLE_CLIENT_ID || root.dataset.googleClientId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const updateStatus = (message) => {
        status.textContent = message;
    };

    const handleCredentialResponse = async (response) => {
        updateStatus('Verifying Google sign-in...');

        try {
            const result = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify({ credential: response.credential }),
                credentials: 'same-origin',
            });

            const payload = await result.json();

            if (!result.ok) {
                throw new Error(payload.message || 'Google sign-in failed.');
            }

            if (payload.status !== 'ok') {
                throw new Error('Unexpected Google sign-in response.');
            }

            updateStatus('Signed in. Reloading...');
            window.location.reload();
        } catch (error) {
            console.error('[Google One Tap] Login failed.', error);
            updateStatus(error instanceof Error ? error.message : 'Google sign-in failed.');
        }
    };

    const initOneTap = () => {
        if (!window.google?.accounts?.id) {
            return false;
        }

        console.info('[Google One Tap] Initializing.');

        window.google.accounts.id.initialize({
            client_id: clientId,
            callback: handleCredentialResponse,
            auto_select: true,
        });

        window.google.accounts.id.prompt((notification) => {
            if (notification.isNotDisplayed()) {
                console.warn('[Google One Tap] Prompt not displayed.', notification.getNotDisplayedReason());
            }

            if (notification.isSkippedMoment()) {
                console.warn('[Google One Tap] Prompt skipped.', notification.getSkippedReason());
            }

            if (notification.isDismissedMoment()) {
                console.warn('[Google One Tap] Prompt dismissed.', notification.getDismissedReason());
            }
        });

        updateStatus('Waiting for Google One Tap...');

        return true;
    };

    let attempts = 0;
    const maxAttempts = 20;

    const loadGoogleScript = () => {
        if (document.querySelector('script[data-google-one-tap]')) {
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://accounts.google.com/gsi/client';
        script.async = true;
        script.defer = true;
        script.dataset.googleOneTap = 'true';
        document.head.appendChild(script);
    };

    const waitForGoogle = () => {
        attempts += 1;

        if (initOneTap()) {
            return;
        }

        if (attempts >= maxAttempts) {
            console.error('[Google One Tap] Google script did not load.');
            updateStatus('Google sign-in is temporarily unavailable.');
            return;
        }

        window.setTimeout(waitForGoogle, 250);
    };

    window.addEventListener('load', () => {
        loadGoogleScript();
        waitForGoogle();
    });
}
