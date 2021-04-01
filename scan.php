<?php
    require_once __DIR__ . "/vendor/autoload.php";
    require_once __DIR__ . "/ScanningProcessor.php";

    set_time_limit(0);

    $scanner = new ScanningProcessor();
    $scanner->scan();