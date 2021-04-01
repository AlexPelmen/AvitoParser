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
        public function insert($model) {
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
                    $model->id, 
                    '$model->title',
                    '$model->link',
                    $model->price,
                    '$model->city',
                    $model->time,
                    ".($model->dislike ? "TRUE" : "FALSE")."
                );");
            }
            catch(Exception $e) {
                $this->logger->error("Ошибка при записи в базу данных. Запрос insert", $e);
            }
        }


        /**
         * Возвращает только id тех объявлений, которых еще нет в бд
         */
        public function filterNewIds($ids) {
            try{
                $query = "SELECT `id` FROM ads_data WHERE `id` NOT LIKE (".implode(',',$ids).")";
                $this->logger->log("Выполняем sql запрос\nДлина ".strlen($query)."\n$query");
                $res = $this->db->query($query);
                return mysqli_fetch_array($res);
            }
            catch(Exception $e) {
                $this->logger->error("Ошибка при получении списка новых идентификаторов, при запросе к бд", $e);
            }
        }
     }