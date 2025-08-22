<?php

$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname = getenv('DB_NAME');

header('Content-Type: application/json');

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    log_debug('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit;
}

$sql = "SELECT p.id, p.name, p.url, p.icon, p.size, p.description, t.name AS type_name
        FROM project p
        LEFT JOIN type t ON p.type_id = t.id";
$result = $conn->query($sql);
if (!$result) {
    log_debug('project fetch query failed: ' . $conn->error);
    http_response_code(500);
    exit;
}

$projects = [];
while ($row = $result->fetch_assoc()) {
    $row['technologies'] = [];
    $tech_sql = "SELECT tech.name FROM project_technology pt JOIN technology tech ON pt.technology_id = tech.id WHERE pt.project_id = ?";
    $stmt = $conn->prepare($tech_sql);
    if (!$stmt) {
        log_debug('tech_sql query prepare failed: ' . $conn->error);
        continue;
    }
    $stmt->bind_param('i', $row['id']);
    $stmt->execute();
    $tech_result = $stmt->get_result();
    if (!$tech_result) {
        log_debug('tech_result query failed: ' . $stmt->error);
    }
    while ($tech = $tech_result->fetch_assoc()) {
        $row['technologies'][] = $tech;
    }
    $stmt->close();
    $projects[] = $row;
}

$conn->close();
log_debug('Returning ' . count($projects) . ' projects');
echo json_encode($projects);
?>
