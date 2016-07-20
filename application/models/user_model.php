<?php

class User_model extends CI_Model {

	var $table;

	public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
    }

    function insert($new_one)
    {
        $this->db->insert($this->table, $new_one);
        return $this->db->insert_id();
    }

    function update($new_one)
    {
        $this->db->where('id', $new_one['id']);
        $this->db->update($this->table, $new_one);
        return ($this->db->affected_rows() != 1) ? false : true;
    }

    function get( $id = null,
        
                  $username = null,
                  $password = null,
                  $full_name = null,
                  $email = null,
                  $mobile = null,
                  $start_date = null,
                  $expiry_date = null,
                  $type = null,
                  $path = null,

                  $limit = null,
                  $offset = null)
    {
        $this->db->select('*');
        $this->db->from($this->table);

        if ($id)
            $this->db->where('id', $id);
        if ($username)
            $this->db->where('username', $username);
        if ($password)
            $this->db->where('password', $password);
        if ($full_name)
            $this->db->like('full_name', $full_name);
        if ($email)
            $this->db->where('email', $email);
        if ($mobile)
            $this->db->where('mobile', $mobile);
        if ($start_date)
            $this->db->where('start_date', $start_date);
        if ($expiry_date)
            $this->db->where('expiry_date', $expiry_date);
        if ($type)
            $this->db->where('type', $type);
        if ($path)
            $this->db->like('path', $path);


        if ($limit == null)
            $limit = PHP_INT_MAX;

        $this->db->limit($limit, $offset);       
        
        $query = $this->db->get();
        return $query->result();
    }

    function get_by_device_token( $device_token = null)
    {
        $this->db->select('users.*');
        $this->db->from('tokens');

        $this->db->join('users', 'tokens.user_id = users.id', 'left');
        $this->db->join('accounts', 'accounts.user_id = users.id', 'left');
        $this->db->join('devicetokens', 'devicetokens.account_id = accounts.id', 'left');

        if ($device_token)
            $this->db->like('devicetokens.device_token', $device_token);
        
        
        $query = $this->db->get();
        return $query->result();
    }

    function get_unique_name ($user_id)
    {
        $this->db->select('*');
        $this->db->from($this->table);

        if ($user_id)
            $this->db->where('id', $user_id);
                
        $query = $this->db->get();
        $users = $query->result();

        if (count($users) == 0)
            return null;
        else
            $users[0]->unique_name;
    }

    function get_player_id ($user_id)
    {
        $this->db->select('*');
        $this->db->from("devicetokens");

        $this->db->join('accounts', 'accounts.id = devicetokens.account_id', 'left');
        $this->db->join('users', 'accounts.user_id = users.id', 'left');

        if ($user_id)
            $this->db->where('users.id', $user_id);
                
        $query = $this->db->get();
        $devicetokens = $query->result();
        
        if (count($devicetokens) == 0)
            return null;
        else
            $devicetokens[0]->player_id;
    }

    
}