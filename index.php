<?php
// ==========================================
// AULA 01: O CÓDIGO SPAGHETTI (Tudo misturado)
// ==========================================

// 1. CONEXÃO COM O BANCO DE DADOS E CRIAÇÃO DA TABELA (Acoplamento de Infraestrutura)
$dbFile = __DIR__ . '/tasks.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    due_date TEXT,
    responsible TEXT,
    done INTEGER DEFAULT 0
)");

// Verificar e adicionar colunas caso a tabela já exista
$columns = $pdo->query("PRAGMA table_info(tasks)")->fetchAll(PDO::FETCH_COLUMN, 1);

if (!in_array('description', $columns)) {
    $pdo->exec("ALTER TABLE tasks ADD COLUMN description TEXT");
}

if (!in_array('due_date', $columns)) {
    $pdo->exec("ALTER TABLE tasks ADD COLUMN due_date TEXT");
}

if (!in_array('responsible', $columns)) {
    $pdo->exec("ALTER TABLE tasks ADD COLUMN responsible TEXT");
}

// 2. LÓGICA DE NEGÓCIO E CONTROLE DE REQUISIÇÕES MISTURADOS
$error = '';

// Criar nova tarefa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $due_date = trim($_POST['due_date'] ?? '');
    $responsible = trim($_POST['responsible'] ?? '');

    // Regra de negócio solta no meio do arquivo
    if (empty($title)) {
        $error = "O título da tarefa não pode estar vazio!";
    } elseif (empty($due_date)) {
        $error = "A data de vencimento é obrigatória!";
    } elseif (empty($responsible)) {
        $error = "O responsável é obrigatório!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, due_date, responsible) VALUES (:title, :description, :due_date, :responsible)");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $due_date,
            ':responsible' => $responsible
        ]);

        // Redirecionamento misturado com a lógica
        header("Location: index.php");
        exit;
    }
}

// Concluir ou excluir tarefa
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    if ($_GET['action'] === 'complete') {
        $pdo->exec("UPDATE tasks SET done = 1 WHERE id = $id");
    } elseif ($_GET['action'] === 'delete') {
        $pdo->exec("DELETE FROM tasks WHERE id = $id");
    }

    header("Location: index.php");
    exit;
}

// 3. BUSCA DE DADOS MISTURADA COM A VISUALIZAÇÃO
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY id DESC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Master - Spaghetti</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #333;
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            font-size: 1.5rem;
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .error {
            color: #dc2626;
            background: #fee2e2;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="date"],
        textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            display: block;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        li.done .task-title {
            text-decoration: line-through;
            color: #9ca3af;
        }

        .task-info {
            font-size: 0.9rem;
            color: #666;
        }

        .actions a {
            text-decoration: none;
            margin-left: 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Task Master (Spaghetti Edition)</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php" class="form-group">
            <input type="text" name="title" placeholder="O que precisa ser feito?" autocomplete="off" required>

            <textarea name="description" placeholder="Descrição da tarefa (opcional)"></textarea>

            <input type="date" name="due_date" id="date" required>

            <input type="text" name="responsible" placeholder="Responsável" autocomplete="off" required>

            <button type="submit">Adicionar</button>
        </form>

        <ul>
            <?php foreach ($tasks as $task): ?>
                <li class="<?php echo $task['done'] ? 'done' : ''; ?>">
                    <strong class="task-title"><?php echo htmlspecialchars($task['title']); ?></strong>

                    <div class="task-info">
                        <p><strong>Descrição:</strong> <?php echo htmlspecialchars($task['description'] ?? ''); ?></p>

                        <p><strong>Data de vencimento:</strong>
                            <?php echo !empty($task['due_date']) ? date('d/m/Y', strtotime($task['due_date'])) : 'Não informada'; ?>
                        </p>

                        <p><strong>Responsável:</strong> <?php echo htmlspecialchars($task['responsible'] ?? ''); ?></p>

                        <p><strong>Status:</strong> <?php echo $task['done'] ? 'Concluída' : 'Pendente'; ?></p>
                    </div>

                    <div class="actions">
                        <?php if (!$task['done']): ?>
                            <a href="?action=complete&id=<?php echo $task['id']; ?>" title="Concluir">✅</a>
                        <?php endif; ?>
                        <a href="?action=delete&id=<?php echo $task['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir esta tarefa?');" title="Excluir">❌</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

</body>

</html>