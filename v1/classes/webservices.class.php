<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cryptec Standalone Webservices-API
 *
 * @author BrettonE
 */
require_once '_api.class.php'; // parent class

class SACAP_API extends API {

    public function __construct($argRequest, $argOrigin, $argClientConfigs) {

        parent::__construct($argRequest, $argClientConfigs);
    }

    /**
     * Example of an Endpoint
     */
    protected function api_test() {

        if ($this->Method == 'GET') {

            require 'methods/api.test.get.php';
        } else { // else post:

            require 'methods/api.test.post.php';
        }
    }
    
    protected function morne_sample_method(){
		
        if ($this->Method == 'GET') {

            require 'methods/morne.get.php';
        } else { //else post:

            require 'methods/morne.post.php';
        }
	}
    
    
    
    
    
    /*

    protected function products($argFilters) {

        // only caters for get(default):

        switch ($this->Method) {

            case "GET":
            default:  // get response

                if (isset($this->Args[0]) && (int) $this->Args[0] > 0) {

                    $this->ResourceID = (int) $this->Args[0];
                }

                require 'methods/products.get.php';

                break;
        }
    }

    protected function product($argFilters) {

        // only caters for get(default):

        switch ($this->Method) {
            case "GET":
            default:  // get response
                //if (isset($this->Args[0]) && (int) $this->Args[0] > 0) {
                // $this->ResourceID = (int) $this->Args[0];
                //}

                require 'methods/product.get.php';

                break;
        }
    }

    protected function projects($argFilters) {
        if ($this->Method == 'GET') {
            //return "List all projects";
        } else {
            //return "Only accepts GET requests";
        }
    }

    protected function websearch($Args) {

        // these are the compulsory user inputs for this method:
        $InputsData = array(
            'records_per_page' => array(false, 30),
            'page' => array(false, 0),
            'search_term' => array(true, '')
        );

        $UserInputs = $this->_ProcessGetPostInputs($InputsData, true, false, __METHOD__);

        require CLASS_DIR . 'class.PublicBrowser.php';

        $PB = new PublicBrowser($this->PublicBrowserConfigs, true, $this->UserSession);

        $PB->SetSearchPhrase($UserInputs['search_term']);

        $PB->Query();

        $PB->GetCollection();

        $ResultItems = $PB->GetProductObjects();

        $this->Response = array(
            1 => false,
            2 => true,
            3 => 'I am a string',
            4 => 6655336,
            5 => CLASS_DIR,
            6 => $ResultItems
        );
    }

    protected function clientorders() {

        // only caters for get(default) and post:
        switch ($this->Method) {

            case "POST": // send data, get response

                require 'methods/clientorders.post.php';

                break;

            default:  // get response

                if (!isset($this->Args[0]) || (int) $this->Args[0] == 0) {

                    // invalid public key, no match in global configs:
                    $this->ErrorOccured();
                    $this->SetLastErrorMessage($this->ErrorCodes[16]);
                    return $this->ProcessRequest(); // process error data and respond to client
                }

                $this->ResourceID = (int) $this->Args[0];

                require 'methods/clientorders.get.php';

                break;
        }
    }

    protected function memberdata() {

        // only caters for get(default) and post:
        switch ($this->Method) {

            case "POST": // send data, get response

                require 'methods/memberdata.post.php';

                break;

            default:  // get response

                if (!isset($this->Args[0]) || (int) $this->Args[0] == 0) {

                    // invalid public key, no match in global configs:
                    $this->ErrorOccured();
                    $this->SetLastErrorMessage($this->ErrorCodes[16]);
                    return $this->ProcessRequest(); // process error data and respond to client
                }

                $this->ResourceID = (int) $this->Args[0];

                require 'methods/memberdata.get.php';

                break;
        }
    }

    protected function product_categories2() {

        // only caters for get(default) and post:
        switch ($this->Method) {

            //case "POST": // send data, get response
            //    require 'methods/product.categories.post.php';
            //    break;
            default:  // get response
                //if (!isset($this->Args[0]) || (int) $this->Args[0] == 0) {
                // invalid public key, no match in global configs:
                //$this->ErrorOccured();
                //$this->SetLastErrorMessage($this->ErrorCodes[16]);
                //return $this->ProcessRequest(); // process error data and respond to client
                //}
                //$this->ResourceID = (int) $this->Args[0];

                require 'methods/product.categories2.get.php';

                break;
        }
    }

    protected function product_categories_items() {

        // only caters for get(default) and post:
        switch ($this->Method) {

            //case "POST": // send data, get response
            //    require 'methods/product.categories.post.php';
            //    break;
            default:  // get response
                //if (!isset($this->Args[0]) || (int) $this->Args[0] == 0) {
                // invalid public key, no match in global configs:
                //$this->ErrorOccured();
                //$this->SetLastErrorMessage($this->ErrorCodes[16]);
                //return $this->ProcessRequest(); // process error data and respond to client
                //}
                //$this->ResourceID = (int) $this->Args[0];

                require 'methods/product.categories.items.get.php';

                break;
        }
    }

    protected function product_collection() {

        // only caters for get(default) and post:
        switch ($this->Method) {

            //case "POST": // send data, get response
            //    require 'methods/product.categories.post.php';
            //    break;
            default:  // get response

                require 'methods/product.collection.get.php';

                break;
        }
    }

    */

}
