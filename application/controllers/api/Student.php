<?php
require APPPATH . "libraries/REST_Controller.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

class Student extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("api/student_model");
        $this->load->helper([
            "authorization",
            "jwt"
        ]);
    }

    public function register_post()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (isset($data->name) && isset($data->email) && isset($data->branch_id) && isset($data->phone) && isset($data->password) && isset($data->gender)) {
            if(!empty($this->student_model->is_email_exists($data->email))){
                    $this->response([
                        "status" => 0,
                        "message" => " Email already has been registered"
                    ], parent::HTTP_NOT_FOUND);
                }else{
                $student_data = [
                    "name" => $data->name,
                    "email" => $data->email,
                    "phone" => $data->phone,
                    "branch_id" => $data->branch_id,
                    "password" => password_hash($data->password, PASSWORD_DEFAULT),
                    "gender" => $data->gender,
                ];
                if ($this->student_model->create_student($student_data)) {
                    $this->response([
                        "status" => 1,
                        "message" => " student has been successfully  created"
                    ], parent::HTTP_OK);
                } else {
                    $this->response([
                        "status" => 0,
                        "message" => "failed to delete student"
                    ], parent::HTTP_OK);
                }
            }
        } else {
            $this->response([
                "status" => 0,
                "message" => "all field are needed"
            ], parent::HTTP_NOT_FOUND);
        }
    }

    public function list_get()
    {
        $data = $this->student_model->students_list();
        if(count($data) > 0){
            $this->response([
                "status" => 1,
                "message" => "student list",
                "data" => $data
            ], parent::HTTP_OK);
        }
    }

    public function update_details_put(){
        $data = json_decode(file_get_contents("php://input"));
        if (isset($data->id) && isset($data->name) && isset($data->email) && isset($data->phone) && isset($data->branch_id) && isset($data->gender)) {
            $student_data = [
                "name" => $data->name,
                "email" => $data->email,
                "phone" => $data->phone,
                "branch_id" => $data->branch_id,
                "gender" => $data->gender,
            ];
            if($this->student_model->update_student($data->id, $student_data)){
                $this->response([
                    "status" => 1,
                    "message" => "student has been successfully updated",
                ], parent::HTTP_OK);
            }else{
                $this->response([
                    "status" => 0,
                    "message" => "fail to update student data",
                ], parent::HTTP_INTERNAL_SERVER_ERROR);
            }
        }else{
            $this->response([
                "status" => 0,
                "message" => "all field are needed",
            ], parent::HTTP_NOT_FOUND);
        }
    }

    public function delete_student_delete(){
        $data = json_decode(file_get_contents("php://input"));
        if(isset($data->id)){
            if($this->student_model->find_by_id($data->id)){
                if(!empty($this->student_model->delete_student($data->id))){
                    $this->response([
                        "status" => 1,
                        "message" => "student has been deleted",
                    ], parent::HTTP_OK);
                }else{
                    $this->response([
                        "status" => 0,
                        "message" => "fail to delete student",
                    ], parent::HTTP_INTERNAL_SERVER_ERROR);
                }
            }else{
                $this->response([
                    "status" => 0,
                    "message" => "student does not exist",
                ], parent::HTTP_NOT_FOUND);
            }
        }else{
            $this->response([
                "status" => 0,
                "message" => "student id should be needed",
            ]);
        }
    }

    public function login_post(){
        $data = json_decode(file_get_contents("php://input"));
        if(isset($data->email) && isset($data->password)){
            $email = $data->email;
            $password = $data->password;
            if(!empty($this->student_model->is_email_exists($email))){
                $student_details = $this->student_model->is_email_exists($email);
                if(!empty($student_details)){
                    if(password_verify($password, $student_details->password)){
                        $token = authorization::generateToken((array)$student_details);
                        $this->response([
                            "status" => 1,
                            "message" => "login successfully",
                            "token" => $token,
                        ], parent::HTTP_OK);
                    }else{
                        $this->response([
                            "status" => 0,
                            "message" => "password does not match",
                        ], parent::HTTP_NOT_FOUND);
                    }
                }
            }else{
                $this->response([
                    "status" => 0,
                    "message" => "email address not exist",
                ], parent::HTTP_NOT_FOUND);
            }
        }else{
            $this->response([
                "status" => 0,
                "message" => "login details needed",
            ], parent::HTTP_NOT_FOUND);
        }
    }

    public function student_details_get(){
        $headers = $this->input->request_headers();
        $token = $headers["Authorization"];
        try{
            $student_data = authorization::validateToken($token);
            if($student_data == false){
                $this->response([
                    "status" => 0,
                    "message" => "authorize accsess",
                ], parent::HTTP_UNAUTHORIZED);
            }else{
                $student_id = $student_data->data->id;
                $this->response([
                    "status" => 1,
                    "message" => "student data",
                    "data" => $student_data,
                    "student_id" => $student_id
                ], parent::HTTP_OK);
            }
        }catch(Exception $ex){
            $this->response([
                "status" => 0,
                "message" => $ex->getMessage(),
            ], parent::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
