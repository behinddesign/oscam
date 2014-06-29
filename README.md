oscam
=====

Oscam XML API to PHP SimpleXML or array, handles connection, auth and parsing. 

```<?php
include __DIR__ . '/../vendor/autoload.php';

use graham192\OSCam\OSCam;

$oscam = new OSCam();

try {
    $oscam->setConnection("IP",PORT)->setAuth("USER","PASS");

    var_export($oscam->getStatus());
    var_export($oscam->getStatusWithLog());
    var_export($oscam->getReaderStats("my_reader"));
    var_export($oscam->getReaderEntitlements("my_reader"));
    var_export($oscam->getUserStats());
    var_export($oscam->getUserStats("my_user"));
}catch(Exception $e){
    echo $e->getMessage()."\n";
    die();
}```

