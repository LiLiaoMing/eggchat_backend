<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require 'lib/Service_Controller.php';

class User extends Service_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // $this->load->model('user_model', 'user');
    }
    
    /**
     * @apiDefine SignupResponse
     * @apiSuccess {String}     status              Status of the API call.
     * @apiSuccess {String}     message             Description of the API call status.
     * @apiSuccess {Number}     code                Code (200: success, 501 : duplicated error.)
     */

    /**
     * @api {post} /user/ Signup
     * @apiVersion 0.1.0
     * @apiName Signup
     * @apiGroup User
     *
     * @apiParam {String} username         <code>mandatory</code> Username
     * @apiParam {String} password         <code>mandatory</code> Password
     * @apiParam {String} mobile           <code>mandatory</code> Mobile number
     * @apiParam {String} email            <code>mandatory</code> User email
     * @apiParam {Number} level             <code>mandatory</code>  User type (Ex: 1-Core, 2-Client, 3-Profile, 4-User, 5-Normal)
     * @apiParam {String} avatar           <code>optional</code>  avatar image url
     * @apiParam {String} full_name        <code>optional</code>  User full name
     * @apiParam {String} reply_email      <code>optional</code>  Reply email
     * @apiParam {Number} max_per_group    <code>optional</code>  Max member limit per group
     * @apiParam {Number} max_circulate    <code>optional</code>  Max number limit to circulate at a time
     * @apiParam {String} department       <code>optional</code>  Department
     * @apiParam {String} pri_contact      <code>optional</code>  Primary contact
     * @apiParam {String} pri_contact_no   <code>optional</code>  Primary contact no
     * @apiParam {String} note             <code>optional</code>  Note
     * @apiParam {Number} max_member       <code>optional</code>  Max member limit to create
     * @apiParam {Number} max_group        <code>optional</code>  Max group limit to create
     * @apiParam {Date} start_date         <code>optional</code>  Start date
     * @apiParam {Date} expiry_date        <code>optional</code>  Expiry date
     * @apiParam {Date} path               <code>optional</code>  Parent tree path
     * @apiParam {Date} permission         <code>optional</code>  Permission
     *
     * @apiUse SignupResponse
     */
    public function index_post()
    {
?
    }


    /**
     * @apiDefine LoginResponse
     * @apiSuccess {Object[]}   data                Result of the API call.
     * @apiSuccess {Object}     .user               User profile data.
     * @apiSuccess {String}     .token              Authentication Token.
     * @apiSuccess {String}     .qb_token           QB-Token.
     */

    /**
     * @api {get} /user/login Login
     * @apiVersion 0.1.0
     * @apiName Login
     * @apiGroup User
     *
     * @apiHeader {String} username         <code>mandatory</code> username of the User.
     * @apiHeader {String} password         <code>mandatory</code> Password of the User.
     *
     * @apiUse LoginResponse
     */
    
    public function login_get() 
    {
        $v = $this->new_validator($this->head());
        $v->rule('required', ['username', 'password']);

        if ($v->validate())
        {

            $users = $this->user->get(null, $this->head('username'), $this->head('password'));
            if (count($users) == 0 )
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => "Invalid credential.",
                    'code'=> 504
                ], REST_Controller::HTTP_OK);
            }

            $result = $this->qb->signinUser($this->head('username'));
            if (isset($result->errors))
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => $result,
                    'code' => 501
                ], REST_Controller::HTTP_OK);
            }
            
            $qb_token = $result->session->token;

            $tokens = $this->token->get($users[0]->id);
            if (count($tokens) > 0)
            {
                $new_one = array (
                    'id' => $tokens[0]->id,
                    'token' => $tokens[0]->token,
                    'qb_token' => $qb_token,
                    'updated_at' => date("Y-m-d H:i:s")
                );
                $this->token->update($new_one);    
            }
            else
            {
                $new_one = array (
                    'uid' => $users[0]->id,
                    'token' => md5(time()),
                    'qb_token' => $qb_token,
                    'updated_at' => date("Y-m-d H:i:s")
                );
                $this->token->insert($new_one);    
            }
            

            unset($users[0]->password);
            $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => '',
                    'code' => 200,
                    'data' => array('user' => $users[0], 'token' => $new_one['token'], 'qb_token' => $new_one['qb_token'])
                ], REST_Controller::HTTP_OK); 
        }
        else
        {
            $this->response([
                'status' => 'fail', // "success", "fail", "not available", 
                'message' => $v->errors(),
                'data' => null
            ], REST_Controller::HTTP_OK);
        }
    }
}
