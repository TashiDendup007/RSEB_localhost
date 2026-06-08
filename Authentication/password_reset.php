<?php 
	include('../CONNECTIONS/db.php');

	if (isset($_POST['reset_password'])) {

		$username = htmlspecialchars($_POST['username']);
		$password = "adMin@" . date("Y");
		

		$save = $dbh->prepare("SELECT email FROM users WHERE username = :usrName");
		$save->bindParam(':usrName', $username);
		$save->execute();

		$row = $save->fetch(PDO::FETCH_ASSOC);

        if ($row) {
        	// verify passwrod details from the db
	        $email = $row['email'];

	        // Hash the new password using bcrypt
	        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

	        // Update the password and set is_bcrypt to true
	        $query = $dbh->prepare("UPDATE users SET password = ?, is_bcrypt = 1, log_check = 1 WHERE username = ?");
	        $query->execute([$hashedPassword, $username]);

	        // Redirect to the access page with a success message
	        // header('Location: ../access.php?err=5');
	        $response = array(
	            'status' => 'success',
	            'message' => '',
	            'redirect' => 'access.php?err=5',
	        );
			include('passwordResetMail.php');

	        header('Content-Type: application/json');
	        echo json_encode($response);
	        exit();
        } else {
        	$response = array(
	            'status' => 'fail',
	            'message' => '<div class="alert alert-error">Invalid Username</div>',
	            'redirect' => '',
	        );
	        header('Content-Type: application/json');
	        echo json_encode($response);
            die();
        }

	}
	elseif(isset($_POST['get_username_details']))
	{
	$username = $_POST['username'];
	try {
		// Prepare SELECT query
		$stmt = $dbh->prepare("SELECT name, email, phone FROM users WHERE username = ?");
		
		// Execute with the username parameter
		$stmt->execute([$username]);
		
		// Fetch the result (single row)
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($user) {
			// Return data as JSON (for AJAX)
			echo json_encode([
				'success' => true,
				'name'    => $user['name'],
				'email'   => $user['email'],
				'contact' => $user['phone']
			]);
		} else {
			echo json_encode(['success' => false, 'message' => 'User not found!']);
		}
	} catch (PDOException $e) {
		echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
	}
	}

	// function to check password strength

	
	// function validatePassword($password) {
    //     // Define the pattern for a strong password
    //     $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        
    //     // Check if the password matches the pattern
    //     if (preg_match($pattern, $password)) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

	// function generateStrongPassword() {
	// 	// Define character sets
	// 	$uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	// 	$symbols = '!@#$%^&*';
		
	// 	// Combine them (only uppercase + symbols)
	// 	$chars = $uppercase . $symbols;
		
	// 	// Generate password
	// 	$password = '';
	// 	for ($i = 0; $i < 8; $i++) {
	// 		$password .= $chars[random_int(0, strlen($chars) - 1)];
	// 	}
		
	// 	return $password;
	// }
	

?>