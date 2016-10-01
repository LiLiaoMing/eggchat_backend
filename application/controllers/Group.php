<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'lib/Service_Controller.php';

class Group extends Service_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        $this->load->model('group_model', 'group');
        $this->load->model('user_model', 'user');
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
     * @apiParam {Number} owner_id       <code>optional</code> This is for website. (owner_id)
     * @apiParam {String} email          <code>optional</code> Group email
     * @apiParam {String} public         <code>optional</code> Group public status (default : 1 , private: 0)
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
        $v->rule('email', ['email']);
        $v->rule('numeric', ['public', 'owner_id']);
        
        if ($v->validate())
        {
            $qb_token = null;
            $owner_id = null;

            if ($this->post('owner_id'))
            {
                $qb_token = $this->update_qb_token($this->post('owner_id'));
                $owner_id = $this->post('owner_id');
            }
            else
            {
                $qb_token = $this->current_user['qb_token'];
                $owner_id = $this->current_user['uid'];
            }

            $users = $this->user->get($owner_id);
            $groups = $this->group->search_profile_groups($users[0]->path, $this->post('name'));

            if (count($groups) > 0)
            {
                $this->response([
                    'status' => 'fail',  
                    'message' => 'Cannot create group with this name. Duplicated!'
                ], REST_Controller::HTTP_OK);
            }

            $qb_result = $this->qb->createGroup($qb_token, 2, $this->post('name'), $this->post('occupants_ids'));

            if (isset($qb_result->errors))
            {
                $this->response([
                    'data' => $qb_result
                ], REST_Controller::HTTP_OK);
            }

            $new_one = array (
                'qb_id' => $qb_result->_id,
                'owner_id' => $owner_id,
                'occupants_ids' => json_encode($qb_result->occupants_ids),
                'name' => $this->post('name'),
                'email' => $this->post('email'),
                'public' => $this->post('public'),
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
     * @apiSuccess {Object[]}   groups              Array of groups
     */

    /**
     * @api {get} /group/ Search
     * @apiVersion 0.1.0
     * @apiName SearchGroup
     * @apiGroup Group
     *
     * @apiParam {String} sort_field       <code>optional</code> Sort field.
     * @apiParam {String} sort_method      <code>optional</code> Sort method(asc, desc).
     * @apiParam {Number} public           <code>optional</code> Public.
     * @apiParam {String} path             <code>optional</code> Path.
     * @apiParam {String} offset           <code>optional</code> Offset.
     * @apiParam {String} amount           <code>optional</code> Amount per a page.
     * @apiParam {Number} from_mobile      <code>optional</code> Setting for from-mobile.
     *
     * @apiUse Authentication
     *
     * @apiUse SearchGroupResponse
     */
    public function index_get()
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->get());
        $v->rule('integer', ['public', 'offset', 'amount', 'from_mobile']);

        if ($v->validate())
        {
            // if ($this->get('from_mobile'))
            // {
                $this->response('here', REST_Controller::HTTP_OK);
            //     $this->response([
            //                     'status' => 'success', // "success", "fail", "not available", 
            //                     'message' => '',
            //                     'code' => 200,
            //                     'data' => [
            //                         'result'=>$this->group->search_for_mobile(
            //                                         $this->get('amount'),
            //                                         $this->get('offset')
            //                                         ),
                                    
            //                         'count'=>$this->group->search_count_for_mobile()
            //                         ]
            //                 ], REST_Controller::HTTP_OK);    
            // }
            // else
            // {
            //     $this->response([
            //                     'status' => 'success', // "success", "fail", "not available", 
            //                     'message' => '',
            //                     'code' => 200,
            //                     'data' => [
            //                         'result'=>$this->group->search(
            //                                         $this->current_user['uid'], 
            //                                         $this->get('public'), 
            //                                         $this->get('path'),
            //                                         $this->get('sort_field'), 
            //                                         $this->get('sort_method'), 
            //                                         $this->get('amount'),
            //                                         $this->get('offset')
            //                                         ),
                                    
            //                         'count'=>$this->group->search_count(
            //                                         $this->current_user['uid'], 
            //                                         $this->get('public'),
            //                                         $this->get('path')
            //                                         )
            //                         ]
            //                 ], REST_Controller::HTTP_OK);    
            // }    
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
     * @apiParam {String} group_qbid     <code>mandatory</code> Group ID
     * @apiParam {String} name           <code>optional</code> Group name
     * @apiParam {String} occupants_ids  <code>optional</code> Group occupants_ids (Ex: 55,558,12345)
     * @apiParam {String} email          <code>optional</code> Group email
     * @apiParam {String} public         <code>optional</code> Group public status (default : 1 , private: 0)
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
        $v->rule('required', ['group_qbid']);
        $v->rule('email', ['email']);
        $v->rule('numeric', ['public', 'owner_id']);

        if ($v->validate())
        {
            if ( count($this->group->get($this->put('group_qbid'))) == 0)
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => 'Invalid group id',
                    'code' => 501
                ], REST_Controller::HTTP_OK);
            }



            $group = $this->group->get($this->put('group_qbid'))[0];
            // if ($this->put('name'))
            // {   
            //     if ($group->owner_id != $this->current_user['uid'])
            //     {
            //         $this->response([
            //             'status' => 'fail', // "success", "fail", "not available", 
            //             'message' => 'You are not the owner of this group, cannot change name.'
            //         ], REST_Controller::HTTP_OK);
            //     }
            // }

            $qb_token = $this->update_qb_token($group->owner_id);
            // $this->response([
            //     'data' => $qb_token
            // ], REST_Controller::HTTP_OK);


            $qb_result = $this->qb->updateGroup($qb_token, $this->put('group_qbid'), $this->put('name'), $this->put('occupants_ids'));

            if (isset($qb_result->errors))
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => $qb_result->errors,
                    'code' => 501,
                    // 'data' => $qb_result
                ], REST_Controller::HTTP_OK);    
            }

            $new_one = array ('id' => $group->id);
            if ($this->put('name') != null)
                $new_one['name'] = $this->put('name');
            if ($this->put('email') != null)
                $new_one['email'] = $this->put('email');
            if ($this->put('public') != null)
                $new_one['public'] = $this->put('public');
            $new_one['occupants_ids'] = json_encode($qb_result->occupants_ids);

            if ($this->group->update($new_one) == true)
            {
                $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => '',
                    'code' => 200,
                    // 'data' => $qb_result
                ], REST_Controller::HTTP_OK);    
            }
            else
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => 'No id or No new field value to update'
                ], REST_Controller::HTTP_BAD_REQUEST);        
            }
            
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
     * @api {put} /group/omit Remove Member From Group
     * @apiVersion 0.1.0
     * @apiName OmitGroupMember
     * @apiGroup Group
     *
     * @apiParam {String} group_qbid     <code>mandatory</code> Group ID
     * @apiParam {String} occupants_ids  <code>optional</code> Group occupants_ids (Ex: 55,558,12345)
     *
     * @apiUse Authentication
     *
     * @apiUse UpdateGroupResponse
     */
    public function omit_put()
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->put());
        $v->rule('required', ['group_qbid']);

        if ($v->validate())
        {
            if ( count($this->group->get($this->put('group_qbid'))) == 0)
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => 'Invalid group id',
                    'code' => 501
                ], REST_Controller::HTTP_OK);
            }



            $group = $this->group->get($this->put('group_qbid'))[0];
            // if ($this->put('name'))
            // {   
            //     if ($group->owner_id != $this->current_user['uid'])
            //     {
            //         $this->response([
            //             'status' => 'fail', // "success", "fail", "not available", 
            //             'message' => 'You are not the owner of this group, cannot change name.'
            //         ], REST_Controller::HTTP_OK);
            //     }
            // }

            $users = $this->user->get($group->owner_id);
            if (strpos($this->put('occupants_ids'), $users[0]->qb_id) !== false)
            {
                $this->response([
                    'status' => 'false', // "success", "fail", "not available", 
                    'message' => "Group owner can't be removed out of its group.",
                    'code' => 501,
                    // 'data' => $qb_result
                ], REST_Controller::HTTP_OK);    
            }

            $qb_token = $this->update_qb_token($group->owner_id);
            // $this->response([
            //     'data' => $qb_token
            // ], REST_Controller::HTTP_OK);


            $qb_result = $this->qb->removeGroupMember($qb_token, $this->put('group_qbid'), $this->put('occupants_ids'));

            if (isset($qb_result->errors))
            {
                $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => '',
                    'code' => 200,
                    // 'data' => $qb_result
                ], REST_Controller::HTTP_OK);    
            }

            $new_one = array ('id' => $group->id);
            $new_one['occupants_ids'] = json_encode($qb_result->occupants_ids);

            if ($this->group->update($new_one) == true)
            {
                $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => '',
                    'code' => 200,
                    // 'data' => $qb_result
                ], REST_Controller::HTTP_OK);    
            }
            else
            {
                $this->response([
                    'status' => 'fail', // "success", "fail", "not available", 
                    'message' => 'No id or No new field value to update'
                ], REST_Controller::HTTP_BAD_REQUEST);        
            }
            
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
     * @apiDefine GroupUsers
     * @apiSuccess {Object[]}   users               Result of the API call.
     */

    /**
     * @api {get} /group/users -(admin) Group Users
     * @apiVersion 0.1.0
     * @apiName GroupUsers
     * @apiGroup Group
     *
     * @apiParam {Number} qb_id            <code>mandatory</code> Group QB ID.
     * @apiParam {String} sort_field       <code>optional</code> Sort field.
     * @apiParam {String} sort_method      <code>optional</code> Sort method(asc, desc).
     * @apiParam {String} offset           <code>optional</code> Offset.
     * @apiParam {String} amount           <code>optional</code> Amount per a page.
     *
     * @apiUse Authentication
     *
     * @apiUse SearchResponse
     */
    
    public function users_get() 
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->get());
        $v->rule('required', ['qb_id']);
        $v->rule('integer', ['offset', 'amount']);

        if ($v->validate())
        {
            
            $group = $this->group->get($this->get('qb_id'))[0];
            $qb_token = $this->update_qb_token($group->owner_id);

            $occupants_ids = $this->qb->getGroup($qb_token, $this->get('qb_id'))->items[0]->occupants_ids;

            $offset = 0;
            $total_amount = count($occupants_ids);
            $amount = $total_amount;
            if ($this->get('offset') != null)
                $offset = $this->get('offset');
            if ($this->get('amount') != null)
                $amount = $this->get('amount');

            $occupants_ids = array_slice($occupants_ids, $offset, $amount);

            $users = array();

            for($i=0; $i<count($occupants_ids); $i++)
            {
                $users[] = $this->user->get_by_qbid($occupants_ids[$i])[0];
            }


            $GLOBALS['sort_field'] = $this->get('sort_field');
            $GLOBALS['sort_method'] = $this->get('sort_method');                

            function cmp($a, $b) //use ($sort_field, $sort_method)
            {

                $property_name = $GLOBALS['sort_field'];
                if ( $GLOBALS['sort_method'] == 'asc')
                    return strcmp($a->{$property_name}, $b->{$property_name});
                else
                    return !strcmp($a->{$property_name}, $b->{$property_name});

            }
            
            usort($users, "cmp");


            $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => '',
                    'data' => [
                        'result'=>$users,
                        'count'=>$total_amount
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
     * @api {get} /group/circulate - Circulate
     * @apiVersion 0.1.0
     * @apiName Circulate
     * @apiGroup Group
     *
     * @apiParam {String} qb_ids           <code>mandatory</code> User IDs.(Ex: 55,558,12345)
     * @apiParam {String} message          <code>mandatory</code> Circulate message.
     *
     * @apiUse Authentication
     *
     * @apiUse SearchResponse
     */
    
    public function circulate_get() 
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->get());
        $v->rule('required', ['qb_ids', 'message']);
        $v->rule('integer', ['offset', 'amount']);

        if ($v->validate())
        {
            $qb_ids = explode(',', $this->get('qb_ids'));

            $qb_token = $this->current_user['qb_token'];
            
            for($i=0; $i < count($qb_ids); $i++)
            {
                $qb_chat_dialog_id = $this->qb->createGroup($qb_token, 3, null, $qb_ids[$i])->_id;
                $qb_result = $this->qb->createCirculate($qb_token, $qb_chat_dialog_id, $qb_ids[$i], $this->get('message'));

            }
            
            $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => ''
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
     * @api {get} /group/gcirculate - Circulate(Group)
     * @apiVersion 0.1.0
     * @apiName GroupCirculate
     * @apiGroup Group
     *
     * @apiParam {String} qb_ids         <code>mandatory</code> Group IDs.(Ex: 55,558,12345)
     * @apiParam {String} message        <code>mandatory</code> Circulate message.
     *
     * @apiUse Authentication
     *
     * @apiUse SearchResponse
     */
    
    public function gcirculate_get() 
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->get());
        $v->rule('required', ['qb_ids', 'message']);
        $v->rule('integer', ['offset', 'amount']);

        if ($v->validate())
        {
            $qb_ids = explode(',', $this->get('qb_ids'));

            for($i=0; $i < count($qb_ids); $i++)
            {
                $group = $this->group->get($qb_ids[$i])[0];
                $qb_token = $this->update_qb_token($group->owner_id);

                $occupants_ids = $this->qb->getGroup($qb_token, $qb_ids[$i])->items[0]->occupants_ids;

                $qb_token = $this->current_user['qb_token'];
                for($j=0; $j < count($occupants_ids); $j++)
                {
                    $qb_chat_dialog_id = $this->qb->createGroup($qb_token, 3, null, $occupants_ids[$j])->_id;
                    $qb_result = $this->qb->createCirculate($qb_token, $qb_chat_dialog_id, $occupants_ids[$j], $this->get('message'));

                }
            }

            $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => ''
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
     * @api {get} /group/reply - Circulate Reply
     * @apiVersion 0.1.0
     * @apiName CirculateReply
     * @apiGroup Group
     *
     * @apiParam {String} circulater_id  <code>mandatory</code> Circulater ID(qb_id).
     * @apiParam {String} message        <code>mandatory</code> Circulate message.
     *
     * @apiUse Authentication
     *
     * @apiUse SearchResponse
     */
    
    public function reply_get() 
    {
        if ($this->check_auth() == false)
            return;

        $v = $this->new_validator($this->get());
        $v->rule('required', ['circulater_id', 'message']);

        if ($v->validate())
        {

            $buf_user = $this->user->get_by_qbid($this->get('circulater_id'))[0];
            
            $result = $this->qb->sendViaMailgun($this->current_user['email'],
                $buf_user->email,
                'Circulate Reply',
                $this->get('message'));

            $this->response([
                    'status' => 'success', // "success", "fail", "not available", 
                    'message' => $result
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
}
