<?php

/*
 * Waits until MySQL accepts the app's own login. Run by docker/entrypoint.sh
 * before migrations or the scheduler start.
 *
 * Compose already holds the containers until fablab-db reports healthy. This
 * is the second guard, and it explains the one failure that waiting cannot
 * fix: a password the database does not recognise.
 */

if ((getenv('DB_CONNECTION') ?: 'mysql') !== 'mysql') {
    exit(0);
}

$host = getenv('DB_HOST') ?: 'fablab-db';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'fablab_db';
$username = getenv('DB_USERNAME') ?: 'fablab';
$password = getenv('DB_PASSWORD') ?: '';
$timeout = (int) (getenv('DB_WAIT_TIMEOUT') ?: 120);

$deadline = time() + $timeout;
$said = false;

while (true) {
    try {
        new PDO("mysql:host={$host};port={$port};dbname={$database}", $username, $password, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        exit(0);
    } catch (PDOException $e) {
        // 1045: wrong user or password. 1044/1049: no such database for this
        // user. Waiting will not fix these.
        // A failed connect leaves errorInfo empty and puts the code here.
        $code = (int) ($e->errorInfo[1] ?? $e->getCode());

        if (in_array($code, [1044, 1045, 1049], true)) {
            fwrite(STDERR, "fablab: the database refused the login for '{$username}': {$e->getMessage()}\n");
            fwrite(STDERR, "fablab: MySQL takes DB_DATABASE, DB_USERNAME and DB_PASSWORD from docker/.env only once,\n");
            fwrite(STDERR, "fablab: when it first creates its volume. If they were changed after that, put the\n");
            fwrite(STDERR, "fablab: old values back (see the Troubleshooting section of docs/DockerDeployment.md).\n");
            exit(1);
        }

        if (time() >= $deadline) {
            fwrite(STDERR, "fablab: gave up waiting for the database at {$host}:{$port} after {$timeout}s: {$e->getMessage()}\n");
            exit(1);
        }

        if (! $said) {
            fwrite(STDERR, "fablab: waiting for the database at {$host}:{$port}...\n");
            $said = true;
        }

        sleep(2);
    }
}
