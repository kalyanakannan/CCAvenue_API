<?php

class CCAvenue_API {


 	/**
     * Execute api call in CCAvenue
     *
     * @return metting detials as xml
     */

 	// Provide working key share by CCAvenues

	private $working_key = '';

	// Provide access code Shared by CCAVENUES

	private $access_code = '';

	private $URL="https://login.ccavenue.com/apis/servlet/DoWebTrans";

	public function api_call($final_data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$final_data);

		// Get server response ... curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec ($ch);
		curl_close ($ch);

		$information=explode('&',$result);
		$dataSize=sizeof($information);
		$status1=explode('=',$information[0]);
		$status2=explode('=',$information[1]);
		if($status1[1] == '1'){
			$recorddata=$status2[1];
			return $recorddata;
		}
		else
		{
			$status=decrypt($status2[1],$this->working_key);
			return $status;
		}
	}

	public function orderStatusTracker($post_data)
	{
		/*
			function for get order status
			
		*/	
		$merchant_data = json_encode($post_data);

		/*
			sample $post_data after json encode

			{
			   "reference_no": "225013271813",
			   "order_no": "33231644"
			}
		*/
 
		// Encrypt merchant data with working key shared by ccavenue
		 
		$encrypted_data = self::encrypt($merchant_data, $this->working_key);
		 
		//make final request string for the API call
		 
		$final_data ="request_type=JSON&access_code=".$this->access_code."&command=orderStatusTracker&response_type=JSON&enc_request=".$encrypted_data;
		$result = self::api_call($final_data);
		return $result;
	}

	public function getPendingOrders($post_data)
	{
		/*
			function for get pending orders
		*/
		$merchant_data = json_encode($post_data);
 
		// Encrypt merchant data with working key shared by ccavenue
		 
		$encrypted_data = self::encrypt($merchant_data, $this->working_key);
		 
		//make final request string for the API call
		 
		$final_data ="request_type=JSON&access_code=".$this->access_code."&command=getPendingOrders&response_type=JSON&enc_request=".$encrypted_data;
		$result = self::api_call($final_data);
		return $result;
	}

	public function confirmOrder($post_data)
	{
		/*
			function for confimorder
		*/
		$merchant_data = json_encode($post_data);

		/*
			sample $post_data after json encode

			{
		   		"order_List": [
		      { "reference_no":"203000099429", "amount": "1.00"},
		      { "reference_no": "203000104640", "amount": "1.00"}
		   		]
			}
		*/
 
		// Encrypt merchant data with working key shared by ccavenue
		 
		$encrypted_data = self::encrypt($merchant_data, $this->working_key);
		 
		//make final request string for the API call
		 
		$final_data ="request_type=JSON&access_code=".$this->access_code."&command=confirmOrder&response_type=JSON&enc_request=".$encrypted_data;
		$result = self::api_call($final_data);

		return $result;

	}

	public function refundOrder($post_data)
	{
		/*
			function for refund
		*/
		$merchant_data = json_encode($post_data);

		/* sample $post_data after json encode

			{
   				"reference_no":"1236547",
   				"refund_amount":"1.0",
   				"refund_ref_no":"API1234"
			}

		*/
 
		// Encrypt merchant data with working key shared by ccavenue
		 
		$encrypted_data = self::encrypt($merchant_data, $this->working_key);
		 
		//make final request string for the API call
		 
		$final_data ="request_type=JSON&access_code=".$this->access_code."&command=refundOrder&response_type=JSON&enc_request=".$encrypted_data;
		$result = self::api_call($final_data);

		return $result;
	}

	public function cancelOrder($post_data)
	{
		/*
			function for cancelOrder
		*/

		$merchant_data = json_encode($post_data);

		/* sample $post_data after json encode

			{
			   "order_List": [
			      {"reference_no":"203000099429","amount": "1.00" },
			      {"reference_no":"203000099429","amount": "1.00" }
			   ] 
			}

		*/
 
		// Encrypt merchant data with working key shared by ccavenue
		 
		$encrypted_data = self::encrypt($merchant_data, $this->working_key);
		 
		//make final request string for the API call
		 
		$final_data ="request_type=JSON&access_code=".$this->access_code."&command=cancelOrder&response_type=JSON&enc_request=".$encrypted_data;
		$result = self::api_call($final_data);

		return $result;
	}

	public function encrypt($plainText, $key)
	{ 

		$secretKey = self::hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
		$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
		$plainPad = self::pkcs5_pad($plainText, $blockSize);
		if (mcrypt_generic_init($openMode, $secretKey, $initVector) != -1) {
			$encryptedText = mcrypt_generic($openMode, $plainPad);
			 mcrypt_generic_deinit($openMode);
		} 
		return bin2hex($encryptedText);
 	}


 	public function decrypt($encryptedText, $key)
 	{

		$secretKey = self::hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d,0x0e, 0x0f);
		$encryptedText = self::hextobin($encryptedText);
		$encryptedText = rtrim($encryptedText, "\000");
		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
		mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
		mcrypt_generic_deinit($openMode);
 		return $decryptedText;
 	} 
 
	 // Remove repeated content from request strign
	public function pkcs5_pad($plainText, $blockSize) 
	{
		$pad = $blockSize - (strlen($plainText) % $blockSize);
	 	return $plainText . str_repeat(chr($pad), $pad);
	 } 
	 
 
	 //********** Hexadecimal to Binary function for php 4.0 version ******** 
	public function hextobin($hexString)
	{ 
		$length = strlen($hexString);
	 	$binString = "";
		$count = 0;
		while ($count < $length)
		{ 
			$subString = substr($hexString, $count, 2);
			$packedString = pack("H*", $subString);
			if ($count == 0) { 
				$binString = $packedString;
			} else { 
				$binString.=$packedString;
			} 
			$count+=2;
		}
		return $binString;
	}

}
?>
