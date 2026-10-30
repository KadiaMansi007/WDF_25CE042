import { fetchJSON } from './api.js';
import { debounce, escapeHTML, searchItems, filterBy, uniqueValues, setStatus } from './utils.js';
 
const state = { items: [], query: '', category: 'all' };
 
const list = document.getElementById('faq-list');
const status = document.getElementById('faq-status');
const searchBox = document.getElementById('faq-search');
const categoryBox = document.getElementById('faq-category');
 
function itemHTML(faq) {
    return '<article class="faq-item">' +
        '<h3><button type="button" class="accordion-btn" id="faq-btn-' + faq.id + '" aria-expanded="false" ' +
        'aria-controls="faq-panel-' + faq.id + '">' + escapeHTML(faq.question) + '</button></h3>' +
        '<div class="accordion-panel" id="faq-panel-' + faq.id + '" role="region" aria-labelledby="faq-btn-' + faq.id + '" hidden>' +
        '<p>' + escapeHTML(faq.answer) + '</p></div>' +
        '</article>';
}
 
function render() {
    let rows = searchItems(state.items, ['question', 'answer'], state.query);
    rows = filterBy(rows, 'category', state.category);
 
    list.innerHTML = rows.map(itemHTML).join('');
    list.setAttribute('aria-busy', 'false');
    setStatus(status, rows.length ? rows.length + ' question(s) found.' : 'No questions match your search.', 'info');
}
 
async function load() {
    setStatus(status, 'Loading FAQs…', 'loading');
    try {
        const { data, fromCache } = await fetchJSON('data/faqs.json');
        state.items = data;
        uniqueValues(data, 'category').forEach(function (category) {
            if (!categoryBox.querySelector('option[value="' + category + '"]')) {
                categoryBox.add(new Option(category, category));
            }
        });
        render();
        if (fromCache) {
            setStatus(status, 'You appear to be offline. Showing the last saved FAQs.', 'warning');
        }
    } catch (error) {
        list.setAttribute('aria-busy', 'false');
        setStatus(status, 'Could not load FAQs (' + error.message + '). ', 'error');
        const retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'btn-small';
        retry.textContent = 'Try again';
        retry.addEventListener('click', load);
        status.appendChild(retry);
    }
}
 
searchBox.addEventListener('input', debounce(function () { state.query = searchBox.value; render(); }, 250));
categoryBox.addEventListener('change', function () { state.category = categoryBox.value; render(); });
 
load();