<?php
    /**
     * Реализует логику выполнения запросов к авито
     * Получает HTML с данными
     */

    require_once __DIR__ . "/vendor/autoload.php";
    require_once __DIR__ . "/constants.php";
    require_once __DIR__ . "/MinkBrowser.php";

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
            $fCookie,
            $lastViewingTime,
            $mink;
        
            
        // Инициализация переменных            
        public function __construct ($attributes, $parser, $database, $logger) {
            
            $this->logger = $logger; 

            $this->cookies = \GuzzleHttp\Cookie\CookieJar::fromArray([], 'avito.ru');

            $this->client = new GuzzleHttp\Client([
                'base_uri' => BASE_URI,
                'cookies' => $this->cookies,
                'headers' => BASE_HEADERS,
            ]);

            $this->parser = $parser;
            $this->database = $database;              
            $this->collection = new AviAdsCollection();    
            $this->mink = new MinkBrowser($this->logger);     
                    
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

            try{
                $this->logger->log("Request GET /$city/$category?q=$query&p=$page");

                $res= $this->client->request('GET', "/$city/$category/", [
                    'query' => "q=$query&p=$page",
                    'cookies' => $this->cookies,
                ]);                      
                
                $this->updateLastViewingTime();

                $htmlStream = $res->getBody();          //читаем поток, чтобы вернуть HTML
                return [
                    "status" => 200,
                    "response" => $htmlStream->read($htmlStream->getSize()),
                ];
            }
            catch(Exception $e) {  
                switch($e->getResponse()->getStatusCode()) {
                    case 429: 
                        $this->logger->log("429 Too many requests. $responses429-th try ", null);  
                        break;
                    case 404:
                        $this->logger->log("404 Not found. $responses404-th try ", null);
                        break;
                    default:
                        $this->logger->error("Ошибка при выполнении запроса", $e);
                        break;
                };
                return [
                    "status" => $e->getResponse()->getStatusCode(),
                    "response" => "",
                ];                                          
            }            
        }


        /**
         * Через Mink
         */
        public function getDataWithBrowser() {
            $city = $this->city;
            $category = $this->category;
            $query = $this->query;
            $page = $this->page;

            $request = "https://avito.ru/$city/$category?q=$query&p=$page";
            $this->logger->log("MINK $request");

            try{
                $data = $this->mink->getMetaData($request);
                $this->updateCookies($data['cookies']);
            }
            catch(Exception $e) {
                $this->logger->error("Ошибка при выполнении сессии Mink", $e);
                return [];
            }
            return $data['data'];
        }



        /**
         * Получаем объявления и парсим их
         */
        public function get() {
            $res = $this->getHtml();
            switch($res["status"]) {
                case 200: 
                    $html = $res["response"];
                    $count = $this->parser->parseJSON($html, $this->collection, $this->locationId);  // Тут заполняется коллекция    
                    break;
                case 429: 
                    $data = $this->getDataWithBrowser();
                    $count = $this->parser->dataToCollection($data, $this->collection, $this->locationId); // Тут заполняется коллекция  
                default:
                    $count = 0;
                    break;                    
            }
            
            if($count) {
                $this->database->insertCollection($this->collection);
                $this->collection->clear();
            }
            $this->logger->log("$count ads recieved", null);

            return $count;
        }


        public function getWithMeta() {
            $data = $this->getDataWithBrowser();
            $meta = $this->parser->dataToCollectionWithMeta($data, $this->collection); // Тут заполняется коллекция  
         
            if($meta['recieved']) {
                $this->database->insertCollection($this->collection);
                $this->collection->clear();
            }

            $total = $meta['total'] ?? "Error";
            $itemsOnPage = $meta['itemsOnPage'] ?? "Error";
            $isAuthenticated = $meta['isAuthenticated'] ? "true" : "false";
            $userId = $meta['userId'] ?? "none";
            $searchHash = $meta['searchHash'] ?? "Error";
            $recieved = $meta['recieved'] ?? "None";

            $this->logger->logMessage("\n<request meta>");
            $this->logger->log("There are $total ads for this request", null);
            $this->logger->log("Items on the page: $itemsOnPage", null);
            $this->logger->log("Is authenticated: $isAuthenticated", null);
            $this->logger->log("Avito user id: $userId", null);
            $this->logger->log("Avito search hash: $searchHash", null);
            $this->logger->log("$recieved ads recieved", null);
            $this->logger->logMessage("</request meta>\n");

            return $meta;
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
            if(!$count) return 0;            

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
        public function updateCookies($cookies) {  

            if(!$this->cookies->count()) { //установка нового набор кук
                foreach(explode(';', $cookies) as $cookieString) {
                    $cookie = GuzzleHttp\Cookie\SetCookie::fromString($cookieString);
                    $cookie->setDomain("avito.ru");
                    $this->cookies->setCookie($cookie);
                }
                $this->logger->log("Новые куки установлены из сессии mink", null);
            }
            else{   // обновление кук f и ft
                $this->logger->log("Refreshing cookies", null);  
                $cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray(
                    explode(';', $cookies)
                );
                $f = $cookieJar->getCookieByName('f');
                $ft = $cookieJar->getCookieByName('ft');
    
                $this->cookies->setCookie($f);
                $this->cookies->setCookie($ft);
    
                unset($cookieJar);
            }
        }


        /**
         * Обновить в куки время последнего запроса
         */
        public function updateLastViewingTime() {
            $this->lastViewingTime = time()*1000;
            $cookie = $this->cookies->getCookieByName("lastViewingTime");
            if($cookie) {
                $cookie->setValue($this->lastViewingTime);
                $this->cookies->setCookie($cookie);
            }
        }
    }