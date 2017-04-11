<?php

set_time_limit(0);

header("Access-Control-Allow-Orgin: *");
header("Access-Control-Allow-Methods: *");

header("Content-Type: application/json; charset=utf-8");

//header("Content-Type: text/html; charset=utf-8");

//header("Content-Type: text/plain; charset=utf-8");

function GenRandString($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Local development ws user:
$APIPrivateKey = 'YzkwZGQxYzc3M2E0MGNhNzg3YmE3MDBhYjZlMGQ1ZmZkMDI3ZTQ5Zjg4MmQ3ZmFkY2Y3MGZkZDc4YzFmYzNkNg==';
$APIPublicKey = 'wB70gYpMer7Z8Oc9';
$APIUserKey = 2;

// Simulate a client->server FTG API call:
$APIBaseURI = "http://webservices.local";
$APIVersion = "v1";

$APIResource = "trendiy-clientorders";

//for($i=0; $i<10; $i++){

// create security nonce:
$ClientData = array(
    "p1" => $APIPublicKey, // public key(ftg client instance key)
    "p2" => $APIUserKey, // test client key (used to retrieve private key against ftg users list)
    "p3" => time(), // unix timestamp, pure int
    "p4" => $APIResource, // resource being request read/updated
    "p5" => GenRandString(16), // random number, 16 digits, alpha numeric only
    "p6" => GenRandString(24), // random number, 24 digits, alpha numeric only
    "p7" => GenRandString(32), // random number, 32 digits, alpha numeric only
);

$QueryString = http_build_query($ClientData);

$NonceHash = urlencode(base64_encode(mhash(MHASH_SHA256, $QueryString, $APIPrivateKey)));

$API_Call = "{$APIBaseURI}/{$APIVersion}/{$APIResource}/?h={$NonceHash}&{$QueryString}&elog";

$curl = curl_init($API_Call);

curl_setopt($curl, CURLOPT_FAILONERROR, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//curl_setopt($curl, CURLOPT_POST,true);

$cfile = new CURLFile('/storage/webservices.local/client-tests/IQ_SampleSourceOrderFile2.xml');

//$cfile = new CURLFile('/storage/_client_order_files/storeserver.cryptec/2015-09-30/REEVOLVEUMHLANGA_1_3-20150930-030959-WOSGRAPF-106.xml','application/xml','/storage/_client_order_files/storeserver.cryptec/2015-09-30/REEVOLVEUMHLANGA_1_3-20150930-030959-WOSGRAPF-106.xml');

// submit a file via post:
$PostData = array('XML_FILE' => $cfile);

curl_setopt($curl, CURLOPT_POSTFIELDS, $PostData);

$cresult = curl_exec($curl);

if(curl_error($curl)){

    echo "error... " . curl_error($curl);
}  else {

    echo $cresult;
}   