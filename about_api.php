<?php
include "includes/dbconn.php";

header('Content-Type: application/json');

$aboutQuery = $conn->query("SELECT description FROM about LIMIT 1");
if (!$aboutQuery) {
    echo json_encode(["error" => "Failed to fetch about description"]);
    exit;
}
$aboutRow = $aboutQuery->fetch_assoc();
$description = isset($aboutRow['description']) ? trim($aboutRow['description']) : '';

$teamQuery = $conn->query("SELECT name, role, image FROM team");
if (!$teamQuery) {
    echo json_encode(["error" => "Failed to fetch team members"]);
    exit;
}
$teamMembers = [];
while ($row = $teamQuery->fetch_assoc()) {
    if (!empty($row['name']) && !empty($row['role']) && !empty($row['image'])) {
        $teamMembers[] = [
            'name' => trim($row['name']),
            'role' => trim($row['role']),
            'image' => trim($row['image'])
        ];
    }
}

echo json_encode(["description" => $description, "team" => $teamMembers]);
$conn->close();
exit;
?>
