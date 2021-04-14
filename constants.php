<?php
    /**
     * Константы
     *  
     */

    // Запросы 
    const BASE_URI = 'https://avito.ru';
    const BASE_HEADERS = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36',
        "accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
        "accept-language" => "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
        "cache-control" => "max-age=0",
        "sec-ch-ua" => "\"Google Chrome\";v=\"89\", \"Chromium\";v=\"89\", \";Not A Brand\";v=\"99\"",
        "sec-ch-ua-mobile" => "?0",
        "sec-fetch-dest" => "document",
        "sec-fetch-mode" => "navigate",
        "sec-fetch-site" => "none",
        "sec-fetch-user" => "?1",
        "upgrade-insecure-requests" => "1",
    ];
    const BASE_SLEEP_TIME = 1;
    const BASE_SLEEP_TIME_429 = 30;
    const BASE_MAX_NUM_429 = 2;
    const BASE_SLEEP_TIME_404 = 5;
    const BASE_MAX_NUM_404 = 2;

    const MAX_PAGES_NUM = 100;

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
    const AVITO_SELECTOR_ITEM_TITLE = "h3.title-root.iva-item-title";
    const AVITO_SELECTOR_ITEM_LINK = ".iva-item-sliderLink";
    const AVITO_SELECTOR_ITEM_PRICE = ".price-text";
    const AVITO_SELECTOR_ITEM_ADRESS  = ".geo-address span";
    const AVITO_SELECTOR_ITEM_DATE = "[data-marker=item-date]";
    const AVITO_SELECTIOR_ADS_NUMBER = ".page-title-count";

    //Mink
    const BASE_MINK_HOST = "127.0.0.1";
    const BASE_MINK_PORT = "9222";
    const BASE_MINK_PAGE_LOAD_TIME = 10;


