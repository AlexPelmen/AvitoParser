<?php
    /**
     * Обработка HTML и парсинг данных по объявлениям
     */

     require_once __DIR__ . "/constants.php";

     class AviParser {
         public
            $logger,
            $dom;

        public function __construct($logger) {
            $this->logger = $logger;
            $this->dom = new PHPHtmlParser\Dom;
        }

        public function parse($htmlStream) {
            $this->dom->loadStr($htmlStream);
            $items = $this->getItems();
        }

        private function getPrimaryProp($obj, $prop) {
            try{
                return $obj->{$prop};
            }
            catch(Exception $e) {
                $this->logger->error($e);
                return null;
            }
        }

        private function getOptionalProp($obj, $prop) {
            try{
                return $obj->{$prop};
            }
            catch(Exception $e) {
                $this->logger->log("Не удалось получить необязательное свойство `$prop` объявления.\n".json_encode($obj));
                return "not stated";
            }
        }

        public function getItems($item) {
            return $item->find(AVITO_SELECTOR_ITEM);
        }

        public function getItemId($item) {
            return $this->getPrimaryProp(
                $item->find(AVITO_SELECTOR_ITEM_ID),
                "data-item-id"
            );
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
     }