
<?php
// This page is included by index.php, which should start the session.
require_once __DIR__ . '/connect.php';

if (!isset($_SESSION['UserID'])) {
    echo '<p>Please log in to use your stationery checklist.</p>';
    return;
}

$userID = (int) $_SESSION['UserID'];

// Create a dedicated table for checklist items if it does not exist.
$conn->query("
    CREATE TABLE IF NOT EXISTS stationery_checklist_items (
        ItemID INT AUTO_INCREMENT PRIMARY KEY,
        UserID INT NOT NULL,
        ItemName VARCHAR(150) NOT NULL,
        IsPacked TINYINT(1) NOT NULL DEFAULT 0
    )
");

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $itemName = trim($_POST['item_name'] ?? '');

        if ($itemName === '') {
            $error = 'Please enter a stationery item.';
        } elseif (strlen($itemName) > 150) {
            $error = 'The item name must be 150 characters or fewer.';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO stationery_checklist_items
                (UserID, ItemName) VALUES (?, ?)"
            );
            $stmt->bind_param('is', $userID, $itemName);
            $stmt->execute();
            $stmt->close();

            header('Location: index.php?page=stationerychecklist');
            exit;
        }
    }

    if ($action === 'toggle') {
        $itemID = (int) ($_POST['item_id'] ?? 0);

        $stmt = $conn->prepare(
            "UPDATE stationery_checklist_items
             SET IsPacked = 1 - IsPacked
             WHERE ItemID = ? AND UserID = ?"
        );
        $stmt->bind_param('ii', $itemID, $userID);
        $stmt->execute();
        $stmt->close();

        header('Location: index.php?page=stationerychecklist');
        exit;
    }

    if ($action === 'delete') {
        $itemID = (int) ($_POST['item_id'] ?? 0);

        $stmt = $conn->prepare(
            "DELETE FROM stationery_checklist_items
             WHERE ItemID = ? AND UserID = ?"
        );
        $stmt->bind_param('ii', $itemID, $userID);
        $stmt->execute();
        $stmt->close();

        header('Location: index.php?page=stationerychecklist');
        exit;
    }
}

// Only display checklist items belonging to the logged-in user.
$stmt = $conn->prepare(
    "SELECT ItemID, ItemName, IsPacked
     FROM stationery_checklist_items
     WHERE UserID = ?
     ORDER BY ItemID DESC"
);
$stmt->bind_param('i', $userID);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

$total = count($items);
$packed = count(array_filter($items, fn($item) => (int)$item['IsPacked'] === 1));
?>

<div class="checklist-page">
    <h2>My Stationery Checklist</h2>
    <p>Check your stationery before heading to school!</p>

    <?php if ($error !== ''): ?>
        <p class="checklist-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <div class="checklist-progress">
        <strong><?= $packed ?> of <?= $total ?> items packed</strong>
        <div class="progress-track">
            <div class="progress-fill"
                 style="width: <?= $total ? round($packed / $total * 100) : 0 ?>%">
            </div>
        </div>
    </div>

    <form method="POST" action="index.php?page=stationerychecklist"
          class="checklist-add">
        <input type="hidden" name="action" value="add">
        <input type="text" name="item_name"
               placeholder="Add stationery (e.g. pen)"
               maxlength="150" required>
        <button type="submit">+ Add item</button>
    </form>

    <?php if (empty($items)): ?>
        <p class="checklist-empty">
            Your checklist is empty. Add your first stationery item above!
        </p>
    <?php else: ?>
        <div class="checklist-items">
            <?php foreach ($items as $item): ?>
                <div class="checklist-item">
                    <form method="POST"
                          action="index.php?page=stationerychecklist"
                          class="checklist-toggle">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="item_id"
                               value="<?= (int)$item['ItemID'] ?>">

                        <button type="submit"
                                class="check-button"
                                aria-label="Toggle packed status">
                            <?= (int)$item['IsPacked'] === 1 ? '☑' : '☐' ?>
                        </button>

                        <span class="<?= (int)$item['IsPacked'] === 1 ? 'packed' : '' ?>">
                            <?= htmlspecialchars($item['ItemName'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </form>

                    <form method="POST"
                          action="index.php?page=stationerychecklist"
                          onsubmit="return confirm('Delete this item?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="item_id"
                               value="<?= (int)$item['ItemID'] ?>">
                        <button type="submit" class="delete-button">
                            Delete
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
