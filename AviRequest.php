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
            $cookies,
            $stateCookie,
            $lastViewingTime;
        
            
        // Инициализация переменных            
        public function __construct ($attributes, $parser, $database, $logger) {
            
            $this->logger = $logger; 

            $this->cookies = \GuzzleHttp\Cookie\CookieJar::fromArray(DEFAULT_COOKIES, 'avito.ru');
            $this->refreshState();

            $this->client = new GuzzleHttp\Client([
                'base_uri' => BASE_URI,
                'cookies' => $this->cookies,
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
              
            $this->collection = new AviAdsCollection();         
                    
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
            $responses404 = 0;
            
            while($responses429 <= BASE_MAX_NUM_429 && $responses404 <= BASE_MAX_NUM_404 ) {
                try{
                    $this->logger->log("Request GET /$city/$category?q=$query&p=$page");

                    $res= $this->client->request('GET', "/$city/$category/", [
                        'query' => "q=$query&p=$page",
                        'cookies' => $this->cookies,
                    ]);  
                    
                    $this->updateLastViewingTime();

                    $htmlStream = $res->getBody();          //читаем поток, чтобы вернуть HTML
                    return $htmlStream->read($htmlStream->getSize());
                }
                catch(Exception $e) {  
                    switch($e->getResponse()->getStatusCode()) {
                        case 429: 
                            $responses429++;
                            $this->logger->log("429 Too many requests. $responses429-th try ", null);  
                            $this->refreshState();

                            if($responses429 <= BASE_MAX_NUM_429) {                    
                                sleep(BASE_SLEEP_TIME_429);
                            }
                            
                            break;
                        case 404:
                            $responses404++;
                            $this->logger->log("404 Not found. $responses404-th try ", null);
                            $this->refreshState();  
    
                            if($responses404 <= BASE_MAX_NUM_404) {                    
                                sleep(BASE_SLEEP_TIME_404);
                            }
                            break;
                        default:
                            $this->logger->error("Ошибка при выполнении запроса", $e);
                            return null;
                    }                                           
                }
            }

            // Если попытки закончились
            if($responses429 > BASE_MAX_NUM_429)
                $this->logger->error("429 Too many requests. ".BASE_MAX_NUM_429." tries were unsuccessed", $e);
            if($responses404 > BASE_MAX_NUM_404)
                $this->logger->error("404 Not found. ".BASE_MAX_NUM_404." tries were unsuccessed", $e);
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

        /**
         * Обновить cookie state для того, чтобы скинуть 429 ошибку 
         */
        public function refreshState() {
            $value = "state=".uniqid("state_").';';            
            $this->stateCookie = GuzzleHttp\Cookie\SetCookie::fromString($value);
            $this->logger->log("Refreshing cookie state: $value", null);
            $this->stateCookie->setDomain("avito.ru");           
            $this->cookies->setCookie($this->stateCookie);
        }


        /**
         * Обновить в куки время последнего запроса
         */
        public function updateLastViewingTime() {
            $this->lastViewingTime = time()*1000;
            $cookie = $this->cookies->getCookieByName("lastViewingTime");
            $cookie->setValue($this->lastViewingTime);
            $this->cookies->setCookie($cookie);
        }
    }