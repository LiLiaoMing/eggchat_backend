<?php

class Group_model extends CI_Model {

	var $table;

	public function __construct()
    {
        parent::__construct();
        $this->table = 'groups';
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
                  $email = null,
                  $password = null,
                  $unique_name = null,
                  $limit = null,
                  $offset = null)
    {
        $this->db->select('*');
        $this->db->from($this->table);

        if ($id)
            $this->db->where('id', $id);
        if ($email)
            $this->db->where('email', $email);
        if ($password)
            $this->db->where('password', $password);
        if ($unique_name)
            $this->db->where('unique_name', $unique_name);
        
        if ($limit == null)
            $limit = PHP_INT_MAX;

        $this->db->limit($limit, $offset);       
        
        $query = $this->db->get();
        return $query->result();
    }

    function search ( $uid, $limit = null, $offset = null)  
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id', $uid);       
        $current_user = $this->db->get()->result()[0];

        
        $this->db->select('*');
        $this->db->from('groups');
        $this->db->join('users', 'groups.owner_id = users.id', 'left');

        $this->db->where('groups.owner_id', $uid);
        $this->db->or_like('users.path', '.'.$uid.'.');
        if ($current_user->level == 4)
            $this->db->or_where('users.path', $current_user->path);
        if ($current_user->level == 5)
            $this->db->or_where('users.level', 5);

        if ($limit == null)
            $limit = PHP_INT_MAX;

        $this->db->limit($limit, $offset);       
        $query = $this->db->get();
        return $query->result();
    }
}