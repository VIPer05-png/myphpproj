<?php
session_start();

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['credential'])) {
    
    $jwt = $_POST['credential'];
    
    // Verify the JWT with Google's public tokeninfo endpoint
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $jwt;
    $response = file_get_contents($url);
    
    if ($response !== FALSE) {
        $userData = json_decode($response, true);
        
        if (isset($userData['email']) && isset($userData['email_verified']) && $userData['email_verified'] == "true") {
            $email = $userData['email'];
            $name = $userData['name'] ?? explode('@', $email)[0];
            
            // Check if user exists in our database
            $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // User exists, log them in
                $user = $result->fetch_assoc();
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: ../dashboard.php");
                exit();
                
            } else {
                // User doesn't exist, create an account automatically
                
                // 1. Generate unique username (cut at 40 chars max to fit varchar 50 and leave room for numbers)
                $base_username = substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 40);
                $username = $base_username;
                $counter = 1;
                
                while(true) {
                    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
                    $check->bind_param("s", $username);
                    $check->execute();
                    $check->store_result();
                    if ($check->num_rows > 0) {
                        $username = $base_username . $counter;
                        $counter++;
                    } else {
                        break;
                    }
                }
                
                // 2. Generate random non-accessible password for OAuth users
                $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                
                // 3. Insert user
                $insert = $conn->prepare("INSERT INTO users(username, password, email, role) VALUES(?, ?, ?, 'user')");
                $insert->bind_param("sss", $username, $random_password, $email);
                
                if ($insert->execute()) {
                    $_SESSION['user'] = $username;
                    $_SESSION['role'] = 'user';
                    
                    $_SESSION['toast_msg'] = "Welcome to Cyberhut! Account created via Google.";
                    $_SESSION['toast_type'] = "success";
                    
                    header("Location: ../dashboard.php");
                    exit();
                } else {
                    die("Failed to create user account.");
                }
            }
        } else {
            die("Email not verified by Google.");
        }
    } else {
        die("Invalid Google Token.");
    }

} else {
    // If accessed directly without POST data
    header("Location: ../login.php");
    exit();
}
?>
