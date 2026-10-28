CREATE DATABASE IF NOT EXISTS arenax_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE arenax_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS results;
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS tournament_registrations;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS tournaments;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS players;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE players (
  player_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE,
  game_handle VARCHAR(80),
  country VARCHAR(80),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE teams (
  team_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  tag VARCHAR(12) UNIQUE,
  captain_player_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_team_captain FOREIGN KEY (captain_player_id) REFERENCES players(player_id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE team_members (
  team_id INT UNSIGNED NOT NULL,
  player_id INT UNSIGNED NOT NULL,
  role ENUM('Player','Captain','Substitute') NOT NULL DEFAULT 'Player',
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (team_id, player_id),
  CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES teams(team_id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_tm_player FOREIGN KEY (player_id) REFERENCES players(player_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tournaments (
  tournament_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  game VARCHAR(80) NOT NULL,
  format ENUM('Single Elimination','Double Elimination','Round Robin','Battle Royale') NOT NULL DEFAULT 'Single Elimination',
  status ENUM('upcoming','ongoing','completed') NOT NULL DEFAULT 'upcoming',
  start_date DATE NULL,
  max_teams SMALLINT UNSIGNED NOT NULL DEFAULT 8,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tournament_registrations (
  tournament_id INT UNSIGNED NOT NULL,
  team_id INT UNSIGNED NOT NULL,
  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (tournament_id, team_id),
  CONSTRAINT fk_tr_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(tournament_id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_tr_team FOREIGN KEY (team_id) REFERENCES teams(team_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE matches (
  match_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tournament_id INT UNSIGNED NOT NULL,
  team_a_id INT UNSIGNED NOT NULL,
  team_b_id INT UNSIGNED NOT NULL,
  scheduled_at DATETIME NULL,
  status ENUM('scheduled','live','completed') NOT NULL DEFAULT 'scheduled',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_match_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(tournament_id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_match_team_a FOREIGN KEY (team_a_id) REFERENCES teams(team_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_match_team_b FOREIGN KEY (team_b_id) REFERENCES teams(team_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_different_teams CHECK (team_a_id <> team_b_id),
  INDEX idx_match_tournament (tournament_id),
  INDEX idx_match_date (scheduled_at)
) ENGINE=InnoDB;

CREATE TABLE results (
  result_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  match_id INT UNSIGNED NOT NULL UNIQUE,
  team_a_id INT UNSIGNED NOT NULL,
  team_b_id INT UNSIGNED NOT NULL,
  score_a INT UNSIGNED NOT NULL DEFAULT 0,
  score_b INT UNSIGNED NOT NULL DEFAULT 0,
  winner_team_id INT UNSIGNED NULL,
  played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_result_match FOREIGN KEY (match_id) REFERENCES matches(match_id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_result_team_a FOREIGN KEY (team_a_id) REFERENCES teams(team_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_result_team_b FOREIGN KEY (team_b_id) REFERENCES teams(team_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_result_winner FOREIGN KEY (winner_team_id) REFERENCES teams(team_id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- Optional sample data is intentionally omitted so you can demonstrate INSERTs from the website.
