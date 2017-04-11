<?php

class WS_Utils{
	
    static public function GetRandomString($StrLen = 32, $StrAddChars = null, $UCaseOnly = false) {

        $AlphaChars = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
        $NumChars = array(1, 2, 3, 4, 5, 6, 7, 8);

        // shuffle the arrays:
        shuffle($AlphaChars);
        shuffle($NumChars);
		
        // combine the 2 arrays and shuffle
        $AllChars = array_merge($AlphaChars, $NumChars);


        for ($i = 0; $i < strlen($StrAddChars); $i++) {

            $AllChars[] = $StrAddChars[$i];
        }

        if (sizeof($AllChars) < $StrLen) {

            $dupCnt = ceil($StrLen / sizeof($AllChars));

            $DupChars = $AllChars;

            for ($i = 0; $i < $dupCnt - 1; $i++)
                $AllChars = array_merge($AllChars, $DupChars);
        }

        shuffle($AllChars);

        $rStr = "";

        switch ($StrLen) {

            case 0:

                return(null);

                break;

            case 1:

                $key = array_rand($AllChars, 1);

                $rStr = $AllChars[$key];

                if (rand(1, 2) == 2)
                    $rStr = strtoupper($rStr);

                break;

            default:

                $keys = array_rand($AllChars, $StrLen);

                foreach ($keys as $key) {

                    $rChr = $AllChars[$key];

                    if (rand(1, 2) == 2)
                        $rChr = strtoupper($rChr);

                    $rStr .= $rChr;
                }

                break;
        }

        if ($UCaseOnly)
            return(strtoupper($rStr));

        return($rStr);

    }	
	
    public static function CreateDateBasedSubDir($argParentPath) {

        $SubDir = $argParentPath . date("Y-m-d") . "/";

        if (!is_dir($SubDir) && !file_exists($SubDir))
            @mkdir($SubDir); // @ = no error

        if (is_dir($SubDir))
            return($SubDir);

        return(false);
    }	
	
	public static function IsServerAvailable($argHost, $argPort = null, $argTimeout = 5){

        $conn = @fsockopen($argHost, $argPort,$ErrNum,$ErrDesc, $argTimeout);

        if (is_resource($conn)){

            fclose($conn);

            return(true);
        } else {

            CRYPTEC_API::LogEntryAdd("WS-Utils - IsServerAvailable - Error[$ErrNum] - $ErrDesc");
            
            return(false);
        }        

    }
	
}