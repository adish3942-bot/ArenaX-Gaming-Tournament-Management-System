<?php
session_start();
require 'config.php';
require 'functions.php';
$stats = [
 'players' => (int)$pdo->query("SELECT COUNT(*) FROM players")->fetchColumn(),
 'teams' => (int)$pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn(),
 'tournaments' => (int)$pdo->query("SELECT COUNT(*) FROM tournaments")->fetchColumn(),
 'matches' => (int)$pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn(),
];
$upcoming = $pdo->query("SELECT m.match_id,m.scheduled_at,m.status,t.name tournament,ta.name team_a,tb.name team_b
 FROM matches m JOIN tournaments t ON t.tournament_id=m.tournament_id
 JOIN teams ta ON ta.team_id=m.team_a_id JOIN teams tb ON tb.team_id=m.team_b_id
 ORDER BY m.scheduled_at DESC LIMIT 6")->fetchAll();
$top = $pdo->query("SELECT t.team_id,t.name,COUNT(r.result_id) played,
 SUM(CASE WHEN r.winner_team_id=t.team_id THEN 1 ELSE 0 END) wins,
 COALESCE(SUM(CASE WHEN r.winner_team_id=t.team_id THEN 3 WHEN r.winner_team_id IS NULL THEN 1 ELSE 0 END),0) points
 FROM teams t LEFT JOIN results r ON r.team_a_id=t.team_id OR r.team_b_id=t.team_id
 GROUP BY t.team_id,t.name ORDER BY points DESC,wins DESC,t.name LIMIT 5")->fetchAll();
page_header('Dashboard','dashboard');
?>
<section class="hero"><div class="row align-items-center"><div class="col-md-9"><div class="hero-kicker">TOURNAMENT OPERATIONS</div><h2>Keep every tournament in order.</h2><p>Players, teams, schedules and results in one place. Changes are saved to the ArenaX database.</p><a href="tournaments.php" class="btn btn-primary me-2"><i class="bi bi-plus-lg me-1"></i> Create tournament</a><a href="matches.php" class="btn btn-outline-light">Record a result <i class="bi bi-arrow-up-right ms-1"></i></a></div><div class="col-md-3 text-center hero-symbol"><i class="bi bi-trophy-fill"></i></div></div></section>
<div class="row g-3 mb-4">
<?php foreach ([['Players',$stats['players'],'bi-people','Registered competitors'],['Teams',$stats['teams'],'bi-shield','Teams in the arena'],['Tournaments',$stats['tournaments'],'bi-trophy','Events created'],['Matches',$stats['matches'],'bi-controller','Scheduled matches']] as $s): ?>
<div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon"><i class="bi <?= e($s[2]) ?>"></i></div><div class="stat-label"><?= e($s[0]) ?></div><div class="stat-value"><?= $s[1] ?></div><div class="stat-foot"><?= e($s[3]) ?></div></div></div>
<?php endforeach; ?>
</div>
<div class="row g-4"><div class="col-xl-7"><div class="cardx"><div class="d-flex justify-content-between align-items-center mb-3"><div class="section-title mb-0">Recent match schedule</div><a href="matches.php" class="small">View all <i class="bi bi-arrow-right"></i></a></div>
<?php if (!$upcoming): ?><div class="empty-state"><i class="bi bi-calendar2-event"></i>No matches yet. Create a tournament and schedule your first match.</div><?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Match</th><th>Tournament</th><th>Date</th><th>Status</th></tr></thead><tbody><?php foreach($upcoming as $m): ?><tr><td><div class="fw-semibold"><?= e($m['team_a']) ?></div><div class="small muted">vs <?= e($m['team_b']) ?></div></td><td><?= e($m['tournament']) ?></td><td><?= e(date('M j, Y',strtotime($m['scheduled_at']))) ?></td><td><span class="tag <?= $m['status']==='completed'?'tag-green':'' ?>"><?= e(ucfirst($m['status'])) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div>
<div class="col-xl-5"><div class="cardx"><div class="d-flex justify-content-between align-items-center mb-3"><div class="section-title mb-0">Top teams · all events</div><a href="leaderboard.php" class="small">Leaderboard</a></div><?php if (!$top || (int)$top[0]['played']===0): ?><div class="empty-state"><i class="bi bi-bar-chart"></i>Results will appear here once matches are played.</div><?php else: ?><table class="table"><thead><tr><th>Rank / Team</th><th>Wins</th><th>Pts</th></tr></thead><tbody><?php foreach($top as $i=>$t): ?><tr><td><span class="rank-number <?= $i<3?'rank-'.($i+1):'' ?> me-2">#<?= $i+1 ?></span><span class="fw-semibold"><?= e($t['name']) ?></span></td><td><?= (int)$t['wins'] ?></td><td><span class="tag"><?= (int)$t['points'] ?></span></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></div></div></div>
<?php page_footer(); ?>
