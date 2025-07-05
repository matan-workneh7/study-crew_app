# Study Crew Database Migration Guide

This guide provides step-by-step instructions for migrating the Study Crew application from SQLite to MySQL/MariaDB.

## Prerequisites

1. XAMPP installed and running on your system
2. PHP 8.0 or higher
3. MySQLi PHP extension enabled
4. MySQL/MariaDB server running

## Migration Steps

1. **Install Required PHP Extensions**
   ```bash
   sudo yum install php-mysqli
   sudo /opt/lampp/lampp restart
   ```

2. **Backup Your Current Database**
   Before proceeding, make a backup of your current SQLite database:
   ```bash
   cp database.sqlite database.sqlite.backup
   ```

3. **Update Configuration**
   Ensure your `config.php` file is properly configured with MySQL credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'study_crew');
   ```

4. **Run Migration Script**
   Execute the migration script to transfer data from SQLite to MySQL:
   ```bash
   php migrate.php
   ```
   The script will:
   - Create the MySQL database if it doesn't exist
   - Create all necessary tables
   - Transfer data from SQLite to MySQL
   - Handle duplicate entries gracefully

5. **Verify Migration**
   After migration, you can verify the data in phpMyAdmin:
   - Open http://localhost/phpmyadmin
   - Select the 'study_crew' database
   - Check all tables (users, courses, assistants, connections)

6. **Clean Up**
   Once you've verified the migration was successful, you can remove the old SQLite database:
   ```bash
   rm database.sqlite
   ```

## Database Schema Changes

The database schema has been updated to be MySQL-compatible:

1. **Users Table**
   - Changed `id` to AUTO_INCREMENT
   - Changed text fields to VARCHAR with appropriate lengths
   - Added ENUM for academic_year
   - Added ON UPDATE CURRENT_TIMESTAMP for updated_at

2. **Courses Table**
   - Changed `id` to VARCHAR(10) as primary key
   - Added proper ENUM for year
   - Added CHECK constraint for semester
   - Added proper VARCHAR lengths

3. **Assistants Table**
   - Added proper foreign key constraints
   - Changed text fields to appropriate VARCHAR lengths
   - Added proper ENUM for status
   - Added ON UPDATE CURRENT_TIMESTAMP for updated_at

4. **Connections Table**
   - Added proper foreign key constraints
   - Changed text fields to appropriate VARCHAR lengths
   - Added proper ENUM for status
   - Added ON UPDATE CURRENT_TIMESTAMP for updated_at

## Troubleshooting

If you encounter any issues during migration:

1. **MySQLi Extension Not Found**
   - Install the MySQLi extension as shown in step 1
   - Restart XAMPP after installation

2. **Database Connection Issues**
   - Verify MySQL server is running
   - Check database credentials in config.php
   - Ensure MySQL socket path is correct

3. **Duplicate Entry Errors**
   - The migration script uses INSERT IGNORE for courses
   - This is expected behavior when migrating standard course data
   - Other duplicate errors should be investigated

## Post-Migration Notes

1. The application now uses MySQL instead of SQLite
2. All data has been preserved during migration
3. You can now use phpMyAdmin to manage the database
4. The database schema is optimized for MySQL performance

## Support

If you encounter any issues during migration, please:
1. Check the error logs
2. Verify your MySQL configuration
3. Contact the development team for assistance
