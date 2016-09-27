<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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

    function get( $qb_id = null,
                  $owner_id = null,
                  $limit = null,
                  $offset = null)
    {
        $this->db->select('*');
        $this->db->from($this->table);

        if ($qb_id)
            $this->db->where('qb_id', $qb_id);
        if ($owner_id)
            $this->db->where('owner_id', $owner_id);
        
        if ($limit == null)
            $limit = PHP_INT_MAX;

        $this->db->limit($limit, $offset);       
        
        $query = $this->db->get();
        return $query->result();
    }

    function search ( $uid, $public, $path, $sort_field, $sort_method, $limit = null, $offset = null)  
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id', $uid);       
        $current_user = $this->db->get()->result()[0];

        
        $this->db->select('groups.*');
        $this->db->from('groups');
        $this->db->join('users', 'groups.owner_id = users.id', 'left');

        $this->db->where('groups.owner_id', $uid);
        if ($path)
            $this->db->like('users.path', $path);

        $this->db->or_like('users.path', '.'.$uid.'.');
        if ($current_user->level == 4)
            $this->db->or_where('users.path', $current_user->path);
        if ($current_user->level == 5)
            $this->db->or_where('users.level', 5);
        
        if ($public)
            $this->db->where('groups.public', $public);

        if ($sort_field)
            $this->db->order_by($sort_field, $sort_method);

        if ($limit == null)
            $limit = PHP_INT_MAX;

        $this->db->limit($limit, $offset);       
        $query = $this->db->get();
        return $query->result();
    }

    function search_count ( $uid, $public, $path)  
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id', $uid);       
        $current_user = $this->db->get()->result()[0];

        
        $this->db->select('groups.*');
        $this->db->from('groups');
        $this->db->join('users', 'groups.owner_id = users.id', 'left');

        $this->db->where('groups.owner_id', $uid);
        if ($path)
            $this->db->like('users.path', $path);
        $this->db->or_like('users.path', '.'.$uid.'.');
        if ($current_user->level == 4)
            $this->db->or_where('users.path', $current_user->path);
        if ($current_user->level == 5)
            $this->db->or_where('users.level', 5);
        if ($public)
            $this->db->where('groups.public', $public);

        $query = $this->db->get();
        return $query->num_rows();
    }

    function get_count( $level = null )
    {
        $this->db->select('*');
        $this->db->from($this->table);

        $this->db->join('users', 'users.id = groups.owner_id', 'left');
        if ($level)
            $this->db->where('users.level', $level);


        $query = $this->db->get();
        return $query->num_rows();
    }

    function search_profile_groups ( $profile_path, $group_name)  
    {
        $this->db->select('groups.*');
        $this->db->from('groups');
        $this->db->join('users', 'groups.owner_id = users.id', 'left');

        $this->db->where('groups.name', $group_name);
        $this->db->where('users.path', $profile_path);

        $query = $this->db->get();
        return $query->result();
    }
}