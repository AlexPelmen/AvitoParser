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
                'browser' => new Session(new ChromeDriver("http://".BASE_MINK_HOST.':'.BASE_MINK_PORT, null, 'http://www.google.com'))
            ));
            $this->mink->setDefaultSessionName('browser');

        }


        public function getMetaData($request) {
            try{
                $session = $this->mink->getSession();
                $session->visit($request);
                $session->executeScript("document.cookie = '';");
                
                sleep(BASE_MINK_PAGE_LOAD_TIME);

                $cookie = $session->evaluateScript(
                    "return document.cookie;"
                );

                $data = $session->evaluateScript(
                    "return document.getElementsByClassName('js-initial')[0].getAttribute('data-state');"
                );
                $data = json_decode($data);
                
                return [
                    "cookies" => $cookie,
                    "data" => $data
                ];
            }
            catch(Exception $e) {
                $this->logger->error("Ошибка при использовании браузера Mink", $e);
                return [];
            }
            
        }
    }