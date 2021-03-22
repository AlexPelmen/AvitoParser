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
            $this->adsCollection = new AviAdsCollection();
            $this->requests = [];
            $this->parser = new AviHtmlParser($this->logger);
        }

        public function scan() {
            $this->logger->log("Started");
            $requestData = $this->getRequestSettingsFromConfig();
            foreach($requestData as $rData){
                $attrs = new AviRequestAttributes($rData->city, $rData->category, $rData->query);
                $this->requests []= new AviRequest($attrs, $this->parser, $this->adsCollection, $this->logger);
            };

            $ids = $this->executeRequests();
            if($ids) {
                $newIds = $this->database->filterNewIds($ids);  // получаем новые id для того, чтобы снизить количество INSERT запросов к бд
                foreach($newIds as $id) {
                    $model = $this->adsCollection->getById($id);
                    $this->database->insert($model);
                }
            }
            $this->logger->log("Finished");
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
            $ids = [];
            foreach($this->requests as $request) {
                $ids = array_merge($ids, $this->executeOneRequest($request));
            }
            return $ids;
        }

        
        public function executeOneRequest($request) {
            return $request->getAllPagesData();            
        }        
    }