
<?php
        /**
         * @author Tosin Komolafe
         * @copyright CrotSoftware 2012
         */

	// open a http channel, transmit data and return received buffer
	function xml_post($post_xml, $url, $port){
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$ch = curl_init();    // initialize curl handle
		curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);              // Fail on errors
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	// allow redirects
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($ch, CURLOPT_PORT, $port);			//Set the port number
		curl_setopt($ch, CURLOPT_TIMEOUT, 600); // times out after 10 minutes
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_xml); // add POST fields
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

		if($port==443){
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}

		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
?>