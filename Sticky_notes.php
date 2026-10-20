
<?php
// sticky_notes.php
// index.php should start the session and include connect.php.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check that the user is logged in.
if (!isset($_SESSION['UserID'])) {
    echo "<p>Please log in to view your sticky notes.</p>";
    exit;
}

$userID = (int) $_SESSION['UserID'];
$message = "";

// Check the database connection.
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection error. Please check connect.php.");
}

// Add a sticky note.
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_note"])) {

    $title = trim($_POST["note_title"] ?? "");
    $content = trim($_POST["note_content"] ?? "");

    if ($title === "") {
        $message = "Please enter a title for your sticky note.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO sticky_notes (UserID, Note_title, Note_content)
             VALUES (?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param("iss", $userID, $title, $content);

            if ($stmt->execute()) {
                $message = "Sticky note added successfully.";
            } else {
                $message = "Could not save the sticky note.";
            }

            $stmt->close();
        } else {
            $message = "Could not prepare the database query.";
        }
    }
}

// Delete a sticky note belonging to this user.
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete_note"])) {

    $noteID = filter_input(
        INPUT_POST,
        "note_id",
        FILTER_VALIDATE_INT
    );

    if ($noteID) {
        $stmt = $conn->prepare(
            "DELETE FROM sticky_notes
             WHERE NoteID = ? AND UserID = ?"
        );

        if ($stmt) {
            $stmt->bind_param("ii", $noteID, $userID);
            $stmt->execute();
            $stmt->close();

            $message = "Sticky note deleted if it belonged to your account.";
        }
    }
}

// Retrieve this user's sticky notes.
$stmt = $conn->prepare(
    "SELECT NoteID, Note_title, Note_content, Created_date
     FROM sticky_notes
     WHERE UserID = ?
     ORDER BY NoteID DESC"
);

$notes = [];

if ($stmt) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }

    $stmt->close();
}
?>

<div class="sticky-notes-page">
    <h2>My Sticky Notes</h2>

    <?php if ($message !== ""): ?>
        <p class="sticky-message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="post"
          action="index.php?page=sticky_notes"
          class="sticky-note-form">

        <label for="note_title">Note title</label>
        <input
            type="text"
            id="note_title"
            name="note_title"
            maxlength="150"
            required
        >

        <label for="note_content">Note</label>
        <textarea
            id="note_content"
            name="note_content"
            rows="4"
            placeholder="Write a reminder..."
        ></textarea>

        <button type="submit" name="add_note" value="1">
            + Add Sticky Note
        </button>
    </form>

    <div class="sticky-notes-grid">
        <?php if (count($notes) === 0): ?>
            <p>You have no sticky notes yet. Add one above!</p>
        <?php else: ?>
            <?php foreach ($notes as $note): ?>
                <article class="sticky-note-card">
                    <h3>
                        <?= htmlspecialchars(
                            $note["NoteTitle"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </h3>

                    <p><?= nl2br(htmlspecialchars(
                        $note["NoteContent"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    )) ?></p>

                    <?php if (!empty($note["CreatedDate"])): ?>
                        <small>
                            Created:
                            <?= htmlspecialchars(
                                $note["CreatedDate"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </small>
                    <?php endif; ?>

                    <form method="post"
                          action="index.php?page=sticky_notes"
                          onsubmit="return confirm('Delete this sticky note?');">

                        <input
                            type="hidden"
                            name="note_id"
                            value="<?= (int) $note["NoteID"] ?>"
                        >

                        <button type="submit"
                                name="delete_note"
                                value="1"
                                class="delete-note-button">
                            Delete
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
