<?php

//require 'database.class.php';

require_once "ws_utils.class.php"; // webservices utilities
require_once "database.class.php"; // webservices logging db

/* 

valid request state ids:

0 - api request initiated
1 - logging database connection failed
2 - no auth-hash defined
3 - auth-hash is invalid
4 - public key is invalid
5 - webservices api access is off
6 - api client config cannot be loaded
7 - initiating hash auth
8 - invalid user, or disabled
9 - missing or invalid hash key
10 - authentification failed
11 - request out of allowed time bounds - check client/server time
12 - request authentificated
13 - request replay detected
14 - request method and arguments filter & defined
15 - method controller initiated
16 - error out before calling request method
17 - invalid request method
18 - executing request method
19 - request method execution complete, going to response 
20 - outputting api response
22 - access denied to method for user

*/

abstract class API {

    protected $ErrorCodes = [

        10  => [ '[100010] Webservices - Authentification Failed', 'No authentification hash string in incoming data stream' ],
        20  => [ '[100020] Webservices - Authentification Failed', 'Incomplete hash string name-value pairs in incoming data stream' ],
        30  => [ '[100030] Webservices - Authentification Failed', 'Supplied public key is invalid' ],
        40  => [ '[100040] Webservices - Authentification Failed', 'The supplied user account is disabled' ],
        50  => [ '[100050] Webservices - Authentification Failed', 'User key is invalid' ],
        60  => [ '[100060] Webservices - Authentification Failed', 'Authorisation nonce mismatch' ],
        65  => [ '[100065] Webservices - Authentification Failed', 'Access denied to requested resource/method' ],      
        70  => [ '[100070] Webservices - Request Error', 'Request time out of bounds' ],
        80  => [ '[100080] Webservices - Request Error', 'Compulsory user input not found' ],
        90  => [ '[100090] Webservices - Request Error', 'Web-services user configuration not loaded' ],
        100 => [ '[100100] Webservices - Request Error', 'Input XML file not detected' ],
        110 => [ '[100110] Webservices - Request Error', 'User key is invalid' ],
        120 => [ '[100120] Webservices - Request Error', 'Input file not xml' ],
        130 => [ '[100130] Webservices - Request Error', 'Input xml file could not be stored' ],
        140 => [ '[100140] Webservices - Request Error', 'Required Public Browser config cannot be loaded' ],
        150 => [ '[100150] Webservices - Request Error', 'Required Client Email config cannot be loaded' ],
        160 => [ '[100160] Webservices - Request Error', 'Resource ID argument not defined' ],
        170 => [ '[100170] Webservices - Request Error', 'User invalid or disabled' ],
        180 => [ '[100180] Webservices - Request Error', 'Required argument(s ] not found' ],
        190 => [ '[100190] Webservices - Request Error', 'Request has been identified as duplicate(replay off ]' ],
        200 => [ '[100200] Webservices - Request Error', 'Supplied resource identifier is invalid' ],
        210 => [ '[100210] Webservices - Request Error', 'Requested resource is offline' ],
        220 => [ '[100220] Webservices - Request Error', 'Internal endpoint processing error occurred, refer to debug request-log for more details' ],
        230 => [ '[100230] Webservices - Request Error', 'Request logging database connection could not be established' ],
        240 => [ '[100240] Webservices - Request Error', 'Request resource/method currently unavailable' ],
    ];

    protected $Authenticated = false;

    protected $AllowReplay = true; // whether or not requests with the same nonce signature will be processed (duplicate requests)

    protected $AuthGetKey = 'h';

    protected $AuthHashKeys = array('p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7');

    protected $AuthHashString;

    protected $PublicKey_GetPostKey = 'p1';

    protected $UserKey_GetPostKey = 'p2';

    protected $PublicKey; // specify client instance key

    protected $UserKey; // specify client user - id

    protected $ResourceID;

    protected $APIUser;

    protected $PrivateKey; // specify client user - private key, stored against profile

    protected $ClientConfigs;

    protected $RequestBounds = 600; // 5 minutes before and after

    protected $DBConf;

    protected $DBConn;

    protected $GZOutput = false;

    protected $GZOutputStream = false;

    protected $Base64Encode = false;

    protected $UpdatedSinceTS; // used to narrow down records returned, to those updated after this timestamp

    protected $ResponseType = 0; // for json or 1 for xml

    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $Method = '';

    /**
     * Property: endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $Endpoint = '';

    /**
     * Property: Verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $Verb = '';

    /**
     * Property: Args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>
     */
    protected $Args = Array();

    /**
     * Property: File
     * Stores the input of the PUT request
     */
    protected $File;

