export function escapeHTML(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
 
export function debounce(fn, delay = 250) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}
 
export function searchItems(items, fields, query) {
    const q = query.trim().toLowerCase();
    if (!q) return items;
    return items.filter(item =>
        fields.some(field => String(item[field]).toLowerCase().includes(q)));
}
 
export function filterBy(items, field, value) {
    return value === 'all' ? items : items.filter(item => String(item[field]) === value);
}
 
export function sortItems(items, field, direction = 'asc') {
    const factor = direction === 'desc' ? -1 : 1;
    return [...items].sort((a, b) => {
        if (typeof a[field] === 'number') return (a[field] - b[field]) * factor;
        return String(a[field]).localeCompare(String(b[field])) * factor;
    });
}
 
export function paginate(items, page, pageSize) {
    const totalPages = Math.max(1, Math.ceil(items.length / pageSize));
    const current = Math.min(Math.max(page, 1), totalPages);
    const start = (current - 1) * pageSize;
    return { rows: items.slice(start, start + pageSize), page: current, totalPages, total: items.length };
}
 
export function uniqueValues(items, field) {
    return [...new Set(items.map(item => item[field]))].sort();
}
 
export function setStatus(element, message, type = 'info') {
    element.textContent = message;
    element.className = 'status ' + type;
}