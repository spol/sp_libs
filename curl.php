<?php 

class Curl {
    var $channel;

    private $headers = array();
    
    function __construct()
    {
        $this->reset();
    }
    
    function reset()
    {
        $this->channel = curl_init( );
        // you might want the headers for http codes
        curl_setopt($this->channel, CURLOPT_HEADER, true );
        // you wanna follow stuff like meta and location headers
        curl_setopt($this->channel, CURLOPT_FOLLOWLOCATION, true );
        // you want all the data back to test it for errors
        curl_setopt($this->channel, CURLOPT_RETURNTRANSFER, true );
        // probably unecessary, but cookies may be needed to
        curl_setopt($this->channel, CURLOPT_COOKIEJAR, 'cookie.txt');
        // as above
        curl_setopt($this->channel, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($this->channel, CURLOPT_CONNECTTIMEOUT, 30); 
        curl_setopt($this->channel, CURLOPT_TIMEOUT, 30); 
    }
    
    function setOption($option, $value) {
        curl_setopt( $this->channel, $option, $value);    
    }

    function addHeader($name, $value) {
        $this->headers[$name] = $value;
    }
    
    function setUserAgent($useragent) {
    	$this->setOption('CURLOPT_USERAGENT', $useragent);
    }

    function setPostFields($vars) {
        curl_setopt( $this->channel, CURLOPT_POSTFIELDS, $vars );
        curl_setopt( $this->channel, CURLOPT_POST, true );
    }
    
    function post($url) {
        curl_setopt( $this->channel, CURLOPT_POST, true );
        curl_setopt( $this->channel, CURLOPT_HTTPHEADER, $this->headers );
        return $this->makeRequest($url);
    }
    
    function get($url) {
        curl_setopt( $this->channel, CURLOPT_HTTPGET, true );
        curl_setopt( $this->channel, CURLOPT_HTTPHEADER, $this->headers );
        return $this->makeRequest($url);
    }

    function makeRequest($url)
    {
        // setup the url to post / get from / to
        curl_setopt( $this->channel, CURLOPT_URL, $url );
        // the actual post bit
        // return data
        $response = curl_exec( $this->channel );

        $sections = explode("\r\n\r\n", $response, 2);
        if (count($sections) != 2) {
            //var_dump($sections);
            throw new exception("Error retrieving data");
        }
        list($header, $body) = $sections;
        $header = explode("\n", $header);
        $status = array_shift($header);
        $status = substr($status, 9, 3);
        $headers = array();
        foreach ($header as $line) {
            list ($key, $value) = explode(":", $line, 2);
            $headers[$key] = trim($value);
        }
        $response = new CurlResponse();
        $response->status = $status;
        $response->header = $headers;
        $response->content = $body;

        return $response;
    }
}

class CurlResponse {

	public $content;
	public $status;
	public $headers;
	
	public function jsonContent() {
		return json_decode($this->content);
	}

}

?>