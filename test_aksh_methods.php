<?php
$html = file_get_contents('http://app.aika168.com:8088/openapiv3.asmx');
preg_match_all('/<a href="([^"]+)">([^<]+)<\/a>/', $html, $matches);
foreach ($matches[2] as $match) {
    echo $match . "\n";
}
