<?php
header('Content-Type: application/json');

$python_path = 'C:\\xampp\\htdocs\\linkedin\\venv\\Scripts\\python.exe';
$fetch_script = 'C:\\xampp\\htdocs\\linkedin\\fetch_connections.py';

$email = $_POST['email'];
$password = $_POST['password'];

$command = escapeshellcmd("$python_path $fetch_script $email $password");
exec($command, $output, $return_var);

$response = [];
if ($return_var === 0) {
    $response['status'] = 'success';
    $response['connections'] = json_decode(implode("\n", $output));
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to fetch connections';
    $response['raw_output'] = $output;
}

echo json_encode($response);
?>
