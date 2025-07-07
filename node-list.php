<?php
session_start();

$dataFile = 'data.json';
$usersFile = 'users.txt';
$logFile = 'edit-log.txt';

function loadUsers($file) {
    $users = [];
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            list($username, $passwordHash, $role) = explode(":", $line);
            $users[$username] = ['password' => $passwordHash, 'role' => $role];
        }
    }
    return $users;
}

$users = loadUsers($usersFile);

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (array_key_exists($username, $users)) {
        if (password_verify($password, $users[$username]['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $users[$username]['role'];
        } else {
            $error = "Invalid login";
        }
    } else {
        $error = "Invalid login";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: node-list.html");
    exit;
}

if (isset($_POST['save']) && $_SESSION['logged_in']) {
    $longs = $_POST['long'] ?? [];
    $shorts = $_POST['short'] ?? [];
    $ids = $_POST['id'] ?? [];
    $roles = $_POST['role'] ?? [];
    $newData = [];
    $seenIds = [];

    for ($i = 0; $i < count($longs); $i++) {
        $id = trim($ids[$i]);
        $long = trim($longs[$i]);
        $short = trim($shorts[$i]);
        $role = trim($roles[$i]);

        if ($long === "" && $short === "" && $id === "") continue;
        if ($id === "") continue;
        if (in_array($id, $seenIds)) continue;

        $newData[] = ["long" => $long, "short" => $short, "id" => $id, "role" => $role];
        $seenIds[] = $id;
    }

    usort($newData, function ($a, $b) {
        return strcasecmp($a['long'], $b['long']);
    });

    $logLine = date('Y-m-d H:i:s') . " - {$_SESSION['username']} updated node list (" . count($newData) . " entries)\n";
    file_put_contents($logFile, $logLine, FILE_APPEND);

    $jsonData = json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($dataFile, $jsonData);
    header("Location: node-list.php?saved=1");
    exit;
}

$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>MeshSTL Master List Editor</title>
  <link rel="icon" href="MeshSTL.webp" type="image/x-icon" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 min-h-screen p-4 sm:p-6">

<?php if (!isset($_SESSION['logged_in'])): ?>
  <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold text-center mb-6">Login to Edit</h2>
    <?php if (!empty($error)) echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>
    <form method="POST" class="space-y-4">
      <label class="block font-semibold">Username:</label>
      <input type="text" name="username" class="w-full border border-gray-300 rounded px-3 py-2" required />
      <label class="block font-semibold">Password:</label>
      <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2" required />
      <button type="submit" name="login" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Login</button>
    </form>
  </div>
<?php else: ?>
  <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
    <h2 class="text-3xl font-bold text-center sm:text-left">MeshSTL Master List Editor</h2>
    <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4">
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="mods.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 whitespace-nowrap text-center">Admin</a>
      <?php endif; ?>
      <a href="?logout=1" class="text-red-600 hover:underline whitespace-nowrap text-center">Logout</a>
    </div>
  </div>

  <?php if (isset($_GET['saved'])): ?>
    <p class="text-green-600 mb-4 text-center sm:text-left">Saved successfully!</p>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div class="overflow-x-auto border rounded shadow bg-white">
      <table class="min-w-full table-auto border-collapse">
        <thead>
          <tr class="bg-gray-200 text-left">
            <th class="p-3 border-b border-gray-300">Long Name</th>
            <th class="p-3 border-b border-gray-300">Short Name</th>
            <th class="p-3 border-b border-gray-300">User ID</th>
            <th class="p-3 border-b border-gray-300">Role</th>
            <th class="p-3 border-b border-gray-300">Actions</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($data as $row): ?>
          <tr class="border-b border-gray-300">
            <td class="p-2"><input type="text" name="long[]" value="<?=htmlspecialchars($row['long'])?>" class="w-full border rounded px-2 py-1" /></td>
            <td class="p-2"><input type="text" name="short[]" value="<?=htmlspecialchars($row['short'])?>" class="w-full border rounded px-2 py-1" /></td>
            <td class="p-2"><input type="text" name="id[]" value="<?=htmlspecialchars($row['id'])?>" class="w-full border rounded px-2 py-1" /></td>
            <td class="p-2">
              <select name="role[]" class="w-full border rounded px-2 py-1">
                <?php
                $roles = ["CLIENT", "CLIENT_MUTE", "CLIENT_HIDDEN", "TRACKER", "LOST_AND_FOUND", "SENSOR", "TAK", "TAK_TRACKER", "REPEATER", "ROUTER", "ROUTER_LATE", "ROUTER_CLIENT (Deprecated)", "UNKNOWN"];
                foreach ($roles as $r) {
                    $selected = ($row['role'] === $r) ? 'selected' : '';
                    echo "<option value=\"$r\" $selected>$r</option>";
                }
                ?>
              </select>
            </td>
            <td class="p-2 text-center">
              <button type="button" onclick="deleteRow(this)" class="text-red-500 hover:underline">Delete</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="flex flex-col sm:flex-row sm:justify-start gap-3">
      <button type="submit" name="save" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full sm:w-auto">Save</button>
      <button type="button" id="addRow" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 w-full sm:w-auto">Add New Row</button>
    </div>
  </form>
<?php endif; ?>

<script>
document.getElementById('addRow').addEventListener('click', function() {
  var tableBody = document.getElementById('tableBody');
  var newRow = document.createElement('tr');
  newRow.className = "border-b border-gray-300";
  newRow.innerHTML = `
    <td class="p-2"><input type="text" name="long[]" class="w-full border rounded px-2 py-1" /></td>
    <td class="p-2"><input type="text" name="short[]" class="w-full border rounded px-2 py-1" /></td>
    <td class="p-2"><input type="text" name="id[]" class="w-full border rounded px-2 py-1" /></td>
    <td class="p-2">
      <select name="role[]" class="w-full border rounded px-2 py-1">
        <option value="CLIENT">CLIENT</option>
        <option value="CLIENT_MUTE">CLIENT_MUTE</option>
        <option value="CLIENT_HIDDEN">CLIENT_HIDDEN</option>
        <option value="TRACKER">TRACKER</option>
        <option value="LOST_AND_FOUND">LOST_AND_FOUND</option>
        <option value="SENSOR">SENSOR</option>
        <option value="TAK">TAK</option>
        <option value="TAK_TRACKER">TAK_TRACKER</option>
        <option value="REPEATER">REPEATER</option>
        <option value="ROUTER">ROUTER</option>
        <option value="ROUTER_LATE">ROUTER_LATE</option>
        <option value="ROUTER_CLIENT (Deprecated)">ROUTER_CLIENT (Deprecated)</option>
        <option value="UNKNOWN">UNKNOWN</option>
      </select>
    </td>
    <td class="p-2 text-center"><button type="button" onclick="deleteRow(this)" class="text-red-500 hover:underline">Delete</button></td>
  `;
  tableBody.appendChild(newRow);
});

function deleteRow(button) {
  button.closest('tr').remove();
}
</script>

</body>
</html>
