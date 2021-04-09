<?php 

    require __DIR__ . "/vendor/autoload.php";

    use Behat\Mink\Mink;
    use Behat\Mink\Session;
    use DMore\ChromeDriver\ChromeDriver;

    class MinkBrowser{
        public 
            $logger,
            $mink;
        
        public function __construct($logger) {
            $this->logger = $logger;
            $this->mink = new Mink(array(
                'browser' => new Session(new ChromeDriver('http://localhost:9222', null, 'http://www.google.com'))
            ));
            $this->mink->setDefaultSessionName('browser');

        }


        public function getMetaData($request) {
            try{
                $session = $mink->getSession();
                $session->visit('https://www.avito.ru/moskva/audio_i_video?p=1&q=go+pro+hero+9');
                $session->executeScript("document.cookie = '';");
                
                sleep(BASE_MINK_PAGE_LOAD_TIME);

                $cookie = $session->evaluateScript(
                    "return document.cookie;"
                );

                $data = $session->evaluateScript(
                    "return JSON.parse($(\".js-initial\").attr(\"data-state\"));"
                );

                return [
                    "cookies" => $cookies,
                    "data" => $data
                ];
            }
            catch(Exception $e) {
                $this->logger->error("Ошибка при использовании браузера Mink", $e);
                return [];
            }
            
        }
    }