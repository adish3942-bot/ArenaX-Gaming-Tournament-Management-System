<?php
session_start(); require 'config.php'; require 'functions.php'; verify_csrf();
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 try {
  if (isset($_POST['delete_id'])) { $st=$pdo->prepare("DELETE FROM players WHERE player_id=?"); $st->execute([int_post('delete_id')]); flash('Player deleted.'); redirect('players.php'); }
  if (isset($_POST['save_player'])) {
   $name=trim((string)post('name')); $email=trim((string)post('email')); $game=trim((string)post('game_handle')); $country=trim((string)post('country')); $id=int_post('player_id');
   if ($name==='') throw new RuntimeException('Player name is required.');
   if ($id) { $st=$pdo->prepare("UPDATE players SET name=?,email=?,game_handle=?,country=? WHERE player_id=?"); $st->execute([$name,$email?:null,$game?:null,$country?:null,$id]); flash('Player updated successfully.'); }
   else { $st=$pdo->prepare("INSERT INTO players(name,email,game_handle,country) VALUES(?,?,?,?)"); $st->execute([$name,$email?:null,$game?:null,$country?:null]); flash('Player added successfully.'); }
   redirect('players.php');
  }
 } catch(Throwable $e) { $error=$e instanceof PDOException && $e->getCode()==='23000'?'That email is already registered or this record is linked to another table.':$e->getMessage(); }
}
$edit=null;
if (isset($_GET['edit'])) { $st=$pdo->prepare("SELECT * FROM players WHERE player_id=?"); $st->execute([(int)$_GET['edit']]); $edit=$st->fetch() ?: null; }
$q=trim((string)($_GET['q']??'')); $st=$pdo->prepare("SELECT * FROM players WHERE name LIKE ? OR COALESCE(email,'') LIKE ? OR COALESCE(game_handle,'') LIKE ? ORDER BY created_at DESC"); $like="%$q%"; $st->execute([$like,$like,$like]); $players=$st->fetchAll();
page_header('Players','players');
?>
<div class="page-heading"><div><h1>Players</h1><p>Build your roster of competitors and manage player profiles.</p></div><span class="tag"><?= count($players) ?> players found</span></div>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<div class="row g-4"><div class="col-xl-4"><div class="cardx"><div class="section-title"><?= $edit?'Edit player':'Register a player' ?></div><form method="post"><?= csrf_field() ?><input type="hidden" name="player_id" value="<?= e($edit['player_id']??'') ?>">
<div class="mb-3"><label class="form-label">Player name *</label><input class="form-control" name="name" required maxlength="100" value="<?= e($edit['name']??'') ?>" placeholder="e.g. ShadowX"></div>
<div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" maxlength="150" value="<?= e($edit['email']??'') ?>" placeholder="player@example.com"></div>
<div class="mb-3"><label class="form-label">Game handle / IGN</label><input class="form-control" name="game_handle" maxlength="80" value="<?= e($edit['game_handle']??'') ?>" placeholder="In-game username"></div>
<div class="mb-3"><label class="form-label">Country / region</label><input class="form-control" name="country" maxlength="80" value="<?= e($edit['country']??'') ?>" placeholder="e.g. India"></div>
<button class="btn btn-primary w-100" name="save_player" value="1"><i class="bi bi-check2-circle me-1"></i><?= $edit?'Save changes':'Add player' ?></button>
<?php if($edit): ?><a href="players.php" class="btn btn-outline-light w-100 mt-2">Cancel edit</a><?php endif; ?></form></div><div class="small-note mt-3"><i class="bi bi-info-circle me-1"></i>Player records are stored in the <code>players</code> MySQL table.</div></div>
<div class="col-xl-8"><div class="cardx"><div class="d-flex justify-content-between gap-3 flex-wrap align-items-center mb-3"><div class="section-title mb-0">Player directory</div><form class="d-flex gap-2 search-box" method="get"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search players..."><button class="btn btn-outline-light"><i class="bi bi-search"></i></button></form></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Player</th><th>Game handle</th><th>Region</th><th>Actions</th></tr></thead><tbody><?php foreach($players as $p): ?><tr><td><div class="fw-semibold"><?= e($p['name']) ?></div><div class="small muted"><?= e($p['email']?:'No email') ?></div></td><td><?= e($p['game_handle']?:'—') ?></td><td><?= e($p['country']?:'—') ?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-light" href="players.php?edit=<?= (int)$p['player_id'] ?>"><i class="bi bi-pencil"></i></a> <?= delete_button('players.php',(int)$p['player_id']) ?></td></tr><?php endforeach; ?></tbody></table><?php if(!$players): ?><div class="empty-state"><i class="bi bi-person-plus"></i>No players found.</div><?php endif; ?></div></div></div></div>
<?php page_footer(); ?>
