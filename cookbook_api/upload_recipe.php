<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recipe_name   = $_POST['recipe_name'];
    $ingredients   = $_POST['ingredients'];
    $user_id       = $_POST['user_id'];  // From Android login session

    // ---- IMAGE UPLOAD ----
    $image_url = "";

    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {

        $target_dir = "uploads/";
        
        // Create folder if not exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allowed file formats
        $allowed = array("jpg", "jpeg", "png");

        if (!in_array($imageFileType, $allowed)) {
            echo json_encode([
                "success" => false,
                "message" => "Only JPG, JPEG, PNG files allowed"
            ]);
            exit;
        }

        // Move uploaded image
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $image_name;
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Image upload failed"
            ]);
            exit;
        }
    }

    // ---- INSERT INTO DATABASE ----
    $stmt = $conn->prepare("
        INSERT INTO recipes (user_id, recipe_name, ingredients, image)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("isss", $user_id, $recipe_name, $ingredients, $image_url);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Recipe uploaded successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to save recipe"
        ]);
    }

    $stmt->close();
}

$conn->close();
?>
