
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "automatizare";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_users = "SELECT id, name FROM linkedin_users";
$result_users = $conn->query($sql_users);

$sql_messages = "SELECT id, mesaj FROM messages";
$result_messages = $conn->query($sql_messages);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LinkedIn Message Sender</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites/dist/css/foundation.min.css">
</head>
<body>
    <div class="grid-container">
        <h1>Send LinkedIn Messages</h1>
        <form id="userForm">
            <label>
                <input type="checkbox" id="select_all"> Select All
            </label>
            <ul>
                <?php
                if ($result_users->num_rows > 0) {
                    while($row = $result_users->fetch_assoc()) {
                        echo '<li><label><input type="checkbox" name="users[]" value="'.$row["id"].'"> '.$row["name"].'</label></li>';
                    }
                } else {
                    echo "0 results";
                }
                ?>
            </ul>

            <label for="custom_message">Custom Message:</label>
            <textarea name="custom_message" id="custom_message" rows="4" cols="50"></textarea>

            <label>Select Message:</label>
            <label><input type="radio" name="message_source" value="custom" checked> Custom Message</label>
            <label><input type="radio" name="message_source" value="list"> List of Messages</label>

            <select name="message" id="message" disabled>
                <?php
                if ($result_messages->num_rows > 0) {
                    while($row = $result_messages->fetch_assoc()) {
                        echo '<option value="'.$row["id"].'">'.$row["mesaj"].'</option>';
                    }
                } else {
                    echo '<option value="">No messages available</option>';
                }
                ?>
            </select>

            <label for="closing_time">Closing Time (in seconds):</label>
            <input type="number" id="closing_time" name="closing_time" min="7" value="10">

            <button type="submit" class="button">Send Message</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.getElementById('select_all').addEventListener('click', function(event) {
            var checkboxes = document.querySelectorAll('input[name="users[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = event.target.checked;
            });
        });

        $('#userForm').on('submit', function(event) {
            event.preventDefault();
            var selectedUsers = $('input[name="users[]"]:checked').map(function() {
                return $(this).val();
            }).get();
            var selectedMessage = $('#message').val();
            var customMessage = $('#custom_message').val();
            var messageSource = $('input[name="message_source"]:checked').val();
            var closingTime = $('#closing_time').val(); 

            if (selectedUsers.length === 0) {
                alert('Please select at least one user.');
                return;
            }

            if (messageSource === 'list') {
                if (selectedMessage === '') {
                    alert('Please select a message from the list.');
                    return;
                }
            } else {
                customMessage = customMessage.trim();
                if (customMessage === '') {
                    alert('Please enter a custom message.');
                    return;
                }
            }

            $.post('send_message.php', { users: selectedUsers, message: selectedMessage, custom_message: customMessage, message_source: messageSource }, function(response) {
                console.log(response);
                if (response.status === 'success') {
                    alert('Message processing complete.');
                } else {
                    alert('Failed to process message: ' + response.message);
                }
            }, 'json')
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
                alert('Failed to process message.');
            });

            // timp minim de inchidere 7 secunde 
            closingTime = Math.max(closingTime, 7);

            // adaugare delay pt procesare mesak
            setTimeout(function() {
                window.close();
            }, (parseInt(closingTime) + 3) * 1000); // secunde convertite in milisecunde plus 3 secunde buffer aditional
        });

        $('input[name="message_source"]').on('change', function() {
            if ($(this).val() === 'list') {
                $('#message').prop('disabled', false);
                $('#custom_message').prop('disabled', true);
            } else {
                $('#message').prop('disabled', true);
                $('#custom_message').prop('disabled', false);
            }
        });
    </script>
</body>
</html>

