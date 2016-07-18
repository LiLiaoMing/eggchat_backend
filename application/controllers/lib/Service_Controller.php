<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . './libraries/REST_Controller.php';
require APPPATH . './libraries/QBconfig.php';
require APPPATH . './libraries/QBhelper.php';
require 'Validator.php';
require 'Authentication.php';
require 'apidoc_define.php';

class Service_Controller extends REST_Controller {

    public $qb;

    function __construct()
    {
        $this->qb = new QBhelper();

        // Construct the parent class
        parent::__construct();
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
        $v->rule('required', ['authentication']);

        if($v->validate())
            if (Authentication::check($this->head('authentication')))
                return true;

        $this->response([
                'status' => 'fail', // "success", "fail", "not available", 
                'message' => 'This is not authenticated!',
                'result' => null
            ], REST_Controller::HTTP_UNAUTHORIZED);  

        return false;
    }
}
