<?php
/**
 * This file is part of HitTracker.
 *
 * HitTracker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2014 <johnny@localmomentum.net>
 * @license AGPL-3
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150507061327 extends AbstractMigration
{
    private function upPostgreSQL(Schema $schema)
    {
        $stmts = [];

        $stmts[] = 'CREATE SEQUENCE sylius_settings_id_seq INCREMENT BY 1 MINVALUE 1 START 1';
        $stmts[] = 'CREATE TABLE sylius_settings (
                        id INT NOT NULL,
                        schema_alias VARCHAR(255) NOT NULL,
                        namespace VARCHAR(255),
                        parameters JSON NOT NULL,
                        PRIMARY KEY(id)
                   )';
        $stmts[] = 'CREATE UNIQUE INDEX settings_idx ON sylius_settings (schema_alias, namespace)';

        $stmts[] = 'CREATE TABLE sessions (
                        session_id VARCHAR(128) NOT NULL,
                        session_data BYTEA NOT NULL,
                        session_time INT NOT NULL,
                        session_lifetime INT NOT NULL,
                        PRIMARY KEY(session_id)
                  )';
        $stmts[] = 'CREATE TABLE game_players (
                      id SERIAL NOT NULL,
                      game_id INT DEFAULT NULL,
                      unit_id INT DEFAULT NULL,
                      team VARCHAR(255) DEFAULT NULL,
                      name VARCHAR(255) NOT NULL,
                      hit_points INT NOT NULL,
                      score INT NOT NULL,
                      zone_hits JSONB NOT NULL,
                      created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                      updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                      PRIMARY KEY(id)
                  )';
        $stmts[] = 'CREATE INDEX idx_player_game_id ON game_players (game_id)';
        $stmts[] = 'CREATE INDEX idx_player_unit_id ON game_players (unit_id)';
        $stmts[] = 'CREATE UNIQUE INDEX idx_player_game_unit ON game_players (game_id, unit_id)';

        $stmts[] = 'CREATE TABLE units (
                      id INT NOT NULL,
                      radio_id VARCHAR(17) NOT NULL,
                      unit_type VARCHAR(255) NOT NULL,
                      color VARCHAR(255) DEFAULT NULL,
                      zones INT NOT NULL,
                      active BOOLEAN NOT NULL,
                      created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                      updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                      PRIMARY KEY(id)
                  )';
        $stmts[] = 'CREATE UNIQUE INDEX idx_unit_radio_id ON units (radio_id)';
        $stmts[] = 'CREATE TABLE games (
                      id SERIAL NOT NULL,
                      arena INT NOT NULL,
                      game_type VARCHAR(255) NOT NULL,
                      settings JSONB NOT NULL,
                      ends_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                      created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                      PRIMARY KEY(id)
                  )';
        $stmts[] = 'ALTER TABLE game_players ADD CONSTRAINT FK_players_games
                  FOREIGN KEY (game_id) REFERENCES games (id)
                  ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE';
        $stmts[] = 'ALTER TABLE game_players ADD CONSTRAINT fk_players_unit
                  FOREIGN KEY (unit_id) REFERENCES units (id)
                  NOT DEFERRABLE INITIALLY IMMEDIATE';


        foreach ($stmts as $stmt) {
            $this->addSql($stmt);
        }
    }

    private function upSqlite(Schema $schema)
    {
        $stmts = [];

        $stmts[] = 'CREATE TABLE game_players (
                        id INTEGER NOT NULL,
                        game_id INTEGER DEFAULT NULL,
                        unit_id INTEGER DEFAULT NULL,
                        team VARCHAR(255) DEFAULT NULL,
                        name VARCHAR(255) NOT NULL,
                        hit_points INTEGER NOT NULL,
                        zone_hits CLOB NOT NULL --(DC2Type:json_array)
                        ,score INTEGER NOT NULL,
                        created_at DATETIME NOT NULL,
                        updated_at DATETIME DEFAULT NULL,
                        PRIMARY KEY(id)
                   )';
        $stmts[] = 'CREATE INDEX idx_player_game_id ON game_players (game_id)';
        $stmts[] = 'CREATE INDEX idx_player_unit_id ON game_players (unit_id)';
        $smmts[] = 'CREATE UNIQUE INDEX idx_player_game_unit ON game_players (game_id, unit_id)';

        $stmts[] = 'CREATE TABLE games (
                        id INTEGER NOT NULL,
                        arena INTEGER NOT NULL,
                        settings CLOB NOT NULL --(DC2Type:json_document)
                        ,ends_at DATETIME NOT NULL,
                        created_at DATETIME NOT NULL,
                        game_type VARCHAR(255) NOT NULL,
                        PRIMARY KEY(id)
                   )';

        $stmts[] = 'CREATE TABLE units (
                        id INTEGER NOT NULL,
                        radio_id VARCHAR(17) NOT NULL,
                        unit_type VARCHAR(255) NOT NULL,
                        color VARCHAR(255) DEFAULT NULL,
                        zones INTEGER NOT NULL,
                        active BOOLEAN NOT NULL,
                        created_at DATETIME NOT NULL,
                        updated_at DATETIME DEFAULT NULL,
                        PRIMARY KEY(id)
                   )';
        $stmts[] = 'CREATE UNIQUE INDEX idx_unit_radio_id ON units (radio_id)';

        $stmts[] = 'CREATE TABLE sylius_settings (
                        id INTEGER NOT NULL,
                        schema_alias VARCHAR(255) NOT NULL,
                        namespace VARCHAR(255) DEFAULT NULL,
                        parameters CLOB NOT NULL --(DC2Type:json_array)
                        , PRIMARY KEY(id)
                   )';
        $stmts[] = 'CREATE UNIQUE INDEX settings_idx ON sylius_settings (schema_alias, namespace)';

        foreach ($stmts as $stmt) {
            $this->addSql($stmt);
        }
    }

    public function up(Schema $schema)
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $this->upSqlite($schema);
                break;
            default:
                $this->upPostgreSQL($schema);
        }
    }

    public function down(Schema $schema)
    {
        $stmts = [];

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $stmts[] = 'DROP TABLE game_players';
                $stmts[] = 'DROP TABLE games';
                $stmts[] = 'DROP TABLE units';
                $stmts[] = 'DROP TABLE sylius_settings';
                break;
            default:
                $stmts[] = 'CREATE SCHEMA public';
                $stmts[] = 'ALTER TABLE game_players DROP CONSTRAINT fk_players_units';
                $stmts[] = 'ALTER TABLE game_players DROP CONSTRAINT fk_players_games';
                $stmts[] = 'DROP SEQUENCE sylius_settings_parameter_id_seq CASCADE';
                $stmts[] = 'DROP TABLE sessions';
                $stmts[] = 'DROP TABLE game_players';
                $stmts[] = 'DROP TABLE units';
                $stmts[] = 'DROP TABLE games';
                $stmts[] = 'DROP TABLE sylius_settings_parameter';
        }

        foreach ($stmts as $stmt) {
            $this->addSql($stmt);
        }
    }
}