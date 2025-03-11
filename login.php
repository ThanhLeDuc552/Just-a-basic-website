<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etech - Manager</title>
    <link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
	<link rel="stylesheet" href="styles/style.css"> <!-- General styling -->
    <link rel="stylesheet" href="styles/login.css"> <!-- Login page styling -->
</head>
<body>
    <?php include_once("header.inc")?>
    <main>
        <div class="container">
            <div class="login-info">
                <h1>Manager Login</h1>
                <div class="input-field">
                    <label for="username">Username</label>
                    <span class="icon">✉</span>
                    <input type="text" name="username" class="opt-box" id="username" placeholder="Username or Email">
                </div>
                <div class="input-field">
                    <label for="password">Password</label>
                    <span class="icon"></span>
                    <input type="text" name="password" class="opt-box" id="password" placeholder="Password">
                </div>
            </div>
            <div class="picture">
                <img src="styles/images/business_man.png" alt="">
            </div>
        </div>
    </main>
    <?php include_once("footer.inc")?>
</body>
</html>