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
    require_once __DIR__ . "/AviRequestAttributes.php";
    require_once __DIR__ . "/AviRequest.php";
    require_once __DIR__ . "/AviHtmlParser.php";


    class ScanningProcessor {
        
        public
            $database,
            $logger,
            $parser,
            $adsCollection,
            $requests;


        /**
         * Инициилизируем инструменты
         */
        public function __construct() {
            $this->logger = new Logger();
            $this->database = new DataBaseAPI($this->logger);
            $this->requests = [];
            $this->parser = new AviHtmlParser($this->logger);
        }

        public function scan() {
            $this->logger->log("Начало сканирования");
            $requestData = $this->getRequestSettingsFromConfig();
            foreach($requestData as $rData){
                $attrs = new AviRequestAttributes($rData->city, $rData->category, $rData->query);
                $this->requests []= new AviRequest($attrs, $this->parser, $this->database, $this->logger);
            };

            $count = $this->executeRequests();
            $this->logger->log("Сканирование завершено. Просканировано $count объявлений\n\n");
        }

        
        private function getRequestSettingsFromConfig() {
            try{
                return json_decode(file_get_contents(BASE_CONFIG_PATH));
            }
            catch(Exception $e) {
                $this->logger->error("Ошибка при чтении настроек config файла", $e);
            }            
        }


        public function executeRequests() {
            $count = 0;
            foreach($this->requests as $request) {
                $count += $request->getAllPagesData(); 
            }
            return $count;
        }     
    }