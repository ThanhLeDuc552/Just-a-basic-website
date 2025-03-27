    <?php 
    $title = 'Manager Login';
    $style = 'login.css';
    include_once 'initial_page_settings.inc';
    ?>
    <?php 
    session_start();
    include "functions.inc";
    include "settings.php";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = isset($_POST["username"]) ? sanitize_input($_POST["username"]) : "";
        $email = isset($_POST["username"]) ? sanitize_input($_POST["username"]) : "";
        $password = isset($_POST["password"]) ? custom_password_hash($_POST['password']) : '';

        if (is_account_locked($username, $conn)) {
            $err_msg = "Your account has been temporarily locked due to multiple failed login attempts. Please try again later.";
        } else {
            // Verify credentials
            $sql = "SELECT ManagerID, Username, Password, FirstName, LastName FROM managers WHERE Username = ? OR Email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $username, $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                if ($password === $row['Password']) {
                    // Successful login
                    $_SESSION['manager_id'] = $row['ManagerID'];
                    $_SESSION['manager_name'] = $row['FirstName'] . ' ' . $row['LastName'];
                    $_SESSION['username'] = $row['Username'];

                    // Log successful login
                    log_login_attempt($row["Username"], true, $conn);

                    // Update last login time
                    update_last_login($row["Username"], $conn);

                    header("Location: manage.php");
                    exit();
                } else {
                    // Failed login - wrong password
                    $err_msg = "Invalid username or password";
                    log_login_attempt($row["Username"], false, $conn);

                    // Check if account should be locked
                    if (check_account_lockout($row["Username"], $conn)) {
                        lock_account($row["Username"], $conn);
                        $err_msg = "Your account has been temporarily locked due to multiple failed login attempts. Please try again later.";
                    }
                }
            } else {
                // Failed login - username not found
                $err_msg = "Invalid username or password";
            }
        }
    }
    ?>
    <main>
        <div class="login-info">
            <form action="login.php" method="post">
                <h1>Manager Login</h1>
                <?php if (!empty($err_msg)): ?>
                    <div class="error_message">
                        <?php echo htmlspecialchars($err_msg); ?>
                    </div>
                <?php endif; ?>
                <div class="input-field">
                    <label for="username">Username</label>
                    <span class="icon">📧</span>
                    <input type="text" name="username" class="opt-box" id="username" placeholder="Username or Email">
                </div>
                <div class="input-field">
                    <label for="password">Password</label>
                    <span class="icon">🔒</span>
                    <input type="password" name="password" class="opt-box" id="password" placeholder="Password">
                </div>
            <button class="btn login-button">Login</button>
            <p>Don't have an account? Register <a href="register.php">here</a></p>
            <p>Return to <a href="index.php">home</a></p>
            </form>
        </div>
    </main>
    <?php if (!empty($err_msg)) $err_msg = ''; ?>
</body>
</html>