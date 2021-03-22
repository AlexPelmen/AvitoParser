<?php
    require_once __DIR__ . "/vendor/autoload.php";
    require_once __DIR__ . "/ScanningProcessor.php";

    $scanner = new ScanningProcessor();
    $scanner->scan();