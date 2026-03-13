document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-back-button]');

    if (!button) {
        return;
    }

    event.preventDefault();
    window.history.back();
});
