<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f4f4; }
        .login-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        .login-container h2 { margin-bottom: 20px; color: #333; }
        .login-container input[type="text"], .login-container input[type="password"] { width: calc(100% - 20px); padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .login-container button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .login-container button:hover { background-color: #0056b3; }
        .error-message { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>CRM Login</h2>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="error-message"><?php echo session()->getFlashdata('error'); ?></div>
        <?php endif; ?>
        <form action="<?php echo site_url('auth/process_login'); ?>" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>