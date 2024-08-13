<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();
date_default_timezone_set('Asia/Manila');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        if (!isset($_GET['student_id'])) {
            $sql = "SELECT * FROM students";
        }

        if (isset($_GET['student_id'])) {
            $student_id = $_GET['student_id'];
            $sql = "SELECT * FROM students WHERE student_id = :student_id";
        }


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($student_id)) {
                $stmt->bindParam(':student_id', $student_id);
            }

            $stmt->execute();
            $student = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($student);
        }


        break;

    case "POST":
        $student = json_decode(file_get_contents('php://input'));


        // handleImage 

        // Process the base64 encoded image
        if (isset($student->student_image_path) && !empty($student->student_image_path)) {
            // Extract the base64 string and file type
            if (preg_match('/^data:image\/(\w+);base64,/', $student->student_image_path, $type)) {
                $student->student_image_path = substr($student->student_image_path, strpos($student->student_image_path, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif, etc.

                // Check the file extension
                if (in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    // Decode the image
                    $decoded_image = base64_decode($student->student_image_path);

                    // Create a unique file name
                    $file_name = uniqid() . '.' . $type;

                    // Specify the upload directory
                    $upload_dir = 'uploads/';
                    $target_file = $upload_dir . $file_name;

                    // Save the file to the server
                    if (file_put_contents($target_file, $decoded_image)) {
                        $student->student_image_path = $target_file; // Save the file path to the student_image_path variable
                    } else {
                        $response = [
                            "status" => "error",
                            "message" => "Failed to save the image file"
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "Invalid image type"
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Invalid image format"
                ];
                echo json_encode($response);
                exit;
            }
        } else {
            $student->student_image_path = null;
        }

        $sql = "INSERT INTO students (student_id, student_id_code, student_image_path, student_name, student_datebirth, student_address, student_gender, student_grade_level, student_program, student_block_section, student_parent_name, student_parent_number, student_parent_email) 
                    VALUES (:student_id, :student_id_code, :student_image_path, :student_name, :student_datebirth, :student_address, :student_gender, :student_grade_level, :student_program, :student_block_section, :student_parent_name, :student_parent_number, :student_parent_email)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':student_id', $student->student_id);
        $stmt->bindParam(':student_id_code', $student->student_id_code);
        $stmt->bindParam(':student_image_path', $student->student_image_path);
        $stmt->bindParam(':student_name', $student->student_name);
        $stmt->bindParam(':student_datebirth', $student->student_datebirth);
        $stmt->bindParam(':student_address', $student->student_address);
        $stmt->bindParam(':student_gender', $student->student_gender);
        $stmt->bindParam(':student_grade_level', $student->student_grade_level);
        $stmt->bindParam(':student_program', $student->student_program);
        $stmt->bindParam(':student_block_section', $student->student_block_section);
        $stmt->bindParam(':student_parent_name', $student->student_parent_name);
        $stmt->bindParam(':student_parent_number', $student->student_parent_number);
        $stmt->bindParam(':student_parent_email', $student->student_parent_email);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "Student created successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Student creation failed"
            ];
        }

        echo json_encode($response);
        break;

    case "PUT":
        $student = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE students 
                    SET 
                        student_id_code = :student_id_code,
                        student_image_path = :student_image_path,
                        student_name = :student_name,
                        student_datebirth = :student_datebirth,
                        student_address = :student_address,
                        student_gender = :student_gender,
                        student_grade_level = :student_grade_level,
                        student_program = :student_program,
                        student_block_section = :student_block_section,
                        student_parent_name = :student_parent_name,
                        student_parent_number = :student_parent_number,
                        student_parent_email = :student_parent_email
                    WHERE student_id = :student_id";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':student_id', $student->student_id);
        $stmt->bindParam(':student_id_code', $student->student_id_code);
        $stmt->bindParam(':student_image_path', $student->student_image_path);
        $stmt->bindParam(':student_name', $student->student_name);
        $stmt->bindParam(':student_datebirth', $student->student_datebirth);
        $stmt->bindParam(':student_address', $student->student_address);
        $stmt->bindParam(':student_gender', $student->student_gender);
        $stmt->bindParam(':student_grade_level', $student->student_grade_level);
        $stmt->bindParam(':student_program', $student->student_program);
        $stmt->bindParam(':student_block_section', $student->student_block_section);
        $stmt->bindParam(':student_parent_name', $student->student_parent_name);
        $stmt->bindParam(':student_parent_number', $student->student_parent_number);
        $stmt->bindParam(':student_parent_email', $student->student_parent_email);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "Student updated successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Student update failed"
            ];
        }

        echo json_encode($response);
        break;


    case "DELETE":
        $user = json_decode(file_get_contents('php://input'));
        $sql = "DELETE FROM students WHERE student_id = :student_id";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':student_id', $user->student_id);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "student_id deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "student_id delete failed"
            ];
        }

        echo json_encode($response);
        break;
}
