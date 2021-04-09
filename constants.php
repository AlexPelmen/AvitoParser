<?php
    /**
     * Константы
     *  
     */

    // Запросы 
    const BASE_URI = 'https://avito.ru';
    const BASE_SLEEP_TIME = 1;
    const BASE_SLEEP_TIME_429 = 30;
    const BASE_MAX_NUM_429 = 2;
    const BASE_SLEEP_TIME_404 = 5;
    const BASE_MAX_NUM_404 = 2;

    // const DEFAULT_COOKIES = [
    //     "buyer_selected_search_radius4" => "0_general",
    //     "isCriteoSetNew" => "true",
    //     "showedStoryIds" => "61-58-50-49-48-47-42-32",
    //     "lastViewingTime" => 1617802999000,
    //     "abp" => "1",
    //     "no-ssr" => "1",
    //     "f" => "5.0c4f4b6d233fb90636b4dd61b04726f147e1eada7172e06c47e1eada7172e06c47e1eada7172e06c47e1eada7172e06cb59320d6eb6303c1b59320d6eb6303c1b59320d6eb6303c147e1eada7172e06c8a38e2c5b3e08b898a38e2c5b3e08b890df103df0c26013a0df103df0c26013a2ebf3cb6fd35a0ac0df103df0c26013a8b1472fe2f9ba6b984dcacfe8ebe897bfa4d7ea84258c63d59c9621b2c0fa58f915ac1de0d034112ad09145d3e31a56946b8ae4e81acb9fae2415097439d4047fb0fb526bb39450a46b8ae4e81acb9fa34d62295fceb188dd99271d186dc1cd03de19da9ed218fe2d50b96489ab264edd50b96489ab264edd50b96489ab264ed46b8ae4e81acb9fa51b1fde863bf5c12f8ee35c29834d631c9ba923b7b327da78fe44b90230da2aceb6fa41872a5ca4e2985db2d99140e2d0ee226f11256b780315536c94b3e90e338f0f5e6e0d2832e960a06c8b1b2133da291fc3f0bfffdd50df103df0c26013a0df103df0c26013aafbc9dcfc006bed997d74c27146670dfa01eb4b4be78b42b3de19da9ed218fe23de19da9ed218fe2dc4f5790d1ff098f2fd5948f5c676efa78a492ecab7d2b7c",
    //     "ft" => "\"Qoj0zJbLmNr9odmM2pSV8pdqHcGr+geLGE9qZeFhJWHwZC4diBzUJuW6vloPr6fIC+vMUspTA7ImaaK9+EW+dAx76/o+Vi+Srv/Q+vPl+5ispU+lYKTwIEDhKrp6Fr85MJmJLkHyWEUg830qezlJ9rgdONbFjIHB8MN7HDAWAZ07MM/T34yI5eLrFb4j2jsl\"",
    //     "SEARCH_HISTORY_IDS" => "%2C4",
    // ];

    const DEFAULT_COOKIES = [
        "buyer_selected_search_radius4" => "0_general" ,
        "_ym_uid" => "1615372210232452432" ,
        "_ym_d" => "1615372210",
        "_gcl_au" => "1.1.996315498.1615372211",
        "_ga" => "GA1.2.1222664713.1615372211",
        "__gads" => "ID=d883552673cffb34:T=1615372210:S=ALNI_MaQg1kN8YDwQ9l0nCCY81XKWdJkGQ",
        "isCriteoSetNew" => "true",
        "__utmz" => "99926606.1616406670.1.1.utmcsr=business.avito.ru|utmccn=(referral)|utmcmd=referral|utmcct=/",
        "showedStoryIds" => "61-58-50-49-48-47-42-32",
        "lastViewingTime" => "1617282044745",
        "_gid" => "GA1.2.376445482.1617616911",
        "abp" => "1",
        "no-ssr" => "1",
        "_ym_isad" => "1",
        "__utmc" => "99926606",
        "buyer_popup_location" => "637640",
        "f" => "5.0c4f4b6d233fb90636b4dd61b04726f147e1eada7172e06c47e1eada7172e06c47e1eada7172e06c47e1eada7172e06cb59320d6eb6303c1b59320d6eb6303c1b59320d6eb6303c147e1eada7172e06c8a38e2c5b3e08b898a38e2c5b3e08b890df103df0c26013a0df103df0c26013a2ebf3cb6fd35a0ac0df103df0c26013a8b1472fe2f9ba6b984dcacfe8ebe897bfa4d7ea84258c63d59c9621b2c0fa58f915ac1de0d034112ad09145d3e31a56946b8ae4e81acb9fae2415097439d4047fb0fb526bb39450a46b8ae4e81acb9fa34d62295fceb188dd99271d186dc1cd03de19da9ed218fe2d50b96489ab264edd50b96489ab264edd50b96489ab264ed46b8ae4e81acb9fa51b1fde863bf5c12f8ee35c29834d631c9ba923b7b327da78fe44b90230da2aceb6fa41872a5ca4e2985db2d99140e2d0ee226f11256b780315536c94b3e90e338f0f5e6e0d2832e960a06c8b1b2133da291fc3f0bfffdd50df103df0c26013a0df103df0c26013aafbc9dcfc006bed997d74c27146670df4858ca900540f65c3de19da9ed218fe23de19da9ed218fe2dc4f5790d1ff098f2fd5948f5c676efa78a492ecab7d2b7f",
        "ft" > "=\"+5hdr6aeT/DGnbNtPJ7y1XjCYsUlY6J6zlhmqeZBzzx0rCjNKk5OJrJvOJ11I1gYMxrCeg4ouI9vMyQ4gR3tIRnXWeEbUWCjonjs6T+JqhSZJFm+Bw5K7fFToKgqUDyKiUkZQTyRamd/jAxrxG4+SfIJ9IDOkGl5ELfptoxitpwIz2vEDIq8oPvZs9Hd6V77",
        "__utma" => "99926606.1222664713.1615372211.1617795978.1617798502.14",
        "SEARCH_HISTORY_IDS" => "4%2C",
        "buyer_from_page" => "catalog",
    ];

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
    const BASE_MINK_PAGE_LOAD_TIME = 5;


