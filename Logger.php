<?php

    /**
     * Реализует логирование ошибок выполнения программы в файл
     * 
     */

    require_once __DIR__ . "/vendor/autoload.php";
    require_once __DIR__ . "/constants.php";    

    class Logger {
        
        public function error($e) {
            $out = date("[d.m.Y h:i:s]");
            $out .= $e;
            $out .= "\n";
            
            file_put_contents(BASE_LOG_ERROR_PATH, $out, FILE_APPEND);
        }

        public function log($text) {
            $out = date("[d.m.Y h:i:s]");
            $out .= $text;
            $out .= "\n";

            file_put_contents(BASE_LOG_PATH, $out, FILE_APPEND);
        }
    }