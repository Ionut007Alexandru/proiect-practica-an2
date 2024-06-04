<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "automatizare";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selected_users = $_POST['users'];
$selected_custom_message = $_POST['custom_message'];
$message_source = $_POST['message_source'];

if ($message_source === 'list') {
    $selected_message = $_POST['message'];
    $sql_message = "SELECT mesaj FROM messages WHERE id = $selected_message";
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
    $message = $selected_custom_message;
    $sql_check_custom_message = "SELECT id FROM messages WHERE mesaj = ?";
    $stmt = $conn->prepare($sql_check_custom_message);
    $stmt->bind_param("s", $message);
    $stmt->execute();
    $result_check_custom_message = $stmt->get_result();

    if ($result_check_custom_message->num_rows == 0) {
        $sql_insert_custom_message = "INSERT INTO messages (mesaj) VALUES (?)";
        $stmt = $conn->prepare($sql_insert_custom_message);
        $stmt->bind_param("s", $message);
        if ($stmt->execute() === FALSE) {
            $response['status'] = 'error';
            $response['message'] = 'Failed to insert custom message into the database.';
            echo json_encode($response);
            exit;
        }
    }
}

$sql_users = "SELECT name, button_code FROM linkedin_users WHERE id IN (" . implode(',', $selected_users) . ")";
$result_users = $conn->query($sql_users);

$users = [];
while ($row = $result_users->fetch_assoc()) {
    $row['message'] = $message;
    $users[] = $row;
}

$file_path = 'selected_users.json';
file_put_contents($file_path, json_encode($users));

$python_script = 'C:\\xampp\\htdocs\\linkedin\\main.py';
$output = [];
$return_var = null;
exec("python $python_script 2>&1", $output, $return_var);

if ($return_var === 0) {
    $response['status'] = 'success';
    $response['message'] = 'Message sent successfully.';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error executing Python script: ' . implode("\n", $output);
}

$conn->close();

echo json_encode($response);
?>
