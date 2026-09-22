<?php

session_start();

require_once("connect.php");


if (!isset($_SESSION['UserID'])) {

    echo "<h2>Please log in first.</h2>";
    echo "<p><a href='index.php?page=login'>Login here</a></p>";

    exit;
}

$UserID = $_SESSION['UserID'];

$message = "";




if (isset($_POST['add_task'])) {

    $TaskName = trim($_POST['TaskName']);
    $TaskDescription = trim($_POST['TaskDescription']);
    $DueDate = $_POST['DueDate'];

    if (empty($TaskName)) {

        $message = "Please enter a task name.";

    } else {

        $sql = "INSERT INTO to_dolist
                (UserID, TaskName, TaskDescription, DueDate, Completed)
                VALUES (?, ?, ?, ?, FALSE)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            die("Database error: " . $conn->error);

        }

        $stmt->bind_param(
            "isss",
            $UserID,
            $TaskName,
            $TaskDescription,
            $DueDate
        );

        if ($stmt->execute()) {

            $message = "Task added successfully.";

        } else {

            $message = "There was an error adding the task.";

        }

        $stmt->close();
    }
}




if (isset($_POST['complete_task'])) {

    $TaskID = intval($_POST['TaskID']);

    $sql = "UPDATE to_dolist
            SET Completed = TRUE
            WHERE TaskID = ?
            AND UserID = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        die("Database error: " . $conn->error);

    }

    $stmt->bind_param(
        "ii",
        $TaskID,
        $UserID
    );

    $stmt->execute();

    $stmt->close();
}




if (isset($_POST['delete_task'])) {

    $TaskID = intval($_POST['TaskID']);

    $sql = "DELETE FROM to_dolist
            WHERE TaskID = ?
            AND UserID = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        die("Database error: " . $conn->error);

    }

    $stmt->bind_param(
        "ii",
        $TaskID,
        $UserID
    );

    $stmt->execute();

    $stmt->close();
}




$sql = "SELECT *
        FROM to_dolist
        WHERE UserID = ?
        ORDER BY Completed ASC, DueDate ASC, CreatedDate DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database error: " . $conn->error);

}

$stmt->bind_param("i", $UserID);

$stmt->execute();

$result = $stmt->get_result();

?>

<div class="todo-page">

    <h2>To-do List</h2>

    <?php if (!empty($message)): ?>

        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>


   

    <div class="card">

        <h3>Add a Task</h3>

        <form method="POST" action="">

            <label for="TaskName">
                Task:
            </label>

            <input
                type="text"
                id="TaskName"
                name="TaskName"
                placeholder="e.g. Complete calculus homework"
                required
            >


            <label for="TaskDescription">
                Description:
            </label>

            <textarea
                id="TaskDescription"
                name="TaskDescription"
                placeholder="Add more information about the task..."
            ></textarea>


            <label for="DueDate">
                Due Date:
            </label>

            <input
                type="date"
                id="DueDate"
                name="DueDate"
            >


            <input
                type="submit"
                name="add_task"
                value="+ Add Task"
            >

        </form>

    </div>


   

    <h3>My Tasks</h3>

    <?php if ($result->num_rows > 0): ?>

        <div class="todo-list">

            <?php while ($task = $result->fetch_assoc()): ?>

                <div class="todo-item">

                    <div class="todo-information">

                        <?php if ($task['Completed']): ?>

                            <h3 class="completed-task">
                                <?php echo htmlspecialchars($task['TaskName']); ?>
                            </h3>

                        <?php else: ?>

                            <h3>
                                <?php echo htmlspecialchars($task['TaskName']); ?>
                            </h3>

                        <?php endif; ?>


                        <?php if (!empty($task['TaskDescription'])): ?>

                            <p>
                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $task['TaskDescription']
                                    )
                                );
                                ?>
                            </p>

                        <?php endif; ?>


                        <?php if (!empty($task['DueDate'])): ?>

                            <p>
                                <strong>Due:</strong>

                                <?php
                                echo htmlspecialchars(
                                    $task['DueDate']
                                );
                                ?>
                            </p>

                        <?php endif; ?>


                        <?php if ($task['Completed']): ?>

                            <p class="completed-text">
                                ✓ Completed
                            </p>

                        <?php endif; ?>

                    </div>


                    <div class="todo-actions">

                        <?php if (!$task['Completed']): ?>

                            <form method="POST" action="">

                                <input
                                    type="hidden"
                                    name="TaskID"
                                    value="<?php echo $task['TaskID']; ?>"
                                >

                                <input
                                    type="submit"
                                    name="complete_task"
                                    value="Complete"
                                >

                            </form>

                        <?php endif; ?>


                        <form method="POST" action="">

                            <input
                                type="hidden"
                                name="TaskID"
                                value="<?php echo $task['TaskID']; ?>"
                            >

                            <input
                                type="submit"
                                name="delete_task"
                                value="Delete"
                            >

                        </form>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="card">

            <p>
                You don't have any tasks yet.
            </p>

            <p>
                Add your first task above!
            </p>

        </div>

    <?php endif; ?>

</div>

<?php

$stmt->close();

?>