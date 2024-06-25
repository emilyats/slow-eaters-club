<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $priority = $_POST['priority'];
        $due_date = $_POST['due_date'];

        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, priority, due_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $title, $description, $priority, $due_date);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['update'])) {
        $task_id = $_POST['task_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $priority = $_POST['priority'];
        $due_date = $_POST['due_date'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, priority = ?, due_date = ?, status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssiii", $title, $description, $priority, $due_date, $status, $task_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        $task_id = $_POST['task_id'];

        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

$tasks = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id ORDER BY due_date");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Management</title>
</head>
<body>
    <h1>Task Management</h1>
    <form method="POST" action="tasks.php">
        <input type="hidden" name="task_id" id="task_id">
        Title: <input type="text" name="title" id="title" required><br>
        Description: <textarea name="description" id="description" required></textarea><br>
        Priority: 
        <select name="priority" id="priority">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select><br>
        Due Date: <input type="datetime-local" name="due_date" id="due_date" required><br>
        Status: 
        <select name="status" id="status">
            <option value="pending">Pending</option>
            <option value="in-progress">In Progress</option>
            <option value="completed">Completed</option>
        </select><br>
        <button type="submit" name="create">Create Task</button>
        <button type="submit" name="update">Update Task</button>
    </form>
    <hr>
    <h2>Tasks</h2>
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Priority</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($task = $tasks->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $task['title']; ?></td>
            <td><?php echo $task['description']; ?></td>
            <td><?php echo $task['priority']; ?></td>
            <td><?php echo $task['due_date']; ?></td>
            <td><?php echo $task['status']; ?></td>
            <td>
                <form method="POST" action="tasks.php" style="display:inline;">
                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                    <button type="submit" name="delete">Delete</button>
                </form>
                <button onclick="editTask(<?php echo htmlspecialchars(json_encode($task)); ?>)">Edit</button>
            </td>
        </tr>
        <?php } ?>
    </table>
    <script>
        function editTask(task) {
            document.getElementById('task_id').value = task.id;
            document.getElementById('title').value = task.title;
            document.getElementById('description').value = task.description;
            document.getElementById('priority').value = task.priority;
            document.getElementById('due_date').value = task.due_date.replace(" ", "T");
            document.getElementById('status').value = task.status;
        }
    </script>
</body>
</html>
