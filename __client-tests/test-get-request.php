<?php

set_time_limit(0);

header("Access-Control-Allow-Orgin: *");
header("Access-Control-Allow-Methods: *");

//header("Content-Type:text/xml; charset=utf-8");
header("Content-Type: application/json; charset=utf-8");
//header("Content-Type: text/html; charset=utf-8");
//header("Content-Type: application/x-tar; charset=utf-8");
//header("Content-Encoding: gzip");

// help method to generate random strings:
function GenRandString($length = 16) {
    $characters = array_merge(range("A","Z"), range(0,9));
	rsort($characters);
	
    $randStr = '';
    for ($i = 0; $i < $length; $i++) {
        $randStr .= $characters[rand(0, sizeof($characters) - 1)];
    }
    return $randStr;
}

// simulate a client->server call:
$APIBaseURI = "http://ws-wrapper.local"; // no trailing fwd slash
$APIVersion = "v1";

// Private key must not be transmitted as arg, else API access will be disabled:

$APIUserKey = "SACAP-EDUDEX";
$APIPublicKey = 'nhSnG34jMzQvBTmv';
$APIPrivateKey = 'ZjExOGIxYzAxZDQ0MmRmZjg2ODZkY2Y1OTc1M2FiZmQ4Yzg5NjkxM2QwZTk1ZWQ2N2FmNjEzZmNhYzM1NmQxNg==';

$APIResource = "api_test"; // name of the webservices method being envoked

$st = time();

// query string components for security nonce hash:
$ClientData = array(
	"p1" => $APIPublicKey, // public key(ftg client instance key)
	"p2" => $APIUserKey, // test client key (used to retrieve private key against ftg users list)
	"p3" => time(), // unix timestamp
	"p4" => $APIResource, // resource being request read/updated
	"p5" => GenRandString(16), // random number, 16 digits
	"p6" => GenRandString(24), // random number, 24 digits
	"p7" => GenRandString(32), // random number, 32 digits
);

$QueryString = http_build_query($ClientData);

$NonceHash = urlencode(base64_encode(mhash(MHASH_SHA256, $QueryString, $APIPrivateKey)));

// concat api call url:
$API_Call = "{$APIBaseURI}/{$APIVersion}/{$APIResource}/?h={$NonceHash}&{$QueryString}";

// **********************************************************************************************
// GET request and output result:

$curl = curl_init($API_Call);

curl_setopt($curl, CURLOPT_FAILONERROR, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$cresult = curl_exec($curl);

if(curl_error($curl)){

	echo curl_error($curl);
} else {

	//echo "<pre>";
	
	print_r($cresult);
	
	//echo "</pre>";
}