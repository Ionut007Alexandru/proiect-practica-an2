<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "automatizare";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$python_path = 'C:\\xampp\\htdocs\\linkedin\\venv\\Scripts\\python.exe';
$main_script = 'C:\\xampp\\htdocs\\linkedin\\main.py';

$selected_users = $_POST['users'];
$message_source = $_POST['message_source'];
$selected_message_id = $_POST['message'];
$custom_message = $_POST['custom_message'];
$closing_time = $_POST['closing_time'];
$email = $_POST['email'];
$password = $_POST['password'];

// Fetch the selected message from the database if from the list
if ($message_source === 'list') {
    $sql_message = "SELECT mesaj FROM messages WHERE id = $selected_message_id";
    $result_message = $conn->query($sql_message);
    if ($result_message->num_rows > 0) {
        $row = $result_message->fetch_assoc();
        $message = $row['mesaj'];
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Selected message not found.';
        echo json_encode($response);
        exit;
    }
} else {
    // Use custom message
    $message = $custom_message;
}

$data = [
    'users' => array_map(function($name) use ($message) {
        return ['name' => $name, 'message' => $message];
    }, $selected_users),
    'email' => $email,
    'password' => $password,
    'closing_time' => $closing_time,
];

$file_path = 'selected_users.json';
file_put_contents($file_path, json_encode($data));

$command = escapeshellcmd("$python_path $main_script");
exec($command, $output, $return_var);

$response = [];
if ($return_var === 0) {
    $response['status'] = 'success';
    $response['message'] = 'Message sent successfully.';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to process message';
    $response['raw_output'] = implode("\n", $output);
}

$conn->close();

echo json_encode($response);
?>