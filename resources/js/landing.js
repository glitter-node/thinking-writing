import '../css/landing.css';

const bootDiagrams = () => {
    if (document.querySelector('.mermaid')) {
        void import('./diagrams');
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootDiagrams);
} else {
    bootDiagrams();
}
