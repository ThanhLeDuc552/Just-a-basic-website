    <?php 
    $title = 'Manager Registration';
    $style = 'login.css';
    include_once 'initial_page_settings.inc';
    ?>
    <?php 
    session_start();
    include "functions.inc";
    include "settings.php";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = isset($_POST["username"]) ? sanitize_input($_POST["username"]) : "";
        $password = isset($_POST["password"]) ? $_POST['password'] : '';
        $confirm_password = isset($_POST["confirm_password"]) ? $_POST['confirm_password'] : '';
        $firstName = isset($_POST["firstName"]) ? sanitize_input($_POST["firstName"]) : "";
        $lastName = isset($_POST["lastName"]) ? sanitize_input($_POST["lastName"]) : "";
        $email = isset($_POST["email"]) ? sanitize_input($_POST["email"]) : "";
        
        // Validation
        $err_msg = "";
        
        // Check if username already exists
        $check_sql = "SELECT Username FROM managers WHERE Username = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $err_msg = "Username already exists";
        } elseif (empty($username) || empty($password) || empty($firstName) || empty($lastName) || empty($email)) {
            $err_msg = "All fields are required";
        } elseif ($password !== $confirm_password) {
            $err_msg = "Passwords do not match";
        } elseif (strlen($password) < 8) {
            $err_msg = "Password must be at least 8 characters";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err_msg = "Invalid email format";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new manager into database
            $insert_sql = "INSERT INTO managers (Username, Password, FirstName, LastName, Email) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "sssss", $username, $hashed_password, $firstName, $lastName, $email);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                // Registration successful
                $success_msg = "Registration successful! You can now login.";
            } else {
                // Registration failed
                $err_msg = "Registration failed: " . mysqli_error($conn);
            }
        }
        
        mysqli_stmt_close($check_stmt);
    }
    ?>
    <main>
        <div class="login-info">
            <form action="register.php" method="post">
                <h1>Manager Registration</h1>
                <?php if (!empty($err_msg)): ?>
                    <div class="error_message">
                        <?php echo htmlspecialchars($err_msg); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success_msg)): ?>
                    <div class="success_message">
                        <?php echo htmlspecialchars($success_msg); ?>
                    </div>
                <?php endif; ?>
                <div class="input-field">
                    <label for="firstName">First Name</label>
                    <span class="icon">👤</span>
                    <input type="text" name="firstName" class="opt-box" id="firstName" placeholder="First Name">
                </div>
                <div class="input-field">
                    <label for="lastName">Last Name</label>
                    <span class="icon">👤</span>
                    <input type="text" name="lastName" class="opt-box" id="lastName" placeholder="Last Name">
                </div>
                <div class="input-field">
                    <label for="email">Email</label>
                    <span class="icon">📧</span>
                    <input type="email" name="email" class="opt-box" id="email" placeholder="Email Address">
                </div>
                <div class="input-field">
                    <label for="username">Username</label>
                    <span class="icon">👤</span>
                    <input type="text" name="username" class="opt-box" id="username" placeholder="Choose a Username">
                </div>
                <div class="input-field">
                    <label for="password">Password</label>
                    <span class="icon">🔒</span>
                    <input type="password" name="password" class="opt-box" id="password" placeholder="Password (min. 8 characters)">
                </div>
                <div class="input-field">
                    <label for="confirm_password">Confirm Password</label>
                    <span class="icon">🔒</span>
                    <input type="password" name="confirm_password" class="opt-box" id="confirm_password" placeholder="Confirm Password">
                </div>
                <button type="submit" class="login-button">Register</button>
                <p >Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </main>
</body>
</html>
