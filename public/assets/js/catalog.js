// Листинг категории (Таск 4): инициализация ion.rangeSlider (вендорный
// плагин темы, требует jQuery — грузится через отдельный <script defer>
// в src/Views/shop/catalog/category.php) для фильтра по цене, и запись
// cookie при переключении вида плитка/список без перезагрузки страницы.
//
// Сама фильтрующая форма — обычный GET, без клиентской логики.
// Визуальное переключение классов (.list-style, active у grid-btn/list-btn)
// уже делает вендорный script.js темы по клику на .grid-btn/.list-btn —
// здесь только cookie, чтобы выбор запомнился на следующий заход
// (CatalogController::resolveAndPersistView() читает её при следующем
// заходе без ?view= в URL).

const priceSlider = document.getElementById('priceRangeSlider');
const priceMinInput = document.getElementById('priceMinInput');
const priceMaxInput = document.getElementById('priceMaxInput');

if (priceSlider && priceMinInput && priceMaxInput && window.jQuery) {
    const $ = window.jQuery;

    $(priceSlider).ionRangeSlider({
        type: 'double',
        min: Number(priceSlider.dataset.min),
        max: Number(priceSlider.dataset.max),
        from: Number(priceSlider.dataset.from),
        to: Number(priceSlider.dataset.to),
        prefix: '',
        postfix: ' ₽',
        onChange: (data) => {
            priceMinInput.value = String(data.from);
            priceMaxInput.value = String(data.to);
        },
    });
}

const VIEW_COOKIE_MAX_AGE = 60 * 60 * 24 * 365;

document.querySelectorAll('[data-view-link]').forEach((link) => {
    link.addEventListener('click', () => {
        const view = link.dataset.viewLink === 'list' ? 'list' : 'grid';
        document.cookie = `view=${view}; path=/; max-age=${VIEW_COOKIE_MAX_AGE}; samesite=lax`;
    });
});

export {};
