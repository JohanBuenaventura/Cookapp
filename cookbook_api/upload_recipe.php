<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = $_POST['title'] ?? '';         // use 'title' instead of 'recipe_name'
    $ingredients = $_POST['ingredients'] ?? '';
    $user_id     = $_POST['user_id'] ?? 0;

    $image_path = null;

    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {

        $target_dir = "uploads/";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png"];

        if (!in_array($imageFileType, $allowed)) {
            echo json_encode([
                "success" => false,
                "message" => "Only JPG, JPEG, PNG files allowed"
            ]);
            exit;
        }

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $image_name;
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Image upload failed"
            ]);
            exit;
        }
    }

    // INSERT INTO DB (match your current schema)
    $stmt = $conn->prepare("
        INSERT INTO recipes (user_id, title, ingredients, image_path)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("isss", $user_id, $title, $ingredients, $image_path);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Recipe uploaded successfully"
        ]);
    } else {
        // Debug info
        echo json_encode([
            "success" => false,
            "message" => "Failed to save recipe: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
}
?>
