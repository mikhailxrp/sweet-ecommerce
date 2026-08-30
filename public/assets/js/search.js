// Живые подсказки поиска в шапке (Таск 6). Debounce на вводе, JSON с
// `/search/suggest`; результат вставляется в DOM только через
// `textContent` — никогда через innerHTML с данными от сервера.

const DEBOUNCE_MS = 300;

const input = document.querySelector('.search-box input[name="q"]');
const suggestions = document.getElementById('searchSuggestions');

const hideSuggestions = () => {
    if (suggestions) {
        suggestions.hidden = true;
        suggestions.textContent = '';
    }
};

const renderSuggestions = (items) => {
    if (!suggestions) {
        return;
    }

    suggestions.textContent = '';

    if (items.length === 0) {
        suggestions.hidden = true;
        return;
    }

    items.forEach((item) => {
        const link = document.createElement('a');
        link.href = `/product/${item.slug}`;
        link.className = 'search-suggestions__item';

        const name = document.createElement('span');
        name.className = 'search-suggestions__name';
        name.textContent = item.name;

        const price = document.createElement('span');
        price.className = 'search-suggestions__price';
        price.textContent = item.price;

        link.append(name, price);
        suggestions.append(link);
    });

    suggestions.hidden = false;
};

// Медленный ответ на более ранний (уже неактуальный) запрос иначе мог бы
// прийти позже свежего и перезаписать корректно показанные подсказки
// устаревшими — оборвём предыдущий запрос, как только уходит следующий.
let pendingRequest = null;

const fetchSuggestions = async (value) => {
    pendingRequest?.abort();
    const controller = new AbortController();
    pendingRequest = controller;

    try {
        const response = await fetch(`/search/suggest?q=${encodeURIComponent(value)}`, {
            signal: controller.signal,
        });
        if (!response.ok) {
            hideSuggestions();
            return;
        }
        renderSuggestions(await response.json());
    } catch (error) {
        if (error.name !== 'AbortError') {
            hideSuggestions();
        }
    } finally {
        if (pendingRequest === controller) {
            pendingRequest = null;
        }
    }
};

if (input && suggestions) {
    let debounceTimer = null;

    input.addEventListener('input', () => {
        const value = input.value.trim();
        window.clearTimeout(debounceTimer);

        if (value === '') {
            pendingRequest?.abort();
            hideSuggestions();
            return;
        }

        debounceTimer = window.setTimeout(() => fetchSuggestions(value), DEBOUNCE_MS);
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            hideSuggestions();
        }
    });

    document.addEventListener('click', (event) => {
        if (!input.contains(event.target) && !suggestions.contains(event.target)) {
            hideSuggestions();
        }
    });
}

export {};
