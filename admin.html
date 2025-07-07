<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check admin access
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: node-list.php");
    exit;
}

$usersFile = 'users.txt';
$tokensFile = 'tokens.txt';
$logFile = 'edit-log.txt';

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$messages = [];
$errors = [];

// Logging function
function logAction($message) {
    global $logFile;
    $time = date('Y-m-d H:i:s');
    $user = $_SESSION['username'] ?? 'unknown';
    file_put_contents($logFile, "[$time][$user] $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Input validation helper
function validUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

function loadUsers($file) {
    $users = [];
    if (file_exists($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $parts = explode(":", $line, 3);
            if (count($parts) === 3) {
                $username = trim($parts[0]);
                $passwordHash = $parts[1];
                $role = $parts[2];
                $users[$username] = ['password' => $passwordHash, 'role' => $role];
            }
        }
    }
    return $users;
}

function loadTokens($file) {
    clearstatcache(true, $file);
    $tokens = [];
    if (file_exists($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $parts = explode("::", $line, 2);
            if (count($parts) === 2) {
                $tokens[] = ['token' => trim($parts[0]), 'status' => trim($parts[1])];
            }
        }
    }
    return $tokens;
}

function loadEditLog($file) {
    return file_exists($file) ? array_slice(array_reverse(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)), 0, 10) : [];
}

// Load current data before processing POST
$users = loadUsers($usersFile);
$tokens = loadTokens($tokensFile);
$logEntries = loadEditLog($logFile);

// CSRF check helper
function checkCsrf() {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
}

// Handle delete user (POST)
if (isset($_POST['delete_user'])) {
    checkCsrf();
    $usernameToDelete = $_POST['delete_user'];
    if (isset($users[$usernameToDelete])) {
        $lines = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $filtered = array_filter($lines, fn($line) => !str_starts_with($line, $usernameToDelete . ':'));
        file_put_contents($usersFile, implode(PHP_EOL, $filtered) . PHP_EOL, LOCK_EX);
        logAction("Deleted user '$usernameToDelete'");
        $messages[] = "User '$usernameToDelete' deleted successfully.";
        $users = loadUsers($usersFile);
    } else {
        $errors[] = "User '$usernameToDelete' not found.";
    }
}

// Handle delete token (POST)
if (isset($_POST['delete_token'])) {
    checkCsrf();
    $tokenToDelete = $_POST['delete_token'];
    $tokenExists = false;
    foreach ($tokens as $t) {
        if ($t['token'] === $tokenToDelete) {
            $tokenExists = true;
            break;
        }
    }
    if ($tokenExists) {
        $lines = file($tokensFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $filtered = array_filter($lines, fn($line) => !str_starts_with($line, $tokenToDelete . '::'));
        file_put_contents($tokensFile, implode(PHP_EOL, $filtered) . PHP_EOL, LOCK_EX);
        logAction("Deleted token '$tokenToDelete'");
        $messages[] = "Token deleted successfully.";
        $tokens = loadTokens($tokensFile);
    } else {
        $errors[] = "Token not found.";
    }
}

// Handle create user (POST)
if (isset($_POST['create'])) {
    checkCsrf();
    $newUsername = trim($_POST['new_username']);
    $newPasswordRaw = $_POST['new_password'] ?? '';
    $newRoleRaw = $_POST['new_role'] ?? 'user';
    $allowedRoles = ['admin', 'user'];
    $newRole = in_array($newRoleRaw, $allowedRoles) ? $newRoleRaw : 'user';

    if (!validUsername($newUsername)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    } elseif (strlen($newPasswordRaw) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif (isset($users[$newUsername])) {
        $errors[] = "Username '$newUsername' already exists.";
    }

    if (empty($errors)) {
        $newPassword = password_hash($newPasswordRaw, PASSWORD_BCRYPT);
        $newLine = $newUsername . ':' . $newPassword . ':' . $newRole;
        file_put_contents($usersFile, $newLine . PHP_EOL, FILE_APPEND | LOCK_EX);
        logAction("Created user '$newUsername' with role '$newRole'");
        $messages[] = "User '$newUsername' created successfully.";
        $users = loadUsers($usersFile);
    }
}

// Handle generate token (POST)
if (isset($_POST['generate_token'])) {
    checkCsrf();
    do {
        $token = bin2hex(random_bytes(16));
        $exists = false;
        foreach ($tokens as $t) {
            if ($t['token'] === $token) {
                $exists = true;
                break;
            }
        }
    } while ($exists);
    file_put_contents($tokensFile, "$token::active" . PHP_EOL, FILE_APPEND | LOCK_EX);
    logAction("Generated new token '$token'");
    $messages[] = "New token generated.";
    header("Location: mods.php?token_link=$token");
    exit;
}

// Handle clear log (POST)
if (isset($_POST['clear_log']) && $_POST['clear_log'] === '1') {
    checkCsrf();
    file_put_contents($logFile, "", LOCK_EX);
    logAction("Cleared edit log");
    header("Location: node-list.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
  <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-3 sm:space-y-0">
      <h1 class="text-2xl sm:text-3xl font-bold">Admin Panel</h1>
      <form method="POST" class="w-full sm:w-auto">
        <input type="hidden" name="clear_log" value="1" />
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
        <button type="submit" 
          class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-3 sm:py-2 rounded shadow font-semibold text-sm cursor-pointer"
          title="Clear log and return to editor">
          Back to Editor
        </button>
      </form>
    </div>

    <!-- Show success/error messages -->
    <?php if (!empty($messages)): ?>
      <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
        <?php foreach ($messages as $msg): ?>
          <p><?= htmlspecialchars($msg) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
        <?php foreach ($errors as $err): ?>
          <p><?= htmlspecialchars($err) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Create New User -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-3">Create New User</h2>
      <form method="POST" class="bg-white shadow-md rounded p-4 space-y-4" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
        <input 
          type="text" name="new_username" placeholder="Username" 
          class="p-3 border w-full rounded focus:outline-none focus:ring-2 focus:ring-green-500" required />
        <input 
          type="password" name="new_password" placeholder="Password (min 6 chars)" 
          class="p-3 border w-full rounded focus:outline-none focus:ring-2 focus:ring-green-500" required minlength="6" />
        <select 
          name="new_role" 
          class="p-3 border w-full rounded focus:outline-none focus:ring-2 focus:ring-green-500">
          <option value="admin">Admin</option>
          <option value="user" selected>User</option>
        </select>
        <button 
          type="submit" name="create" 
          class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded font-semibold shadow">
          Create
        </button>
      </form>
    </div>

    <!-- Existing Users -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-3">Existing Users</h2>
      <div class="overflow-x-auto rounded shadow-md bg-white">
        <table class="min-w-full border-collapse table-auto">
          <thead class="bg-gray-200">
            <tr>
              <th class="text-left px-4 py-2 whitespace-nowrap">Username</th>
              <th class="text-left px-4 py-2 whitespace-nowrap">Role</th>
              <th class="px-4 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($users)): ?>
              <tr><td colspan="3" class="px-4 py-3 text-gray-500 italic">No users found.</td></tr>
            <?php else: ?>
              <?php foreach ($users as $username => $data): ?>
                <tr class="border-t even:bg-gray-50">
                  <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($username) ?></td>
                  <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($data['role']) ?></td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <form method="POST" onsubmit="return confirm('Delete user <?= addslashes(htmlspecialchars($username)) ?>? This action cannot be undone.');" class="inline">
                      <input type="hidden" name="delete_user" value="<?= htmlspecialchars($username) ?>" />
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
                      <button type="submit" class="text-red-500 hover:underline bg-transparent border-0 p-0 cursor-pointer">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

<!-- Token Generator -->
<div class="mb-8">
  <h2 class="text-xl font-semibold mb-3">One-Time Registration Token</h2>
  <form method="POST" class="mb-4">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
    <button 
      type="submit" name="generate_token" 
      class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded shadow font-semibold">
      Generate Token
    </button>
  </form>
  <?php if (!empty($_GET['token_link'])): ?>
    <p class="p-3 bg-green-100 text-green-800 rounded break-all">
      New Token: <code><?= htmlspecialchars($_GET['token_link']) ?></code>
    </p>
  <?php endif; ?>
  <p class="mb-3 text-gray-600 text-sm">
    Copy this token and share it with the person who needs to register. Tokens can only be used once.
  </p>
  <?php if (!empty($tokens)): ?>
    <div class="overflow-x-auto rounded shadow-md bg-white">
      <table class="min-w-full border-collapse table-auto text-sm">
        <thead class="bg-gray-200">
          <tr>
            <th class="text-left px-4 py-2">Token</th>
            <th class="text-left px-4 py-2">Status</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tokens as $t): ?>
            <tr class="border-t even:bg-gray-50">
              <td class="px-4 py-2 break-all">
                <a href="register.php?token=<?= urlencode($t['token']) ?>" class="text-blue-600 hover:underline">
                  <?= htmlspecialchars($t['token']) ?>
                </a>
              </td>
              <td class="px-4 py-2"><?= htmlspecialchars($t['status']) ?></td>
              <td class="px-4 py-2 whitespace-nowrap">
                <form method="POST" onsubmit="return confirm('Delete this token?');" class="inline">
                  <input type="hidden" name="delete_token" value="<?= htmlspecialchars($t['token']) ?>" />
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
                  <button type="submit" class="text-red-500 hover:underline bg-transparent border-0 p-0 cursor-pointer">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-gray-500 italic">No tokens found.</p>
  <?php endif; ?>
</div>

    <!-- Edit Log -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-3">Last 10 Edit Log Entries</h2>
      <?php if (!empty($logEntries)): ?>
        <div class="bg-white rounded shadow p-4 max-h-48 overflow-auto font-mono text-xs whitespace-pre-line">
          <?php foreach ($logEntries as $entry): ?>
            <?= htmlspecialchars($entry) ?><br />
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-500 italic">No log entries found.</p>
      <?php endif; ?>
    </div>

  </div>
</body>
</html>
