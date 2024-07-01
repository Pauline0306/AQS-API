<?php

class Get{

    protected $pdo;
    protected $gm;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->gm = new GlobalMethods($pdo);
    }

    public function get_common($table, $condition = null){
        $sql = "SELECT * FROM $table";
        if($condition!=null){
            $sql .= " WHERE {$condition}";
        }
        error_log("Executing query: $sql"); // Log query
        $res = $this->gm->executeQuery($sql);
        if($res['code']==200){
            return $this->gm->returnPayload($res['data'], "success", "Succesfully retrieved from $table", $res['code']);
        }
        error_log("Failed to retrieve data: " . $res['errmsg']); // Log error message
        return $this->gm->returnPayload(null, "failed", "failed to retrieve data", $res['code']);
    }

    public function get_all_students($table, $condition = null){
        $sql = "SELECT * FROM $table";
        error_log("Executing query: $sql"); // Log query

        
        $res = $this->gm->executeQuery($sql);

        if($res['code'] == 200){
            return $this->gm->returnPayload($res['data'], "success", "Succesfully retrieved from $table", $res['code']);
        }
        error_log("Failed to retrieve data: " . $res['errmsg']); // Log error message

        return $this->gm->returnPayload(null, "failed", "failed to retrieve data", $res['code']);
    }

    
    public function getAppointments() {
        $appointment = "appointment"; // Replace with your actual table name
        $sql = "SELECT appointmentid, studid, appointmentdate, appointmenttime, isFirstVisit, remarks, isDone, appointmentNo, instructorid FROM $appointment";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
    
    $appointments = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $appointment_item = array(
            "appointmentId" => $appointmentid,
            "studentId" => $id,
            "appointmentDate" => $appointmentdate,
            "appointmentTime" => $appointmenttime,
            "isFirstVisit" => $isFirstVisit,
            "remarks" => $remarks,
            "isDone" => $isDone,
            "appointmentNo" => $appointmentNo,
            "instructorId" => $instructorid
        );
        array_push($appointments, $appointment_item);
    }
    return $appointments;
}

    public function get_all_appointments($table, $condition = null){
        $sql = "SELECT * FROM $table a INNER JOIN instructors_table it ON a.instructor_id = it.id INNER JOIN students s ON a.student_id = s.id";
        if ($condition != null) {
        $sql .= " WHERE {$condition}";
    }
        error_log("Executing query: $sql"); // Log query
        $res = $this->gm->executeQuery($sql);

        if ($res['code'] == 200) {
        return $this->gm->returnPayload($res['data'], "success", "Succesfully retrieved from $table", $res['code']);
    }
        error_log("Failed to retrieve data: " . $res['errmsg']); // Log error message
        return $this->gm->returnPayload(null, "failed", "failed to retrieve data", $res['code']);
}
    public function get_user_by_id($id) {
        $sql = "SELECT first_name, last_name FROM students WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
        return $this->gm->returnPayload($user, "success", "Successfully retrieved user data", 200);

    } else {
        return $this->gm->returnPayload(null, "failed", "User not found", 404);
}
    }
    public function add_instructor($data) {
        $name = $data->name;
        $position = $data->position;
        $email = $data->email;
        $time = $data->time;
        $day = $data->day;
        $image = $this->handleImageUpload();
    
        $sql = "INSERT INTO instructors_table (name, position, email, day, time, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $position, $email, $day, $time, $image]);
    
        if ($stmt->rowCount() > 0) {
            return $this->gm->returnPayload(null, "success", "Instructor added successfully", 200);
        } else {
            return $this->gm->returnPayload(null, "failed", "Failed to add instructor", 500);
        }
    }

    private function handleImageUpload() {
        $uploadDir = __DIR__ . '.uploads/'; // Relative path to uploads folder
        $uploadedFile = $_FILES['image']; // Assuming 'image' is the name of your file input in the form
    
        // Check if file was uploaded without errors
        if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
            $filename = uniqid() . '_' . basename($uploadedFile['name']); // Generate unique filename
            $destination = $uploadDir . $filename;
    
            // Move the uploaded file to the destination folder
            if (move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
                return 'uploads/' . $filename; // Return the file path or filename to store in the database
            } else {
                return null; // Handle failure to move file
            }
        } else {
            return null; // Handle file upload error
        }
    }
    
    public function update_instructor($id, $data) {
        //$name = $data->name;
        //$position = $data->position;
        //$email = $data->email;
        //$time = $data->time;
        //$day = $data->day;
        //$image = $data->image;

        //$sql = "UPDATE instructors_table SET name = ?, position = ?, email = ?, day = ?, time = ?, image = ? WHERE id = ?";
        //$stmt = $this->pdo->prepare($sql);
        //$stmt->execute([$name, $position, $email, $day, $time, $image, $id]);

        //if ($stmt->rowCount() > 0) {
          //  return $this->gm->returnPayload(null, "success", "Instructor updated successfully", 200);
        //} else {
            //return $this->gm->returnPayload(null, "failed", "Failed to update instructor", 500);
        //}
    //}

    $sql = "UPDATE instructors_table SET name = ?, position = ?, email = ?, day = ?, time = ? ";
    $params = [$data->name, $data->position, $data->email, $data->day, $data->time];
    
    if (!empty($data->image)) {
        $sql .= ", image = ? ";
        $params[] = $data->image;
    }

    $sql .= "WHERE id = ?";
    $params[] = $id;

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        return $this->gm->returnPayload(null, "success", "Instructor updated successfully", 200);
    } else {
        return $this->gm->returnPayload(null, "failed", "Failed to update instructor", 500);
    }
}

    public function delete_instructor($id) {
        try {
            $query = "DELETE FROM instructors_table WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
    
            // Check if deletion was successful
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Instructor deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No instructor found with that ID'
                ];
            }
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error deleting instructor: ' . $e->getMessage()
            ];
        }
    }

}
