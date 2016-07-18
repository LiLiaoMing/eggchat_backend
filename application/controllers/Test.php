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
        $this->response([
            'status' => 'success', // "success", "fail", "not available", 
            'message' => 'this is testing message',
            'data' => 'real data will be coming soon.'
        ], REST_Controller::HTTP_BAD_REQUEST);       
    }

}
