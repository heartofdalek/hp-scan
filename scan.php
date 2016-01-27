<?php
include "lib/HPScan/HPScan.php";

use HPScan\HPScan as Scanner;

/**
copy config.example.php to config.php
and replace device to real
*/
$config = include 'config.php';

try {
    
    $scan = new Scanner($config['device']);
    $scan->setScanDestinationDir('./tests');
    
    $filename = $scan->scan();
    print $filename.PHP_EOL;
    
    // this will not ignore setScanDestinationDir
    $filename = $scan->scanToFile('mydemo.jpg')->scan();
    print $filename.PHP_EOL;
    
    // this will ignore setScanDestinationDir
    $filename = $scan->scanToFile('/tmp/mydemo.jpg')->scan();
    print $filename.PHP_EOL;
    
} catch(Exception $e) {
    print $e->getMessage();
}
