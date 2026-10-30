import { fetchJSON } from './api.js';
import { debounce, escapeHTML, searchItems, filterBy, sortItems, paginate, uniqueValues, setStatus } from './utils.js';
import { renderPagination } from './pagination.js';
 
const PAGE_SIZE = 6;
const state = { items: [], query: '', category: 'all', sort: 'date-asc', page: 1 };
 
const list = document.getElementById('event-list');
const status = document.getElementById('event-status');
const pager = document.getElementById('event-pager');
const searchBox = document.getElementById('event-search');
const categoryBox = document.getElementById('event-category');
const sortBox = document.getElementById('event-sort');
 
function formatDate(iso) {
    return new Date(iso + 'T00:00:00').toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' });
}
 
function cardHTML(event) {
    const details = event.title + ' will be held on ' + formatDate(event.date) + ' at ' + event.venue + '. ' +
        event.description + ' Seats available: ' + event.seats + '.';
    return '<article>' +
        '<h3>' + escapeHTML(event.title) + '</h3>' +
        '<p><strong>Category:</strong> ' + escapeHTML(event.category) + '</p>' +
        '<p><strong>Date:</strong> ' + formatDate(event.date) + '</p>' +
        '<p><strong>Venue:</strong> ' + escapeHTML(event.venue) + '</p>' +
        '<button type="button" class="btn-small" data-modal-title="' + escapeHTML(event.title) +
        '" data-modal-text="' + escapeHTML(details) + '">View Details</button>' +
        '</article>';
}
 
function render() {
 
    let rows = searchItems(state.items, ['title', 'venue', 'category', 'description'], state.query);
    rows = filterBy(rows, 'category', state.category);
    const [field, direction] = state.sort.split('-');
    rows = sortItems(rows, field, direction);
 
    const view = paginate(rows, state.page, PAGE_SIZE);
    state.page = view.page;
 
    list.innerHTML = view.rows.map(cardHTML).join('');
    list.setAttribute('aria-busy', 'false');
 
    if (view.total === 0) {
        setStatus(status, 'No events match your search.', 'info');
    } else {
        const from = (view.page - 1) * PAGE_SIZE + 1;
        setStatus(status, 'Showing ' + from + '–' + (from + view.rows.length - 1) + ' of ' + view.total + ' events.', 'info');
    }
    renderPagination(pager, view.page, view.totalPages, function (newPage) {
        state.page = newPage;
        render();
        list.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}
 
async function load() {
    list.setAttribute('aria-busy', 'true');
    list.innerHTML = '';
    pager.innerHTML = '';
    setStatus(status, 'Loading events…', 'loading');
 
    try {
        const { data, fromCache } = await fetchJSON('data/events.json');
        state.items = data;
        uniqueValues(data, 'category').forEach(function (category) {
            if (!categoryBox.querySelector('option[value="' + category + '"]')) {
                categoryBox.add(new Option(category, category));
            }
        });
        render();
        if (fromCache) {
            setStatus(status, 'You appear to be offline. Showing the last saved events.', 'warning');
        }
    } catch (error) {
        list.setAttribute('aria-busy', 'false');
        setStatus(status, 'Could not load events (' + error.message + '). ', 'error');
        const retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'btn-small';
        retry.textContent = 'Try again';
        retry.addEventListener('click', load);
        status.appendChild(retry);
    }
}
 
searchBox.addEventListener('input', debounce(function () {
    state.query = searchBox.value;
    state.page = 1;
    render();
}, 250));
categoryBox.addEventListener('change', function () { state.category = categoryBox.value; state.page = 1; render(); });
sortBox.addEventListener('change', function () { state.sort = sortBox.value; state.page = 1; render(); });
 
load();