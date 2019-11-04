<?php


include "scrape-class.php";
include "deals-class.php";

$url = "https://www.shedeals.be/nl/";
$domain = 'https://www.shedeals.be';


$scrapeObj = new scrape();
$pageData = $scrapeObj->init($url , $domain);

// echo $pageData['body']; die;


$objDeals = new Deals($scrapeObj,$domain );
$objDeals->init($pageData['body']);






// print_r($classByresp);

?>