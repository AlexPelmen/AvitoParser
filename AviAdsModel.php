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
                    throw "Need `id` param for creating AdsModel";
                if(!isset($params['title']))
                    throw "Need `title` param for creating AdsModel";
                if(!isset($params['link']))
                    throw "Need `link` param for creating AdsModel";

                //необязательные - просто копируем из атрибутов
                $this->attributes->title = $params['price'] ?? null;
                $this->attributes->city = $params['city'] ?? null;
                $this->attributes->time = $params['time'] ?? null;
                $this->attributes->dislike = $params['dislike'] ?? null;                
            }
            catch(Exception $e) {
                $this->logger->error($e);
            }
        }

        public function getJSON () {
            return json_encode($this->attributes);
        }
    }