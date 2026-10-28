ARENAX — GAMING TOURNAMENT MANAGER
==================================

Stack: PHP 8+, MySQL/MariaDB, Bootstrap 5, XAMPP.
Features:
- Dashboard with database-backed statistics
- Players: create, read, update, delete, search
- Teams: create, read, update, delete
- Team roster: assign/remove players
- Tournaments: create, read, update, delete
- Register teams for tournaments
- Matches: schedule, update, delete
- Results: save scores and winner
- Leaderboard based on saved results

INSTALL ON WINDOWS / XAMPP
1. Extract the ArenaX folder.
2. Copy the entire ArenaX folder to:
   C:\xampp\htdocs\ArenaX
3. Open XAMPP Control Panel and start Apache and MySQL.
4. Open http://localhost/phpmyadmin
5. Click Import, choose C:\xampp\htdocs\ArenaX\database.sql, then click Import.
   Alternatively, create a database and import database.sql using the SQL tab.
6. Check config.php. Defaults are:
   host=127.0.0.1, database=arenax_db, username=root, password empty.
   These are the usual local XAMPP defaults.
7. Visit http://localhost/ArenaX/

TEST THE CRUD
- Players: add a player, edit the player's name, search, then delete the test player.
- Teams: create a team.
- Players: use the "Team roster" section on Teams page to assign a player.
- Tournaments: create a tournament and register teams.
- Matches: schedule a match for a tournament using registered teams.
- Results: enter scores and save. The winner and leaderboard are calculated from the result.
- Leaderboard: choose a tournament to view team wins, losses, draws, and points.

IMPORTANT
- Use the site through http://localhost/ArenaX/, not by opening PHP files directly.
- This is a classroom project starter. Before public deployment, add authentication,
  CSRF protection, stricter production configuration, and role permissions.
- Deleting a team/player/tournament may be restricted when related records exist.
  Delete related registrations/matches first, or use the database's relationship rules.
