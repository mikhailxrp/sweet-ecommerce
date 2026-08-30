// Карточка товара (Таск 5): зум галереи и переключение варианта без
// перезагрузки страницы.
//
// Зум — вендорный jQuery-плагин (jquery.elevatezoom.js). Готовый
// zoom-filter.js темы целится в другой вариант шаблона карточки товара
// (.product-main/.image_zoom_cls, без индекса) и на нашей разметке
// (.product-main-2/.image_zoom_cls-{N}) не сработал бы — поэтому здесь
// своя инициализация, а не копия вендорного скрипта.
//
// Синхронизация main+thumbnail слайдеров уже сделана вендорным
// custom_slick.js (asNavFor .product-main-2/.left-slider-image-2) —
// здесь её не трогаем.

const reinitZoom = (img) => {
    if (!window.jQuery) {
        return;
    }
    const $ = window.jQuery;
    const $img = $(img);
    $('.zoomContainer').remove();
    $img.removeData('elevateZoom');
    $img.removeData('zoomImage');
    $img.elevateZoom({ zoomType: 'inner', cursor: 'crosshair' });
};

if (window.jQuery) {
    const $ = window.jQuery;
    $('.product-main-2 img').elevateZoom({ zoomType: 'inner', cursor: 'crosshair' });
    $('.product-main-2').on('afterChange', (event, slick, currentSlide) => {
        const img = document.querySelector(`.image_zoom_cls-${currentSlide}`);
        if (img) {
            reinitZoom(img);
        }
    });
}

const priceCurrent = document.getElementById('productPriceCurrent');
const priceOld = document.getElementById('productPriceOld');
const skuValue = document.getElementById('productSku');
const stockValue = document.getElementById('productStock');

const applyVariant = (link) => {
    const { dataset } = link;

    if (priceCurrent) {
        priceCurrent.textContent = dataset.price;
    }
    if (priceOld) {
        if (dataset.oldPrice) {
            priceOld.textContent = dataset.oldPrice;
            priceOld.hidden = false;
        } else {
            priceOld.hidden = true;
        }
    }
    if (skuValue) {
        skuValue.textContent = dataset.sku;
    }
    if (stockValue) {
        const stock = Number(dataset.stock);
        stockValue.textContent = stock > 0 ? `В наличии: ${stock} шт.` : 'Нет в наличии';
        stockValue.classList.toggle('text-danger', stock <= 0);
    }

    if (dataset.image) {
        const currentSlide = document.querySelector('.product-main-2 .slick-current img')
            ?? document.querySelector('.product-main-2 img');
        if (currentSlide) {
            currentSlide.src = dataset.image;
            currentSlide.dataset.zoomImage = dataset.image;
            reinitZoom(currentSlide);
        }
    }
};

document.querySelectorAll('#variantSelector [data-variant-id]').forEach((link) => {
    link.addEventListener('click', () => {
        document.querySelectorAll('#variantSelector a').forEach((a) => a.classList.remove('active'));
        link.classList.add('active');
        applyVariant(link);
    });
});

export {};
