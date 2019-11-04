<?php
class scrape
{



	private $url,$errors = array(),$proxy = array(0),$emails;
    public  $userAgent,$sameDomain,$depth,$type;
    
    #iniciate the class (requires url)
    public function __construct($userAgent ="web-crawler") {
        $this->userAgent = $userAgent;
    }


    public function getErrors() {
        return $this->errors;
    }
    


    private function setUrl($url) {
        ( ( $this->is_url($url) ) ? ( ( $this->url_exists($url) ) ? $this->url = $url : $this->errors['url'] .= "error: url not reachable.\n" ) : $this->errors['url'] .= "error: malformed url.\n" );
    }



    public function set_proxy($ip,$port) {
        if($this->check_proxy($ip,$port)) {
            $this->proxy[0] = 1;
            $this->proxy[1] = "$ip:$port";
        } else {
            $this->errors['proxy'] .= "Could not connect to given proxy.\n";
        }
    }


    private function check_proxy($ip,$port) {
        $ch = curl_init('http://api.proxyipchecker.com/pchk.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,'ip='.$ip.'&port='.$port);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        list($res_time, $speed, $country, $type) = explode(';', curl_exec($ch));
        return ( ($country) ? true : false );
    }

    #verrify url syntax
    private function is_url($url) { 
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
    # verrify server can access the url
    private function url_exists($url){
        $resURL = curl_init(); 
        curl_setopt($resURL, CURLOPT_URL, $url); 
        curl_setopt($resURL, CURLOPT_TIMEOUT, 5);
        curl_setopt($resURL, CURLOPT_NOBODY, true); 

        curl_exec ($resURL); 
        $intReturnCode = curl_getinfo($resURL, CURLINFO_HTTP_CODE); 
        curl_close ($resURL); 
        return ( ($intReturnCode != 200 && $intReturnCode != 302 && $intReturnCode != 304) ? 0 : 1 );
    }



    # fetch a pages source
    public function getSource($url='',$curlMaxExecTime=5) {
        $url = ( ($url=='') ? (($this->url=='') ? 'bad' : $this->url ) : $url );
        if(is_numeric($curlMaxExecTime) && $this->is_url($url) && $this->url_exists($url)  ) {
            $ch = curl_init();

            curl_setopt ( $ch, CURLOPT_HTTPGET, true );
			curl_setopt ( $ch, CURLOPT_HEADER, true );

            if($this->proxy[0]) {
                curl_setopt($ch, CURLOPT_PROXY, $this->proxy[1]);
            }
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $curlMaxExecTime);
            $output = curl_exec($ch);


            

            if(!$output) {
                $this->errors['src'][$url] = "\$could not fetch source.\n";
            } else {

            	$separator = "\r\n\r\n";
				$header = substr( $output, 0, strpos( $output, $separator ) );
				$body_start = strlen( $header ) + strlen( $separator );
				$body = substr( $output, $body_start, strlen( $output ) - $body_start );
				//Parse Headers
				$header_array = Array();
				foreach ( explode ( "\r\n", $header ) as $i => $line ) {
					if($i === 0) {
						$header_array['http_code'] = $line;
						$status_info = explode( " ", $line );
						$header_array['status_info'] = $status_info;
					} else {
						list ( $key, $value ) = explode ( ': ', $line );
						$header_array[$key] = $value;
					}
				}
				//Form Return Structure
				$responseData = Array("headers" => $header_array, "body" => $body );

				// print_r($responseData);

                return $responseData;
            }
        } else {
            if(!is_numeric($curlMaxExecTime)) $this->errors['src']['settings'] .= "\$curlMaxExecTime must be numeric.\n";
            if(!$this->is_url($url) || !$this->url_exists($url)) $this->errors['src']['url'] .= "\$url must be a valed url.\n";
        }
    }


    public function init($url, $domain ) {

    	$this->setUrl($url);
        $this->domain     = $domain;

        $url        = $this->url;
        $domain     = $this->domain;
        $html       = $this->getSource($url);


        return $html;

    }

}