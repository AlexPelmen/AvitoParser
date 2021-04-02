<?php
    /**
     * Обработка HTML и парсинг данных по объявлениям
     */

     require_once __DIR__ . "/constants.php";
     require_once __DIR__ . "/AviAdsCollection.php";

     class AviHtmlParser {
         public
            $logger,
            $dom;

        public function __construct($logger) {
            $this->logger = $logger;
            $this->dom = new PHPHtmlParser\Dom;

            $this->currentAdsId = null; // Нужно для логирования
        }


        /**
         * Формирование коллекции модлей объявлений
         * @param $htmlStream поток guzzle после запроса к авито
         * @return Array Массив id спаршенных объявлений
         */
        public function parse($html, $collection) {
            $html = $this->clearHtmlClasses($html);
            $this->dom->loadStr($html);            
            $items = $this->getItems();
            $ids = [];
            foreach($items as $item) {
                $id = $this->getItemId($item);
                $this->currentAdsId = $id;
                $ids []= $id;

                $collection->add(
                    new AviAdsModel([
                            "id" => $id,
                            "title" => $this->getItemTitle($item),
                            "link" => $this->getItemLink($item),
                            "price" => $this->getItemPrice($item),
                            "date" => $this->getItemDate($item),
                        ],
                        $this->logger
                    )
                );
            }  
            return $ids;        
        }


        /**
         * Новый метод парсинга инфы из верстки
         * 
         */
        public function parseJSON($html, $collection) {

            $this->dom->loadStr($html); 
            $data = $this->getInitialJson();
            $ids = [];
            $items = $data->catalog->items;

            return $this->itemsToModels($items, $collection);
             
        }


        /**
         * Преобразуем полученные от avito item-ы в модели
         */
        public function itemsToModels($items, $collection) {
            $ids = [];
            foreach($items as $item) {
                switch($item->type) {
                    case "banner":      // рекламные баннеры. Нафиг не надо
                        break;
                    case "item":
                        try{
                            $model = $this->createAdsModelWithObject($item);    // обычные объявления работяг
                            if($model){
                                $collection->add($model);
                                $ids []= $item->id;
                            }
                        }
                        catch(Exception $e) {
                            $this->logger("Ошибка при создании модели из item.", $e);
                        }
                        break;
                    case "vip":     // Проплаченные объявления, но все же релевантные
                        $this->itemsToModels($item->items, $collection);
                        break;
                }
            } 
            return $ids;  
        }


        /**
         * Достаем JSON из верски avito
         */
        public function getInitialJson() {
            try{
                $attr = $this->dom->find(".js-initial")->{"data-state"};
                $json = html_entity_decode($attr);
                return json_decode($json);
            }
            catch(Exception $e) {
                $this->logger->error("Не удалось получить JSON при запросе", $e);
            }
        }


        /**
         * Создаем модель объявления на основе объекта из запроса
         */
        public function createAdsModelWithObject($obj) {
            try{
                return new aviAdsModel([
                    "id" => $obj->id,
                    "title" => $obj->title,
                    "link" => $obj->urlPath,
                    "timestamp" => floor($obj->sortTimeStamp / 1000) ?? null,
                    "location" => $obj->addressDetailed->locationName ?? null,
                    "geo" => $obj->geo->geoReferences ?? null,
                    "images" => $obj->images ?? null,
                    "locationId" => $obj->locationId ?? null,
                    "price" => $obj->priceDetailed->value ?? null, 
                ], $this->logger);
            }
            catch(Exception $e) {
                $this->logger->error("Ошибка при создании модели id: ".$obj->id, $e);
                return null;
            }
        }


        /**
         * Удаляет префикс html классов
         */
        private function clearHtmlClasses($html) {
            return preg_replace_callback(

                '/class="([\w\-\_ ]+)"/', 

                function($match) {   
                    $classNames = explode(' ', $match[1]); 
                    $newClassValue = ""; 
                    foreach($classNames as $name) {
                        $newClassValue .= substr($name, 0, strlen($name) - 6)." ";   
                    }        
                    return 'class="'.$newClassValue.'"';
                },

                $html
            );
        }


        private function getPrimaryProp($obj, $prop) {
            try{
                return $obj->{$prop};
            }
            catch(Exception $e) {
                $this->logger->error("Не удалось получить обязательное свойство `$prop` объявления `{$this->currentAdsId}`", $e);
                return null;
            }
        }

        private function getOptionalProp($obj, $prop) {
            try{
                return $obj->{$prop};
            }
            catch(Exception $e) {
                $this->logger->error("Не удалось получить необязательное свойство `$prop` объявления `{$this->currentAdsId}`", $e);
                return "not stated";
            }
        }

        public function getItems() {
            return $this->dom->find(AVITO_SELECTOR_ITEM);
        }

        public function getItemId($item) {
            return $this->getPrimaryProp($item, "data-item-id");
        }

        public function getItemTitle($item) {
            return $this->getPrimaryProp(
                $item->find(AVITO_SELECTOR_ITEM_TITLE),
                "text"
            );
        }

        public function getItemLink($item) {
            return $this->getPrimaryProp(
                $item->find(AVITO_SELECTOR_ITEM_LINK),
                "href"
            );
        }

        public function getItemPrice($item) {
            return $this->getOptionalProp(
                $item->find(AVITO_SELECTOR_ITEM_PRICE),
                "text"
            );
        }

        //  проблемы с получением места
        // public function getItemAdress($item) {
        //     return $item->find(AVITO_SELECTOR_ITEM_ADRESS);
        // }

        public function getItemDate($item) {
            return $this->getOptionalProp(
                $item->find(AVITO_SELECTOR_ITEM_DATE),
                "text"
            );
        }


        public function getAdsNumber() {
            return $this->getPrimaryProp(
                $this->dom->find(AVITO_SELECTIOR_ADS_NUMBER),
                "text"
            );
        }
     }