<?php
declare(strict_types=1);

function e(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function get_flash(): ?array {
    if (!isset($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
function post(string $key, mixed $default = ''): mixed {
    return $_POST[$key] ?? $default;
}
function int_post(string $key): int {
    return (int)($_POST[$key] ?? 0);
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!hash_equals(csrf_token(), $token)) {
            http_response_code(419);
            exit('Invalid form token. Refresh the page and try again.');
        }
    }
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
function page_header(string $title, string $active = ''): void {
    $flash = get_flash();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= e($title) ?> · ArenaX</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
      <link href="assets/style.css" rel="stylesheet">
    </head>
    <body>
    <div class="app-shell">
      <aside class="sidebar">
        <a class="brand" href="index.php"><span class="brand-mark"><i class="bi bi-trophy-fill"></i></span><span>ARENA<span class="brand-accent">X</span><small>TOURNAMENT HUB</small></span></a>
        <div class="nav-label">WORKSPACE</div>
        <nav class="nav flex-column">
          <a class="nav-link <?= $active==='dashboard'?'active':'' ?>" href="index.php"><i class="bi bi-grid-1x2"></i> Dashboard</a>
          <a class="nav-link <?= $active==='players'?'active':'' ?>" href="players.php"><i class="bi bi-people"></i> Players</a>
          <a class="nav-link <?= $active==='teams'?'active':'' ?>" href="teams.php"><i class="bi bi-shield"></i> Teams</a>
          <a class="nav-link <?= $active==='tournaments'?'active':'' ?>" href="tournaments.php"><i class="bi bi-trophy"></i> Tournaments</a>
          <a class="nav-link <?= $active==='matches'?'active':'' ?>" href="matches.php"><i class="bi bi-controller"></i> Matches & Results</a>
          <a class="nav-link <?= $active==='leaderboard'?'active':'' ?>" href="leaderboard.php"><i class="bi bi-bar-chart"></i> Leaderboard</a>
        </nav>
        <div class="sidebar-bottom"><div class="online-dot"></div> Database connection ready</div>
      </aside>
      <main class="main-content">
        <header class="topbar">
          <div><div class="eyebrow">ESPORTS MANAGEMENT</div><div class="topbar-title"><?= e($title) ?></div></div>
          <div class="topbar-right"><span class="status-pill"><span class="online-dot"></span> LOCAL SERVER</span><div class="avatar">AX</div></div>
        </header>
        <div class="content-wrap">
        <?php if ($flash): ?>
          <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
    <?php
}
function page_footer(): void {
    ?>
        </div>
        <footer class="footer"><span>ARENAX · DBMS MINI PROJECT</span><span>Built with PHP + MySQL</span></footer>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', event => {
          if (!confirm(el.dataset.confirm || 'Are you sure?')) event.preventDefault();
        });
      });
    </script>
    </body></html>
    <?php
}
function delete_button(string $action, int $id, string $label = 'Delete'): string {
    return '<form method="post" class="d-inline" action="' . e($action) . '" onsubmit="return confirm(\'Delete this record?\')">'
        . csrf_field() . '<input type="hidden" name="delete_id" value="' . $id . '">'
        . '<button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> ' . e($label) . '</button></form>';
}
