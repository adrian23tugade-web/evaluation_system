<?php
header('Content-Type: application/json');
include "db.php";

$dept = isset($_GET['dept']) ? mysqli_real_escape_string($conn, $_GET['dept']) : '';

if(empty($dept)){
    echo json_encode([]);
    exit();
}

$result = $conn->query("SELECT id, name FROM teachers WHERE department = '$dept' ORDER BY name ASC");

if(!$result){
    echo json_encode([]);
    exit();
}

$teachers = [];
while($row = $result->fetch_assoc()){
    $teachers[] = [
        'id'   => $row['id'],
        'name' => $row['name']
    ];
}

echo json_encode($teachers);
?>