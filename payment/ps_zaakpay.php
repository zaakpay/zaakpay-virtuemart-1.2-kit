<?php


if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

class ps_zaakpay {

    var $classname = "ps_zaakpay";
    var $payment_code = "ZAAKPAY";

function show_configuration() {
	global $VM_LANG;
	$db = new ps_DB();
	include_once(CLASSPATH ."payment/".$this->classname.".cfg.php"); // Read current Configuration
	
    ?>
<table>
	
	<tr>
		<td><strong>Merchant ID</strong></td>
		<td><input type="text" name="ZAAKPAY_MERCHANT_ID" class="inputbox" value="<?  echo ZAAKPAY_MERCHANT_ID ?>" /></td>
		<td>Please Enter your Zaakpay Merchant ID.</td>
	</tr>
	
	<tr>
		<td><strong>Secret Key</strong></td>
		<td><input type="text" name="ZAAKPAY_SECRET_KEY" class="inputbox" value="<?  echo ZAAKPAY_SECRET_KEY ?>" /></td>
		<td>Please Enter your Secretkey given by Zaakpay</td>
	</tr>
        
	<tr>
		<td><strong><?php echo $VM_LANG->_('PHPSHOP_ADMIN_CFG_PAYMENT_ORDERSTATUS_SUCC') ?></strong></td>
		<td>
			<select name="ZAAKPAY_VERIFIED_STATUS" class="inputbox" >
			<?php
				$q = "SELECT order_status_name,order_status_code FROM #__{vm}_order_status ORDER BY list_order";
				$db->query($q);
				$order_status_code = Array();
				$order_status_name = Array();
				
				while ($db->next_record()) {
					$order_status_code[] = $db->f("order_status_code");
					$order_status_name[] = $db->f("order_status_name");
				}
				
				for ($i = 0; $i < sizeof($order_status_code); $i++) {
					echo "<option value=\"" . $order_status_code[$i];
					if (ZAAKPAY_VERIFIED_STATUS == $order_status_code[$i]) 
						echo "\" selected=\"selected\">";
					else
						echo "\">";
						echo $order_status_name[$i] . "</option>\n";
				}?>
			</select>
		</td>
		<td><?php echo $VM_LANG->_('PHPSHOP_ADMIN_CFG_PAYMENT_ORDERSTATUS_SUCC_EXPLAIN') ?></td>
	</tr>
	
	<tr>
		<td><strong><?php echo $VM_LANG->_('PHPSHOP_ADMIN_CFG_PAYMENT_ORDERSTATUS_FAIL') ?></strong></td>
		<td>
			<select name="ZAAKPAY_INVALID_STATUS" class="inputbox" >
			<?php
				for ($i = 0; $i < sizeof($order_status_code); $i++) {
					echo "<option value=\"" . $order_status_code[$i];
					if (ZAAKPAY_INVALID_STATUS == $order_status_code[$i]) 
						echo "\" selected=\"selected\">";
					else
						echo "\">";
						echo $order_status_name[$i] . "</option>\n";
			} ?>
			</select>
		</td>
		<td><?php echo $VM_LANG->_('PHPSHOP_ADMIN_CFG_PAYMENT_ORDERSTATUS_FAIL_EXPLAIN') ?></td>
	</tr>
    <tr>
   
	<!--<tr>
		<td><strong>Return Url(Optional)</strong></td>
		<td><input type="text" name="ZAAKPAY_RETURN_URL" class="inputbox" value="<?  echo ZAAKPAY_REDIRECT_URL ?>" /></td>
		<td>Example - http://www.yoursite.com/zaakpay_returndata.php</td>
	</tr> -->
    <td><strong>Mode</strong></td>
    <td><input type="text" name="ZAAKPAY_MODE" class="inputbox" value="<? echo ZAAKPAY_MODE ?>"/></td>
    <td>Select a Mode to work with Zaakpay.(Test Mode = 0 (or) Live Mode = 1)</td>
    </tr> 
    
<?php
}
    
function has_configuration() {
	// return false if there's no configuration
	return true;
}
   
  /**
	* Returns the "is_writeable" status of the configuration file
	* @param void
	* @returns boolean True when the configuration file is writeable, false when not
	*/
function configfile_writeable() {
	return is_writeable( CLASSPATH."payment/".$this->classname.".cfg.php" );
}
   
  /**
	* Returns the "is_readable" status of the configuration file
	* @param void
	* @returns boolean True when the configuration file is writeable, false when not
	*/
function configfile_readable() {
	return is_readable( CLASSPATH."payment/".$this->classname.".cfg.php" );
}
   
  /**
	* Writes the configuration file for this payment method
	* @param array An array of objects
	* @returns boolean True when writing was successful
	*/
function write_configuration( &$d ) {
	$my_config_array = array(	"ZAAKPAY_MERCHANT_ID" 			=> $d['ZAAKPAY_MERCHANT_ID'],
								"ZAAKPAY_SECRET_KEY" 			=> $d['ZAAKPAY_SECRET_KEY'],
								//"ZAAKPAY_REDIRECT_URL" 		=> $d['ZAAKPAY_REDIRECT_URL'],
								"ZAAKPAY_VERIFIED_STATUS" 		=> $d['ZAAKPAY_VERIFIED_STATUS'],
								"ZAAKPAY_INVALID_STATUS"		=> $d['ZAAKPAY_INVALID_STATUS'],
								"ZAAKPAY_PENDING_STATUS"		=> $d['ZAAKPAY_PENDING_STATUS'],
								"ZAAKPAY_MODE"					=> $d['ZAAKPAY_MODE']
                            );
	$config = "<?php\n";
	$config .= "if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
	foreach( $my_config_array as $key => $value ) {
		$config .= "define ('$key', '$value');\n";
	}
	$config .= "?>";
  
	if ($fp = fopen(CLASSPATH ."payment/".$this->classname.".cfg.php", "w")) {
		fputs($fp, $config, strlen($config));
		fclose ($fp);
		return true;
	}
	else
		return false;
}
   
  /**************************************************************************
  ** name: process_payment()
  ** returns: 
  ***************************************************************************/
function process_payment($order_number, $order_total, &$d) {
      return true;
    }
   
}