    /**
     * Property: Request
     * User data from GET or POST incoming streams
     */
    protected $Request=Array();

    /**
     * Property: Error
     * Boolean - whether or not an error has occured
     */
    protected $Error;

    /**
     * Property: LastError
     * Store the last error that occurred
     */
    protected $LastError;

    /**
     * Property: ServerTime
     * Store the date/time of the API request in human readable format
     */

    protected $ServerTime;

    /**
     * Property: ServerTimeUTS
     * Store unix timestap of the API request call
     */

    protected $ServerTimeUTS;

    /**
     * Property: APIVersion
     * Store the api version against which the request is being made:
     */

    protected $APIVersion;

    /**
     * Property: QuerySQL
     * Stores the main query used to retrieve records for GET's
     */

    protected $QuerySQL;

    /**
     * Property: RecordCount
     * Stores the record count returned by main query used to retrieve records for GET's
     */

    protected $RecordCount;

    /**
     * Property: NoRecords
     * Whether or not records are to be returned with api output, xml or json
     */

    protected $NoRecords = false;

    /**
     * Property: LogID
     * Stores the record count returned by main query used to retrieve records for GET's
     */
	 
    static protected $LogFileEntries = array();

    static protected $LogFilePath;

    static protected $LogFileName;

    static protected $LogFile_AdditionalPaths = array();

    protected $LogID;

    protected $LogTableName = "api_requests_log";

    protected $RandKey1;

    protected $RandKey2;

    protected $RandKey3;

    protected $FieldSet;

    protected $GetLatestUpdate = false;

    protected $UserSession; // reference to the Specify UserSession, if needed

    protected $PublicBrowserConfigs; // reference to the PublicBrowser configs array

    static private $InternalErrors = array(); // used by the error handler to store errors, warning and notices

    /**
     * Property: DebugLevel
     * Defines what debug/log data is stored and when admin emails are sent: 
     * 0: db logging on, file logging off, ws api admin email only on critical errors
     * 1: db logging on, file logging on, ws api admin email only on critical errors 
     * 2: db logging on, file logging on, ws api admin email sent for every request processed 
     */

    protected $DebugLevel = 2; //

    /**
     * Property: Response
     * The response from the server back to client, for now as
     */

    protected $Response = array(
        'server_time'=>false,
        'server_time_uts'=>false,
        'authenticated'=>false,
        'user_key'=>false,
        'user'=>false,
        'app_dist'=>false,
        'client_name'=>false,
        'client_api_state'=>false,
        'input_file'=>false,
        'output_file'=>false,
        'method'=>false,
        'endpoint'=>false,
        'public_key'=>false,
        'error'=>false,
        'error_msg'=>false,
        'response'=> array(), // must always be array
        'request_state_id'=>false,
        'request_state_text'=>false,        
        'request_initd'=>false,
        'request_trmd'=>false,
        'request_drtn'=>false,
        'request_norcds'=>false,
        'request_updated_since'=>false,
        'request_updated_since_uts'=>false,
        'internal_errors'=> array()
    );

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */

