<?php
include "config.php";

// Fetch all recipes with user info
$query = "SELECT r.id, r.user_id, r.title, r.ingredients, r.image_path, 
                 u.firstname, u.lastname, u.profile_image
          FROM recipes r
          JOIN users u ON r.user_id = u.id
          ORDER BY r.id DESC";

$result = $conn->query($query);

$response = array();

while ($row = $result->fetch_assoc()) {
    $response[] = $row;
}

echo json_encode([
    "success" => true,
    "recipes" => $response
]);

$conn->close();
?>
