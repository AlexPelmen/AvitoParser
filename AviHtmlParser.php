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
        public function parseJSON($html, $collection, $locationId) {
            try{
                if(!$html) throw new Exception("No HTML recieved by parser");
                $this->dom->loadStr($html); 
                $data = $this->getInitialJson();
            }
            catch(Exception $e) {
                $this->logger->error("Can't parse empty request", $e);
                return 0;
            }
            return $this->dataToCollection($data, $collection, $locationId);            
        }



        public function parseJSONWithMeta($html, $collection) {
            try{
                if(!$html) throw new Exception("No HTML recieved by parser");
                $this->dom->loadStr($html); 
                $data = $this->getInitialJson();
            }
            catch(Exception $e) {
                $this->logger->error("Can't parse empty request", $e);
                return [
                    "recieved" => 0,
                ];
            }
            return $this->dataToCollectionWithMeta($data, $collection);
        }


        public function dataToCollection($data, $collection, $locationId) {
            $items = $data->catalog->items;
            $count = $this->itemsToModels($items, $collection);

            // костыль для баги в авито
            $extraItems = $data->catalog->extraBlockItems;
            if(count($extraItems)) {
                $this->logger->log("Extra items scanning", null);
                $itemsByOurLocation = $this->filterExtraItemsByLocation($extraItems, $locationId);
                if(count($itemsByOurLocation)) {
                    $count += $this->itemsToModels($itemsByOurLocation, $collection);
                }
            }           

            return $count;
        }


        public function dataToCollectionWithMeta($data, $collection) {
            try{   
                $items = $data->catalog->items;
                $count = $this->itemsToModels($items, $collection);
                $total = $data->mainCount;
                $itemsOnPage = $data->itemsOnPage;
                @$isAuthenticated = $data->isAuthenticated;
                @$userId = $data->user->id;
                $searchHash = $data->searchHash;              
                $locationId = $data->searchCore->locationId;

                return [
                    "recieved" => $count,
                    "total" => $total,
                    "itemsOnPage" => $itemsOnPage,
                    "isAuthenticated" => $isAuthenticated,
                    "userId" => $userId,
                    "searchHash" => $searchHash,
                    "locationId" => $locationId,
                ];
            }
            catch(Exception $e) {
                $this->log->error("Не удалось получить одно из свойств объявления", $e);
                return [
                    "recieved" => 0
                ];
            }
        }


        /**
         * Преобразуем полученные от avito item-ы в модели
         */
        public function itemsToModels($items, $collection) {
            $count = 0;
            foreach($items as $item) {
                switch($item->type) {
                    case "banner":      // рекламные баннеры. Нафиг не надо
                        break;
                    case "item":
                        try{
                            $model = $this->createAdsModelWithObject($item);    // обычные объявления работяг
                            if($model){
                                $collection->add($model);
                                $count++;
                            }
                        }
                        catch(Exception $e) {
                            $this->logger("Ошибка при создании модели из item.", $e);
                        }
                        break;
                    case "vip":     // Проплаченные объявления, но все же релевантные
                        break;
                        $count += $this->itemsToModels($item->items, $collection);
                        break;
                }
            } 
            return $count;  
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
            $geoParams = [];

            if(isset($geo->formattedAddress)) {
                $geoParams []= $formattedAddress;
            } 
            
            if(count($obj->geo->geoReferences)) {
                foreach($obj->geo->geoReferences as $geo) {
                    if(isset($geo->content)){
                        @$geoParams []= trim("$geo->content $geo->after");
                    }                
                }
                $geoStr = implode(',', $geoParams);
            }

            try{
                return new aviAdsModel([
                    "id" => $obj->id,
                    "title" => $obj->title,
                    "link" => $obj->urlPath,
                    "timestamp" => floor($obj->sortTimeStamp / 1000) ?? null,
                    "location" => $obj->addressDetailed->locationName ?? null,
                    "geo" => $geoStr ?? null,
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
         * Фильтруем объявления из других городов, чтобы выбрать оттуда объявления по определенной location
         */
        public function filterExtraItemsByLocation($items, $locationId) {
            $out = [];
            foreach($items as $item) {
                if($item->location->id == $locationId)
                    $out []= $item;
            }
            return $out;
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