    public function __construct($argRequest, $argClientConfigs) {

        $this->SetResponseHeader("request_state_id", 0); // request initiated
        
        // log the api call start:
        $this->SetResponseHeader("request_initd", microtime(true) );

        self::$LogFilePath = $_SERVER['DOCUMENT_ROOT'] . '/logs/';

        self::$LogFileName = "ws-req-log." . date("Ymdhis") . ".log";

        // set error handler method:

		set_error_handler(array($this, "ErrorHandler"));

		set_exception_handler(array($this, "ErrorHandler"));

        // make sure we can access the Webservices Logging Database: 

        $Conn = APIDatabase::instance(); // the error handler will catch any issues

        if(!$Conn){

            $this->SetResponseHeader("request_state_id", 1); // log db conn failed

            $this->ErrorOccured();
            $this->SetLastErrorMessage($this->ErrorCodes[230]);

            return $this->Response(); // process error data and respond to client            

        }

        $this->ServerTimeUTS = time();

        // Get reference to the API version:
        $tmp = explode('/', rtrim($_SERVER["PHP_SELF"], '/'));

        $this->APIVersion = str_replace("v","",$tmp[0]);

        $this->ServerTime = date("Y-m-d H:i:s",$this->ServerTimeUTS);

        // human readable server time:
        $this->SetResponseHeader("server_time", $this->ServerTime);

        // unix timestamp server time:
        $this->SetResponseHeader("server_time_uts", $this->ServerTimeUTS);

        // Do authentification:

        // 1. check that hash checksum is sent:
        if(!isset($_GET[$this->AuthGetKey]) || strlen(trim($_GET[$this->AuthGetKey]))==0 ){ // no auth string to validate:

            $this->SetResponseHeader("request_state_id", 2); // no auth-hash defined

            $this->ErrorOccured();
            $this->SetLastErrorMessage($this->ErrorCodes[10]);

            return $this->Response(); // process error data and respond to client
        }

        $this->AuthHashString = trim($_GET[$this->AuthGetKey]);

        //2. check the hash string fields are sent:
        foreach($this->AuthHashKeys as $KeyName){

            if(!isset($_GET[$KeyName])){

                $this->SetResponseHeader("request_state_id", 3); // auth hash is invalid

                $this->ErrorOccured();
                $this->SetLastErrorMessage($this->ErrorCodes[20]);

                return $this->Response(); // process error data and respond to client
            }
        }

        $this->PublicKey = $_GET[$this->PublicKey_GetPostKey]; // specify client instance identifier
        $this->UserKey = $_GET[$this->UserKey_GetPostKey]; // specify user identifier

        $this->SetResponseHeader("public_key", $this->PublicKey);
        $this->SetResponseHeader("user_key", $this->UserKey);

        // verify the public/user keys against the global configs array:
        $this->ClientConfigs = null;

        // check if user key has entry in configs array:

        if(!isset($argClientConfigs[$this->UserKey])){

            $this->SetResponseHeader("request_state_id", 4); // public key is invalid

            // invalid public key, no match in global configs:
            $this->ErrorOccured();
            $this->SetLastErrorMessage($this->ErrorCodes[50]);

            return $this->Response(); // process error data and respond to client
        }

        // User key exists in client configs:

        foreach($argClientConfigs as $ClientConfigs){

            if(isset($ClientConfigs['AUTH_PUBLIC_KEY'])){

                //echo $ClientConfigs['AUTH_PUBLIC_KEY']."\n";

                if($ClientConfigs['AUTH_PUBLIC_KEY'] == $this->PublicKey){

                    $this->ClientConfigs = $ClientConfigs;

                    break;
                }
            }
        }

        // if the public key is invalid, error out:
        if(is_null($this->ClientConfigs)){

            $this->SetResponseHeader("request_state_id", 4); // public key is invalid

            // invalid public key, no match in global configs:
            $this->ErrorOccured();
            $this->SetLastErrorMessage($this->ErrorCodes[30]);

            return $this->Response(); // process error data and respond to client
        }

        // if the public key is valid, but api access is off for client, error out:
        if(!isset($this->ClientConfigs['ACCESS_ENABLED']) || !$this->ClientConfigs['ACCESS_ENABLED']){

            $this->SetResponseHeader("request_state_id", 5); // api access denied

            $this->SetResponseHeader("client_api_state", 0);

            // api access is not defined or set disabled:
            $this->ErrorOccured();
            $this->SetLastErrorMessage($this->ErrorCodes[40]);

            return $this->Response(); // process error data and respond to client
        }

        // api access is on for specify client:
        $this->SetResponseHeader("client_api_state", 1);    

        // Set the header for client name:
        $this->SetResponseHeader('client_name', INSTANCE_NAME); // from client conf

        // now we can gain access to the specify client db, so check nonce and private key:
        $this->_AuthenticateHash(); // will error out internally if needed

        $this->Authenticated = true; // if the script got this far, it's authenticated

        // set the request debug level:

        $this->DebugLevel = $this->ClientConfigs["WS_DEBUG_LEVEL"];

        // Access API DB and check that this is a new request, not a replay request :

        if($this->AllowReplay === false){ // check if this request has already been processed:

            require_once 'classes/database.class.php';

            $APIDB = APIDatabase::instance();

            $ReplaySQL = "SELECT count(*) as cnt FROM ".$this->LogTableName." WHERE public_key='{$this->PublicKey}' AND user_key={$this->UserKey} AND rkey_p5='{$this->RandKey1}' AND rkey_p6='{$this->RandKey2}' AND rkey_p7='{$this->RandKey3}' ";

            $DataSet = $APIDB->query($ReplaySQL);

            $DataRow = $DataSet->fetch_assoc();

            if((int) $DataRow['cnt'] > 0){

                $this->SetResponseHeader("request_state_id", 13); // request replay detected

                // api access is not defined or set disabled:
                $this->ErrorOccured();
                $this->SetLastErrorMessage($this->ErrorCodes[190]);

                return $this->Response(); // process error data and respond to client
            }

        }

        $this->Args = explode('/', rtrim($argRequest, '/'));

        $this->Endpoint = array_shift($this->Args);

        if (array_key_exists(0, $this->Args) && !is_numeric($this->Args[0])) {

            $this->Verb = array_shift($this->Args);
        }

        // process the endpoint name if needed:

        $this->Endpoint = str_replace("-", "_", $this->Endpoint);

        $this->Method = $_SERVER['REQUEST_METHOD'];

        if ($this->Method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {

            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {

                $this->Method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {

                $this->Method = 'PUT';
            } else {

                throw new Exception("Unexpected Header");
            }

        }

        $this->SetResponseHeader("endpoint", $this->Endpoint);

        $this->SetResponseHeader("method", $this->Method);

        // check if this user has been given rights to the requested endpoint & method:

        $RequestMethod = strtolower($this->Endpoint . "." . $this->Method);

        if( !in_array($RequestMethod, $this->ClientConfigs['AUTH_ALLOW_METHODS']) ){

            $this->SetResponseHeader("request_state_id", 22); // request replay detected

            // api access is not defined or set disabled:
            $this->ErrorOccured();
            $this->SetLastErrorMessage($this->ErrorCodes[65]);

            return $this->Response(); // process error data and respond to client
        }

        // detect if there's a requested response type set:
        if($this->Verb !== "xml"){

            $this->ResponseType = 0; // 0 for json
        }else{

            $this->ResponseType = 1; // 1 for xml
        }

        // detect if there's a request to compress and stream the data:
        if(isset($_GET['gz'])){

            $this->GZOutput = true;
        }

        if(isset($_GET['gzs'])){

            $this->GZOutput = true;
            $this->GZOutputStream = true;
        }

        // detect if there's a request to limit the response data by a timestamp:
        if(isset($_GET['fromts'])){

            $this->UpdatedSinceTS = (int) $_GET['fromts'];
        }

        // detect if there's a request not to return records data with results:
        if(isset($_GET['norcds'])){

            $this->NoRecords = true;
        }

        if(isset($_GET['glu'])){

            $this->GetLatestUpdate = true;
        }

        if(isset($_GET['b64'])){

            $this->Base64Encode = true;
        }

        switch ( $this->Method ) {

            case 'DELETE':
            case 'POST':

                $this->Request = $this->_CleanInputs($_POST);
                break;

            case 'GET':

                $this->Request = $this->_CleanInputs($_GET);
                break;

            case 'PUT':

                $this->Request = $this->_CleanInputs($_GET);
                $this->File = file_get_contents("php://input");
                break;

            default:

                $this->Response('Invalid Method', 405);
                break;

        }

        $this->SetResponseHeader("request_state_id", 14); // request method and arguments filter & defined

    }

    protected function _ProcessGetPostInputs($argInputsData, $argCheckGET, $argCheckPOST, $argCallerMethod){

        $UserInputs = array();

        foreach($argInputsData as $IptName=>$IptData){

            if($argCheckPOST && isset($_POST[$IptName])){ // eval against user post data:

                $UserInputs[$IptName] = $this->_CleanInputs($_POST[$IptName]);
            }else if($argCheckGET && isset($_GET[$IptName])){ // eval against user get data:

                $UserInputs[$IptName] = $this->_CleanInputs($_GET[$IptName]);
            }else{ // user input not found:

                if($IptData[0] === true){ // input is compulsory, raise error:

                    $this->ErrorOccured();

                    $this->SetLastErrorMessage($this->ErrorCodes[80],$argCallerMethod." -> ".$IptName);

                    return $this->Response(); // process error data and respond to client
                }else{ // just set the default value:

                    $UserInputs[$IptName] = $IptData[1];

                }
            }

        }

        return($UserInputs);
    }

    public function ProcessRequest() {

        $this->SetResponseHeader("request_state_id", 15); // method controller initiated

        $this->SetResponseHeader("request_norcds", $this->NoRecords);

        if($this->UpdatedSinceTS){

            $this->SetResponseHeader("request_updated_since_uts", $this->UpdatedSinceTS);
            $this->SetResponseHeader("request_updated_since", date("Y-m-d H:i:s", $this->UpdatedSinceTS));
        }

        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");

		if(isset($_GET["flog"])){
			
			$this->DebugResponseInclude();

			return $this->Response(null, 200);
		}

        /* note that the method hasn't run yet, so this error would be during object inst and request auth */

        if($this->Error==true){ //pre method exec errors occured

            $this->SetResponseHeader("request_state_id", 16); // error out before calling request method

            // if an error occured, always include the debug log in output:
            $this->DebugResponseInclude();

            $this->Response(null, 200);
        }

        if ((int) method_exists($this,$this->Endpoint) > 0) { // resource method exists:

            $this->SetResponseHeader("request_state_id", 18); // executing request method

            // request can be processed via internal resource method:

            // note that if errors occur within method, the method will error out and continue routine
            $this->{$this->Endpoint}($this->Args);

            $this->SetResponseHeader("request_state_id", 19); // request method execution complete, going to response...

            //check if method exited on error, if so add debug:
            if($this->Error){ 

                $this->DebugResponseInclude();
            }

            // if no errors, continue with response...
            $this->Response(null,200);
        }else{

            $this->SetResponseHeader("request_state_id", 17); // invalid request method

            // method does not exist:
            $this->Response('Invalid Resource Request', 405);
        }

    }

    public function GetRequestRouting() {

        $Routing = array(
            'Method' => $this->Method,
            'Endpoint' => $this->Endpoint,
            'Verb' => $this->Verb,
            'Request' => serialize($this->Request),
            'Args'=> serialize($this->Args)
        );

        return($Routing);
    }

    public function Response($argData = null, $argStatus = 200) {

        $this->SetResponseHeader("request_state_id", 20); // outputting api response

        $this->SetResponseHeader("request_trmd", microtime(true));

        $this->SetResponseHeader("request_drtn", round($this->Response["request_trmd"] - $this->Response["request_initd"],3) . " secs");
        
        $this->SetResponseHeader("internal_errors", self::$InternalErrors);

        $ResponseData = "";

        if(is_string($argData) && $argStatus!==200){
            
            $ResponseData = $argData;
        }

        header("HTTP/1.1 {$argStatus} {$this->_RequestStatus($argStatus)} [{$ResponseData}]");
		
		if(isset($_GET["elog"])){
			
			$this->DebugResponseInclude();
		}

        if(!$this->GZOutput){ // if gz compression is requested, then the headers are set later, after the temp file is generated

            switch($this->ResponseType){
                default:
                case 0: // json:

                    header("Content-Type: application/json; charset=utf-8");
                    break;
                case 1: // xml:

                    header("Content-Type:text/xml; charset=utf-8");
                    break;
            }

        }

        switch($this->ResponseType){

            default:

            case 0:

                $ContentType = "json";

                $Content = json_encode($this->Response);

                break;

            case 1:

                $ContentType = "xml";

                require CLASS_DIR . 'class.ftgXML.php';

                $xml = ftgXML::createXML('root', $this->Response);

                $Content = $xml->saveXML();

                break;
        }
		

        // if no compress is request, return raw output:
        if(!$this->GZOutput){

            // log this request:
            $this->LogRequest(1);

            if($this->Base64Encode){

                $Content = base64_encode($Content);
            }

            echo $Content;

            exit;

        }else{

            // else gz compress the data, store to tmp file and stream it:

            $FileName = 'api.out.'.microtime(true).'.'.$this->UserKey.'.'.$ContentType.".gz";

            $this->SetResponseEntry("output_file", $FileName);

            $FilePath = WS_STREAM_OUT_PATH . $FileName;

            $GZ = gzopen ($FilePath, 'w9'); // max compress

            // Compress the file
            gzwrite ($GZ, $Content);

            // Close the gz file and we are done
            gzclose($GZ);

            if(!$this->GZOutputStream){ // send as binary file data:

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$FileName);
                header('Content-Encoding: gzip');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($FilePath));

                ob_clean();

                flush();

                readfile($FilePath);

                // log this request:
                $this->LogRequest(2);

                exit;

            }else{ // stream gz content, not binary file...

                $GZContent = file_get_contents($FilePath);

                //if($this->Base64Encode){
                //    $GZContent = "b64=".base64_encode($GZContent);
                //}

				$ByteLen = strlen($GZContent);

				//header('Content-Type: application/octet-stream');
                header('Content-Type:text; charset=utf-8');
				header('Content-Length: ' . $ByteLen);
				header('Content-ByteLen: ' . $ByteLen);
                //header('Content-Encoding: gzip');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');

                // log this request:
                $this->LogRequest(3);

                echo $GZContent;

                exit;
            }
        }
    }

