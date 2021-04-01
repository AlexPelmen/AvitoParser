<?php
    require_once __DIR__ . "/AviAdsAttributes.php";

    class AviAdsModel {
        public
            $logger,
            $attributes;
        
        public function __construct($params, $logger) { 
            $this->attributes = new AviAdsAttributes();          
            $this->logger = $logger;
            try{
                // обязательные параметры
                if(!isset($params['id']))
                    throw new ErrorException("Need `id` param for creating AdsModel");
                if(!isset($params['title']))
                    throw new ErrorException("Need `title` param for creating AdsModel");
                if(!isset($params['link']))
                    throw new ErrorException("Need `link` param for creating AdsModel");

                $this->attributes->id = $params['id'];
                $this->attributes->title = $params['title'];
                $this->attributes->link = $params['link'];    
                //необязательные - просто копируем из атрибутов
                
                $this->attributes->price = $params['price'] ?? null;
                $this->attributes->city = $params['city'] ?? null;
                $this->attributes->date = $params['date'] ?? null;
                $this->attributes->dislike = $params['dislike'] ?? 0;                
            }
            catch(Exception $e) {
                $this->logger->error("Ошибка при создании модели объявления", $e);
            }
        }

        public function getJSON () {
            return json_encode($this->attributes);
        }
    }