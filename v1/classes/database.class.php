<?php

require_once "_api.class.php";

/**
 * Description of class
 *
 * @author BrettonE
 */
class APIDatabase extends mysqli {

    public static $conn;

    function __construct() {

        self::instance();

    }

    public static function instance() {

        if(is_null(self::$conn)){

            self::$conn = new mysqli(API_DB_HOST, API_DB_USER, API_DB_PWD, API_DB_SCHEMA);
            // handle any connection errors:
            if( self::$conn->connect_errno > 0){

                self::$conn = false;

                return(false);
            }


        }

        return(self::$conn);
    }

    public static function insert($argDBTableName, $argDBFields, $argDBInsertType = 1) {

        $DBConn = APIDatabase::instance();

        if(!$DBConn){

            return(false);
        }

        $strInsertStmt = "";

        // SET: FIELD = VALUE:
        switch ($argDBInsertType) {

            case 1:

                foreach ($argDBFields as $FldName => $FldValue) {

                    if(is_string($FldValue))
                        $FldValue = trim($FldValue); // cannot trim all values, only strings, else all values are converted to strings... even booleans

                    if ($strInsertStmt !== ""){

                        $strInsertStmt .= ", ";
                    }

                    $strInsertStmt .= "`" . $FldName . "`=";   // add the field name to the string

                    if(strtolower($FldValue) == 'uuid()'){

                        $strInsertStmt .= 'uuid()';
                    } else if (strtolower($FldValue) == 'now()') {   // request for DB timestamp

                        $strInsertStmt .= 'now()';
                    } else if (strtolower($FldValue) == 'unix_timestamp()') {   // request for unix timestamp

                        $strInsertStmt .= 'unix_timestamp()';
                    } else if(is_bool($FldValue)){ // process boolean...

                        $strInsertStmt .= (($FldValue === true) ? 1 : 0);
                    } else {

                        $strInsertStmt .= '"' . self::$conn->escape_string($FldValue) . '"';
                    }
                }

                $stmt = "INSERT INTO `$argDBTableName` SET " . $strInsertStmt;

                break;

            case 2:

                //echo "CASE 2";

                // FIELDS, VALUES
                $FieldsValues = array();
                $FieldsKeys = array();

                foreach ($argDBFields as $FldKey => $FldValue) {

                    if(is_string($FldValue))
                        $FldValue = trim($FldValue); // cannot trim all values, only strings, else all values are converted to strings... even booleans

                    $FieldsKeys[] = "`" . $FldKey . "`";   // add the field name to the string

                    if(strtolower($FldValue) == 'uuid()'){

                        $FieldsValues[] = 'uuid()';
                    } else if (strtolower($FldValue) == 'now()') {   // request for current datetime timestamp
                        $FieldsValues[] = 'now()';

                    } else if (strtolower($FldValue) == 'unix_timestamp()') {   // request for unix timestamp
                        $FieldsValues[] = 'unix_timestamp()';

                    } else if(is_bool($FldValue)){ // boolean false

                        $FieldsValues[] = (($FldValue===false) ? 0 : 1);
                    } else {

                        $FieldsValues[] = '"' . $DBConn->RealEscapeString($FldValue) . '"';
                    }
                }

                $strStmtFields = implode(', ', $FieldsKeys);
                $strStmtValues = implode(', ', $FieldsValues);

                $stmt = "INSERT INTO `$argDBTableName` ( $strStmtFields) VALUES ( $strStmtValues )";

                break;
        }

        //Application::debug_data_store($argDBTableName . "_InsertEntity", $stmt);

        $DBResult = $DBConn->query($stmt);

        return(APIDatabase::InsertID());
    }

    public function query($SQL) {

        self::instance();

        if (!$DataSet = self::$conn->query($SQL)) {

            $this->ProcessQueryError($SQL);
        }

        return($DataSet);
    }

    public static function ProcessQueryError($SQL) {

        self::instance();

        $ClientErrorMsg = "<h2>Fatal Database Error Occured!</h2>";

        $ClientErrorMsg .= "-> Error Message: " . self::$conn->error . "<br/>";
        $ClientErrorMsg .= "-> Error Code: " . self::$conn->errno . "<br/>";
        $ClientErrorMsg .= "-> SQL STMT: " . $SQL . "<br/>";

        $ErrorHTML = "<div class='normal_m1 DBErrorMsg'>$ClientErrorMsg</div>";

        die($ErrorHTML);
    }

    public static function AffectedRows() {

        self::instance();

        return(self::$conn->affected_rows);
    }

    public static function InsertID() {

        if(!self::$conn) return false;


        return(self::$conn->insert_id);
    }

    public static function RealEscapeString($value) {

        //$conn = self::instance();

        //return($conn->escape_string($value));
    }

    public static function TransactionAutoCommit($bool) {

        self::instance();

        return(self::$conn->autocommit($bool));
    }

    public function TransactionCommitNow() {

        self::instance();

        self::$conn->commit($flags = NULL, $name = NULL);
    }

    public function TransactionRollback() {

        self::instance();

        self::$conn->rollback();
    }

    public function GetRow($sql) {

        self::instance();

        $DataSet = self::$conn->query($sql); // validates the result

        $AssocFields = $DataSet->fetch_assoc();

        return($AssocFields);
    }

    public function GetOne($sql) {

        self::instance();

        $DataSet = self::$conn->query($sql); // validates the result

        $DataFields = $DataSet->fetch_row();

        $Value = $DataFields[0];

        return($Value); // only return the first row - first column - value
    }

    public function GetDBTableInfo($argDBTableName) {

        self::instance();

        $DataSet = self::$conn->query('DESCRIBE `' . $argDBTableName . '`');

        return($DataSet);
    }

    function __destruct() {

        self::instance();

        self::$conn->close();
    }

}