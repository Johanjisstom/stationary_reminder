
<?php
// index.php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connect to the database if the connection file exists.
require_once __DIR__ . '/connect.php';

// Read the requested page.
$page = $_GET['page'] ?? 'home';

// Map page names to their PHP files.
// Keep these keys consistent with your navigation links.
$routes = [
    'home'                 => null,
    'to_do_list'           => 'to_do list.php',
    'todo'                 => 'to_do list.php',
    'sticky_notes'         => 'Sticky_notes.php',
    'stationary_checklist' => 'stationery_checklist.php',
    'stationery_checklist' => 'stationery_checklist.php',
    'search'               => 'search.php',
    'profile'              => 'profile.php',
    'login'                => 'login.php',
    'signup'               => 'signup.php',
];

// Unknown page names return to the homepage.
if (!array_key_exists($page, $routes)) {
    $page = 'home';
}

$pageFile = $routes[$page];
$pageContent = '';

// Include the requested page before rendering the shared layout.
// This lets PHP redirects run before the HTML is sent.
if ($pageFile !== null) {
    $fullPath = __DIR__ . '/' . $pageFile;

    if (is_file($fullPath)) {
        ob_start();
        include $fullPath;
        $pageContent = ob_get_clean();
    } else {
        $pageContent =
            '<div class="page-error">' .
            '<h2>Page not found</h2>' .
            '<p>The file <strong>' .
            htmlspecialchars($pageFile, ENT_QUOTES, 'UTF-8') .
            '</strong> could not be found in your project folder.</p>' .
            '<p>Please check the filename in index.php.</p>' .
            '</div>';
    }
} else {
    $pageContent = '
        <section class="home-page">
            <h2>Welcome to Stationery Reminder</h2>
            <p>Keep track of your stationery, tasks and school day.</p>
            <div class="home-links">
                <a href="index.php?page=stationary_checklist">
                    Stationery Checklist
                </a>
                <a href="index.php?page=to_do_list">
                    To-Do Lists
                </a>
                <a href="index.php?page=sticky_notes">
                    Sticky Notes
                </a>
                <a href="index.php?page=search">
                    Search
                </a>
            </div>
        </section>
    ';
}

// If an included page redirected and exited, execution stops above.
// Otherwise, display the shared layout.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stationery Reminder</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <h1>Stationery Reminder</h1>
        <p>Stay organised and ready for school.</p>
    </header>

    <nav class="main-navigation">
        <a href="index.php">Home</a>
        <a href="index.php?page=stationary_checklist">
            Stationery Checklist
        </a>
        <a href="index.php?page=to_do_list">To-Do Lists</a>
        <a href="index.php?page=sticky_notes">Sticky Notes</a>
        <a href="index.php?page=search">Search</a>
        <a href="index.php?page=profile">Profile</a>
    </nav>

    <main class="main-content">
        <?= $pageContent ?>
    </main>

    <footer class="site-footer">
        <p>Stationery Reminder</p>
    </footer>
</body>
</html>
<?php ob_end_flush(); ?>