    private function _CleanInputs($argData) {

        $CleanInput = Array();
        if (is_array($argData)) {
            foreach ($argData as $k => $v) {
                $CleanInput[$k] = $this->_CleanInputs($v);
            }
        } else {
            $CleanInput = trim(strip_tags($argData));
        }
        return $CleanInput;
    }

    private function _RequestStatus($argCode) {

        $Status = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported');

        return ($Status[$argCode]) ? $Status[$argCode] : $Status[500];
    }

    private function _AuthenticateHash() {

        $this->SetResponseHeader("request_state_id", 7); // initiating hash auth

        //$this->SetResponseHeader('user', $this->APIUser->get_username());

        $this->PrivateKey = $this->ClientConfigs['AUTH_PRIVATE_KEY'];

        // check for compulsory args:
        if(!isset($_GET['p1']) || !isset($_GET['p2']) || !isset($_GET['p3']) || !isset($_GET['p4']) || !isset($_GET['p5']) || !isset($_GET['p6']) || !isset($_GET['p7'])){

            $this->SetResponseHeader("request_state_id", 9); // missing or invalid hash key

            $this->ErrorOccured();
            $this->SetLastErrorMessage($this->ErrorCodes[180]);
            return $this->Response(); // process error data and respond to client
        }

        // make sure arg p3 is pure integer:
        $GET["p3"] = (int) $_GET["p3"];

        $this->RandKey1 = $_GET["p5"];
        $this->RandKey2 = $_GET["p6"];
        $this->RandKey3 = $_GET["p7"];

        // rebuild auth hash string from incoming get keys data:
        $AuthArray = array();

        foreach($this->AuthHashKeys as $Key){

            $AuthArray[$Key]=$_GET[$Key];
        }

        // arg hp1 will always be handled as integer, it must be a valid unix timestamp:
        $RequestUTS = $AuthArray["p3"];

        //rebuild the auth hash with private key, and incoming client data:
        $QueryString = http_build_query($AuthArray);

        $IntHash = base64_encode(mhash(MHASH_SHA256, $QueryString, $this->PrivateKey));

        $ExtHash = $this->AuthHashString;

        //check if incoming client auth hash matches regenerated auth hash:
        if($this->AuthHashString !== $IntHash){ // error... hashes don't match!

            $this->SetResponseHeader("request_state_id", 10); // authentification failed

            $this->ErrorOccured();
            $this->SetLastErrorMessage($this->ErrorCodes[60]);

            return $this->Response(); // process error data and respond to client
        }

        // check if the request is within time range:
        $T = time();

        if($RequestUTS < ($T - $this->RequestBounds) || $RequestUTS > ($T + $this->RequestBounds)){

            $this->SetResponseHeader("request_state_id", 11); // request out of allowed time bounds - check client/server time

            // error... request is out of time range limits
            $this->ErrorOccured();
            $this->SetLastErrorMessage($this->ErrorCodes[70]);

            return $this->Response(); // process error data and respond to client
        }

        // hash auth is successful:
        $this->SetResponseHeader('authenticated',true);
        $this->SetResponseHeader("request_state_id", 12); // request authentificated

        return true; // all checks passed
    }

