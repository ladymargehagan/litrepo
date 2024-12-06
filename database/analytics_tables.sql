CREATE TABLE `user_analytics` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `userId` INT NOT NULL,
    `event_type` VARCHAR(50) NOT NULL,
    `event_data` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_preferences` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `userId` INT NOT NULL,
    `preference_key` VARCHAR(50) NOT NULL,
    `preference_value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_pref` (`userId`, `preference_key`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `learning_metrics` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `userId` INT NOT NULL,
    `date` DATE NOT NULL,
    `words_learned` INT DEFAULT 0,
    `exercises_completed` INT DEFAULT 0,
    `time_spent` INT DEFAULT 0, -- in seconds
    `streak_count` INT DEFAULT 0,
    UNIQUE KEY `unique_daily_metric` (`userId`, `date`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 