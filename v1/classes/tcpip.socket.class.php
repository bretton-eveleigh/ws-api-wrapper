<?php

class TCPIP_SocketComms{
	
	private $_conn = false;
    private $_socket = false;
    private $_debug = false;
    private $_user;
    private $_passwd;
    private $_host = '10.3.0.94';
    private $_port = 8010;
    private $_errmsg = '';
    private $_sType = "N";


	function __construct($argUser = 'mymobile', $argPwd = '.mymobile.'){

        $this->_user = $argUser;
        $this->_passwd = $argPwd;
	}

    public function open($ip = '10.3.0.94', $port = 8010){
    
        if ($this->_conn)
            return true;

        $this->host = $ip;
        $this->port = $port;

        $this->_socket = fsockopen($ip, $port, $errno, $errstr, 10);
        if ($this->_socket === false) {
            if ($this->_debug)
                echo "Cannot connect to server! [" . $errno . "][" . $errstr . "]\n";
            $this->_errmsg = "Cannot connect to server: $ip:$port! [" . $errno . "][" . $errstr . "]";

            return false;
        } else {
            $this->_conn = true;

            return true;
        }
    }

    public function close(){

        if (!$this->_conn)
            return true;

        fclose($this->_socket);

        $this->_conn = false;
    }

    private function ErrorMsg(){

        return $this->_errmsg;
    }

    private function _send($message){

        if (!$this->_conn) {
            if ($this->_debug)
                echo "Auto connecting...\n";
            if (!$this->open())    {
                $this->_errmsg = "Failed auto open connection!";
                return false;
            }
            if ($this->_debug)
                echo "Auto connected.\n";
        }

        $i = strlen($message);
        if (fwrite($this->_socket, pack($this->_sType, $i)))
            if (fwrite($this->_socket, $message, $i)) {
                if ($this->_debug)
                    echo "Message sent.\n";
                return true;
            } else
                $this->_errmsg = "Failed on sending packet data!";
        else
            $this->_errmsg = "Failed on sending packet size!";


        return false;
    }

    private function _read(){

        $result = "";
        $left = 4;
        while (!feof($this->_socket) && $left != 0) {
            $block = fread($this->_socket, $left);
            if (!$block) {
                $this->_errmsg = "error reading packet header!";
                return false;
            }
            $left = $left - strlen($block);
            $result .= $block;
        }

        $answer = unpack($this->_sType."length", $result);
        //print_r($answer);
        $left = $answer['length'];

        if ($this->_debug)
            echo "Length to read: $left.\n";

        $status = false;
        $result = "";
        while ($left > 0) {
            $block = fread($this->_socket, $left);
            if (!$block) {
                $this->_errmsg = "error reading packet payload!";
                return false;
            }
            $left = $left - strlen($block);
            $result .= $block;
        }

        if ($this->_debug)
            echo "Answer: [$result].\n";

        return $result;
    }

    public function send($output){

        if ($this->_debug)
            echo "packet data: [$output].\n";

        $result = $this->_send($output);
        if ($this->_debug)
            echo "\$result after send: $result.\n";

        if ($result) {
            if ($this->_debug)
                echo "reading result...\n";
            $result = $this->_read();
        }

        return $result;
    }

	function __destruct(){

        // terminate conn, if exist
        $this->close();

	}

}