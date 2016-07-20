<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require 'lib/Service_Controller.php';

class Group extends Service_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        $this->load->model('group_model', 'group');
    }
    
    /**
     * @apiDefine CreateGroupResponse
     * @apiSuccess {String}     status              Status of the API call.
     * @apiSuccess {String}     message             Description of the API call status.
     * @apiSuccess {Number}     code                Code (200: success, 501 : duplicated error.)
     */

    /**
     * @api {post} /group/ Create
     * @apiVersion 0.1.0
     * @apiName CreateGroup
     * @apiGroup Group
     *
     * @apiParam {String} name           <code>mandatory</code> Group name
     *
     * @apiUse Authentication
     *
     * @apiUse CreateGroupResponse
     */
    public function index_post()
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->post());
        $v->rule('required', ['name']);
        
        if ($v->validate())
        {
            $result = $this->qb->createGroup($this->qb_token, 2, $this->post('name'), $this->uid);

            $this->response([
                'data' => $result
            ], REST_Controller::HTTP_CREATED);    

            $new_one = array (
                'qb_id' => null,
                'owner_id' => $this->uid
            );
            $new_user_id = $this->group->insert($new_one);
                
            $this->response([
                'status' => 'success', // "success", "fail", "not available", 
                'message' => '',
                'code' => 200,
                'data' => $new_one
            ], REST_Controller::HTTP_CREATED);    
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
     * @apiDefine SearchGroupResponse
     * @apiSuccess {String}     status              Status of the API call.
     * @apiSuccess {String}     message             Description of the API call status.
     * @apiSuccess {Number}     code                Code (200: success, 501 : duplicated error.)
     * @apiSuccess {Object[]}   groups              Array of group IDs
     */

    /**
     * @api {get} /group/ Search
     * @apiVersion 0.1.0
     * @apiName SearchGroup
     * @apiGroup Group
     *
     * @apiUse Authentication
     *
     * @apiUse SearchGroupResponse
     */
    public function index_get()
    {
        if ($this->check_auth() == false)
            return;

        $this->response([
            'status' => 'success', // "success", "fail", "not available", 
            'message' => '',
            'code' => 200,
            'data' => $this->group->search($this->uid)
        ], REST_Controller::HTTP_CREATED);    
        
    }
}
