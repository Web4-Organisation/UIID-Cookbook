<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UIID Demosuite</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #000000; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Welcome to the UIID PHP Demosuite</h1>
    <?php if (isset($_SESSION['access_token'])): ?>
        <p>You are already logged in.</p>
        <a href="index.php?action=dashboard" class="btn">Go to Dashboard</a>
        <a href="index.php?action=logout" class="btn">Logout</a>
    <?php else: ?>
        <p>Click the button to log in with UIID.</p>
        <a href="index.php?action=login" class="btn">Login with UIID</a>
    <?php endif; ?>
</body>
</html>
