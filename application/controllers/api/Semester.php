<?php
require APPPATH . "libraries/REST_Controller.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

class Semester extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model("api/semester_model");
        $this->load->helper([
            "authorization",
            "jwt"
        ]);
    }

    public function create_project_post()
    {
        $data = json_decode(file_get_contents("php://input"));
        $headers = $this->input->request_headers();
        $token = $headers["Authorization"];
        try {
            $student_data = authorization::validateToken($token);
            if ($student_data == false) {
                $this->response([
                    "status" => 0,
                    "message" => "unauthorize token",
                ], parent::HTTP_UNAUTHORIZED);
            } else {
                $student_id = $student_data->data->id;
                if(isset($data->title) && isset($data->level) && isset($data->complete_days) && isset($data->semester)){
                    $project_array_data = [
                        "student_id" => $student_id,
                        "title" => $data->title,
                        "level" => $data->level,
                        "level" => isset($data->description) ? $data->description : "",
                        "complete_days" => $data->complete_days,
                        "semester" => $data->semester,
                    ];
                    if($this->semester_model->create_project($project_array_data)){
                        $this->response([
                            "status" => 1,
                            "message" => "project has been created",
                        ], parent::HTTP_OK);
                    }else{
                        $this->response([
                            "status" => 0,
                            "message" => "field to project"
                        ], parent::HTTP_NOT_FOUND);
                    }
                }else{
                    $this->response([
                        "status" => 0,
                        "message" => "all field are required"
                    ], parent::HTTP_NOT_FOUND);
                }
                $this->response([
                    "status" => 1,
                    "message" => "student data",
                    "data" => $student_data,
                    "student_id" => $student_id
                ], parent::HTTP_OK);
            }
        } catch (Exception $ex) {
            $this->response([
                "status" => 0,
                "message" => $ex->getMessage(),
            ], parent::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function projects_list_get()
    {
        $projects = $this->semester_model->get_all_projects();
        if(count($projects) > 0){
            $this->response([
                "status" => 1,
                "message" => "project found",
                "projects" => $projects,
            ], parent::HTTP_OK);
        }else{
            $this->response([
                "status" => 0,
                "message" => "no project found",
            ], parent::HTTP_NOT_FOUND);
        }
    }

    public function get_student_projects_get(){
        $headers = $this->input->request_headers();
        $token = $headers["Authorization"];
        try {
            $student_data = authorization::validateToken($token);
            if ($student_data == false) {
                $this->response([
                    "status" => 0,
                    "message" => "unauthorize accsess",
                ], parent::HTTP_UNAUTHORIZED);
            } else {
                $student_id = $student_data->data->id;
                $projects = $this->semester_model->get_student_projects($student_id);
                $this->response([
                    "status" => 1,
                    "message" => "project found",
                    "projects" => $projects,
                ], parent::HTTP_OK);
            }
        } catch (Exception $ex) {
            $this->response([
                "status" => 0,
                "message" => $ex->getMessage(),
            ], parent::HTTP_NOT_FOUND);
        }
    }

    public function delete_projects_delete(){
        $headers = $this->input->request_headers();
        $token = $headers["Authorization"];
        try {
            $student_data = authorization::validateToken($token);
            if ($student_data == false) {
                $this->response([
                    "status" => 0,
                    "message" => "unauthorize accsess",
                ], parent::HTTP_UNAUTHORIZED);
            } else {
                $student_id = $student_data->data->id;
                if($this->semester_model->delete_projects($student_id)){
                    $this->response([
                        "status" => 1,
                        "message" => "project has been deleted",
                    ], parent::HTTP_OK);
                }else{
                    $this->response([
                        "status" => 0,
                        "message" => "fail to delete projects",
                    ], parent::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch (Exception $ex) {
            $this->response([
                "status" => 0,
                "message" => $ex->getMessage(),
            ], parent::HTTP_NOT_FOUND);
        }
    }
}
