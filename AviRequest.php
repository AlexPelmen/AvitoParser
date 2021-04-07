<?php
    /**
     * Реализует логику выполнения запросов к авито
     * Получает HTML с данными
     */

    require_once __DIR__ . "/vendor/autoload.php";
    require_once __DIR__ . "/constants.php";

    class AviRequest {
        public
            $client,
            $logger,
            $parser,
            $collection,
            $city,
            $query,
            $category,
            $page,
            $locationId,
            $cookie;
        
            
        // Инициализация переменных            
        public function __construct ($attributes, $parser, $database, $logger) {
            $this->client = new GuzzleHttp\Client([
                'base_uri' => BASE_URI,
                'cookies' => true,
                'headers' => [
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
                ]
            ]);

            $this->parser = $parser;
            $this->database = $database;
            $this->logger = $logger;   
            $this->collection = new AviAdsCollection();         
            $this->cookie = [
                "buyer_selected_search_radius4" => "0_general",
                "_ym_uid" => "1615372210232452432",
                "_ym_d" => "1615372210",
                "_gcl_au" => "1.1.996315498.1615372211",
                "_ga" => "GA1.2.1222664713.1615372211",
                "__gads" => "ID=d883552673cffb34:T=1615372210:S=ALNI_MaQg1kN8YDwQ9l0nCCY81XKWdJkGQ",
                "isCriteoSetNew" => "true",
                "__utmz" => "99926606.1616406670.1.1.utmcsr=business.avito.ru|utmccn=(referral)|utmcmd=referral|utmcct=/",
                "showedStoryIds" => "61-58-50-49-48-47-42-32",
                "lastViewingTime" => "1617282044745",
                "_gid" => "GA1.2.376445482.1617616911",
                "_ym_isad" => "1",
                "abp" => "1",
                "no-ssr" => "1",
                "__utmc" => "99926606",
                "f" => "5.0c4f4b6d233fb90636b4dd61b04726f147e1eada7172e06c47e1eada7172e06c47e1eada7172e06c47e1eada7172e06cb59320d6eb6303c1b59320d6eb6303c1b59320d6eb6303c147e1eada7172e06c8a38e2c5b3e08b898a38e2c5b3e08b890df103df0c26013a0df103df0c26013a2ebf3cb6fd35a0ac0df103df0c26013a8b1472fe2f9ba6b984dcacfe8ebe897bfa4d7ea84258c63d59c9621b2c0fa58f915ac1de0d034112ad09145d3e31a56946b8ae4e81acb9fae2415097439d4047fb0fb526bb39450a46b8ae4e81acb9fa34d62295fceb188dd99271d186dc1cd03de19da9ed218fe2d50b96489ab264edd50b96489ab264edd50b96489ab264ed46b8ae4e81acb9fa51b1fde863bf5c12f8ee35c29834d631c9ba923b7b327da78fe44b90230da2aceb6fa41872a5ca4e2985db2d99140e2d0ee226f11256b780315536c94b3e90e338f0f5e6e0d2832e960a06c8b1b2133da291fc3f0bfffdd50df103df0c26013a0df103df0c26013aafbc9dcfc006bed997d74c27146670dfa01eb4b4be78b42b3de19da9ed218fe23de19da9ed218fe2dc4f5790d1ff098f2fd5948f5c676efa78a492ecab7d2b7f",
                "ft" => "\"Qoj0zJbLmNr9odmM2pSV8pdqHcGr+geLGE9qZeFhJWHwZC4diBzUJuW6vloPr6fIC+vMUspTA7ImaaK9+EW+dAx76/o+Vi+Srv/Q+vPl+5ispU+lYKTwIEDhKrp6Fr85MJmJLkHyWEUg830qezlJ9rgdONbFjIHB8MN7HDAWAZ07MM/T34yI5eLrFb4j2jSl\"",
                "SEARCH_HISTORY_IDS" => "%2C4",
                "_ym_visorc" => "b",
                "__utma" => "99926606.1222664713.1615372211.1617707412.1617709534.10",
                "__utmb" => "99926606.25.9.1617712901289"
            ];
            
            if(!isset($attributes->query))
                throw "Empty query supplied to the request processor";
            
            $this->query = $attributes->query;
            $this->city = $attributes->city ?? null;
            $this->category = $attributes->category ?? null;
        }


        /** 
         * Выполняем запрос с данными в атрибутах
         * @return string
         */
        public function getHtml() {

            $city = $this->city;
            $category = $this->category;
            $query = $this->query;
            $page = $this->page;
            $responses429 = 0;
            
            while($responses429 <= BASE_MAX_NUM_429) {
                try{
                    $this->logger->log("Request GET /$city/$category?q=$query&p=$page");

                    $res= $this->client->request('GET', "/$city/$category", [
                        'query' => "q=$query&p=$page",
                    ]);   

                    dump($res->getHeaders()["set-cookie"]);

                    foreach($res->getHeaders()["set-cookie"] as $cookie) {
                        $parts = explode('=', $cookie);
                        $key = $parts[0];
                        $parts = explode(';', $parts[1]);
                        $value = $parts[0];
                        $this->cookie[$key] = $value;
                    }

                    dump($this->cookie);

                    $htmlStream = $res->getBody();          //читаем поток, чтобы вернуть HTML
                    return $htmlStream->read($htmlStream->getSize());
                }
                catch(Exception $e) {                   
                    if($e->getResponse()->getStatusCode() == 429)  //too many request
                    {
                        $responses429++;
                        $this->logger->log("429 Too many requests.$responses429-th try ", null);  

                        if($responses429 <= BASE_MAX_NUM_429) {                    
                            sleep(BASE_SLEEP_TIME_429);
                        }
                    }
                    else {  //другая ошибка
                        $this->logger->error("Ошибка при выполнении запроса", $e);
                        return null;
                    }                                  
                }
            }

            // Если попытки закончились
            $this->logger->error("429 Too many requests. ".BASE_MAX_NUM_429." tries were unsuccessed", $e);
            return null;
        }



        /**
         * Получаем объявления и парсим их
         */
        public function get() {
            $html = $this->getHtml();
            $count = $this->parser->parseJSON($html, $this->collection, $this->locationId);  // Тут заполняется коллекция    
            $this->database->insertCollection($this->collection);
            $this->collection->clear();

            $this->logger->log("$count ads recieved", null);

            return $count;
        }


        public function getWithMeta() {
            $html = $this->getHtml();
            $data = $this->parser->parseJSONWithMeta($html, $this->collection);  // Тут заполняется коллекция    
            $this->database->insertCollection($this->collection);
            $this->collection->clear();

            $this->logger->logMessage("\n<request meta>");
            $this->logger->log("There are ".$data['total']." ads for this request", null);
            $this->logger->log("Items on the page: ".$data['itemsOnPage'], null);
            $this->logger->log("Is authenticated: ".($data['isAuthenticated'] ? "true" : "false"), null);
            $this->logger->log("Avito user id: ".($data['userId'] ?? "none"), null);
            $this->logger->log("Avito search hash: ".$data['searchHash'], null);
            $this->logger->log($data['recieved']." ads recieved", null);
            $this->logger->logMessage("</request meta>\n\n");


            return $data;
        }

        
        // Получаем следующую страницу
        public function getNextPage() {
            $this->page++;
            return $this->get();
        }


        public function getAllPagesData() {
            $this->page = 1;
            $data = $this->getWithMeta();
            $count = $data['recieved'];
            $this->locationId = $data['locationId'];
            while($newCount = $this->getNextPage()) {
                $count += $newCount;
                sleep(BASE_SLEEP_TIME);
            };
            return $count;           
        }
    }