    protected function ErrorOccured(){

        $this->Error = $this->Response['error'] = true;
    }

    protected function SetLastErrorMessage($argErrorDets){

        $this->LastError = $this->Response['error_msg'] = $argErrorDets[0]." [{$argErrorDets[1]}] ";
    }

    protected function SetResponseText($var){

        $this->ResponseText = $this->Response['response_txt'] = $var;
    }

    protected function SetResponseEntry($key,$value){

        $this->Response['response'][$key] = $value;
    }

    protected function SetResponseHeader($argKey,$argValue){

        if(!isset($this->Response[$argKey])){

            throw new Exception(__METHOD__ . " - Fatal Error: response header does not exist [$argKey]");
        }

        $this->Response[$argKey] = $argValue;
    }

    public function GetError(){

        return($this->LastError);
    }

    public function GetLatestUpdate(){

        return($this->GetLatestUpdate);
    }

    private function LogRequest($num){

        //echo "Logging now... $num<br/>";

        $LogFileContents = json_encode($this->Response);

        // we always log to web-services database:

        $Fields = array();

        $Fields['api_request_time'] = $_SERVER["REQUEST_TIME"];
        $Fields['api_version'] = $this->APIVersion;
        $Fields['api_resource'] = $this->Endpoint;
        $Fields['api_resource_id'] = $this->ResourceID;
        $Fields['api_args'] = serialize($this->Args);
        $Fields['api_response_type'] = $this->ResponseType;
        $Fields['api_gzoutput'] = $this->GZOutput;
        $Fields['api_call_url'] = $_SERVER["REQUEST_URI"];
        $Fields['api_remote_ip'] = $_SERVER["REMOTE_ADDR"];

        // may not be available:
        if(isset($_SERVER["HTTP_USER_AGENT"])){

            $Fields['api_remote_ua'] = $_SERVER["HTTP_USER_AGENT"];
        }

        // may not be available in invalid calls:
        if(isset($_GET["h"])){

            $Fields['api_nonce_hash'] = $_GET["h"];
        }

        $Fields['query_sql'] = $this->QuerySQL;
        $Fields['server_time'] = $this->ServerTime;
        $Fields['authenticated'] = (int) $this->Authenticated; // 1=true, 0=false
        $Fields['user_key'] = (int) $this->UserKey;

        // may not be available in invalid calls:
        if(!is_null($this->APIUser)){

            $Fields['user_name'] = $this->APIUser->get_username();
        }

        // may not be available in invalid calls:
        if(defined("INSTANCE")){
            $Fields['client_name'] = INSTANCE_NAME; // const from client main conf.
        }

        $Fields['client_api_state'] = $this->Response["client_api_state"];
        $Fields['input_file'] = $this->Response["input_file"];
        $Fields['output_file'] = $this->Response["output_file"];
        $Fields['api_method'] = $this->Method;
        $Fields['public_key'] = $this->PublicKey;
        $Fields['error'] = (int) $this->Error;
        $Fields['error_msg'] = $this->LastError;
        $Fields['record_count'] = $this->RecordCount;
        $Fields['fieldset'] = $this->FieldSet;
        $Fields['request_state'] = $this->Response["request_state_id"];
        $Fields['request_initd'] = $this->Response["request_initd"];
        $Fields['request_trmd'] = $this->Response["request_trmd"];
        $Fields['request_drtn'] = $this->Response["request_drtn"];

        // may not be available in invalid calls:
        if(isset($_GET["p5"])){

            $Fields['rkey_p5'] = $_GET["p5"];
        }
        if(isset($_GET["p6"])){

            $Fields['rkey_p6'] = $_GET["p6"];
        }
        if(isset($_GET["p7"])){

            $Fields['rkey_p7'] = $_GET["p6"];
        }

        $Fields['creation_datetime'] = 'now()';
        $Fields['creation_user_id'] = 1;
        $Fields['update_user_id'] = 1;

        $Fields['log_file_name'] = self::$LogFileName;
        $Fields['log_file_path'] = self::$LogFilePath;

        //$Fields['log_file_contents'] = $LogFileContents;

        $Fields['status'] = 1;

        require_once 'classes/database.class.php';

        APIDatabase::insert("ws_requests_log", $Fields) ;

        $this->LogID = APIDatabase::InsertID();

        // run any request debug/logging actions

        switch($this->DebugLevel){

            case 0: // on error - log file and admin log email

                if($this->Error){ // only log errors

                    $this->LogFileWrite();

                    $this->SendAdminEmail();
                }

                break;

            case 1: // file log on, db log on, admin email only on critical errors:

                $this->LogFileWrite(); // always write to log

                if($this->Error){ // email on error

                    $this->SendAdminEmail();
                }                

                break;

            case 2: // file log on, db log on, all requests send admin email on

                $this->LogFileWrite(); // always write log

                $this->SendAdminEmail();  // always send email

                break;
        }

    }
	
