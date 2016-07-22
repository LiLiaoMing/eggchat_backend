<?php

class Token_model extends CI_Model {

    var $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'tokens';
    }

    function insert($new_one)
    {
        $this->db->insert($this->table, $new_one);
        return $this->db->insert_id();
    }

    function delete($uid=null, $token=null) 
    {
        if ($uid)
            $this->db->where('uid', $uid);
        if ($token)
            $this->db->where('token', $token);
        $this->db->delete($this->table);
        return ($this->db->affected_rows() != 1) ? false : true;
    }

    function update($new_one)
    {
        $this->db->where('id', $new_one['id']);
        $this->db->update($this->table, $new_one);
        return ($this->db->affected_rows() != 1) ? false : true;
    }

    function get( $uid = null,
                  $token = null,
                  $limit = null,
                  $offset = null)
    {
        $this->db->select('*');
        $this->db->from($this->table);

        if ($uid)
            $this->db->where('uid', $uid);
        if ($token)
            $this->db->where('token', $token);
        
        if ($limit == null)
            $limit = PHP_INT_MAX;

        $this->db->limit($limit, $offset);       
        
        $query = $this->db->get();
        return $query->result();
    }

}