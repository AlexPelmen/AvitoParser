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
            $page;
        
            
        // Инициализация переменных            
        public function __construct ($attributes, $parser, $collection, $logger) {
            $this->client = new GuzzleHttp\Client([
                'base_uri' => BASE_URI,
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
                    "cookie" => "buyer_selected_search_radius4=0_general; _gcl_au=1.1.1668156129.1615822052; _ym_uid=16158220521004781058; _ym_d=1615822052; __gads=ID=01f5523b08315103:T=1615822051:S=ALNI_MagRU369w3sh2Mb_uGfRh4oHFg6HQ; _ga=GA1.2.1333292215.1615822058; isCriteoSetNew=true; showedStoryIds=61-58-51-50-49-47-42-32; lastViewingTime=1616177812047; f=5.f0b321658fd92b2f4b5abdd419952845a68643d4d8df96e9a68643d4d8df96e9a68643d4d8df96e9a68643d4d8df96e94f9572e6986d0c624f9572e6986d0c624f9572e6986d0c62ba029cd346349f36c1e8912fd5a48d02c1e8912fd5a48d0246b8ae4e81acb9fa143114829cf33ca746b8ae4e81acb9fa46b8ae4e81acb9fae992ad2cc54b8aa8ec20f9213b3a1b87615ab5228c34303140e3fb81381f359178ba5f931b08c66a59b49948619279110df103df0c26013a03c77801b122405c2da10fb74cac1eab2da10fb74cac1eabdc5322845a0cba1af722fe85c94f7d0c2da10fb74cac1eab2da10fb74cac1eab2da10fb74cac1eab2da10fb74cac1eab3c02ea8f64acc0bddc5b253bbc650d280c79affd4e5f1d11162fe9fd7c8e976748b2546f6f7b341b260d6a00b989230b5e61d702b2ac73f7b51842b10d61b1cebd68692f50edb27438adc93de73b65ba497eea5644b1bc05fed88e598638463b0df103df0c26013a0df103df0c26013aafbc9dcfc006bed997d74c27146670df5ff982a458d39d383de19da9ed218fe23de19da9ed218fe2dc4f5790d1ff098f147332f8181712da121fd81ea2a6eb1d; ft=\"8yvlH3qu6gZJHulfelS/wZmD7U9WVA0++aJqwwy+MnoORYmv88+TinXS35RfGp+wU7HQGq1eERlwX0NOUowiGr8DSj6/n3LYXCi28p6RK5lVvLvfX+rxWghf5S73QzgIUjTVxJY3sZsMmtta7/S+sNdaWKrBo+vvNIVa/rTrDSRiLX0NGlRBmz9hebYk6/yq\"; abp=1; SEARCH_HISTORY_IDS=%2C4; no-ssr=1; _gid=GA1.2.1892372715.1617353741; st=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjoidko0TkJ5SlU2TElJTGRDY3VNTkdBYnlzVGk3VTVKRW8wVkZQNXBSVll0ZVlpM0lxVUJ1Q2JQVUtjS0FnSGJvRTFRWnM3RkhzanorS2ZMTDBLQ2ZZalBhT25wajE3WXJRMVhCcW5VS0szSUtQZ3FTckpYVUZyUE82Sm5QWnZ0dWN2TEVjTkRaKytjRUJ5bzVJQWtzSDE2aDcvWTkxb1BrQ1JNQ2lLQTE2WHpnVnZpOFVZMkFBN0ZiY1FRT29QZXRGQml5M3NldEp5Q3pRdC8vTitMakFGa2hXZ3M1N3RCa1VsaTN5cEJ5ZHJaL1NoRzZjM2xjTzZJV2NpbHl0azhlR1RIaHYyaHc1RzhTYkQrVjlvUEtnMWlJa3Z1SU1Ic01pRGN6MExRZnZaRjQ9IiwiaWF0IjoxNjE3MzUzNzM3LCJleHAiOjE2MTc5NTg1Mzd9.0_Du9QJbuZk75viSvfzrJUUyYTtF_bHdgBPh2xWk--E; _ym_isad=1",
                ]
            ]);

            $this->parser = $parser;
            $this->logger = $logger;   
            $this->collection = $collection;         

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

                    // $res= $this->client->request('GET', "https://webhook.site/7185a1d3-d5d5-4b95-85d8-d7af9b399c99");  

                    // exit();

                    $res= $this->client->request('GET', "/$city/$category", [
                        'query' => "q=$query&p=$page",
                    ]);         

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
            return $this->parser->parseJSON($html, $this->collection);  // Тут заполняется коллекция          
        }

        
        // Получаем следующую страницу
        public function getNextPage() {
            $this->page++;
            return $this->get();
        }


        public function getAllPagesData() {
            $this->page = 1;
            $ids = $this->get();
            while($newIds = $this->getNextPage()) {
                $ids = array_merge($ids, $newIds);
                sleep(BASE_SLEEP_TIME);
            };
            return $ids;           
        }
    }