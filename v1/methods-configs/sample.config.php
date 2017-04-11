<?php

$IQServer_IP = "192.168.0.100";
$IQServer_Port = 8010;

$IQServer_Charset = "Windows-1252"; // iq server character set
$WS_CharSet = "UTF-8"; // cryptec webservices character set

//$IQServer_IP = "192.168.1.37";
//$IQServer_Port = 2017;

// ---------------------------------------------------------
// Willowbridge Auth:
$IQCompanyNumber = "001";
$IQTerminalNumber = "99";
$IQUserNumber = "99";
$IQUserPassword = "C89ECE949D7A657B4508788FD2496088EABFD870";
$IQPartnerPassphrase = "";

// ---------------------------------------------------------
// Local Dev Auth
//$IQCompanyNumber = "001";
//$IQTerminalNumber = "1";
//$IQUserNumber = 99;
//$IQUserPassword = "C89ECE949D7A657B4508788FD2496088EABFD870";
//$IQPartnerPassphrase = "743B25C6C57BA9A4D02EBBAD9D11B9ADC47A1BCA";

// ---------------------------------------------------------

$IQ_XML_StockMajorDept = "030";
$IQ_XML_StockMinorDept = "0001";

$IQ_XML_DebtorAccount = "CAS001";
$IQ_XML_DebtorAccount_Currency = "ZAR"; // 0
$IQ_XML_DefaultSupplierCode = 284;

$WS_ScriptName = "TRENDIY_IQRETAIL_QUOTEREQUEST";

$WS_LogFileName_Prefix = $WS_ScriptName."-".date("Ymdhis");

$FileQ_SendPath = "/storage/_file_transfer_store/storeserver.cryptec/send-file-bin/";




