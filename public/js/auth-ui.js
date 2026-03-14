(function () {
    function setupPasswordToggles(root) {
        root.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                var inputId = toggle.getAttribute('aria-controls');
                var input = inputId ? document.getElementById(inputId) : null;

                if (!input) {
                    return;
                }

                var showLabel = toggle.getAttribute('data-show-label') || 'Show';
                var hideLabel = toggle.getAttribute('data-hide-label') || 'Hide';
                var shouldShow = input.type === 'password';

                input.type = shouldShow ? 'text' : 'password';
                toggle.textContent = shouldShow ? hideLabel : showLabel;
                toggle.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
            });
        });
    }

    function setupSubmitStates(root) {
        root.querySelectorAll('[data-auth-form]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var submitButton = form.querySelector('[data-submit-button]');
                var submitLabel = form.querySelector('[data-submit-label]');
                var submitLoading = form.querySelector('[data-submit-loading]');

                if (!submitButton) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.setAttribute('aria-busy', 'true');

                if (submitLabel) {
                    submitLabel.classList.add('hidden');
                }

                if (submitLoading) {
                    submitLoading.classList.remove('hidden');
                }
            });
        });
    }

    function initAuthUi() {
        setupPasswordToggles(document);
        setupSubmitStates(document);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAuthUi);
    } else {
        initAuthUi();
    }
})();
