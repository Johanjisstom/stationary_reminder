
<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$userID = $_SESSION['UserID'] ?? $_SESSION['User_ID'] ?? null;

if (!$userID) {
    echo '<div class="profile-page">
            <h2>My Profile</h2>
            <p>Please log in to view your profile.</p>
            <a href="index.php?page=login">Go to Login</a>
          </div>';
    return;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo '<p>Database connection error. Please check connect.php.</p>';
    return;
}

$message = '';
$user = null;


$columns = [];
$columnResult = $conn->query("SHOW COLUMNS FROM users");

if ($columnResult) {
    while ($column = $columnResult->fetch_assoc()) {
        $columns[strtolower($column['Field'])] = $column['Field'];
    }
}


function profileColumn($columns, $candidates) {
    foreach ($candidates as $candidate) {
        $key = strtolower($candidate);

        if (isset($columns[$key])) {
            return $columns[$key];
        }
    }

    return null;
}

$idColumn = profileColumn(
    $columns,
    ['User_ID', 'UserID', 'User_Id', 'ID', 'id']
);

$nameColumn = profileColumn(
    $columns,
    ['User_Name', 'Username', 'UserName', 'Name']
);

$emailColumn = profileColumn(
    $columns,
    ['User_Email', 'Email', 'UserEmail']
);

$roleColumn = profileColumn(
    $columns,
    ['Role', 'User_Role', 'UserRole']
);

if (!$idColumn || !$nameColumn || !$emailColumn) {
    echo '<div class="profile-page">
            <h2>My Profile</h2>
            <p>The users table is missing a required ID, username or email column.
            Please check its Structure tab in phpMyAdmin.</p>
          </div>';
    return;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['update_profile'])) {

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($username === '' || $email === '') {
        $message = 'Please fill in both fields.';
    } elseif (strlen($username) > 100) {
        $message = 'Your username must be 100 characters or fewer.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } elseif (strlen($email) > 254) {
        $message = 'Your email address is too long.';
    } else {
        
        $sql = "UPDATE users
                SET `$nameColumn` = ?, `$emailColumn` = ?
                WHERE `$idColumn` = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $id = (int) $userID;
            $stmt->bind_param('ssi', $username, $email, $id);

            try {
                if ($stmt->execute()) {
                    $message = 'Your profile has been saved.';
                } else {
                    $message = 'Unable to save your profile.';
                }
            } catch (mysqli_sql_exception $e) {
                $message = 'Unable to save your profile. This email may already be in use.';
            }

            $stmt->close();
        } else {
            $message = 'Unable to prepare the profile update.';
        }
    }
}


$selectColumns = "`$idColumn`, `$nameColumn`, `$emailColumn`";

if ($roleColumn) {
    $selectColumns .= ", `$roleColumn`";
}

$sql = "SELECT $selectColumns
        FROM users
        WHERE `$idColumn` = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $id = (int) $userID;
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
}

if (!$user) {
    echo '<div class="profile-page">
            <h2>My Profile</h2>
            <p>Your account could not be found. Please log out and log in again.</p>
          </div>';
    return;
}

$displayName = $user[$nameColumn] ?? '';
$displayEmail = $user[$emailColumn] ?? '';
$displayRole = $roleColumn
    ? ($user[$roleColumn] ?? 'Student')
    : 'Student';
?>

<div class="profile-page">
    <h2>My Profile</h2>
    <p class="profile-intro">
        Manage your account details for Stationery Reminder.
    </p>

    <?php if ($message !== ''): ?>
        <div class="profile-message" role="status">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <section class="profile-card">
        <div class="profile-avatar">
            <?= htmlspecialchars(
                strtoupper(substr($displayName, 0, 1)),
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>

        <h3>
            <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
        </h3>

        <p class="profile-role">
            Account role:
            <?= htmlspecialchars((string) $displayRole, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <form method="post" action="index.php?page=profile">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                maxlength="100"
                value="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                maxlength="254"
                value="<?= htmlspecialchars($displayEmail, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <button type="submit" name="update_profile" value="1">
                Save Changes
            </button>
        </form>

        <p class="profile-note">
            Keep your details up to date so your account information stays accurate.
        </p>
    </section>
</div>
