<?php

//defined( '_JEXEC' ) or die( 'Restricted access' );
define('_VALID_MOS', '1');

$messages = Array();

function debug_msg( $msg ) {
    global $messages;
    if( ZAAKPAY_MODE == "1" ) {
        if( !defined( "_DEBUG_HEADER")  ) {
            echo "<h2>ZAAKPAY Notify_zpay.php Debug OUTPUT</h2>";
            define( "_DEBUG_HEADER", "1" );
        }
        $messages[] = "<pre>$msg</pre>";
        echo end( $messages );
    }
}
	
if ($_POST) {
	header("HTTP/1.0 200 OK");

    global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $database;
    
    
        /*** access Joomla's configuration file ***/
        $my_path = dirname(__FILE__);
        
        if( file_exists($my_path."/../../../configuration.php")) {
            $absolute_path = dirname( $my_path."/../../../configuration.php" );
            require_once($my_path."/../../../configuration.php");
        }
        elseif( file_exists($my_path."/../../configuration.php")){
            $absolute_path = dirname( $my_path."/../../configuration.php" );
            require_once($my_path."/../../configuration.php");
        }
        elseif( file_exists($my_path."/configuration.php")){
            $absolute_path = dirname( $my_path."/configuration.php" );
            require_once( $my_path."/configuration.php" );
        }
        else {
            die( "Joomla Configuration File not found!" );
        }
        
        $absolute_path = realpath( $absolute_path );
        
        // Set up the appropriate CMS framework
        if( class_exists( 'jconfig' ) ) {
			define( '_JEXEC', 1 );
			define( 'JPATH_BASE', $absolute_path );
			define( 'DS', DIRECTORY_SEPARATOR );
			
			// Load the framework
			require_once ( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );
			require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );

			// create the mainframe object
			$mainframe = & JFactory::getApplication( 'site' );
			
			// Initialize the framework
			$mainframe->initialise();
			
			// load system plugin group
			JPluginHelper::importPlugin( 'system' );
			
			// trigger the onBeforeStart events
			$mainframe->triggerEvent( 'onBeforeStart' );
			$lang =& JFactory::getLanguage();
			$mosConfig_lang = $GLOBALS['mosConfig_lang']          = strtolower( $lang->getBackwardLang() );
			// Adjust the live site path
			
			$mosConfig_live_site = str_replace('/administrator/components/com_virtuemart', '', JURI::base());
			$mosConfig_absolute_path = JPATH_BASE;
			debug_msg( "0. Finished Initialization of the joomla1.5 config" );
        } else {
        	define('_VALID_MOS', '1');
        	require_once($mosConfig_absolute_path. '/includes/joomla.php');
        	require_once($mosConfig_absolute_path. '/includes/database.php');
        	$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );
        	$mainframe = new mosMainFrame($database, 'com_virtuemart', $mosConfig_absolute_path );
        }

        // load Joomla Language File
        if (file_exists( $mosConfig_absolute_path. '/language/'.$mosConfig_lang.'.php' )) {
            require_once( $mosConfig_absolute_path. '/language/'.$mosConfig_lang.'.php' );
        }
        elseif (file_exists( $mosConfig_absolute_path. '/language/english.php' )) {
            require_once( $mosConfig_absolute_path. '/language/english.php' );
        }
    /*** END of Joomla config ***/
    
    
    /*** VirtueMart part ***/
		global $database;        
        require_once($mosConfig_absolute_path.'/administrator/components/com_virtuemart/virtuemart.cfg.php');
        include_once( ADMINPATH.'/compat.joomla1.5.php' );
        require_once( ADMINPATH. 'global.php' );
        require_once( CLASSPATH. 'ps_main.php' );
		require_once( CLASSPATH. 'ps_database.php' );
		require_once( CLASSPATH. 'ps_order.php' );

        
        /* @MWM1: Logging enhancements (file logging & composite logger). */
        $vmLogIdentifier = "notify_zpay.php";
        require_once(CLASSPATH."Log/LogInit.php");
              
        /* Load the zaakpay Configuration File */ 
        require_once( CLASSPATH. 'payment/ps_zaakpay.cfg.php' );
        
		                  
	    
    /*** END VirtueMart part ***/

