# Tubes-PEC — Local MySQL Preparation

**Purpose:** Prepare a local MySQL database for Laravel development without running migrations yet.

## 1. Requirements

- Laragon with its MySQL service installed. The assignment explicitly specifies MySQL; if the active Laragon service is MariaDB, confirm with the lecturer that it is accepted before using it.
- PHP CLI used by the project.
- PHP extension `pdo_mysql` enabled.
- Project root: `C:\laragon\www\TubesPEC`.

The PHP CLI version previously reported was PHP 8.5.12. Re-check the active PHP module list because Laragon can switch PHP versions.

## 2. Verify the MySQL PDO driver

From Command Prompt in the project directory, run:

```cmd
php -m | findstr /I "pdo_mysql"
```

Expected output includes:

```text
pdo_mysql
```

If it is missing:

1. Open Laragon's PHP configuration for the PHP version currently selected.
2. Check that the `pdo_mysql` extension is enabled in the active `php.ini`.
3. Restart Laragon services and open a new terminal.
4. Run `php -m | findstr /I "pdo_mysql"` again.

Do not edit a different PHP installation's `php.ini`; confirm the active CLI configuration with:

```cmd
php --ini
```

## 3. Start the database service

1. Open Laragon.
2. Start the MySQL service from Laragon.
3. Open HeidiSQL or the database client bundled with Laragon.
4. Connect using the credentials configured in your local Laragon installation.

Do not assume that the root account has an empty password. Use the actual local credentials configured on your machine.

## 4. Create the development database

Create an empty database named `tubes_pec` with this character set and collation:

```sql
CREATE DATABASE tubes_pec
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

This command creates the database only. It does not create or modify application tables.

Optional verification in the SQL client:

```sql
SELECT VERSION();

SELECT
    SCHEMA_NAME,
    DEFAULT_CHARACTER_SET_NAME,
    DEFAULT_COLLATION_NAME
FROM information_schema.SCHEMATA
WHERE SCHEMA_NAME = 'tubes_pec';
```

Expected character set: `utf8mb4`.  
Expected collation: `utf8mb4_unicode_ci`.

## 5. Configure the local `.env`

Open the existing `.env` file in the Laravel project. Update only the database settings to match the local database and credentials:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tubes_pec
DB_USERNAME=root
DB_PASSWORD=
```

Important:

- `3306` is a common MySQL port, but use the port shown by your Laragon/database configuration if it differs.
- `root` and an empty password are examples only. Use the actual local account credentials.
- Never share or commit your `.env` file.
- Never place production credentials in `.env.example`.

Keep the current `.env` local. The tracked `.env.example` will be updated separately with safe, non-secret MySQL defaults after the local connection is confirmed.

## 6. Clear cached Laravel configuration

After changing `.env`, run:

```cmd
php artisan config:clear
```

This clears cached configuration so Laravel reads the updated local environment values.

## 7. Verify the connection — do not migrate yet

Run:

```cmd
php artisan db:show
```

Confirm that Laravel reports the expected MySQL connection and the `tubes_pec` database.

If the command cannot connect, do not run migrations. Check:

- MySQL service is running.
- Host and port are correct.
- Database name exists.
- Username and password are correct.
- PHP CLI has `pdo_mysql` enabled.
- The active PHP CLI matches the PHP installation selected in Laragon.

## 8. What is intentionally not run yet

Do not run these commands during environment preparation:

```cmd
php artisan migrate
php artisan migrate:fresh
php artisan db:wipe
```

Migrations will be run only after the migration set has been reviewed and the database connection is confirmed. `migrate:fresh` and `db:wipe` are destructive and require explicit approval.

## 9. Safe information to report back

After completing the setup, report:

```text
PHP CLI version:
Active php.ini path:
pdo_mysql enabled: yes/no
MySQL server version:
Database name: tubes_pec
Database port:
Laravel db:show result:
```

Do not include the database password or any other secret in the report.
