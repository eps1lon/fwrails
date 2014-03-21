<?php
class Curlm {
    private static $info_status_a = array();
    
    private $opts = array();
    private $master = null;
    private $handles = array();
    
    public function __construct() {
        $this->master = curl_multi_init();
    }
    
    public function __destruct() {
        foreach ($this->handles as $key => $handle) { // remove,
            $this->remove_handle($key);
        }
        
        curl_multi_close($this->master);              // close and
        $this->master = array();                      // free curl_multi
    }
    
    public function exec($info_cb = null) {
        $start_t = microtime(true);
        
        do {
            $status = curl_multi_exec($this->master, $running);
            $info = curl_multi_info_read($this->master);
            
            if ($info !== false && $info_cb) {
                $info_cb($info);
            }
        } while ($status == CURLM_CALL_MULTI_PERFORM || $running);
        
        return microtime(true) - $start_t;
    }
    
    public function each($cb) {
        foreach ($this->handles as $key => $handle) {
            $response = curl_multi_getcontent($handle);

            $cb($response, $handle, $key);
            
            $this->remove_handle($key);
        }
    }
    
    public function add_handle($url, Array $curlopts = Array()) {
        $handle = curl_init($url);
        curl_setopt_array($handle, $curlopts + $this->opts);
        
        $this->handles[] = $handle;
        curl_multi_add_handle($this->master, $handle);
    }
    
    public function remove_handle($key) {
        if (isset($this->handles[$key])) {
            curl_multi_remove_handle($this->master, $this->handles[$key]); // remove,
            curl_close($this->handles[$key]);                              // close and
            unset($this->handles[$key]);                                   // free handle
            
            return true;
        }
        return false;
    }
    
    public function setopts(Array $opts) {
        $this->opts = $opts + $this->opts;
    }
    
    public function getopts() {
        return $this->opts;
    }
    
    public static function info_status($const) {
        if (empty(self::$info_status_a)) { // init
            foreach (explode(" ", "OK UNSUPPORTED_PROTOCOL FAILED_INIT URL_MALFORMAT ".
                                  "URL_MALFORMAT_USER COULDNT_RESOLVE_PROXY COULDNT_RESOLVE_HOST ".
                                  "COULDNT_CONNECT PARTIAL_FILE HTTP_NOT_FOUND WRITE_ERROR ".
                                  "MALFORMAT_USER READ_ERROR OUT_OF_MEMORY OPERATION_TIMEOUTED ".
                                  "HTTP_RANGE_ERROR HTTP_POST_ERROR FILE_COULDNT_READ_FILE ".
                                  "LIBRARY_NOT_FOUND FUNCTION_NOT_FOUND ABORTED_BY_CALLBACK ".
                                  "BAD_FUNCTION_ARGUMENT BAD_CALLING_ORDER HTTP_PORT_FAILED ".
                                  "TOO_MANY_REDIRECTS OBSOLETE GOT_NOTHING SEND_ERROR RECV_ERROR ".
                                  "SHARE_IN_USE BAD_CONTENT_ENCODING FILESIZE_EXCEEDED") as $c_name) {

                self::$info_status_a[constant("CURLE_$c_name")] = $c_name;
            }
        }
        
        return "CURLE_" . self::$info_status_a[$const];
    }
}