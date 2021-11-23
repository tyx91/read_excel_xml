<?php
require_once dirname(__DIR__).'/vendor/autoload.php';

use Src\Read;
$xml = './user.xml';
$generator = Read::excelXml($xml);
$transField = [
    //excel的列号=>转换后的数组键
    '1'=>'serial_number',
    '2'=>'user_name',
    '3'=>'user_job',
    '4'=>'user_subject',
];
foreach ($generator as $data){
    $data  = Read::transData($transField,$data);
    print_r($data);
}


