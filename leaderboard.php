<?php
session_start(); require 'config.php'; require 'functions.php';
$tournaments=$pdo->query("SELECT tournament_id,name,game FROM tournaments ORDER BY created_at DESC")->fetchAll();
$tid=(int)($_GET['tournament_id']??0);
if($tid){$sql="SELECT t.team_id,t.name,t.tag,
 SUM(CASE WHEN r.result_id IS NOT NULL THEN 1 ELSE 0 END) played,
 SUM(CASE WHEN r.winner_team_id=t.team_id THEN 1 ELSE 0 END) wins,
 SUM(CASE WHEN r.winner_team_id IS NOT NULL AND r.winner_team_id<>t.team_id THEN 1 ELSE 0 END) losses,
 SUM(CASE WHEN r.result_id IS NOT NULL AND r.winner_team_id IS NULL THEN 1 ELSE 0 END) draws,
 COALESCE(SUM(CASE WHEN r.winner_team_id=t.team_id THEN 3 WHEN r.result_id IS NOT NULL AND r.winner_team_id IS NULL THEN 1 ELSE 0 END),0) points
 FROM tournament_registrations tr JOIN teams t ON t.team_id=tr.team_id
 LEFT JOIN results r ON r.match_id IN (SELECT m.match_id FROM matches m WHERE m.tournament_id=tr.tournament_id) AND (r.team_a_id=t.team_id OR r.team_b_id=t.team_id)
 WHERE tr.tournament_id=? GROUP BY t.team_id,t.name,t.tag ORDER BY points DESC,wins DESC,losses ASC,t.name";
 $st=$pdo->prepare($sql);$st->execute([$tid]);$rows=$st->fetchAll();
}else{$rows=$pdo->query("SELECT t.team_id,t.name,t.tag,COUNT(r.result_id) played,SUM(CASE WHEN r.winner_team_id=t.team_id THEN 1 ELSE 0 END) wins,SUM(CASE WHEN r.winner_team_id IS NOT NULL AND r.winner_team_id<>t.team_id THEN 1 ELSE 0 END) losses,SUM(CASE WHEN r.result_id IS NOT NULL AND r.winner_team_id IS NULL THEN 1 ELSE 0 END) draws,COALESCE(SUM(CASE WHEN r.winner_team_id=t.team_id THEN 3 WHEN r.result_id IS NOT NULL AND r.winner_team_id IS NULL THEN 1 ELSE 0 END),0) points FROM teams t LEFT JOIN results r ON r.team_a_id=t.team_id OR r.team_b_id=t.team_id GROUP BY t.team_id,t.name,t.tag ORDER BY points DESC,wins DESC,losses ASC,t.name")->fetchAll();}
$selected=array_values(array_filter($tournaments,fn($t)=>(int)$t['tournament_id']===$tid))[0]??null;
page_header('Leaderboard','leaderboard');
?>
<div class="page-heading"><div><h1>Leaderboard</h1><p>Rankings are calculated from saved match results in MySQL.</p></div><span class="tag"><i class="bi bi-lightning-charge me-1"></i> Live standings</span></div>
<div class="cardx mb-4"><div class="d-flex flex-wrap justify-content-between align-items-center gap-3"><div><div class="section-title mb-1"><?= $selected?e($selected['name']):'Overall standings' ?></div><div class="small-note"><?= $selected?e($selected['game']).' · Tournament rankings':'All recorded results across all tournaments' ?></div></div><form method="get" class="d-flex gap-2"><select class="form-select" name="tournament_id" onchange="this.form.submit()"><option value="0">All tournaments</option><?php foreach($tournaments as $t): ?><option value="<?= (int)$t['tournament_id'] ?>" <?= $tid===(int)$t['tournament_id']?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?></select><noscript><button class="btn btn-primary">Filter</button></noscript></form></div></div>
<div class="cardx"><div class="table-responsive"><table class="table"><thead><tr><th>Rank</th><th>Team</th><th>Played</th><th>Wins</th><th>Losses</th><th>Draws</th><th>Points</th></tr></thead><tbody><?php foreach($rows as $i=>$r): ?><tr><td><span class="rank-number <?= $i<3?'rank-'.($i+1):'' ?>">#<?= $i+1 ?></span></td><td><div class="d-flex align-items-center gap-2"><div class="avatar"><?= e(strtoupper(substr($r['tag']?:$r['name'],0,2))) ?></div><div class="fw-semibold"><?= e($r['name']) ?></div></div></td><td><?= (int)$r['played'] ?></td><td><span style="color:#86efac;font-weight:750"><?= (int)$r['wins'] ?></span></td><td><?= (int)$r['losses'] ?></td><td><?= (int)$r['draws'] ?></td><td><span class="tag"><?= (int)$r['points'] ?> pts</span></td></tr><?php endforeach; ?></tbody></table><?php if(!$rows): ?><div class="empty-state"><i class="bi bi-bar-chart"></i>No teams to rank yet. Create teams, register them for a tournament, then enter match results.</div><?php endif; ?></div></div>
<div class="small-note mt-3">Scoring rule: win = 3 points, draw = 1 point, loss = 0 points. Ranking is ordered by points, then wins.</div>
<?php page_footer(); ?>
