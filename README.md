# URL Shortener Assignment

Laravel 10 application with JWT authentication, company-based user management, role-based access, and queued Gmail invitation emails.

## Requirements

- PHP 8.1 or newer with `pdo_mysql`
- Composer
- MySQL or MariaDB
- Node.js and npm (only required when building frontend assets)
- A Gmail account with 2-Step Verification and an App Password

XAMPP can provide PHP, Apache, and MySQL for local development.

## Project setup

### 1. Download the project

Clone the repository and enter the project directory:

```bash
git clone <repository-url>
cd URL-Shortner-Assignment
```

If the project was downloaded as a ZIP, extract it and open the extracted directory in a terminal.

### 2. Install dependencies

```bash
composer install
```

Install frontend dependencies if frontend assets need to be rebuilt:

```bash
npm install
npm run build
```

### 3. Create the environment file

```bash
cp .env.example .env
```

Generate the Laravel application key and JWT secret:

```bash
php artisan key:generate
php artisan jwt:secret
```

### 4. Create the MySQL databases

Create the application and test databases using phpMyAdmin or MySQL:

```sql
CREATE DATABASE url_shortener_assignment
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE DATABASE url_shortener_assignment_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Configure the application database in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=url_shortener_assignment
DB_USERNAME=root
DB_PASSWORD=
```

The example uses the default local XAMPP credentials. Use secure credentials outside local development.

### 5. Configure the application and Super Admin

Set the local application URL and initial Super Admin credentials in `.env`:

```dotenv
APP_NAME="URL Shortener"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

SUPER_ADMIN_NAME="Super Admin"
SUPER_ADMIN_EMAIL=superadmin@example.com
SUPER_ADMIN_PASSWORD="change-this-password"
```

`APP_URL` is used to generate invitation links, so it must match the address used to open the application.

### 6. Configure Gmail SMTP

Do not use the normal Gmail password.

1. Enable 2-Step Verification on the sender Google account.
2. Create a 16-character Google App Password.
3. Add the Gmail address and App Password to `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME="your-email@gmail.com"
MAIL_PASSWORD="your-16-character-app-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="${APP_NAME}"
```

### 7. Configure the invitation queue

The application stores invitation jobs in MySQL:

```dotenv
QUEUE_CONNECTION=database
ADMIN_INVITATION_EXPIRE_HOURS=72
```

### 8. Run migrations and seed the Super Admin

```bash
php artisan migrate --seed
```

This creates the users, companies, invitations, queue, and failed-job tables, then creates or updates the configured Super Admin.

### 9. Start the application

```bash
php artisan serve --host=localhost --port=8000
```

Open:

```text
http://localhost:8000/login
```

Sign in using `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD` from `.env`.

### 10. Start the queue worker

Open a second terminal in the project directory and run:

```bash
php artisan queue:work database --queue=emails,default --tries=3 --timeout=90
```

Keep this terminal running while testing invitations. The invitation flow is:

```text
Create user → jobs table → queue worker → Gmail SMTP → invitation email
```

Restart long-running workers after code or `.env` changes:

```bash
php artisan config:clear
php artisan queue:restart
```

Then start `queue:work` again if a process manager is not restarting it automatically.

## Roles and access

| Role | Access |
| --- | --- |
| `super_admin` | View every company and its admins; create companies with a primary admin; add admins to existing companies. |
| `admin` | Belongs to one company; invite Admin or Member users; view only users created by that admin. |
| `member` | Sign in to the application but cannot create or manage users. |

Public registration is disabled. Company users receive a queued email invitation and set their password using the invitation link.

Existing Admin or Member records without a company are assigned to `Default Company` when the company migration runs.

## Queue management

List failed jobs:

```bash
php artisan queue:failed
```

Retry every failed job:

```bash
php artisan queue:retry all
```

Process a single queued job and exit:

```bash
php artisan queue:work database --queue=emails --once
```

## Running tests

Ensure `url_shortener_assignment_test` exists and the testing credentials in `phpunit.xml` match the local MySQL server. Then run:

```bash
php artisan test
```

## Common issues

### Invitation email remains in the `jobs` table

The queue worker is not running. Start it with:

```bash
php artisan queue:work database --queue=emails,default --tries=3
```

### Gmail authentication fails

Confirm that 2-Step Verification is enabled and `MAIL_PASSWORD` contains a Google App Password, not the regular Gmail password. After changing it, run:

```bash
php artisan config:clear
php artisan queue:restart
```

### Invitation link uses an old hostname or port

Update `APP_URL`, clear the configuration, restart the worker, and resend the invitation:

```bash
php artisan config:clear
php artisan queue:restart
```

### Database connection fails

Make sure MySQL is running and verify `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.
