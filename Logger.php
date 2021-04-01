<?php

    /**
     * Реализует логирование ошибок выполнения программы в файл
     * 
     */

    require_once __DIR__ . "/vendor/autoload.php";
    require_once __DIR__ . "/constants.php";    

    class Logger {

        private function getText($text, $e) {
            $out = date("[d.m.Y h:i:s]");
            $out .= $text;
            $out .= $e;
            $out .= "\n";
            return $out;
        }
        
        public function error($text, $e = "") {
            file_put_contents(BASE_LOG_ERROR_PATH, $this->getText($text, $e), FILE_APPEND);
        }

        public function log($text, $e = "") {
            file_put_contents(BASE_LOG_PATH, $this->getText($text, $e), FILE_APPEND);
        }
    }