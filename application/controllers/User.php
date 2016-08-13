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
     * @apiParam {String} email            <code>mandatory</code> User email
     * @apiParam {Number} level            <code>mandatory</code>  User type (Ex: 1-Core, 2-Client, 3-Profile, 4-User, 5-Normal)
     * @apiParam {String} mobile           <code>optional</code> Mobile number
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
     * @apiParam {Number} disabled         <code>optional</code>  Disable status
     *
     * @apiUse SignupResponse
     */
    public function index_post()
    {
        $v = $this->new_validator($this->post());
        $v->rule('required', ['username', 'email', 'password', 'level']);
        $v->rule('integer', ['level', 'max_per_group', 'max_circulate', 'max_member', 'max_group', 'disabled']);
        $v->rule('numeric', ['mobile']);
        $v->rule('email', ['email']);
        $v->rule('url', ['avatar']);
        $v->rule('date', ['start_date', 'expiry_date']);

        if ($v->validate())
        {
            /*******************************************************************************************
             * Insert new record to table(users) 
             *******************************************************************************************/
            if ( count($this->user->get(null, $this->post('username'))) > 0)
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => 'Invalid username, already used by another user.',
                    'code' => 501
                ], REST_Controller::HTTP_OK);
            }

            if ( count($this->user->get(null, null, null, null,  $this->post('email'))) > 0)
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => "Invalid email, already used by another user.",
                    'code' => 501
                ], REST_Controller::HTTP_OK);
            }

            if ( $this->post('mobile') && (count($this->user->get(null, null, null, null, null, $this->post('mobile'))) > 0))
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => 'Invalid mobile number, already used by another user.',
                    'code' => 501
                ], REST_Controller::HTTP_OK);
            }

            
            $new_one = array (
                'qb_id' => null,
                'username' => $this->post('username'),
                'mobile' => $this->post('mobile'),
                'password' => $this->post('password'),
                'email' => $this->post('email'),
                'full_name' => $this->post('full_name'),
                'avatar' => $this->post('avatar'),
                'reply_email' => $this->post('reply_email'),
                'max_per_group' => $this->post('max_per_group'),
                'max_circulate' => $this->post('max_circulate'),
                'department' => $this->post('department'),  
                'pri_contact' => $this->post('pri_contact'),
                'pri_contact_no' => $this->post('pri_contact_no'),
                'note' => $this->post('note'),
                'max_member' => $this->post('max_member'),
                'max_group' => $this->post('max_group'),
                'start_date' => $this->post('start_date'),
                'expiry_date' => $this->post('expiry_date'),
                'level' => $this->post('level'),
                'path' => $this->post('path'),
                'permission' => $this->post('permission'),
                'disabled' => $this->post('disabled')
            );
            $new_user_id = $this->user->insert($new_one);


            $qb_result = $this->qb->signupUser( $this->post('full_name'), 
                                   $this->post('username'),
                                   $this->post('email'), 
                                   $this->post('mobile'), 
                                   $this->post('avatar'),
                                   $new_user_id);
            
            if (isset($qb_result->errors))
            {
                $this->user->delete($new_user_id);
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => $qb_result->errors,
                    'code' => 501
                ], REST_Controller::HTTP_OK);
            }

            $update_one = array ('id' => $new_user_id, 'qb_id'=> $qb_result->user->id);
            $this->user->update($update_one);
            $new_one['qb_id'] = $update_one['qb_id'];

            unset($new_one['password']);
            $this->response([
                'status' => 'success', // "success", "fail", "not available", 
                'message' => '',
                'code' => 200,
                'data' => $new_one
            ], REST_Controller::HTTP_OK);    
        }
        else
        {
            $this->response([
                'status' => 'fail', // "success", "fail", "not available", 
                'message' => $v->errors()
            ], REST_Controller::HTTP_OK);
        }
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
            if ((count($users) == 0) || ($users[0]->disabled == "0") )
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

    /**
     * @apiDefine SearchResponse
     * @apiSuccess {Object[]}   users               Result of the API call.
     */

    /**
     * @api {get} /user/search -(admin) Search
     * @apiVersion 0.1.0
     * @apiName Search
     * @apiGroup User
     *
     * @apiParam {String} id               <code>optional</code> id of the User.
     * @apiParam {String} level            <code>optional</code> level of the User.
     * @apiParam {String} path             <code>optional</code> path of the User.
     * @apiParam {String} username         <code>optional</code> username of the User.
     * @apiParam {String} full_name        <code>optional</code> full name of the User.
     * @apiParam {String} email            <code>optional</code> email of the User.
     * @apiParam {String} mobile           <code>optional</code> mobile of the User.
     * @apiParam {String} offset           <code>optional</code> Offset.
     * @apiParam {String} amount           <code>optional</code> Amount per a page.
     *
     * @apiUse Authentication
     *
     * @apiUse SearchResponse
     */
    
    public function search_get() 
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->get());
        $v->rule('integer', ['id', 'level']);
        $v->rule('email', ['email']);
        $v->rule('numeric', ['mobile']);

        if ($v->validate())
        {
            $this->response([
                'status' => 'success', // "success", "fail", "not available", 
                'message' => '',
                'data' => [
                    'result'=>$this->user->get($this->get('id'), 
                                        $this->get('username'),
                                        null,
                                        $this->get('full_name'),
                                        $this->get('email'),
                                        $this->get('mobile'),
                                        null, null,
                                        $this->get('level'),
                                        $this->get('path'),
                                        $this->get('amount'),
                                        $this->get('offset')),
                    
                    'count'=>$this->user->get_count($this->get('id'),
                                        $this->get('username'),
                                        null,
                                        $this->get('full_name'),
                                        $this->get('email'),
                                        $this->get('mobile'),
                                        null, null,
                                        $this->get('level'),
                                        $this->get('path'))
                    ]
            ], REST_Controller::HTTP_OK);    
        }
        else
        {
            $this->response([
                'status' => 'fail', // "success", "fail", "not available", 
                'message' => $v->errors(),
                'data' => null
            ], REST_Controller::HTTP_BAD_REQUEST);  
        }
    }


    /**
     * @api {put} /user/ -(admin) Update
     * @apiVersion 0.1.0
     * @apiName  UpdateUserInfo
     * @apiGroup User
     *
     * @apiUse Authentication
     *
     * @apiParam {String} id               <code>mandatory</code> User ID
     * @apiParam {String} username         <code>optional</code> Username
     * @apiParam {String} password         <code>optional</code> Password
     * @apiParam {String} mobile           <code>optional</code> Mobile number
     * @apiParam {String} email            <code>optional</code> User email
     * @apiParam {Number} level            <code>optional</code>  User type (Ex: 1-Core, 2-Client, 3-Profile, 4-User, 5-Normal)
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
     * @apiParam {Number} disabled         <code>optional</code>  Disabled
     *
     * @apiUse Response
     *
     */
    public function index_put()
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->put());
        $v->rule('required', ['id']);
        $v->rule('integer', ['id', 'level', 'max_per_group', 'max_circulate', 'max_member', 'max_group', 'disabled']);
        $v->rule('numeric', ['mobile']);
        $v->rule('email', ['email']);
        $v->rule('url', ['avatar']);
        $v->rule('date', ['start_date', 'expiry_date']);
        
        if ($v->validate())
        {
            $users = $this->user->get($this->put('id'));
            
            $user = array();

            $new_one = array ('id' => $this->put('id'));

            if ($this->put('username') != null)
            {
                $user['login'] = $this->put('username');
                $new_one['username'] = $this->put('username');
            }
            if ($this->put('password') != null)
                $new_one['password'] = $this->put('password');
            if ($this->put('mobile') != null)
            {
                $user['phone'] = $this->put('mobile');
                $new_one['mobile'] = $this->put('mobile');
            }
            if ($this->put('email') != null)
            {
                $user['email'] = $this->put('email');
                $new_one['email'] = $this->put('email');
            }
            if ($this->put('level') != null)
                $new_one['level'] = $this->put('level');
            if ($this->put('avatar') != null)
            {
                $user['website'] = $this->put('avatar');
                $new_one['avatar'] = $this->put('avatar');
            }
            if ($this->put('full_name') != null)
            {
                $user['full_name'] = $this->put('full_name');
                $new_one['full_name'] = $this->put('full_name');
            }
            if ($this->put('reply_email') != null)
                $new_one['reply_email'] = $this->put('reply_email');
            if ($this->put('max_per_group') != null)
                $new_one['max_per_group'] = $this->put('max_per_group');
            if ($this->put('max_circulate') != null)
                $new_one['max_circulate'] = $this->put('max_circulate');
            if ($this->put('department') != null)
                $new_one['department'] = $this->put('department');
            if ($this->put('pri_contact') != null)
                $new_one['pri_contact'] = $this->put('pri_contact');
            if ($this->put('pri_contact_no') != null)
                $new_one['pri_contact_no'] = $this->put('pri_contact_no');
            if ($this->put('note') != null)
                $new_one['note'] = $this->put('note');
            if ($this->put('max_member') != null)
                $new_one['max_member'] = $this->put('max_member');
            if ($this->put('max_group') != null)
                $new_one['max_group'] = $this->put('max_group');
            if ($this->put('start_date') != null)
                $new_one['start_date'] = $this->put('start_date');
            if ($this->put('expiry_date') != null)
                $new_one['expiry_date'] = $this->put('expiry_date');
            if ($this->put('path') != null)
                $new_one['path'] = $this->put('path');
            if ($this->put('permission') != null)
                $new_one['permission'] = $this->put('permission');
            if ($this->put('disabled') != null)
                $new_one['disabled'] = $this->put('disabled');

            $qb_token = $this->update_qb_token($users[0]->id);

            $qb_result = $this->qb->updateUser( $qb_token, $users[0]->qb_id, $user);
            
            if (isset($qb_result->message))
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => $qb_result->message,
                    'code' => 501
                ], REST_Controller::HTTP_OK);
            }

            if ($this->user->update($new_one) == true)
            {
                $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => ''
                ], REST_Controller::HTTP_OK);        
            }
            else
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => 'No id('.$new_one['user_id'].') or No new field value to update'
                ], REST_Controller::HTTP_BAD_REQUEST);        
            }
        }
        else
        {
            $this->response([
                'status' => 'fail', // "success", "fail", "not available", 
                'message' => $v->errors()
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @api {get} /user/delete -(admin) Delete
     * @apiVersion 0.1.0
     * @apiName Delete
     * @apiGroup User
     *
     * @apiParam {String} id               <code>mandatory</code> id of the User.
     *
     * @apiUse Authentication
     *
     * @apiUse SignupResponse
     */
    
    public function delete_get() 
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->get());
        $v->rule('required', ['id']);
        $v->rule('integer', ['id']);

        if ($v->validate())
        {

            $users = $this->user->get($this->get('id'));
            $qb_token = $this->update_qb_token($users[0]->id);
            $qb_result = $this->qb->deleteUser( $qb_token, $users[0]->qb_id);
            
            if ($this->user->delete($this->get('id')) == true)
            {
                $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => ''
                ], REST_Controller::HTTP_OK);        
            }
            else
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => 'No id('.$new_one['user_id'].')'
                ], REST_Controller::HTTP_BAD_REQUEST);        
            } 
        }
        else
        {
            $this->response([
                'status' => 'fail', // "success", "fail", "not available", 
                'message' => $v->errors(),
                'data' => null
            ], REST_Controller::HTTP_BAD_REQUEST);  
        }
    }
}

