<?php
    /**
     * Реализует логику общения с базой данных
     *  
     */

     require_once __DIR__ . "/constants.php";

     class DataBaseAPI {
        public
            $db,
            $logger;

        /**
         * Подключение базы данных
         * 
         */
        public function __construct($logger) {
            
            $this->logger= $logger;

            $this->db = mysqli_connect(
                DB_HOST,
                DB_LOGIN, 
                DB_PASSWORD,
                DB_DATABASE
            ) 
            or die("Ошибка бызы данных" . mysqli_error($this->db));

            $this->db->query("SET NAMES utf-8");
        }

        /**
         * Запрос на добавление инфы об одном объявлении
         * 
         */
        public function insertAdsInfo($adsModel) {
            try{
                $res = $this->db->query("INSERT INTO ads_data(
                    id, 
                    title,
                    link,
                    price,
                    city,
                    time,
                    dislike 
                ) VALUES (
                    $adsModel->id, 
                    '$adsModel->title',
                    '$adsModel->link',
                    $adsModel->price,
                    '$adsModel->city',
                    $adsModel->time,
                    ".($adsModel->dislike ? "TRUE" : "FALSE")."
                );");
            }
            catch(Exception $e) {
                $this->logger->error($e);
            }
        }

        /**
         * Несколько запросов на добавление информации об объявлениях
         * 
         */
        public function insertAdsInfoArray($adsCollection) {
            try{
                foreach($adsCollection->models as $model) {
                    $this->insertAdsInfo($model);
                }
            }
            catch(Exception $e) {
                $this->logger->error($e);
            }
        }
     }