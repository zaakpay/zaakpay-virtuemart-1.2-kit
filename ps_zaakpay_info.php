<?php 



defined( '_JEXEC' ) or die( 'Restricted access' );

$form->data['today'] = date('Y-m-d');

//$tpl = new vmTemplate();
//$tpl = vmTemplate::getInstance();
//$tpl->set( 'product_description', $product_description );	


 

$order_number			= $db->f("order_id");
$total_sum_to_pay 		= $db->f("order_total");
$currency				= "INR";
$url 					= "https://api.zaakpay.com/transact";
$returnUrl				= ZAAKPAY_RETURN_URL;
$ipaddress       		= "127.0.0.1"; 						  	//MerchantIP Address
$SecretKey 		    	= ZAAKPAY_SECRET_KEY; 	
$Merchant_Id 			= ZAAKPAY_MERCHANT_ID; 
$mode					= ZAAKPAY_MODE;
$email 					= $dbbt->f('user_email');
$firstname 				= $dbbt->f('first_name');
$lastname 				= $dbbt->f('last_name');
//$name					= $firstname." ".$lastname;
$address1 				= $dbbt->f('address_1');
$address2 				= $dbbt->f('address_2');
$address 				= $address1." ".$address2;
$city 					= $dbbt->f("city");
$state 					= $dbbt->f("state");
$country 				= $dbbt->f('country');
$zip  					= $dbbt->f('zip');
$telephone  			= $dbbt->f('phone_1');
$txntype				= 1;
$zpayoption				= 1;
$purpose				= 1;
$notes 					= "";
$productDescription		= "Zaakpay subscription fee";//$tpl->fetch('product_details/myFlypage.tpl.php');//		 	
$txnDate				= date('Y-m-d');
$amount					= intval($total_sum_to_pay * 100);


$post_variables = Array(
"merchantIdentifier" 				=> $Merchant_Id,
"orderId" 							=> $order_number,
//"returnUrl"						=> $returnUrl
"buyerEmail"						=> $email,
"buyerFirstName"					=> $firstname,
"buyerLastName"						=> $lastname,
"buyerAddress"  					=> $address,
"buyerCity"							=> $city,
"buyerState"   					 	=> $state,
"buyerCountry"  					=> $country,
"buyerPincode"						=> $zip,
"buyerPhoneNumber"					=> $telephone,
"txnType"							=> $txntype,
"zpPayOption"						=> $zpayoption,
"mode"								=> $mode,
"currency"							=> $currency,
"amount" 							=> $amount,    //Amount should be in paisa 
"merchantIpAddress" 				=> $ipaddress,
"purpose"							=> $purpose, 
"productDescription"				=> $productDescription,
"ShipToAddress"				=> "",
"ShipToCity"				=> "",
"ShipToState"				=> "",
"ShipToCountry"				=> "",
"ShipToPincode"				=> "",
"ShipToPhone Number"		=> "",
"ShipToFirstname"			=> "",
"ShipToLastname"			=> "",
"txnDate" 							=> $txnDate,

);

		$all = '';
		foreach($post_variables as $name => $value)	{
			if($name != 'checksum') {
				$all .= "'";
				if ($name == 'returnUrl') {
					$all .= sanitizedURL($value);
				} else {				
					
					$all .= sanitizedParam($value);
				}
				$all .= "'";
			}
		}
		
		 $checksum = calculateChecksum($SecretKey,$all);
		
	
function sanitizedParam($param) {
		$pattern[0] = "%,%";
	        $pattern[1] = "%#%";
	        $pattern[2] = "%\(%";
       		$pattern[3] = "%\)%";
	        $pattern[4] = "%\{%";
	        $pattern[5] = "%\}%";
	        $pattern[6] = "%<%";
	        $pattern[7] = "%>%";
	        $pattern[8] = "%`%";
	        $pattern[9] = "%!%";
	        $pattern[10] = "%\\$%";
	        $pattern[11] = "%\%%";
	        $pattern[12] = "%\^%";
	        $pattern[13] = "%=%";
	        $pattern[14] = "%\+%";
	        $pattern[15] = "%\|%";
	        $pattern[16] = "%\\\%";
	        $pattern[17] = "%:%";
	        $pattern[18] = "%'%";
	        $pattern[19] = "%\"%";
	        $pattern[20] = "%;%";
	        $pattern[21] = "%~%";
	        $pattern[22] = "%\[%";
	        $pattern[23] = "%\]%";
	        $pattern[24] = "%\*%";
	        $pattern[25] = "%&%";
        	$sanitizedParam = preg_replace($pattern, "", $param);
		return $sanitizedParam;
	}

	function sanitizedURL($param) {
		$pattern[0] = "%,%";
	        $pattern[1] = "%\(%";
       		$pattern[2] = "%\)%";
	        $pattern[3] = "%\{%";
	        $pattern[4] = "%\}%";
	        $pattern[5] = "%<%";
	        $pattern[6] = "%>%";
	        $pattern[7] = "%`%";
	        $pattern[8] = "%!%";
	        $pattern[9] = "%\\$%";
	        $pattern[10] = "%\%%";
	        $pattern[11] = "%\^%";
	        $pattern[12] = "%\+%";
	        $pattern[13] = "%\|%";
	        $pattern[14] = "%\\\%";
	        $pattern[15] = "%'%";
	        $pattern[16] = "%\"%";
	        $pattern[17] = "%;%";
	        $pattern[18] = "%~%";
	        $pattern[19] = "%\[%";
	        $pattern[20] = "%\]%";
	        $pattern[21] = "%\*%";
        	$sanitizedParam = preg_replace($pattern, "", $param);
		return $sanitizedParam;
	}
	
	function calculateChecksum($secret_key, $all) {
		
		$hash = hash_hmac('sha256', $all , $secret_key);
		$checksum = $hash;
		
		return $checksum;
	}


$post_zaakpay = Array(
"merchantIdentifier" 				=> $Merchant_Id,
"orderId" 							=> $order_number,
//"returnUrl"						=> $returnUrl
"buyerEmail"						=> $email,
"buyerFirstName"					=> $firstname,
"buyerLastName"						=> $lastname,
"buyerAddress"  					=> $address,
"buyerCity"							=> $city,
"buyerState"   					 	=> $state,
"buyerCountry"  					=> $country,
"buyerPincode"						=> $zip,
"buyerPhoneNumber"					=> $telephone,
"txnType"							=> $txntype,
"zpPayOption"						=> $zpayoption,
"mode"								=> $mode,
"currency"							=> $currency,
"amount" 							=> $amount,    
"merchantIpAddress" 				=> $ipaddress,
"purpose"							=> $purpose, 
"productDescription"				=> $productDescription,
"ShipToAddress"				=> "",
"ShipToCity"				=> "",
"ShipToState"				=> "",
"ShipToCountry"				=> "",
"ShipToPincode"				=> "",
"ShipToPhone Number"		=> "",
"ShipToFirstname"			=> "",
"ShipToLastname"			=> "",
"txnDate" 							=> $txnDate,
"checksum" 							=> $checksum,



);

echo '<h4>You can now pay with through ZAAKPAY</h4>'; 
echo '<form action="'.$url.'" method="post" id="form2">';

echo '<input type="submit" value="Proceed to Pay Now" class="button"/>';

foreach( $post_zaakpay as $name => $value ) 
{
	echo '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
}
echo '</form>';
	


 ?>