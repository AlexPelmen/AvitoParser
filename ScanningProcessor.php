<?php
    /**
     * Реализует логику выполнения множества запросов к авито,
     * выполнения обработки полученных данных и занесения их
     * в базу данных. 
     * 
     */

    require_once __DIR__ . "/vendor/autoload.php";
    require_once __DIR__ . "/constants.php";
    require_once __DIR__ . "/Logger.php";
    require_once __DIR__ . "/DataBaseAPI.php";
    require_once __DIR__ . "/AviAdsCollection.php";
    require_once __DIR__ . "/AviAdsModel.php";
    require_once __DIR__ . "/AviAdsRequest.php";


    class ScanningProcessor {
        
        public
            $database,
            $logger,
            $adsCollection,
            $requests;


        /**
         * Инициилизируем инструменты
         */
        public function __construct() {
            $this->logger = new Logger();
            $this->database = new DataBaseAPI($logger);
            $this->adsCollection = new AdsCollection();
            $this->requests = [];

            $requestData = $this->getRequestSettingsFromConfig();
            foreach($requestData as $rData){
                $attrs = new AviRequestAttributes($rData->city, $rData->category, $rData->query);
                $this->requests []= new AviRequest($attrs, $this->logger);
            };

            $this->executeRequests();
        }

        
        private function getRequestSettingsFromConfig() {
            try{
                return json_decode(file_get_contents(BASE_CONFIG_PATH));
            }
            catch(Exception $e) {
                $this->logger->error($e);
            }            
        }


        public function executeRequests() {
            foreach($this->requests as $request) {
                $data = $this->executeOneRequest($request);

                 /* Туду посылаем данные в обработку */

            }
        }


        public function executeOneRequest($request) {
            
        }





        
        
    }