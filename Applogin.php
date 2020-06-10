<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Applogin extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Validate_model');
		$this->load->model('Applogin_model');
		$this->load->model('SendOTP_model');
	}
	//Register the app users..
	public function Register()
	{
		header("Content-Type: application/json");
		$register_data 	= json_decode(file_get_contents('php://input')); //For decode the JSON string
		$email    	    = $register_data->email;
		$mobile_number 	= $register_data->mobile_number;
		$social_id      = $register_data->social_id;
		if($this->Validate_model->StringValidate($email, 'Email id should not be empty'))
			return;
		if($this->Validate_model->StringValidate($mobile_number, 'Mobile number should not be empty'))
			return;
		
		$post_data = array(
			'first_name' 			=> $register_data->first_name,
			'last_name' 			=> $register_data->last_name,
			'mobile_no' 			=> $register_data->mobile_number,
			'email_id' 				=> $register_data->email,
			'latitude'      		=> $register_data->latitude,
			'longitude'      		=> $register_data->longitude,
			'password' 				=> md5($register_data->password),
			'push_notif_reg_token'  => $register_data->push_notif_reg_token,
			'isFacebookLogin' 		=> $register_data->isFacebookLogin,
			'isGmailLogin' 			=> $register_data->isGmailLogin,
			'isNormalLogin'			=> $register_data->isNormalLogin,
			'session_token' 		=> $register_data->session_token,
			'social_id'             => $register_data->social_id,
			'country_code'             => $register_data->country_code,
			'date_created' 			=> date('Y-m-d'),
			'status'				=> '1'
		);
		
		$email_data   = array('email_id'  => $email); //Array for check datas
		$mobile_data  = array('mobile_no' => $mobile_number);

		$check_emailid       	   = $this->Applogin_model->getall_datas('customers',$email_data,'isNormalLogin,isGmailLogin,isFacebookLogin')->result_array();

		$check_mobile_number       = $this->Applogin_model->getall_datas('customers',$mobile_data,'isNormalLogin,isGmailLogin,isFacebookLogin')->result_array();
		
		if(!(empty($check_emailid))){ //To check email already exists or not with other login types..
			$exists_type = null;
			if(($check_emailid[0]['isFacebookLogin'])== "true") $exists_type = 'facebook login';
			else if(($check_emailid[0]['isGmailLogin'])== "true") $exists_type = 'gmail login';
			else $exists_type = 'normal login';
			$arr = array('status' => array('status' => 'KO','message' => 'Email already exists in '.$exists_type), 'details' => new ArrayObject());
		}
		else if(!(empty($check_mobile_number))){ //To check mobile number already exists or not
			$exists_type = null;
			if(($check_mobile_number[0]['isFacebookLogin'])== "true") $exists_type = 'facebook login';
			else if(($check_mobile_number[0]['isGmailLogin'])== "true") $exists_type = 'gmail login';
			else $exists_type = 'normal login';
			$arr = array('status' => array('status' => 'KO','message' => 'Mobile number already exists in '.$exists_type), 'details' => new ArrayObject());
		}
		/*else if(!empty($user_id_registered_email))
		{
			$this->SendOTP_model->sendOTP($user_id_registered_email[0]['email_id'],$mobile_number,$user_id_registered_email[0]['customer_id'], 20, $country_code);
			$arr = array('status' => array('status' => 'OK','message' => OTPStatusMessage_s), 'details' => array('customer_id'=>(string)$user_id_registered_email[0]['customer_id']));
		}
		else if(!empty($user_id_registered))
		{
			$this->SendOTP_model->sendOTP($user_id_registered[0]['email_id'],$mobile_number,$user_id_registered[0]['customer_id'], 20, $country_code);
			$arr = array('status' => array('status' => 'OK','message' => OTPStatusMessage_s), 'details' => array('customer_id'=>(string)$user_id_registered[0]['customer_id']));
		}
		else if(!empty($user_id_registered_social))
		{
			$this->SendOTP_model->sendOTP($user_id_registered[0]['social_id'],$mobile_number,$user_id_registered[0]['customer_id'], 20, $country_code);
			$arr = array('status' => array('status' => 'OK','message' => OTPStatusMessage_s), 'details' => array('customer_id'=>(string)$user_id_registered[0]['customer_id']));
		}*/
		else {
			$user_id = $this->Applogin_model->insert_data('customers',$post_data); //Insert data and get the user_id
			//if($this->SendOTP_model->sendOTP($email,$mobile_number,$user_id, 20, $country_code)) // Sent notification to mobile number
			if(!empty($user_id))
			{
				$arr = array('status' => array('status' => 'OK','message' => 'Customer registered successfully..'), 'details' => array('customer_id' => (string)$user_id));
			}
			else
			{
				$arr = array('status' => array('status' => 'KO','message' => 'Something wrong.please try again'), 'details' => new ArrayObject());
			}
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}

	public function VerifyOTPSignUp()
	{
		header("Content-Type: application/json");
		$validateotp_data 	  = json_decode(file_get_contents('php://input'));
		$user_id    	= $validateotp_data->validateotp->user_id;
		$otp_number    	= $validateotp_data->validateotp->otp_number;
		$test_opt = false;
		//Added for to aollow dummy data.. start
		if($otp_number == "1111")
		{
			$test_opt = true;
		} //End..
		$otp_data  = array('otp_num' 		=> $otp_number, 
							 'user_id' 		=> $user_id,
							 'is_sent' 		=> 0,
							 'msg_type'		=> '20',
							 'expire_date 	>= now() AND' => '1=1'
							); 
		$otp_data_update  = array('otp_num' => $otp_number, 
							'user_id' => $user_id
							); 
		$res_otp   = $this->Applogin_model->check_address('otp_details','otp_num',$otp_data);
		if($res_otp == false || $test_opt == true){ //Need to remove $test_opt

			$update_data      = array('is_active' => 1, 'session_token'=> 'testsession', 'verify_otp' => 1);

			$update_otpdata = array('is_sent' => 1);//Update OTP sent as 1

			$update_condition = array('user_id' => $user_id);
			//Update the status code as 1 for activate the account
			$this->Applogin_model->update_data('users',$update_condition,$update_data);
			$this->Applogin_model->update_data('otp_details',$otp_data_update,$update_otpdata);
			$whereget_data   = array('user_id_d' => $user_id,'mobile_number_d' => 'NO', 'password_d' => 'NO', 'social_id_d' => 'NO');
			$data_get  = $this->Applogin_model->getall_datas_sp('CALL `AppLogin_SP`(?,?,?,?)',$whereget_data)->result_array();		
			$arr = array('status' => array('status' => 'OK','message' => 'Successfully verified.'), 'details' => $data_get[0]);
		}
		else
		{
			$arr = array('status' => array('status' => 'KO','message' => 'Failed to verify, incorrect OTP.'), 'details' => new ArrayObject());
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}

	public function Forgotpassword()
	{
	    header("Content-Type: application/json");
		$forgotpassword_data 	  = json_decode(file_get_contents('php://input')); //For decode the JSON string
		$mobile_number    	= $forgotpassword_data->mobile_number;

		$getdata_mobile = array('mobile_no'=> $mobile_number);
		$country_code   = $forgotpassword_data->country_code;
		 //For get the user_id for respective mobile number..
		$UserId=$this->Applogin_model->getall_datas('customers',$getdata_mobile,'customer_id,email_id')->result_array();
		if(empty($UserId))
		{
			$arr = array('status' => array('status' => 'KO','message' => 'Account not exists.'), 'details' => new ArrayObject());
		}
		else 
		{
			$result= $this->SendOTP_model->sendOTP($UserId[0]['email_id'],$mobile_number,$UserId[0]['customer_id'], 21, $country_code);
			if($result == true)
			{
				$arr = array('status' => array('status' => 'OK','message' => "OTP has been sent to your mobile.."), 'details' => array('customer_id' => (string)$UserId[0]['customer_id']));
			}
			else
			{
				$arr = array('status' => array('status' => 'KO','message' => 'Something wrong to  sent OTP...'), 'details' => new ArrayObject());
			}
		}
		
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}
	//To check the social Login if alerady registered or not..
	public function SocialLoginValidate()
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
			return; 
		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$valid_array = array('email', 'social_id');
		if($this->Validate_model->CheckParams($decoded, $valid_array))
			return;

		$email    	    = $decoded['email']; //Need to discuss why keeping the email id..
		$social_id      = $decoded['social_id'];
		$condi_social = array('social_id' => $social_id, 'status' => 1);
		if(strlen($social_id) != 0 &&($this->Applogin_model->check_address('customers','social_id',$condi_social)) == false)
		{
			$whereget_data   = array('user_id_d' => 'NO','mobile_number_d' => 'NO', 'password_d' => 'NO', 'social_id_d' => $social_id);
			$data_get  = $this->Applogin_model->getall_datas_sp('CALL `AppLogin_SP`(?,?,?,?)',$whereget_data)->result_array();
			$arr_social = array('status' => array('status' => 'OK','message' => 'Successfully LoggedIn'), 'details' => $data_get[0]);
			echo json_encode($arr_social,JSON_PRETTY_PRINT);
			return;
		}
		else 
		{
			$arr = array('status' => array('status' => 'KO','message' => 'Social id not registered.'), 'details' => new ArrayObject());
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}
	//To verify the OTP for forgot password..
	public function VerifyOTPForPass()
	{
		header("Content-Type: application/json");
		$validateotp_data 	  = json_decode(file_get_contents('php://input'));
		$customer_id    	= $validateotp_data->customer_id;
		$otp_number    	= $validateotp_data->otp_number;
		$otp_data  		= array('otp_num' 	=> $otp_number, 
							 'customer_id' 		=> $customer_id,
							 'msg_type'		=> '21',
							 'is_sent' 		=> 0,
							 'expire_date 	>= now() AND' => '1=1'
							); 
		$otp_data_update  = array('otp_num' => $otp_number, 
							'customer_id' => $customer_id
							); 
		//Added for test otp to allow the 1111 to verify without validate -start
		if($otp_number == "1111")
		{
			$update_otpdata = array('is_sent' => 1);//Update OTP sent as 1
			$this->Applogin_model->update_data('otp_details',$otp_data_update,$update_otpdata);
			$arr = array('status' => array('status' => 'OK','message' => 'Successfully verified.'), 'details' => array('customer_id' => $user_id));
			echo json_encode($arr,JSON_PRETTY_PRINT);
			return;
		}
		//-End/,
		
		 //To validate the otp valid or not..
		if(!($this->Applogin_model->check_address('otp_details','otp_num',$otp_data)))
		{
			$update_otpdata = array('is_sent' => 1);//Update OTP sent as 1
			$this->Applogin_model->update_data('otp_details',$otp_data_update,$update_otpdata);
			$arr = array('status' => array('status' => 'OK','message' => 'Successfully verified.'), 'details' => array('customer_id' => $customer_id));
		}
		else
		{
			$arr = array('status' => array('status' => 'KO','message' => 'Failed to Verify, Incorrect OTP.'), 'details' => new ArrayObject());
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}

	//To update the password in users table.
	public function UpdatePassword()
	{
		header("Content-Type: application/json");
		$validatepass_data 	  = json_decode(file_get_contents('php://input'));
		$customer_id    	= $validatepass_data->customer_id;
		$password    	= md5($validatepass_data->password);
		$update_data = array('password' => $password, 'session_token'=> 'testsession3');

		$update_condition = array('customer_id' => $customer_id);
		$result = $this->Applogin_model->update_data('customers',$update_condition,$update_data);
		if($result == true)
		{
			$arr = array('status' => array('status' => 'OK','message' => 'Password updated.'), 'details' => array('customer_id' => $customer_id));
		}
		else 
		{
			$arr = array('status' => array('status' => 'KO','message' => 'The password should be different from previous password'), 'details' => new ArrayObject());
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}
	//Login for app users..
	public function Login(){
		header("Content-Type: application/json");
		$data 	  = json_decode(file_get_contents('php://input')); //For decode the JSON string
		$mobile_number    = $data->mobile_number;
		$password = md5($data->password);
		$push_notif_reg_token = $data->push_notif_reg_token;
		if(strlen($push_notif_reg_token) != 0)
		{
			$update_condition_push 	  = array('push_notif_reg_token' => $push_notif_reg_token); //update the push notification token as empty if there is a same data.
			$update_data_push      	  = array('push_notif_reg_token'=>'');
			$this->Applogin_model->update_data('customers',$update_condition_push,$update_data_push);
			$update_condition 	  = array('mobile_no' => $mobile_number, 'password' => $password); //update the push notification token.
			$update_data      	  = array('push_notif_reg_token'=>$push_notif_reg_token, 'is_online' => 1);
			$this->Applogin_model->update_data('customers',$update_condition,$update_data);
		}
		else
		{
			$update_condition 	  = array('mobile_no' => $mobile_number, 'password' => $password); //update the push notification token.
			$update_data      	  = array('is_online'=> 1);
			$this->Applogin_model->update_data('customers',$update_condition,$update_data);
		}
		
		$whereget_data  = array('mobile_no' => $mobile_number, 'password' => $password,'status'=>1);
		
		$data  = $this->Crud_model->get_records_where('customers',$whereget_data);
			
		if(!empty($data))
		{
			$arr = array('status' => array('status' => 'OK','message' => 'Successfully LoggedIn'), 'details' => $data[0]);
		}
		else
		{
			$arr = array('status' => array('status' => 'KO','message' => 'Mobile number or Password incorrect.'), 'details' => new ArrayObject());
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}
	
	public function Onboarding()
	{
		header("Content-Type: application/json");
		$data=$this->Applogin_model->getonboarding()->result_array();
		if($data != [])
			$arr = array('status' => array('status' => 'OK','message' => 'Success.'), 'details' => $data);
		else
			$arr = array('status' => array('status' => 'KO','message' => 'There is no data.'), 'details' => array());
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}
	public function AppLogout()
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
			return; 
		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		
		$user_id = $decoded['user_id'];		
        if(!($this->ValidateUser($user_id))) //Function for validate user.
			return;

		$update_condition = array('customer_id' => $user_id);
		$update_data      = array('is_online' => 0);
		$result = $this->Applogin_model->update_data('customers',$update_condition,$update_data);
		if($result == true)
		{
			$arr = array('status' => array('status' => 'OK','message' => 'User logout successfully'), 'details' => array('user_id' => $user_id));
		}
		else 
		{
			$arr = array('status' => array('status' => 'KO','message' => 'User id not exists'), 'details' => new ArrayObject());
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);
		
	}
	//Sub function for Validate the user..
    public function ValidateUser($user_id)
	{
		$condition_data_user   = array('customer_id' => $user_id, 'status' => 1);
		if($this->Applogin_model->check_address('customers','customer_id',$condition_data_user))
		{
			$arr_user = array('status' => array('status' => 'KO','message' => 'User profile not exists.'), 'details' => new ArrayObject());
			echo json_encode($arr_user,JSON_PRETTY_PRINT);
			return false;
		}
		return true;
	}
	//ResendOTP
	public function ReSendOTP()
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
		return; 

		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$valid_array = array('mobile_number','country_code','msg_type');
		
		if($this->Validate_model->CheckParams($decoded, $valid_array))
			return;
			
		$mobile_number    		  = $decoded['mobile_number'];
		$country_code   		  = $decoded['country_code'];
		$msg_type				  = $decoded['msg_type'];
		$getdata_mobile 		  = array('mobile_number'=> $mobile_number);
		
		 //For get the user_id for respective mobile number..
		$UserId=$this->Applogin_model->getall_datas('users',$getdata_mobile,'user_id,email')->result_array();
		if(empty($UserId))
		{
			$arr = array('status' => array('status' => 'KO','message' => 'Account not exists.'), 'details' => new ArrayObject());
		}
		else 
		{
			$result= $this->SendOTP_model->sendOTP($UserId[0]['email'],$mobile_number,$UserId[0]['user_id'], $msg_type, $country_code);
			if($result == true)
			{
				$arr = array('status' => array('status' => 'OK','message' => OTPStatusMessage_s), 'details' => array('user_id' => $UserId[0]['user_id']));
			}
			else
			{
				$arr = array('status' => array('status' => 'KO','message' => OTPStatusMessage_f), 'details' => new ArrayObject());
			}
		}
		
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}
	public function GetTranslateLanguageList()
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
			return; 
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
		$valid_array = array('user_id');
		if($this->Validate_model->CheckParams($decoded, $valid_array))
            return;

        $whereget_data    = array('user_id' => $decoded['user_id']);
        $data_get         = $this->Applogin_model->getall_datas_sp('CALL `GetTranslateLanguageList_SP`(?)',$whereget_data)->result_array();
		if(empty($data_get))
        {
            $arr_get 		  = array('status'   => array("status" => 'KO', "message"=> 'There is no translate language or incorrect user id'),
                            	  'details'  => new ArrayObject());	
        }
        else
        {
            $arr_get 		  = array('status'   => array("status" => 'OK', "message"=> 'Translate language details available'),
                            	  'details'  => $data_get);	

        }
        echo json_encode($arr_get,JSON_PRETTY_PRINT);
	}
	public function UpdateTranslationLanguage()
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
			return; 
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
		$valid_array = array('user_id','language_type');
		if($this->Validate_model->CheckParams($decoded, $valid_array))
            return;
		$update_condition = array('user_id' => $decoded['user_id']);
		$update_data = array('language_type' => $decoded['language_type']);
		$res = $this->Applogin_model->update_data('users',$update_condition,$update_data);
		if($res)
		{
			$arr = array('status' => array('status' => 'OK','message' => 'Translation language updated'), 'details' => array('user_id' => $decoded['user_id']));
		}
		else
		{
			$arr = array('status' => array('status' => 'KO','message' => 'Failed in translation update'), 'details' => new ArrayObject());
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}

	public function send_OTP()
	{
	    header("Content-Type: application/json");
		$verify_mobile 	  = json_decode(file_get_contents('php://input')); //For decode the JSON string
		$mobile_number    	= $verify_mobile->mobile_number;

		$getdata_mobile = array('mobile_no'=> $mobile_number);
		
			$result= $this->SendOTP_model->send_OTP($mobile_number,21);
			if($result == true)
			{
				$arr = array('status' => array('status' => 'OK','message' => "OTP has been sent to your mobile.."), 'details' => array('mobile_no' => (string)$mobile_number));
			}
			else
			{
				$arr = array('status' => array('status' => 'KO','message' => 'Something wrong to  sent OTP...'), 'details' => new ArrayObject());
			}
		
		
		echo json_encode($arr,JSON_PRETTY_PRINT);
	}

	public function VerifyOTPForSignup()
	{
		header("Content-Type: application/json");
		$validateotp_data 	  = json_decode(file_get_contents('php://input'));
		$mobile_number    	= $validateotp_data->mobile_number;
		$otp_number    	= $validateotp_data->otp_number;
		if($otp_number!=''){
		$otp_data  		= array('otp_num' 	=> $otp_number, 
							 'mobile_no' 		=> $mobile_number,
							 'msg_type'		=> '21',
							 'is_sent' 		=> 0,
							 'expire_date 	>= now() AND' => '1=1'
							); 
		$otp_data_update  = array('otp_num' => $otp_number, 
							'mobile_no' => $mobile_number
							); 
$exists_count=$this->Crud_model->get_records_where('otp_details',$otp_data);
		 //To validate the otp valid or not.. 
		if(!empty($exists_count))
		{
			$update_otpdata = array('is_sent' => 1);//Update OTP sent as 1
			$this->Applogin_model->update_data('otp_details',$otp_data_update,$update_otpdata);
			$update_customer=$this->Crud_model->get_by_sql2("UPDATE customers SET otp_verified=1 WHERE mobile_no='".$mobile_number."'");
			$arr = array('status' => array('status' => 'OK','message' => 'Successfully verified.'), 'details' => array('mobile_no' => $mobile_number));
		}
		else
		{
			$arr = array('status' => array('status' => 'KO','message' => 'Failed to Verify, Incorrect OTP.'), 'details' => new ArrayObject());
		}
	}else
		{
			$arr = array('status' => array('status' => 'KO','message' => 'Please enter OTP.'), 'details' => array('mobile_no' => $mobile_number));
		}


			echo json_encode($arr,JSON_PRETTY_PRINT);

	}
}