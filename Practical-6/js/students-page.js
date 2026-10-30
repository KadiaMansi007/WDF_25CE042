import { fetchJSON } from './api.js';
import { debounce, escapeHTML, searchItems, filterBy, sortItems, paginate, uniqueValues, setStatus } from './utils.js';
import { renderPagination } from './pagination.js';
 
const PAGE_SIZE = 8;
const state = { items: [], query: '', department: 'all', sort: 'name-asc', page: 1 };
 
const body = document.getElementById('student-rows');
const status = document.getElementById('student-status');
const pager = document.getElementById('student-pager');
const searchBox = document.getElementById('student-search');
const deptBox = document.getElementById('student-dept');
const sortBox = document.getElementById('student-sort');
 
function rowHTML(student) {
    return '<tr>' +
        '<td>' + escapeHTML(student.enrollment) + '</td>' +
        '<td>' + escapeHTML(student.name) + '</td>' +
        '<td>' + escapeHTML(student.department) + '</td>' +
        '<td>' + student.year + '</td>' +
        '<td>' + escapeHTML(student.email) + '</td>' +
        '<td>' + student.cgpa.toFixed(2) + '</td>' +
        '</tr>';
}
 
function render() {
    let rows = searchItems(state.items, ['name', 'enrollment', 'email'], state.query);
    rows = filterBy(rows, 'department', state.department);
    const [field, direction] = state.sort.split('-');
    rows = sortItems(rows, field, direction);
 
    const view = paginate(rows, state.page, PAGE_SIZE);
    state.page = view.page;
    body.innerHTML = view.rows.map(rowHTML).join('') ||
        '<tr><td colspan="6">No students found.</td></tr>';
 
    setStatus(status, view.total + ' student(s) found.', 'info');
    renderPagination(pager, view.page, view.totalPages, function (newPage) {
        state.page = newPage;
        render();
    });
}
 
async function load() {
    setStatus(status, 'Loading student records…', 'loading');
    body.innerHTML = '';
    try {
        const { data, fromCache } = await fetchJSON('data/students.json');
        state.items = data;
        uniqueValues(data, 'department').forEach(function (department) {
            if (![...deptBox.options].some(option => option.value === department)) {
                deptBox.add(new Option(department, department));
            }
        });
        render();
        if (fromCache) {
            setStatus(status, 'You appear to be offline. Showing the last saved records.', 'warning');
        }
    } catch (error) {
        setStatus(status, 'Could not load student records (' + error.message + '). ', 'error');
        const retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'btn-small';
        retry.textContent = 'Try again';
        retry.addEventListener('click', load);
        status.appendChild(retry);
    }
}
 
searchBox.addEventListener('input', debounce(function () { state.query = searchBox.value; state.page = 1; render(); }, 250));
deptBox.addEventListener('change', function () { state.department = deptBox.value; state.page = 1; render(); });
sortBox.addEventListener('change', function () { state.sort = sortBox.value; state.page = 1; render(); });
 
load();