	public static function LogEntryAdd($argString, $argStringRepeat=1){
		
        switch($argString){

            case 1:

                $LogText = str_repeat(PHP_EOL, (int) $argStringRepeat);

                break;

            case 2:

                $LogText = "!-----------------------------------------";

                //$LogText .= str_repeat(PHP_EOL, (int) $argStringRepeat);

                break;

            default:

                $LogText = trim($argString);
        }

		self::$LogFileEntries[] = $LogText;
	}
	
	public function LogFileWrite(){
		
        $LogFileContents = json_encode($this->Response);

        file_put_contents(self::$LogFilePath . self::$LogFileName, $LogFileContents);

        foreach(self::$LogFile_AdditionalPaths as $LogFileParams){

            file_put_contents($LogFileParams[1]. $LogFileParams[0], $LogFileContents);
        }

	}

    public function SendAdminEmail(){

        $LogFileName = self::$LogFileName;

        // $to = "bretton@cryptec.co.za, kobus@ardea.co.za, elsabe@ardea.co.za";

        $to = "bretton@cryptec.co.za";
		
		$InstanceName = " - ";
		
		if(defined('INSTANCE_NAME')){
		
			$InstanceName = INSTANCE_NAME;
		}
        
        $subject = $InstanceName .  " - Webservices Request Processed";

        if($this->Error){

            $subject = "Webservices Request Processed - Errors Occured";
        }

        $from = "support@findthegap.co.za";

        $MessageLog = implode("<br/>", self::$LogFileEntries);

        $MessageLog = str_replace(PHP_EOL, "<br/>", $MessageLog);

        $myAttachment = chunk_split(base64_encode(file_get_contents( self::$LogFilePath . self::$LogFileName)));
 
        $headers = "From: \"$InstanceName Webservices\" <support@findthegap.co.za>\r\n" .
            "Repy-To: support@findthegap.co.za\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: multipart/mixed; boundary= \"1a2a3a\"\r\n";

        $body = "--1a2a3a\r\n" .
            "Content-Type: multipart/alternative; boundary= \"4a5a6a\"\r\n" .
            "--4a5a6a\r\n" .
            "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n" .
            "Content-Transfer-Encoding: 7bit\r\n" .
            "The attachment contains the log-files .\r\n" .
            "--4a5a6a\r\n" .
            "Content-Type: text/html; charset=\"iso-8859-1\"\r\n" .
            "<html>
                <head>
                    <title>Cryptec Webservice - Request Log</title>
                </head>
                <body>
                    Cryptec Webservice Request Log:
                    <br/><br/>
                    {$MessageLog}
                </body>
            </html>\r\n" .
            "--1a2a3a\r\n" .
            "Content-Type: application/octet-stream; name=\"{$LogFileName}\"\r\n" .
            "Content-Transfer-Encoding: base64\r\n" .
            "Content-Disposition: attachment\r\n" .
            $myAttachment. "\r\n" .
            "--1a2a3a--";
 
            $success = mail($to, $subject, $body, $headers);
   
            // need to update db to reflect log email sent...

            //if ($success) {

               // echo "Mail to " . $to . " failed.";

                //}else {

               // echo "Success : Mail was send to " . $to ;
            //}


    }
	
