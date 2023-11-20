## Installation
Simply clone this repository with
    git clone https://github.com/rbroering/rpWiki

Then install dependencies with
    npm install
and
    composer install

In case you would like to change environment variables, e.g. to choose a different name and password for the MariaDB user, create an .env file.

Run
    docker compose up

Add a logo in src/custom/ with the name "Wordmark.png" to let it show in the header.

Lastly, copy copy src/example.htaccess over to src/.htaccess and src/Config.Examle.php to src/Config.php and edit them as you wish.
