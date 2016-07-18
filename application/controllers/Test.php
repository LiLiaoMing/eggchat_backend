<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require 'lib/Service_Controller.php';

class Test extends Service_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

    }
    public function index_get() 
    {
        $token = $this->qb->generateSession();

        $this->response([
            'status' => 'success',
            'message' => APPPATH,
            'data' => 'real data will be coming soon.',
            'token' => $token,
        ], REST_Controller::HTTP_BAD_REQUEST);       
    }

}
