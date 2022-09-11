<?php

class Student_model extends CI_Model{

    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

    public function create_student($student_data){
        return $this->db->insert("tbl_students", $student_data);
    }
    
    public function is_email_exists($email){
        $this->db->select("*");
        $this->db->from("tbl_students");
        $this->db->where("email", $email);
        $query = $this->db->get();
        return $query->row();
    }
    
    public function students_list(){
        // 全てタブナイン
        $this->db->select("student.*,branch.name as branch_name");
        $this->db->from("tbl_students as student");
        $this->db->join("tbl_branches as branch", "branch.id = student.branch_id", "inner");
        $this->db->order_by("id", "DESC");
        $query = $this->db->get();
        return $query->result();
    }

    public function update_student($student_id, $student_data){
        $this->db->where("id", $student_id);
        return $this->db->update("tbl_students", $student_data);
    }
    
    public function delete_student($student_id){
        $this->db->where("id", $student_id);
        return $this->db->delete("tbl_students");
    }
    
    public function find_by_id($student_id)
    {
        $this->db->select("*");
        $this->db->from("tbl_students");
        $this->db->where("id", $student_id);
        $query = $this->db->get();
        return $query->row();
    }
}
