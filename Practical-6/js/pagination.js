export function renderPagination(container, page, totalPages, onChange) {
    container.innerHTML = '';
    if (totalPages <= 1) return;
 
    function makeButton(label, targetPage, options = {}) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = label;
        if (options.ariaLabel) button.setAttribute('aria-label', options.ariaLabel);
        if (options.disabled) button.disabled = true;
        if (options.current) {
            button.setAttribute('aria-current', 'page');
            button.classList.add('current');
        }
        button.addEventListener('click', () => onChange(targetPage));
        container.appendChild(button);
    }
 
    makeButton('Previous', page - 1, { disabled: page === 1 });
    for (let i = 1; i <= totalPages; i++) {
        makeButton(String(i), i, { current: i === page, ariaLabel: 'Page ' + i });
    }
    makeButton('Next', page + 1, { disabled: page === totalPages });
}