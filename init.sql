-- Initialize database for capstone project
CREATE DATABASE IF NOT EXISTS capstone_db;
USE capstone_db;

-- Grant privileges to laravel user
GRANT ALL PRIVILEGES ON capstone_db.* TO 'laravel_user'@'%';
FLUSH PRIVILEGES;
