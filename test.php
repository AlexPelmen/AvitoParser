<?php

    require __DIR__ . "/vendor/autoload.php";

    use Behat\Mink\Mink;
    use Behat\Mink\Session;
    use DMore\ChromeDriver\ChromeDriver;

    echo "Come on, bitches!!!\n";

    $mink = new Mink(array(
        'browser' => new Session(new ChromeDriver('http://localhost:9222', null, 'http://www.google.com'))
    ));

    // set the default session name
    $mink->setDefaultSessionName('browser');

    $session = $mink->getSession();
    $session->visit('https://www.avito.ru/moskva/audio_i_video?p=1&q=go+pro+hero+9');
    
    //echo $session->getCookie('f');

    //echo $session->executeScript('document.cookie');
    sleep(5);
    echo $session->evaluateScript(
        "return document.cookie;"
    );

    echo "That's all, pals)))\n";