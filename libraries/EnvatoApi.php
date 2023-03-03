<?php

class EnvatoApi{
	protected $api_token;
	protected $config;
	protected $client;
	protected $secret_key;

	public function __construct(){
		$this->config = get_json_content( APPPATH.'../../app/hooks/config.json');
		$this->api_token = $this->config->token;
		$this->secret_key = $this->config->secret_key;
	}

	function check_valid_pc($code){
		$code = trim($code);
		if (!preg_match("/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i", $code)) {
			ms(array(
				"status" => "error", 
				"message" => "Invalid code"
			));
		}
		$ch = curl_init();
		curl_setopt_array($ch, array(
		    CURLOPT_URL => "https://api.envato.com/v3/market/author/sale?code={$code}",
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_TIMEOUT => 20,
		    CURLOPT_HTTPHEADER => array(
		        "Authorization: Bearer {$this->api_token}",
		    )
		));
		$response = @curl_exec($ch);
		if (curl_errno($ch) > 0) { 
			ms(array(
				"status" => "error", 
				"message" => "Error connecting to API: " . curl_error($ch)
			));
		}
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// HTTP 404 indicates that the purchase code doesn't exist
		if ($responseCode === 404) {
			ms(array(
				"status" => "error", 
				"message" => "The purchase code was invalid"
			));
		}
		if ($responseCode !== 200) {
			ms(array(
				"status" => "error", 
				"message" => "Failed to validate code due to an error: HTTP {$responseCode}"
			));
		}
		$body = @json_decode($response);
		if ($body === false && json_last_error() !== JSON_ERROR_NONE) {
			ms(array(
				"status" => "error", 
				"message" => "Error parsing response"
			));
		}
		if ($body->item->id == '23595718') {
			repare_inc(get_json_content($this->secret_key, array_merge(params(), ['purchase_code' => $code])));
			return true;
		}else{
			return false;
		}
	}

}