	public static function SetLogFileName($argVar){
		
		self::$LogFileName = $argVar;
	}
	
	public static function SetLogFilePath($argVar){
		
		self::$LogFilePath = $argVar;
	}

    public static function AddLogFilePath($argLogFileName, $argLogFilePath){

        // can only be set if path is writeable:

        if(!is_writable($argLogFilePath)){

            return(false);
        }

        // if no trailing forward slash in path, add it:
        if(substr($argLogFilePath, -1) !== "/"){

            $argLogFilePath = $argLogFilePath . "/";
        } 

        self::$LogFile_AdditionalPaths[] = array($argLogFileName, $argLogFilePath);

        return(true);
    }
	
    static public function GetUserVar($argType, $argVarName, $argDefaultValue = '', $argFilter = FILTER_DEFAULT){

        $var = filter_input($argType, $argVarName, $argFilter);

        if(is_null($var)) $var = $argDefaultValue;

        return($var);
    }

    private function _Inst_DBConn(){

        //if(is_null($this->DBConn))
        //    $this->DBConn = new mysqli($this->DBConf['host'], $this->DBConf['username'], $this->DBConf['password'], $this->DBConf['schema']);

        //return($this->DBConn);

    }

    protected function SetDebugLevel($argInt){

        $this->DebugLevel = (int) $argInt;
    }
	
