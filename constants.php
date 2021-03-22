<?php
    /**
     * Константы
     *  
     */

    // Запросы 
    const BASE_URI = 'https://avito.ru';

    // Логирование
    const BASE_LOG_ERROR_PATH = __DIR__ . "/logs/errors.log";
    const BASE_LOG_PATH = __DIR__ . "/logs/log.log";

    // База данных
    const DB_LOGIN = "admin";
    const DB_PASSWORD = "admin";
    const DB_HOST = "localhost";
    const DB_DATABASE = "avipars";

    //Конфиг
    const BASE_CONFIG_PATH = __DIR__ . "/config.json";

    //Парсинг
    const AVITO_SELECTOR_ITEM = '.iva-item-root.photo-slider-slider.iva-item-list';
    const AVITO_SELECTOR_ITEM_ID = 'data-item-id';
    const AVITO_SELECTOR_ITEM_TITLE = "h3.title-root.iva-item-title";
    const AVITO_SELECTOR_ITEM_LINK = ".iva-item-sliderLink";
    const AVITO_SELECTOR_ITEM_PRICE = ".price-text";
    const AVITO_SELECTOR_ITEM_ADRESS  = ".geo-address span";
    const AVITO_SELECTOR_ITEM_DATE = "[data-marker=item-date]";
    const AVITO_SELECTIOR_ADS_NUMBER = ".page-title-count";

