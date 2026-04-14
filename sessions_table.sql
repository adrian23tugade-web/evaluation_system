-- Sessions table for Vercel serverless PHP session handling
-- Run this in your FreeDDB (or any MySQL) database via phpMyAdmin

CREATE TABLE IF NOT EXISTS `sessions` (
  `id`      varchar(128)  NOT NULL,
  `data`    mediumtext    NOT NULL,
  `expires` datetime      NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
