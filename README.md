# About

Repository for KAFAMS maintenance and evolution work.

### Setting Up

1. Clone this repository
2. Put the files in xampp/htdocs
3. Install Composer (https://getcomposer.org/) & latest php version in xampp\php
4. Run "composer install" in terminal
5. Rename .env.example to .env
    > Open XAMPP from here on (Apache & MySQL services) and make new database with "kafams" name
6. Run "php artisan key:generate" in terminal
7. Run "php artisan migrate:fresh" in terminal
8. Run "npm install -g pnpm" on your PC's command prompt/terminal/powershell (Ensure you have npm installed in PC)
9. Run "pnpm i" in terminal
10. Run "pnpm i bootstrap-icons" in terminal
11. Run "pnpm run dev" in terminal
12. Run "php artisan serve" in second terminal to run local server
13. Run "php artisan storage:link" (only once)

### Post-Setup

Run "pnpm run dev" & "php artisan serve" every time to start local server

Run "php artisan migrate:fresh --seed" to seed database and fetch/execute migration scripts (Do this after each pull)

# References

-   [Original repository](https://github.com/Oh-Hoa-Yang/KAFAMS)
