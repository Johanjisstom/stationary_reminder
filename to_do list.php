
<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/connect.php';


$returnPage = 'index.php?page=to_dolist';

// Require login.
if (!isset($_SESSION['UserID'])) {
    echo '<div class="todo-page">';
    echo '<h2>Please log in first.</h2>';
    echo '<p><a href="index.php?page=login">Login here</a></p>';
    echo '</div>';
    return;
}

$UserID = (int) $_SESSION['UserID'];



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    if (isset($_POST['add_task'])) {

        $TaskName = trim($_POST['Task_name'] ?? '');
        $TaskDescription = trim($_POST['Task_description'] ?? '');
        $DueDate = trim($_POST['Due_date'] ?? '');

        if ($TaskName === '') {
            $_SESSION['todo_message'] = 'Please enter a task name.';
        } else {

            // An empty due date is saved as NULL.
            $sql = "INSERT INTO to_dolist
                    (UserID, Task_name, Task_description, Due_date, Completed)
                    VALUES (?, ?, ?, NULLIF(?, ''), 0)";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param(
                    'isss',
                    $UserID,
                    $TaskName,
                    $TaskDescription,
                    $DueDate
                );

                if ($stmt->execute()) {
                    $_SESSION['todo_message'] = 'Task added successfully!';
                } else {
                    error_log('Add task error: ' . $stmt->error);
                    $_SESSION['todo_message'] =
                        'Unable to add the task. Please check the database.';
                }

                $stmt->close();
            } else {
                error_log('Prepare add task error: ' . $conn->error);
                $_SESSION['todo_message'] = 'Unable to prepare the task.';
            }
        }
    }

   
    elseif (isset($_POST['complete_task'])) {

        $TaskID = (int) ($_POST['TaskID'] ?? 0);

        $stmt = $conn->prepare(
            "UPDATE to_dolist
             SET Completed = 1
             WHERE TaskID = ? AND UserID = ?"
        );

        if ($stmt) {
            $stmt->bind_param('ii', $TaskID, $UserID);
            $stmt->execute();

            $_SESSION['todo_message'] =
                $stmt->affected_rows > 0
                ? 'Task marked as completed!'
                : 'Task was not changed. It may already be completed.';

            $stmt->close();
        } else {
            error_log('Complete task error: ' . $conn->error);
            $_SESSION['todo_message'] = 'Unable to complete the task.';
        }
    }

    
    elseif (isset($_POST['delete_task'])) {

        $TaskID = (int) ($_POST['TaskID'] ?? 0);

        $stmt = $conn->prepare(
            "DELETE FROM to_dolist
             WHERE TaskID = ? AND UserID = ?"
        );

        if ($stmt) {
            $stmt->bind_param('ii', $TaskID, $UserID);
            $stmt->execute();

            $_SESSION['todo_message'] =
                $stmt->affected_rows > 0
                ? 'Task deleted successfully.'
                : 'Task could not be found.';

            $stmt->close();
        } else {
            error_log('Delete task error: ' . $conn->error);
            $_SESSION['todo_message'] = 'Unable to delete the task.';
        }
    }

    
    header('Location: ' . $returnPage);
    exit;
}



$message = $_SESSION['todo_message'] ?? '';
unset($_SESSION['todo_message']);



$sql = "SELECT TaskID, UserID, Task_name, Task_description,
               Due_date, Completed, Created_date
        FROM to_dolist
        WHERE UserID = ?
        ORDER BY Completed ASC, Due_date ASC, Created_date DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('Load tasks error: ' . $conn->error);
    echo '<div class="todo-page">';
    echo '<h2>My To-Do List</h2>';
    echo '<p>Unable to load your tasks. Please check your database structure.</p>';
    echo '</div>';
    return;
}

$stmt->bind_param('i', $UserID);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="todo-page">

    <h2>My To-Do List</h2>

    <p class="todo-intro">
        Organise your schoolwork and remember important tasks.
    </p>

    <?php if ($message !== ''): ?>
        <p class="todo-message" role="status">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <!-- ADD TASK FORM -->
    <section class="todo-card">

        <h3>Add a Task</h3>

        <form method="POST" action="index.php?page=to_do_list">

            <label for="Task_name">Task name:</label>

            <input
                type="text"
                id="Task_name"
                name="Task_name"
                placeholder="e.g. Complete homework"
                maxlength="255"
                required
            >

            <label for="Task_description">Description:</label>

            <textarea
                id="Task_description"
                name="Task_description"
                placeholder="Add more information about your task..."
                rows="4"
            ></textarea>

            <label for="Due_date">Due date:</label>

            <input
                type="date"
                id="Due_date"
                name="Due_date"
            >

            <button
                type="submit"
                name="add_task"
                value="1"
                class="todo-add-button"
            >
                + Add Task
            </button>

        </form>

    </section>

    <!-- TASK LIST -->
    <h3 class="todo-section-title">My Tasks</h3>

    <?php if ($result->num_rows > 0): ?>

        <div class="todo-list">

            <?php while ($task = $result->fetch_assoc()): ?>

                <article class="todo-item">

                    <div class="todo-information">

                        <h3 class="<?= (int)$task['Completed'] === 1
                            ? 'completed-task'
                            : '' ?>">

                            <?= htmlspecialchars(
                                $task['Task_name'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </h3>

                        <?php if (!empty($task['Task_description'])): ?>

                            <p class="todo-description">
                                <?= nl2br(htmlspecialchars(
                                    $task['Task_description'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )) ?>
                            </p>

                        <?php endif; ?>

                        <?php if (!empty($task['Due_date'])): ?>

                            <p class="todo-due-date">
                                <strong>Due:</strong>
                                <?= htmlspecialchars(
                                    $task['Due_date'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>

                        <?php endif; ?>

                        <?php if ((int)$task['Completed'] === 1): ?>

                            <p class="todo-completed-text">
                                ✓ Completed
                            </p>

                        <?php else: ?>

                            <p class="todo-pending-text">
                                In progress
                            </p>

                        <?php endif; ?>

                    </div>

                    <!-- TASK ACTION BUTTONS -->
                    <div class="todo-actions">

                        <?php if ((int)$task['Completed'] !== 1): ?>

                            <form
                                method="POST"
                                action="index.php?page=to_do_list"
                            >

                                <input
                                    type="hidden"
                                    name="TaskID"
                                    value="<?= (int)$task['TaskID'] ?>"
                                >

                                <button
                                    type="submit"
                                    name="complete_task"
                                    value="1"
                                    class="todo-complete-button"
                                >
                                    ✓ Complete
                                </button>

                            </form>

                        <?php endif; ?>

                        <form
                            method="POST"
                            action="index.php?page=to_do_list"
                            onsubmit="return confirm('Are you sure you want to delete this task?');"
                        >

                            <input
                                type="hidden"
                                name="TaskID"
                                value="<?= (int)$task['TaskID'] ?>"
                            >

                            <button
                                type="submit"
                                name="delete_task"
                                value="1"
                                class="todo-delete-button"
                            >
                                Delete
                            </button>

                        </form>

                    </div>

                </article>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <section class="todo-empty-state">

            <h3>No tasks yet!</h3>

            <p>
                Add your first task above to start organising
                your schoolwork.
            </p>

        </section>

    <?php endif; ?>

</div>

<?php
$stmt->close();
?>
