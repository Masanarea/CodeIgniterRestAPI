<?php
require APPPATH . "libraries/REST_Controller.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
class Branch extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model("api/branch_model");
        $this->load->helper("security");
    }

    public function create_post() {
        $data = json_decode(file_get_contents("php://input"));
        if(isset($data->name)){
            $branch_name = $this->security->xss_clean($data->name);
            $branch_data = [
                "name" => $branch_name,
            ];
            if($this->branch_model->create($branch_data)){
                $this->response([
                    "status" => 1,
                    "message" => "branch has been created successfully"
                ], parent::HTTP_OK);
            }else{
                $this->response([
                    "status" => 0,
                    "message" => "failed to create branch"
                ], parent::HTTP_OK);
            }
        }else{
            $this->response([
                "status" => 0,
                "message" => "Branch name should be needed"
            ], parent::HTTP_NOT_FOUND);
        }
    }

    public function list_get() {
        $branch_list = $this->branch_model->get_all_branch();
    if(count($branch_list) > 0){
        $this->response([
            "status" => 1,
            "message" => "branch list",
            "data" => $branch_list
        ], parent::HTTP_NOT_FOUND);
    }else{
        $this->response([
            "status" => 0,
            "message" => "no data found"
        ], parent::HTTP_NOT_FOUND);
    }
    }

    public function delete_branch_delete() {
        $data = json_decode(file_get_contents("php://input"));
        if (isset($data->branch_id)) {
            $branch_data = [
                "name" => $data->branch_id,
            ];
            if ($this->branch_model->delete_branch($data->branch_id)) {
                $this->response([
                    "status" => 1,
                    "message" => "branch has been successfully delete"
                ], parent::HTTP_OK);
            } else {
                $this->response([
                    "status" => 0,
                    "message" => "failed to delete branch"
                ], parent::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $this->response([
                "status" => 0,
                "message" => "Branch is needed"
            ], parent::HTTP_NOT_FOUND);
        }
    }
    public function index() {
        echo "working..";
    }
}