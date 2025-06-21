<?php
// Path ke file JSON
$dataFile = 'data.json';

// Load data
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : ['tasks' => []];
$tasks = $data['tasks'];

// Untuk filtering dan editing
$filter = $_GET['filter'] ?? 'all';
$editTask = null;

// Filter task berdasarkan URL query
$filteredTasks = array_filter($tasks, function ($task) use ($filter) {
    if ($filter === 'done') return $task['status'] === 'completed';
    if ($filter === 'undone') return $task['status'] !== 'completed';
    return true;
});

// Statistik
$stats = [
    'total' => count($tasks),
    'pending' => count(array_filter($tasks, fn($t) => $t['status'] === 'pending')),
    'in_progress' => count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress')),
    'completed' => count(array_filter($tasks, fn($t) => $t['status'] === 'completed')),
];

// fungsi form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $newTask = [
            'id' => count($tasks) ? max(array_column($tasks, 'id')) + 1 : 1,
            'title' => $_POST['task'],
            'description' => '',
            'status' => 'pending'
        ];
        $tasks[] = $newTask;
    }

    if ($action === 'update') {
        foreach ($tasks as &$task) {
            if ($task['id'] == $_POST['id']) {
                $task['title'] = $_POST['task'];
                break;
            }
        }
        unset($task);
    }

    if ($action === 'toggle') {
        foreach ($tasks as &$task) {
            if ($task['id'] == $_POST['id']) {
                $task['status'] = $_POST['done'] === '1' ? 'completed' : 'pending';
                break;
            }
        }
        unset($task);
    }

    // Simpan data
    file_put_contents($dataFile, json_encode(['tasks' => $tasks], JSON_PRETTY_PRINT));

    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['filter']) ? '?filter=' . $_GET['filter'] : ''));
    exit;
}

// fungsi edit
if (isset($_GET['edit'])) {
    foreach ($tasks as $task) {
        if ($task['id'] == $_GET['edit']) {
            $editTask = $task;
            break;
        }
    }
}

// fungsi delete
if (isset($_GET['delete'])) {
    $tasks = array_filter($tasks, fn($t) => $t['id'] != $_GET['delete']);
    file_put_contents($dataFile, json_encode(['tasks' => array_values($tasks)], JSON_PRETTY_PRINT));
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['filter']) ? '?filter=' . $_GET['filter'] : ''));
    exit;
}
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
                <li><a href="<?= $_SERVER['PHP_SELF'] ?>" <?= $filter === 'all' ? 'class="active"' : '' ?>>Semua</a></li>
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
                    <a href="<?= $_SERVER['PHP_SELF'] ?>">❌ Batal</a>
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
        // Add smooth interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Animate task items on load
            const taskItems = document.querySelectorAll('.task-item');
            taskItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 300 + index * 50);
            });
        });
    </script>
</body>
</html>