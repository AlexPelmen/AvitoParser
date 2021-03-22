<?php
    class AviAdsCollection {
        public 
            $models;
        
        public function __construct($modelsArray = []) {
            $this->models = $modelsArray;
        }

        public function add($model) {
            if($model instanceof AviAdsModel)
                $this->models []= $model;
            else
                throw "AviAdsCollection must contain AviAdsModels only. Another instance given";
        }

        public function clear() {
            $this->models = [];
        }
    }