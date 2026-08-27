 <?php
include 'db.php';

$error = "";

if (isset($_POST['register'])) {
    $user_id   = trim($_POST['userid']);
    $user_type = $_POST['user_type'];
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);
    $area      = trim($_POST['area']);
    $road      = trim($_POST['road']);
    $phone     = trim($_POST['phone']);

    $first_char = $user_id[0] ?? '';
    $rest_of_id = substr($user_id, 1);

    //. Length validation (1 to 8 characters)
    if (empty($user_id) || strlen($user_id) > 8) {
        $error = "User ID must be between 1 and 8 characters.";
    } 
    // Checks if 1st char is 'A' (or 'a') AND the rest are numbers
    else if (($first_char == 'A' || $first_char == 'a') && is_numeric($rest_of_id)) {
        $error = "Only Admin can have an ID starting with 'A' followed by numbers.";
    } 
    else {
        // Duplicate check (UserID or Email)
        $check_sql = "SELECT userID FROM user_info WHERE userID = '$user_id' OR email = '$email'";
        $check_res = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_res) > 0) {
            $error = "This User ID or Email is already registered!";
        } else {
            // Insert into user_info table
            $sql_user = "INSERT INTO user_info (userID, name, email, password, road, area, AdminID) 
                         VALUES ('$user_id', '$name', '$email', '$password', '$road', '$area', 'A1')";
            
            if (mysqli_query($conn, $sql_user)) {
                // Insert phone number into userphone table
                $insert_phn = "INSERT INTO userphone (userID, phone) VALUES ('$user_id', '$phone')";
                mysqli_query($conn, $insert_phn);

                // Insert only userID into student or faculty role table
                if ($user_type == "student") {
                    $sql_role = "INSERT INTO student_info (userID) VALUES ('$user_id')";
                } else {
                    $sql_role = "INSERT INTO faculty_info (userID) VALUES ('$user_id')";
                }

                if (mysqli_query($conn, $sql_role)) {
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Failed to create role profile: " . mysqli_error($conn);
                }
            } else {
                $error = "Failed to register user: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Studyverse</title>
    <link rel="stylesheet" href="user_register.css">
</head>
<body>

<div class="card">
    <div class="card-header">
        <h2>Studyverse</h2>
        <p>Create your account</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-box"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="user_register.php">
        <div class="form-row">
            <div class="form-group">
                <label>I am a:</label>
                <select name="user_type">
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                </select>
            </div>
            <div class="form-group">
                <label>User ID:</label>
                <input type="text" name="userid" maxlength="8" placeholder="Max 8 chars" required>
            </div>
        </div>

        <div class="form-group">
            <label>Full Name:</label>
            <input type="text" name="name" placeholder="John Doe" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email Address:</label>
                <input type="email" name="email" placeholder="name@domain.com" required>
            </div>
            <div class="form-group">
                <label>Phone Number:</label>
                <input type="text" name="phone" placeholder="017xxxxxxxx" required>
            </div>
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" placeholder="Create a password" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Area:</label>
                <input type="text" name="area" placeholder="e.g. Banani" required>
            </div>
            <div class="form-group">
                <label>Road:</label>
                <input type="text" name="road" placeholder="e.g. Road 11" required>
            </div>
        </div>

        <input type="submit" name="register" value="Register" class="btn-submit">
    </form>

    <div class="footer-text">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>