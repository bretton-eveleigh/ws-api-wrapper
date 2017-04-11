<?php

$sampleObject = new stdClass();
$sampleObject->property1 = "Object1";
$sampleObject->property2 = range("A","E");

$sampleObject2 = new stdClass();
$sampleObject2->property1 = "Object2";
$sampleObject2->property2 = range(1,5);

$this->SetResponseEntry("response_bool", true);
$this->SetResponseEntry("response_string","This is a string response from Morne GET method");
$this->SetResponseEntry("listOfObjects",array(
	$sampleObject,
	$sampleObject2
));