	private function DebugResponseInclude(){

            $this->SetResponseEntry("request_log", self::$LogFileEntries);

			$this->SetResponseEntry("debug_headers", getallheaders());
		
			$this->SetResponseEntry("debug_get",$_GET);
		
			$this->SetResponseEntry("debug_post",$_POST);
		
			$this->SetResponseEntry("debug_request",$_REQUEST);
			
			$this->SetResponseEntry("debug_files",$_FILES);
		
	}
	
	public function ErrorHandler($errno, $errstr, $errfile, $errline){

        if(isset($_GET["debug"])){ // for dev...

            echo "[" . $errno . "] - " . $errstr . "/n/n"; 

        }
		
		if (!(error_reporting() & $errno)) {
			
			// This error code is not included in error_reporting
			return;
		}
		
		if($errno==2048){ // ignoring strict
			
			return; 
		}
		
		switch ($errno) {
			
			case E_USER_ERROR:

                self::$InternalErrors[] = "WS-INTERNAL-USER-ERROR: [$errno] $errstr<br />\n" .
				        "  Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />" .
				        "Processing aborted...";

				break;

			case E_USER_WARNING:

				self::$InternalErrors[] = "WARNING: [$errno] $errstr at line $errline of file [$errfile] <br />\n";
				break;

			case E_USER_NOTICE:
				self::$InternalErrors[] = "NOTICE: [$errno] $errstr at line $errline of file [$errfile]\n";
				break;

			default:
				self::$InternalErrors[] = "WS-INTERNAL: [$errno] $errstr at line $errline of file [$errfile]\n";
				break;
				
		}

		/* Don't execute PHP internal error handler */
		return true;
		
	}
	
}