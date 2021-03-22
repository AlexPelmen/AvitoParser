<?php
    /**
     * Атрибуты для запросов к авито
     * Параметр page не передаем в рамках данных атрибутов, так как
     * он перебирается в классе AviRequest
     * 
     */
    class AviRequestAttributes {
        public
            $city, 
            $category, 
            $query;

        public function __construct($city, $category, $query) {
            $this->city = $city;
            $this->category = $category;
            $this->query = $query;
        }
    }