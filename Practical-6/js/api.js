const CACHE_PREFIX = 'studenthub.cache.';
 
function readCache(url) {
    try {
        return JSON.parse(localStorage.getItem(CACHE_PREFIX + url));
    } catch (error) {
        return null;
    }
}
 
function writeCache(url, data) {
    try {
        localStorage.setItem(CACHE_PREFIX + url, JSON.stringify(data));
    } catch (error) {
 
    }
}
 
export async function fetchJSON(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Server responded with status ' + response.status);
        }
        const data = await response.json();
        writeCache(url, data);
        return { data, fromCache: false };
    } catch (error) {
        const cached = readCache(url);
        if (cached) {
            return { data: cached, fromCache: true };
        }
        throw error;
    }
}