
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class QBhelper {

	function __construct() {

	}

	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Genernate new session token
	 *
	 *--------------------------------------------------------------------------------------------------------*/

	public function generateSession() {
		
		// Generate signature
		$nonce = rand();
		$timestamp = time(); // time() method must return current timestamp in UTC but seems like hi is return timestamp in current time zone
		$signature_string = "application_id=" . QB_APP_ID . "&auth_key=" . QB_AUTH_KEY . "&nonce=" . $nonce . "&timestamp=" . $timestamp;
		$signature = hash_hmac('sha1', $signature_string , QB_AUTH_SECRET);

		$post_body = http_build_query( array(
			'application_id' => QB_APP_ID,
			'auth_key' => QB_AUTH_KEY,
			'timestamp' => $timestamp,
			'nonce' => $nonce,
			'signature' => $signature
		));	

		// Configure cURL
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, QB_API_ENDPOINT . '/' . QB_PATH_AUTH); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($curl, CURLOPT_POST, true); // Use POST
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_body); // Setup post body
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Receive server response
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		// Execute request and read response
		$response = curl_exec($curl);

		$result = null;
		ob_start();
		try {
			$result = json_decode($response)->session->token;
		}
		catch (Exception $e) {
		}

		// Close connection
		curl_close($curl);
		ob_end_clean();
		return $result;
	}


	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Signup
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function signupUser($fullname, $username, $email, $phone, $avatar, $path, $external_user_id) {
		$token = $this->generateSession();

		$request = json_encode(array(
			'user' => array(
				'full_name' => $fullname,
		 		'login' => $username,
		  		'email' => $email,
		  		'password' => QB_DEFAULT_PASSWORD,
		  		'phone' => $phone,
		  		'website' => $avatar,
		  		'custom_data' => $path,
		  		'external_user_id' => $external_user_id
		  	)
		));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, QB_API_ENDPOINT . '/' . QB_PATH_USER); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'QuickBlox-REST-API-Version: 0.1.0',
		  'QB-Token: ' . $token
		));
		$response = curl_exec($ch);
		
		$result = null;
		
		ob_start();
		try {
			$result = json_decode($response);
		}
		catch (Exception $e) {
			$result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		}
		curl_close($ch);
		ob_end_clean();
		return $result;
	}

	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Update User
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function updateUser($qb_token, $qb_id, $user) {

		$request = json_encode(array(
			'user' => $user
		));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, QB_API_ENDPOINT . '/users/' . $qb_id.'.json'); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'QuickBlox-REST-API-Version: 0.1.0',
		  'QB-Token: ' . $qb_token
		));
		$response = curl_exec($ch);
		
		$result = null;
		
		ob_start();
		try {
			$result = json_decode($response);
		}
		catch (Exception $e) {
			$result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		}
		curl_close($ch);
		ob_end_clean();
		return $result;
	}

	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Delete User
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function deleteUser($qb_token, $qb_id) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, QB_API_ENDPOINT . '/users/' . $qb_id.'.json'); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'QuickBlox-REST-API-Version: 0.1.0',
		  'QB-Token: ' . $qb_token
		));
		$response = curl_exec($ch);
		
		$result = null;
		
		ob_start();
		try {
			$result = json_decode($response);
		}
		catch (Exception $e) {
			$result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		}
		curl_close($ch);
		ob_end_clean();
		return $result;
	}


	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Signin
	 *
	 *--------------------------------------------------------------------------------------------------------*/

	public function signinUser($username = null) {
		
		$nonce = rand();
		$timestamp = time(); // time() method must return current timestamp in UTC but seems like hi is return timestamp in current time zone
		$signature_string = "application_id=" . QB_APP_ID . "&auth_key=" . QB_AUTH_KEY . "&nonce=" . $nonce . "&timestamp=" . $timestamp."&user[login]=".$username."&user[password]=".QB_DEFAULT_PASSWORD;
		$signature = hash_hmac('sha1', $signature_string , QB_AUTH_SECRET);

		$post_body = http_build_query( array(
			'application_id' => QB_APP_ID,
			'auth_key' => QB_AUTH_KEY,
			'timestamp' => $timestamp,
			'nonce' => $nonce,
			'signature' => $signature,
			'user' => array('login'=>$username, 'password'=>QB_DEFAULT_PASSWORD)
		));
		

		// Configure cURL
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, QB_API_ENDPOINT . '/' . QB_PATH_AUTH); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($curl, CURLOPT_POST, true); // Use POST
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_body); // Setup post body
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Receive server response
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		// Execute request and read response
		$response = curl_exec($curl);
		$result = null;

		ob_start();
		try {
			$result = json_decode($response);
		}
		catch (Exception $e) {
		}
		curl_close($curl);
		ob_end_clean();
		return $result;
	}

	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Sign out
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function signoutUser($email) {

	}

	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Create a new group
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function createGroup($token, $type, $name, $ids) {

		$request = json_encode(array(
			'type' => $type,
	 		'name' => $name,
	  		'occupants_ids' => $ids
		));

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, QB_API_ENDPOINT . '/' . QB_PATH_DIALOG); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'QuickBlox-REST-API-Version: 0.1.0',
		  'QB-Token: ' . $token
		));
		$response = curl_exec($ch);
		
		$result = null;
		
		ob_start();
		try {
			$result = json_decode($response);
			// $result = $response;
		}
		catch (Exception $e) {
			// $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$result = 'error';
		}
		curl_close($ch);
		ob_end_clean();
		return $result;
	}

	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Update group
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function updateGroup($token, $group_id, $name, $ids) {

		$request = null;
		if (strlen($ids) > 0)	
		{
			$request = json_encode(array(
		 		'name' => $name,
		  		'push_all' => array (
		  			'occupants_ids' => explode(',', $ids)
		  		)
			));
		}
		else
		{
			$request = json_encode(array(
		 		'name' => $name
			));	
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, QB_API_ENDPOINT . '/chat/Dialog/' . $group_id .'.json'); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'QuickBlox-REST-API-Version: 0.1.0',
		  'QB-Token: ' . $token
		));
		$response = curl_exec($ch);
		
		$result = null;
		
		ob_start();
		try {
			$result = json_decode($response);
			// $result = $response;
		}
		catch (Exception $e) {
			// $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$result = 'error';
		}
		curl_close($ch);
		ob_end_clean();
		return $result;
	}

	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Update group (remove group members)
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function removeGroupMember($token, $group_id, $ids) {

		$request = null;
		if (strlen($ids) > 0)	
		{
			$request = json_encode(array(
		  		'pull_all' => array (
		  			'occupants_ids' => explode(',', $ids)
		  		)
			));
		}
		else
		{
			$request = json_encode(array(
		 		'name' => $name
			));	
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, QB_API_ENDPOINT . '/chat/Dialog/' . $group_id .'.json'); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'QuickBlox-REST-API-Version: 0.1.0',
		  'QB-Token: ' . $token
		));
		$response = curl_exec($ch);
		
		$result = null;
		
		ob_start();
		try {
			$result = json_decode($response);
			// $result = $response;
		}
		catch (Exception $e) {
			// $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$result = 'error';
		}
		curl_close($ch);
		ob_end_clean();
		return $result;
	}


	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Get group
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function getGroup($qb_token, $group_id) {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, QB_API_ENDPOINT . '/' . QB_PATH_DIALOG . '?_id=' . $group_id); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'QuickBlox-REST-API-Version: 0.1.0',
		  'QB-Token: ' . $qb_token
		));
		$response = curl_exec($ch);
		
		$result = null;
		
		ob_start();
		try {
			$result = json_decode($response);
			// $result = $response;
		}
		catch (Exception $e) {
			// $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$result = 'error';
		}
		curl_close($ch);
		ob_end_clean();
		return $result;
	}

	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Signup
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function createCirculate($qb_token, $chat_dialog_id, $recipient_id, $message) {

		$request = json_encode(array(
			'chat_dialog_id' =>  $chat_dialog_id,
	 		'message' => $message,
	  		'recipient_id' => $recipient_id,
	  		'send_to_chat' => 1,
	  		'circulate' => 1,
		));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, QB_API_ENDPOINT . '/' . QB_PATH_MESSAGE); // Full path is - https://api.quickblox.com/auth.json
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'QuickBlox-REST-API-Version: 0.1.0',
		  'QB-Token: ' . $qb_token
		));
		$response = curl_exec($ch);
		
		$result = null;
		
		ob_start();
		try {
			$result = json_decode($response);
		}
		catch (Exception $e) {
			$result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		}
		curl_close($ch);
		ob_end_clean();
		return $result;
	}


	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Send GCM
	 *
	 *--------------------------------------------------------------------------------------------------------*/
	public function sendGCM($id, $content) {
		// API access key from Google API's Console

		ob_start();
		
		$registrationIds = array( $id );
		// prep the bundle

		$fields = array
		(
			'registration_ids' 	=> $registrationIds,
			'data'			=> $content
		);
		 
		$headers = array
		(
			'Authorization: key=' . GCM_API_KEY,
			'Content-Type: application/json'
		);
		 
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );

		echo $result;

		curl_close( $ch );

		ob_end_clean();

		return $result;
	}

	/*--------------------------------------------------------------------------------------------------------
	 *
	 *      Send APN
	 * 
	 *--------------------------------------------------------------------------------------------------------*/
	public function sendAPN($deviceToken, $content, $pemFilename = "") {
		if ($pemFilename == "")
			$pemFilename = 'pushcert.pem';
		
		// set time limit to zero in order to avoid timeout
		set_time_limit(0);
		 
		// charset header for output
		header('content-type: text/html; charset: utf-8');

		// this is the pass phrase you defined when creating the key
		$passphrase = 'qwert';
		// you can post a variable to this string or edit the message here
		// tr_to_utf function needed to fix the Turkish characters
		//$message = tr_to_utf("blah blah blah...");
		 
		// load your device ids to an array
		$deviceIds = array(
			$deviceToken
		);
		// this is where you can customize your notification
		//$payload = '{"aps":{"alert":"' . $message . '","sound":"default", "type": "ipray_invitation", "sender":"' . $sender . '", "receiver":"' . $receiver . '"}}';
		$payload = '{"aps":' . $content . '}';
		//$payload = '{"aps":{"alert":"blah blah blah...","sound":"default", "type": "ipray_invitation"}}';

		$result = 'Start' . '<br />';
		ob_start();

		////////////////////////////////////////////////////////////////////////////////
		// start to create connection
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFilename);
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

		echo count($deviceIds) . ' devices will receive notifications.<br />';

		$undelivered = 0;

		foreach ($deviceIds as $item) {
		    // wait for some time
		    sleep(1);

		    // Open a connection to the APNS server
		    $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

		    if (!$fp) {
		        exit("Failed to connect: $err $errstr" . '<br />');
		    } else {
		        echo 'Apple service is online. ' . '<br />';
		    }

		    // Build the binary notification
		    $msg = chr(0) . pack('n', 32) . pack('H*', $item) . pack('n', strlen($payload)) . $payload;

		    // Send it to the server
		    $result = fwrite($fp, $msg, strlen($msg));

		    if (!$result) {
		        //echo 'Undelivered message count: ' . $item . '<br />';
		        $undelivered++;
		    } else {
		        echo 'Delivered message count: ' . $item . '<br />';
		    }

		    if ($fp) {
		        fclose($fp);
		        echo 'The connection has been closed by the client' . '<br />';
		    }
		}

		echo count($deviceIds) . ' devices have received notifications.<br />';

		ob_end_clean();

		// set time limit back to a normal value
		set_time_limit(30);

		return $undelivered;
	}

	public function sendViaMailgun($from, $to, $subject, $html) {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    curl_setopt($ch, CURLOPT_USERPWD, 'api:key-7e055f5229a3cc5f47cdb456549baa9d');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	    curl_setopt($ch, CURLOPT_URL, 
	          'https://api.mailgun.net/v3/email.eggchat.net/messages');
	    curl_setopt($ch, CURLOPT_POSTFIELDS, 
	            array('from' => $from,
	                  'to' => $to,
	                  'subject' => $subject,
	                  'text' => $html));
	    $result = curl_exec($ch);

	    $result = curl_error($ch);
		curl_close($ch);
		return $result;
	}
	
}
?>