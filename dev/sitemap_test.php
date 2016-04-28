<?php

$xml = simplexml_load_file("../sitemap.xml");

$url = $xml->addChild("url");
$url->addChild("loc", "HOLA");
$url->addChild("changefreq", "monthly");
$url->addChild("priority", "0.8");

$url = $xml->addChild("url");
$url->addChild("loc", "HOLA");
$url->addChild("changefreq", "monthly");
$url->addChild("priority", "0.8");

$url = $xml->addChild("url");
$url->addChild("loc", "HOLA");
$url->addChild("changefreq", "monthly");
$url->addChild("priority", "0.8");

$url = $xml->addChild("url");
$url->addChild("loc", "HOLA");
$url->addChild("changefreq", "monthly");
$url->addChild("priority", "0.8");

$url = $xml->addChild("url");
$url->addChild("loc", "HOLA");
$url->addChild("changefreq", "monthly");
$url->addChild("priority", "0.8");


echo $xml->asXML();

$xml->asXML("../sitemap.xml");



?>