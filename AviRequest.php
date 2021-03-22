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
                'base_uri' => BASE_URI
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
         * @return guzzleStream
         */
        public function getHtml() {
            $city = $this->city;
            $category = $this->category;
            $query = $this->query;
            $page = $this->page;
            
            try{
                $this->logger->log("Request GET /$city/$category?q=$query&p=$page");

                $res= $this->client->request('GET', "/$city/$category", [
                    'query' => "q=$query&p=$page"
                ]); 
                $htmlStream = $res->getBody();     
                return $htmlStream->read($htmlStream->getSize());
            }
            catch(Exception $e) {
                $this->logger->error($e);
                return null;
            }
        }

        /**
         * Получаем объявления и парсим их
         */
        public function get() {
            $htmlStream = $this->getHtml();
            return $this->parser->parse($htmlStream, $this->collection);   // Тут заполняется коллекция
        }

        
        // Получаем следующую страницу
        public function getNextPage() {
            $this->page++;
            return $this->get();
        }


        public function getAllPagesData() {
            $this->page = 1;
            $ids = [];
            while($newIds = $this->get()) {
                $ids = array_merge($ids, $newIds);
                sleep(BASE_SLEEP_TIME);
            };
            return $ids;           
        }
    }