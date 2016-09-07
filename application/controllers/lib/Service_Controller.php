<?php

defined('BASEPATH') OR exit('No direct script access allowed');


// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . './libraries/REST_Controller.php';
require APPPATH . './libraries/Constants.php';
require APPPATH . './libraries/QBhelper.php';
require 'Validator.php';
require 'Authentication.php';
require 'apidoc_define.php';



class Service_Controller extends REST_Controller {

    public $qb;
    public $current_user;

    function __construct()
    {
        $this->qb = new QBhelper();
        $this->current_user = array('uid' => null, 'username' => null, 'qb_id' => null, 'email' => null, 'qb_token' => null);

        // Construct the parent class
        parent::__construct();
        $this->load->model('token_model', 'token');
        $this->load->model('user_model', 'user');

        date_default_timezone_set("UTC");
    }

    /*
     *  Http Request Parameters Process for Application/JSON and Plain/Text
     *
     *  @author     Li liaoMing, liliaoming56@outlook.com
     *  @date       12/6/2015
     *  @version    1.0
     */

    public function head($key = NULL, $xss_clean = NULL)
    {
        $this->_head_args = $this->input->request_headers();
        
        return parent::head($key, $xss_clean);
    }

    public function get($key = NULL, $xss_clean = NULL)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
        {
            $this->_get_args = (array)json_decode(str_replace("%22", '"', $_SERVER['QUERY_STRING']));
        }

        return parent::get($key, $xss_clean);
    }

    // public function post($key = NULL, $xss_clean = NULL)
    // {
    //     return parent::post($key = NULL, $xss_clean = NULL);
    // }
    
    // public function put($key = NULL, $xss_clean = NULL)
    // {
    //     return parent::put($key, $xss_clean);   
    // }

    public function delete($key = NULL, $xss_clean = NULL)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
        {
            $this->_delete_args = (array)json_decode($this->input->raw_input_stream);
        }
        
        return parent::delete($key, $xss_clean);   
    }

    /*
     *  Validator Generation
     *
     *  @author     Li liaoMing, liliaoming56@outlook.com
     *  @date       12/6/2015
     *  @version    1.0
     */
    protected function new_validator($arr) 
    {
        return new Valitron\Validator($arr);
    }

    /*
     *  Http Request Authentication Check
     *
     *  @author     Li liaoMing, liliaoming56@outlook.com
     *  @date       12/6/2015
     *  @version    1.0
     */
    protected function check_auth() 
    {
        $v = $this->new_validator($this->head());
        $v->rule('required', ['Token']);

        if($v->validate())
        {
            // if ($this->head('token') == "free")
            // {
            //     $this->current_user['uid'] = 1;
            //     return true;
            // }
            
            $tokens = $this->token->get(null, $this->head('Token'));
            if (count($tokens) > 0)
            {
                $this->current_user['uid'] = $tokens[0]->uid;
                $buf_user = $this->user->get($tokens[0]->uid)[0];
                $this->current_user['username'] = $buf_user->username;
                $this->current_user['qb_id'] = $buf_user->qb_id;
                $this->current_user['email'] = $buf_user->email;
                $this->current_user['qb_token'] = $this->update_qb_token($this->current_user['uid']);

                return true;
            }
        }

        $this->response([
                'status' => 'fail', // "success", "fail", "not available", 
                'message' => 'This is not authenticated! Invalid token!',
                'result' => null
            ], REST_Controller::HTTP_OK);  

        return false;
    }

    protected function update_qb_token($uid) 
    {
        $qb_token = null;
        $user = $this->user->get($uid)[0];
        $tokens = $this->token->get($uid);

        if (count($tokens) > 0)
        {
            // $diff_sec = abs(strtotime($tokens[0]->updated_at) - strtotime(date('Y-m-d H:i:s')));
            // $diff_hour = round($diff_sec/(60 * 60), 2);
            // if ($diff_hour > 1)
            // {
                $result = $this->qb->signinUser( $user->username );

                if (isset($result->errors))
                    return null;
                
                $qb_token = $result->session->token;

                $new_one = array (
                    'id' => $tokens[0]->id,
                    'qb_token' => $qb_token,
                    'updated_at' => date("Y-m-d H:i:s")
                );
                $this->token->update($new_one);
            // }
            // else
            // {
            //     $qb_token = $tokens[0]->qb_token;
            //     return 'dsjfkdjfd';
            //     // $new_one = array( 'id' => $tokens[0]->id, 'updated_at' => date('Y-m-d H:i:s'));
            //     // $this->token->update($new_one);
            // }
        }
        else
        {
            $result = $this->qb->signinUser( $user->username );
            if (isset($result->errors))
                return null;

            $qb_token = $result->session->token;


            $new_one = array (
                'uid' => $uid,
                'token' => md5(time()),
                'qb_token' => $qb_token,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->token->insert($new_one);
        }
        return $qb_token;
    }
}