/*==================================== ZAAKPAY PART ============================================*/


	
	$post_msg = "";
	$pure_feedback 	= array();
	
	foreach ($_POST as $ipnkey => $ipnval) {
        $post_msg .= "$ipnkey=$ipnval&amp;";
    }
	
	$post_msg = "";
	
 
	foreach ($_POST as $ipnkey => $ipnval) {
		// Fix issue with magic quotes
		if (get_magic_quotes_gpc())	{
			$ipnkey = stripslashes ($ipnkey);
			$ipnval = stripslashes ($ipnval);
		}
		// ^ Antidote to potential variable injection and poisoning    
        if (!eregi("^[_0-9a-z-]{1,30}$",$ipnkey))  { 
            unset ($ipnkey); 
            unset ($ipnval); 
        } 
		$pure_feedback[$ipnkey] = $ipnval;
    } 
 
	// prerequest
	$wm_post_0	= trim($_POST['orderId']);
	$wm_post_1	= trim($_POST['responseCode']);
	$wm_post_2	= trim($_POST['responseDescription']);
	$wm_post_3	= trim($_POST['checksum']);
/*$myFile = "D:/rsum.txt";
$fh = fopen($myFile, 'w') or die("can't open file");

fwrite($fh, $wm_post_3);*/
	foreach ($pure_feedback as $wm_name => $wm_value){
		if($wm_name == orderId){
			$invoice = $wm_value;
		}
		if($wm_name == responseCode){
			$res_code = $wm_value;
		}
		if($wm_name == responseDescription){
			$res_desc = $wm_value;
		}
	}
	
	
	$secret_key	= "1d817d9879fa4343a029a42d1bb97062";  //Pleaee insert your own Secretkey here
	
    $qv = "SELECT `order_id`, `order_number`, `user_id`, `order_subtotal`,
                    `order_total`, `order_currency`, `order_tax`, 
                    `order_shipping_tax`, `coupon_discount`, `order_discount`, `ip_address`
                FROM `#__{vm}_orders` 
                WHERE `order_id`='".$invoice."'";
	$db_wm = new ps_DB;
	$query = $db_wm->query($qv);	
    $db_wm->query($qv);
    $db_wm->next_record();
	if( $db_wm->f( 'order_id' ) ) {
		$order_id = $db_wm->f( 'order_id' );
		if(!$order_id or $order_id=="") {
			$err=1;
			echo "ERROR: NO SUCH PRODUCT AVAILABLE";
			exit;
		}
	}
	
		$sql = "UPDATE #__{vm}_orders SET zpay_response_code='".$res_code."',zpay_response_description='".$res_desc."' WHERE order_id='".$invoice."'";
	
	$db = new ps_DB;
	$result = $db->query($sql);
	$db->query($sql);
		
	
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
	
	$all = '';
		foreach($pure_feedback as $name => $value)	{
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
		
		
	
	 
	  $hash = hash_hmac('sha256', $all , $secret_key);
	  
	  
	  foreach ($pure_feedback as $wm_name => $wm_value)
	  {
		  
	  if($hash != $wm_post_3)
	  {
		  
		if($wm_name == "responseCode")
		{
			echo '<br><tr><td width="50%" align="center" valign="middle">'.$wm_name.'</td>
						<td width="50%" align="center" valign="middle"><font color=Red>***</font></td></tr><br>';
		}
		else if($wm_name == "responseDescription")
		{
			echo '<tr><td width="50%" align="center" valign="middle">'.$wm_name.'</td> 
						<td width="50%" align="center" valign="middle"><font color=Red>This response is compromised. The Transaction might have been Successfull</font></td></tr><br>';
		}
		else
		{
			echo '<tr><td width="50%" align="center" valign="middle">'.$wm_name.'</td> 
						<td width="50%" align="center" valign="middle">'.$wm_value.'</td></tr><br>';
		}
	  }
	  else
	  {
		  echo '<tr><td width="50%" align="center" valign="middle">'.$wm_name.'</td>
					<td width="50%" align="center" valign="middle">'.$wm_value.'</td></tr><br>';
	  }
	  }
		 if($hash == $wm_post_3)
		 {
			 //confirm
			 echo '<tr><td width="50%" align="center" valign="middle">Checksum Verified </td> 
			 				<td width="50%" align="center" valign="middle"><font color=Blue>Yes</font></td></tr><br>';
			 $d['order_id'] = $order_id;
			 $d['order_status'] = ZAAKPAY_VERIFIED_STATUS;
			 $ps_order= new ps_order;
			$ps_order->order_status_update($d);
			
			$d['order_status'] = "confirmed";
		 }
		 else
		 {
			 $d['order_id'] = $order_id;
			 $d['order_status'] = ZAAKPAY_INVALID_STATUS;
			 $ps_order= new ps_order;
			$ps_order->order_status_update($d);
			
			$d['order_status'] = "cancelled";
			 echo '<tr><td width="50%" align="center" valign="middle">Checksum Verified </td> 
			 			<td width="50%" align="center" valign="middle"><font color=Red>No</font></td></tr><br>';
			
			 
		 }
		 
		 
	  }

?>
