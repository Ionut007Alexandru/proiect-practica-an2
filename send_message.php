<?php
// Your database connection details
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

// Logging function for debugging
function log_debug($message) {
    file_put_contents('command.log', $message . "\n", FILE_APPEND);
}

log_debug("Starting send_message.php script");

// Get the selected users, custom message, and message source from the form submission
$selected_users = $_POST['users'] ?? [];
$selected_custom_message = $_POST['custom_message'] ?? '';
$message_source = $_POST['message_source'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$closing_time = $_POST['closing_time'] ?? '';

log_debug("Form data: " . print_r($_POST, true));

if ($message_source === 'list') {
    // If a message from the list is selected, fetch it from the database
    $selected_message = $_POST['message'] ?? '';
    $sql_message = "SELECT mesaj FROM messages WHERE id = $selected_message";
    $result_message = $conn->query($sql_message);
    if ($result_message->num_rows > 0) {
        $row = $result_message->fetch_assoc();
        $message = $row['mesaj'];
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Selected message not found.';
        log_debug("Error: " . $response['message']);
        echo json_encode($response);
        exit;
    }
} else {
    $message = $selected_custom_message;
}

log_debug("Message to send: $message");

// Fetch the selected users from the database
$sql_users = "SELECT name, button_code FROM linkedin_users WHERE id IN (" . implode(',', $selected_users) . ")";
$result_users = $conn->query($sql_users);

$users = [];
while ($row = $result_users->fetch_assoc()) {
    $row['message'] = $message;
    $users[] = $row;
}

$data = [
    'users' => $users,
    'email' => $email,
    'password' => $password,
    'closing_time' => $closing_time,
];

// Save selected users and message to a JSON file
$file_path = 'selected_users.json';
file_put_contents($file_path, json_encode($data));
log_debug("Saved users and message to JSON file");

$response['status'] = 'success';
$response['message'] = 'Message data saved.';

// Use the correct Python executable path
$python_path = 'C:\\Python312\\python.exe'; // Update this to the correct path where Python is installed
$python_script = 'C:\\xampp\\htdocs\\linkedin\\main.py';
$command = "$python_path $python_script $email $password";

// Log the command for debugging
log_debug("Command: $command");

$output = [];
$return_var = null;
exec($command . ' 2>&1', $output, $return_var);

// Log the output and return value for debugging
log_debug("Command output: " . implode("\n", $output));
log_debug("Return var: $return_var");

if ($return_var === 0) {
    $response['status'] = 'success';
    $response['message'] = 'Message sent successfully.';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error executing Python script: ' . implode("\n", $output);
    log_debug("Error: " . $response['message']);
}

$conn->close();

echo json_encode($response);
?>
