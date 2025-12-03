<?php
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if ($username === '' || $password === '') {
        $error = 'Preencha usuário e senha.';
    } else {
        // hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $id = saveItem('users', [
            'username' => $username,
            'password' => $hash,
            'role' => $role
        ]);
        header('Location: add_user.php?created=1');
        exit;
    }
}

$users = readData('users');
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Adicionar Usuário - Apartment Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>
<main class="main-content">
    <h1>Adicionar Usuário</h1>
    <?php if ($error): ?>
        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['created'])): ?>
        <div class="success">Usuário criado com sucesso.</div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Usuário</label>
            <input name="username" required>
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="admin">Admin</option>
                <option value="user" selected>User</option>
            </select>
        </div>
        <div class="form-actions">
            <button class="btn-submit" type="submit">Criar</button>
        </div>
    </form>

    <h2>Usuários Existentes</h2>
    <ul>
        <?php foreach ($users as $u): ?>
            <li><?php echo htmlspecialchars($u['username'] ?? ''); ?> (id: <?php echo htmlspecialchars($u['id'] ?? ''); ?>)</li>
        <?php endforeach; ?>
    </ul>
</main>
</body>
</html>
