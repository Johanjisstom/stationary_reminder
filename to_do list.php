
<?php


require_once __DIR__ . '/connect.php';

if (!isset($_SESSION['UserID'])) {
    echo '<h2>Please log in first.</h2>';
    echo '<p><a href="index.php?page=login">Login here</a></p>';
    return;
}

$UserID = (int) $_SESSION['UserID'];
$message = '';


$returnPage = 'index.php?page=to_dolist';

// Add a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $TaskName = trim($_POST['TaskName'] ?? '');
    $TaskDescription = trim($_POST['TaskDescription'] ?? '');
    $DueDate = trim($_POST['DueDate'] ?? '');

    if ($TaskName === '') {
        $_SESSION['todo_message'] = 'Please enter a task name.';
    } else {
        $sql = "INSERT INTO to_dolist
                (UserID, TaskName, TaskDescription, DueDate, Completed)
                VALUES (?, ?, ?, NULLIF(?, ''), 0)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            error_log('To-do insert error: ' . $conn->error);
            $_SESSION['todo_message'] = 'Could not prepare the task. Please check the database structure.';
        } else {
            $stmt->bind_param(
                'isss',
                $UserID,
                $TaskName,
                $TaskDescription,
                $DueDate
            );

            if ($stmt->execute()) {
                $_SESSION['todo_message'] = 'Task added successfully.';
            } else {
                error_log('To-do insert error: ' . $stmt->error);
                $_SESSION['todo_message'] = 'Could not add the task. Please check your database fields.';
            }

            $stmt->close();
        }
    }

    header('Location: ' . $returnPage);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_task'])) {
    $TaskID = (int) ($_POST['TaskID'] ?? 0);

    $stmt = $conn->prepare(
        "UPDATE to_dolist
         SET Completed = 1
         WHERE TaskID = ? AND UserID = ?"
    );

    if ($stmt) {
        $stmt->bind_param('ii', $TaskID, $UserID);
        $stmt->execute();
        $stmt->close();

        $_SESSION['todo_message'] = 'Task updated.';
    } else {
        error_log('To-do completion error: ' . $conn->error);
        $_SESSION['todo_message'] = 'Could not update the task.';
    }

    header('Location: ' . $returnPage);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
    $TaskID = (int) ($_POST['TaskID'] ?? 0);

    $stmt = $conn->prepare(
        "DELETE FROM to_dolist
         WHERE TaskID = ? AND UserID = ?"
    );

    if ($stmt) {
        $stmt->bind_param('ii', $TaskID, $UserID);
        $stmt->execute();
        $stmt->close();

        $_SESSION['todo_message'] = 'Task deleted.';
    } else {
        error_log('To-do delete error: ' . $conn->error);
        $_SESSION['todo_message'] = 'Could not delete the task.';
    }

    header('Location: ' . $returnPage);
    exit;
}


if (isset($_SESSION['todo_message'])) {
    $message = $_SESSION['todo_message'];
    unset($_SESSION['todo_message']);
}


$sql = "SELECT *
        FROM to_dolist
        WHERE UserID = ?
        ORDER BY Completed ASC, DueDate ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('To-do select error: ' . $conn->error);
    echo '<div class="todo-page"><p>Could not load your tasks. Please check the database structure.</p></div>';
    return;
}

$stmt->bind_param('i', $UserID);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="todo-page">
    <h2>To-do List</h2>

    <?php if ($message !== ''): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <div class="card">
        <h3>Add a Task</h3>

        <form method="POST" action="index.php?page=to_do_list">
            <label for="TaskName">Task:</label>
            <input
                type="text"
                id="TaskName"
                name="TaskName"
                placeholder="e.g. Complete homework"
                maxlength="255"
                required
            >

            <label for="TaskDescription">Description:</label>
            <textarea
                id="TaskDescription"
                name="TaskDescription"
                placeholder="Add more information about the task..."
            ></textarea>

            <label for="DueDate">Due Date:</label>
            <input type="date" id="DueDate" name="DueDate">

            <button type="submit" name="add_task" value="1">
                + Add Task
            </button>
        </form>
    </div>

    <h3>My Tasks</h3>

    <?php if ($result->num_rows > 0): ?>
        <div class="todo-list">
            <?php while ($task = $result->fetch_assoc()): ?>
                <div class="todo-item">
                    <div class="todo-information">
                        <h3 class="<?= $task['Completed'] ? 'completed-task' : '' ?>">
                            <?= htmlspecialchars($task['TaskName'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                        <?php if (!empty($task['TaskDescription'])): ?>
                            <p>
                                <?= nl2br(htmlspecialchars($task['TaskDescription'], ENT_QUOTES, 'UTF-8')) ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($task['DueDate'])): ?>
                            <p>
                                <strong>Due:</strong>
                                <?= htmlspecialchars($task['DueDate'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($task['Completed']): ?>
                            <p class="completed-text">✓ Completed</p>
                        <?php endif; ?>
                    </div>

                    <div class="todo-actions">
                        <?php if (!$task['Completed']): ?>
                            <form method="POST" action="index.php?page=to_do_list">
                                <input type="hidden" name="TaskID" value="<?= (int) $task['TaskID'] ?>">
                                <button type="submit" name="complete_task" value="1">
                                    Complete
                                </button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" action="index.php?page=to_do_list"
                              onsubmit="return confirm('Delete this task?');">
                            <input type="hidden" name="TaskID" value="<?= (int) $task['TaskID'] ?>">
                            <button type="submit" name="delete_task" value="1">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <p>You don't have any tasks yet.</p>
            <p>Add your first task above!</p>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
?>
