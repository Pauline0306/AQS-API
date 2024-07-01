<?php
require_once "./config/Connection.php";
require_once "./mainmodule/Get.php";
require_once "./mainmodule/Auth.php";
require_once "./mainmodule/Global.php";


    header('Access-Control-Allow-Origin:  http://localhost:4200');
    
    // Allow specific HTTP methods
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
    
    // Allow specific headers
    header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization');
    
    // Set Content-Type header to application/json for all responses
    header('Content-Type: application/json');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
        exit(0);
    }



$db = new Connection();
$pdo = $db->connect();
$global = new GlobalMethods($pdo);
$get = new Get($pdo);
$auth = new Auth($pdo);

    // Check if 'request' parameter is set in the request
    if (isset($_REQUEST['request'])) {
        // Split the request into an array based on '/'
        $req = explode('/', $_REQUEST['request']);
    } else {
        // If 'request' parameter is not set, return a 404 response
        echo json_encode(["error" => "Not Found"]);
        http_response_code(404);
        exit();
    }

switch($_SERVER['REQUEST_METHOD']){
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        switch($req[0]){
            
    case 'login':
                if (isset($data->email) && isset($data->password)) {
                    echo json_encode($auth->login($data->email, $data->password));
                } else {
                    echo json_encode([
                        'status' => 400,
                        'message' => 'Invalid input data'
                    ]);
                }
                break;

    case 'addInstructor':
                    // Handle add instructor request
                //echo json_encode($get->add_instructor($data));
                //break;
                 // Handle add instructor request
                 if (isset($_POST['name']) && isset($_POST['position']) && isset($_POST['email']) && isset($_POST['day']) && isset($_POST['time'])) {
                    $name = $_POST['name'];
                    $position = $_POST['position'];
                    $email = $_POST['email'];
                    $day = $_POST['day'];
                    $time = $_POST['time'];
                    $image = null;

                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $target_dir = "uploads/";
                        $target_file = $target_dir . basename($_FILES["image"]["name"]);
                        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));


                        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                            echo json_encode(['status' => 400, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed']);
                            exit();
                        }

                        
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                            $image = $target_file;
                        } else {
                            echo json_encode(['status' => 500, 'message' => 'Failed to upload image']);
                            exit();
                        }
                    }

                    $query = "INSERT INTO instructors_table (name, position, email, day, time, image) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($query);
                    if ($stmt->execute([$name, $position, $email, $day, $time, $image])) {
                        echo json_encode(['status' => 200, 'message' => 'Instructor added successfully']);
                    } else {
                        echo json_encode(['status' => 500, 'message' => 'Failed to add instructor']);
                    }
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Invalid input data']);
                }
                break;

            default:
                echo json_encode(["error" => "Invalid Request"]);
                http_response_code(400);
                break;
        }
        break;


            case 'GET':
                switch($req[0]){
        
                    case 'getallstudents':
                        echo json_encode($get->get_all_students('students'));
                    break;
        
                    case 'getstudentbyid':
                        echo json_encode($get->get_common('students', "id ='$req[1]'"));
                    break;
                    case 'getAllInstructors':
                        echo json_encode($get->get_common('instructors_table'));
                        break;
        
                    case 'getAllAppointments':
                        echo json_encode($get->get_all_appointments('appointment'));
                        break;
        
                    case 'getAllAppointmentsPerUser':
                        echo json_encode($get->get_all_appointments('appointment', " a.student_id = '$req[1]'"));
                        break;
        
                    case 'getAllUpcomingAppointments':
                        echo json_encode($get->get_all_appointments('appointment', " a.appointment_date_time > CURRENT_TIMESTAMP AND a.is_approved = 'TRUE' AND a.student_id = '$req[1]'"));
                        break;
        
                    case 'getAllHistoryAppointments':
                        echo json_encode($get->get_all_appointments('appointment', " a.appointment_date_time < CURRENT_TIMESTAMP AND a.is_finished = 'TRUE' AND a.student_id = '$req[1]'"));
                        break;
    

            default:
            echo json_encode(["error" => "Request not found"]);
            http_response_code(404);
            break;
        }
        break;


        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));
            switch($req[0]){
                case 'updateInstructor':
                    echo json_encode($get->update_instructor($req[1], $data));
                    break;

                default:
                echo json_encode(["error" => "Request not found"]);
                http_response_code(404);
                    break;
            }
            break;

            case 'DELETE':
                switch($req[0]){
                    case 'deleteInstructor':
                        echo json_encode($get->delete_instructor($req[1]));
                        break;

                    default:
                    echo json_encode(["error" => "Request not found"]);
                    http_response_code(404);
                        break;
            }
            break;

    default:
    echo json_encode(["error" => "Failed request"]);
    http_response_code(400);
    break;
        }
