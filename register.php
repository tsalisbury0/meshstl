<?php
session_start();

$usersFile = 'users.txt';
$tokensFile = 'tokens.txt';

function loadTokens($file) {
    $tokens = [];
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            list($token, $status) = explode("::", $line);
            $tokens[trim($token)] = trim($status);
        }
    }
    return $tokens;
}

function saveTokens($tokens, $file) {
    $data = "";
    foreach ($tokens as $token => $status) {
        $data .= $token . "::" . $status . PHP_EOL;
    }
    file_put_contents($file, $data);
}

function usernameExists($username, $usersFile) {
    if (!file_exists($usersFile)) return false;
    $lines = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode(':', $line);
        if ($parts[0] === $username) return true;
    }
    return false;
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$tokens = loadTokens($tokensFile);

$isValidToken = $token && isset($tokens[$token]) && $tokens[$token] === 'active';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValidToken) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username and password are required.";
    } elseif (usernameExists($username, $usersFile)) {
        $error = "Username already exists.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        file_put_contents($usersFile, "$username:$passwordHash:user" . PHP_EOL, FILE_APPEND);

        $tokens[$token] = 'used';
        saveTokens($tokens, $tokensFile);

        $success = "Registration successful. You may now log in.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>MeshSTL Registration</title>
    <link rel="icon" href="MeshSTL.webp" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-center mb-6">Create Your Account</h2>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-6"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-6"><?= htmlspecialchars($success) ?></div>
        <a href="node-list.php" class="block text-center text-blue-600 hover:underline">Go to Login</a>
    <?php elseif (!$isValidToken): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-6 text-center">Invalid or used token.</div>
    <?php else: ?>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />

            <div>
                <label for="username" class="block mb-2 font-semibold text-gray-700">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label for="password" class="block mb-2 font-semibold text-gray-700">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <button
                type="submit"
                class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded transition"
            >
                Register
            </button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
