<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();
date_default_timezone_set('Asia/Manila');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['staff'])) {
            $sql = "SELECT * FROM users WHERE account_type = 'staff' ORDER BY user_id DESC";
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product);
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

        $sql = "INSERT INTO students (student_id, student_id_code, student_image_path, student_name, student_datebirth, student_grade_level, student_program, student_block_section, student_parent_name, student_parent_number, student_parent_email) 
                    VALUES (:student_id, :student_id_code, :student_image_path, :student_name, :student_datebirth, :student_grade_level, :student_program, :student_block_section, :student_parent_name, :student_parent_number, :student_parent_email)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':student_id', $student->student_id);
        $stmt->bindParam(':student_id_code', $student->student_id_code);
        $stmt->bindParam(':student_image_path', $student->student_image_path);
        $stmt->bindParam(':student_name', $student->student_name);
        $stmt->bindParam(':student_datebirth', $student->student_datebirth);
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


    case "DELETE":
        $user = json_decode(file_get_contents('php://input'));
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':user_id', $user->user_id);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "user deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "user delete failed"
            ];
        }

        echo json_encode($response);
        break;
}
