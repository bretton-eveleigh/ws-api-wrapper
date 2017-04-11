<?php

// just a sample output back to request client:

$this->SetResponseEntry("files_data",$_FILES);

$this->SetResponseEntry("post_data",$_POST);

// add debug stack to response:
$this->DebugResponseInclude();