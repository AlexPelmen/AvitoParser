<?php
    /**
     * Реализует логику выполнения запросов к авито
     * Получает HTML с данными
     */

    require_once __DIR__ . "vendor/autoload.php";
    require_once __DIR__ . "vendor/constants.php";

    class AviRequest {
        public
            $client,
            $logger,
            $city,
            $query,
            $category,
            $page;
        
            
        // Инициализация переменных            
        public function __construct ($attributes, $logger) {
            $this->client = new GuzzleHttp\Client([
                'base_uri' => BASE_URI
            ]);

            $this->logger = $logger;

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
        public function get() {
            $city = $this->city;
            $category = $this->category;
            $query = $this->query;
            $page = $this->page;
            
            try{
                $this->logger->out("Request GET /$city/$category?q=$query&p=$page");

                return $this->client->request('GET', "/$city/$category", [
                    'query' => "q=$query&p=$page"
                ]);        
            }
            catch(Exception $e) {
                $this->logger->error($e);
                return null;
            }
        }

        
        // Получаем следующую страницу
        public function getNextPage() {
            $this->page++;
            return $this->get();
        }


        public function getAllPagesData() {
            
            // Нужно понять, когда переставать делать запросы
        }
    }