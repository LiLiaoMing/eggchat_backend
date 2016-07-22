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
     * @apiSuccess {Number}     code                Code (200: success)
     */

    /**
     * @api {post} /group/ Create
     * @apiVersion 0.1.0
     * @apiName CreateGroup
     * @apiGroup Group
     *
     * @apiParam {String} name           <code>mandatory</code> Group name
     * @apiParam {String} occupants_ids  <code>optional</code> Group occupants_ids (Ex: 55,558,12345)
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
            $qb_result = $this->qb->createGroup($this->current_user['qb_token'], 2, $this->post('name'), $this->post('occupants_ids'));

            if (isset($qb_result->errors))
            {
                $this->response([
                    'data' => $qb_result
                ], REST_Controller::HTTP_OK);
            }

            $new_one = array (
                'qb_id' => $qb_result->_id,
                'owner_id' => $this->current_user['uid'],
            );
            $new_user_id = $this->group->insert($new_one);
                
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
     * @apiDefine SearchGroupResponse
     * @apiSuccess {String}     status              Status of the API call.
     * @apiSuccess {String}     message             Description of the API call status.
     * @apiSuccess {Number}     code                Code (200: success)
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
            'data' => $this->group->search($this->current_user['uid'])
        ], REST_Controller::HTTP_OK);    
        
    }


    /**
     * @apiDefine UpdateGroupResponse
     * @apiSuccess {String}     status              Status of the API call.
     * @apiSuccess {String}     message             Description of the API call status.
     * @apiSuccess {Number}     code                Code (200: success)
     */

    /**
     * @api {put} /group/ Update(Join, Change group name)
     * @apiVersion 0.1.0
     * @apiName UpdateGroup
     * @apiGroup Group
     *
     * @apiParam {String} group_id       <code>mandatory</code> Group ID
     * @apiParam {String} name           <code>mandatory</code> Group name
     * @apiParam {String} occupants_ids  <code>optional</code> Group occupants_ids (Ex: 55,558,12345)
     *
     * @apiUse Authentication
     *
     * @apiUse UpdateGroupResponse
     */
    public function index_put()
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->put());
        $v->rule('required', ['group_id']);

        if ($v->validate())
        {
            if ( count($this->group->get($this->put('group_id'))) == 0)
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => 'Invalid group id',
                    'code' => 501
                ], REST_Controller::HTTP_OK);
            }

            $group = $this->group->get($this->put('group_id'))[0];
            if ($this->put('name'))
            {   
                if ($group->owner_id != $this->current_user['uid'])
                {
                    $this->response([
                        'status' => 'fail', // "success", "fail", "not available", 
                        'message' => 'You are not the owner of this group, cannot change name.'
                    ], REST_Controller::HTTP_OK);
                }
            }

            $qb_token = $this->update_qb_token($group->owner_id);

            $qb_result = $this->qb->updateGroup($qb_token, $this->put('group_id'), $this->put('name'), $this->put('occupants_ids'));

            if (isset($qb_result->errors))
            {
                $this->response([
                    'data' => $qb_result
                ], REST_Controller::HTTP_OK);
            }

            $this->response([
                'status' => 'success', // "success", "fail", "not available", 
                'message' => '',
                'code' => 200,
                'data' => $qb_result
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
}
