<?php
// Konfigurasi database
$host = 'localhost';
$dbname = 'todolist';
$username = 'root';
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Tambah tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $stmt = $pdo->prepare("INSERT INTO tasks (title, status) VALUES (?, 'pending')");
    $stmt->execute([htmlspecialchars($_POST['task'])]);
    header('Location: index.php');
    exit();
}

// Toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'toggle') {
    $newStatus = $_POST['done'] === '1' ? 'completed' : 'pending';
    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $_POST['id']]);
    header('Location: index.php');
    exit();
}

// Hapus tugas
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: index.php');
    exit();
}

// Get data untuk edit
$editTask = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editTask = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $stmt = $pdo->prepare("UPDATE tasks SET title = ? WHERE id = ?");
    $stmt->execute([htmlspecialchars($_POST['task']), $_POST['id']]);
    header('Location: index.php');
    exit();
}

// Get statistics untuk dashboard
$statsQuery = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM tasks
";
$statsStmt = $pdo->query($statsQuery);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Filter tampilan
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM tasks";
$params = [];

switch ($filter) {
    case 'done':
        $sql .= " WHERE status = 'completed'";
        break;
    case 'undone':
        $sql .= " WHERE status IN ('pending', 'in_progress')";
        break;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$filteredTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern To-Do List</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>✨ My Tasks</h1>
            <p>Organize your day, achieve your goals</p>
        </div>

        <!-- Navigation -->
        <nav>
            <div class="nav-title">📋 To-Do List</div>
            <ul>
                <li><a href="index.php" <?= $filter === 'all' ? 'class="active"' : '' ?>>Semua</a></li>
                <li><a href="?filter=done" <?= $filter === 'done' ? 'class="active"' : '' ?>>Selesai</a></li>
                <li><a href="?filter=undone" <?= $filter === 'undone' ? 'class="active"' : '' ?>>Belum Selesai</a></li>
            </ul>
        </nav>

        <!-- Dashboard Stats -->
        <div class="dashboard">
            <div class="stat-card total">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card progress">
                <div class="stat-number"><?= $stats['in_progress'] ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card completed">
                <div class="stat-number"><?= $stats['completed'] ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <!-- Main Content -->
        <main>
            <!-- Form -->
            <?php if ($editTask): ?>
                <form method="POST" class="form-edit">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $editTask['id'] ?>">
                    <input type="text" name="task" value="<?= htmlspecialchars($editTask['title']) ?>" placeholder="Edit task..." required>
                    <button type="submit">💾 Simpan</button>
                    <a href="index.php">❌ Batal</a>
                </form>
            <?php else: ?>
                <form method="POST" class="form-create">
                    <input type="hidden" name="action" value="create">
                    <input type="text" name="task" placeholder="What needs to be done? ✍️" required>
                    <button type="submit">➕ Tambah Task</button>
                </form>
            <?php endif; ?>

            <!-- Daftar Tugas -->
            <div class="task-list">
                <?php foreach ($filteredTasks as $task): ?>
                    <div class="task-item <?= $task['status'] === 'completed' ? 'done' : '' ?>">
                        <form method="POST" class="checkbox-form">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                            <input type="hidden" name="done" value="<?= $task['status'] === 'completed' ? '0' : '1' ?>">
                            <input type="checkbox" onchange="this.form.submit()" <?= $task['status'] === 'completed' ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($task['title']) ?></span>
                        </form>
                        <div class="actions">
                            <a href="?edit=<?= $task['id'] ?>">✏️ Edit</a>
                            <a href="?delete=<?= $task['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this task?')">🗑️ Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty State -->
            <?php if (empty($filteredTasks)): ?>
                <div class="empty-state">
                    <span class="emoji">
                        <?= $filter === 'done' ? '🎯' : ($filter === 'undone' ? '🎉' : '📝') ?>
                    </span>
                    <h3>
                        <?= $filter === 'done' ? 'No completed tasks yet' : 
                           ($filter === 'undone' ? 'All tasks completed!' : 'No tasks yet') ?>
                    </h3>
                    <p>
                        <?= $filter === 'done' ? 'Complete some tasks to see them here' : 
                           ($filter === 'undone' ? 'Great job! You\'ve completed everything' : 'Add your first task to get started') ?>
                    </p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Add some smooth interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.animation = `fadeInUp 0.6s ease forwards ${index * 0.1}s`;
            });

            // Animate task items on load
            const taskItems = document.querySelectorAll('.task-item');
            taskItems.forEach((item, index) => {
                item.style.animation = `fadeInUp 0.6s ease forwards ${0.3 + index * 0.05}s`;
            });
        });

        // Add keyframe animations via JavaScript
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>