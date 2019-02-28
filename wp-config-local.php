<?php

if ('ON' === getenv('LANDO')) {
    $aryLandoInfo = json_decode(getenv('LANDO_INFO', true));
    define('DB_NAME', $aryLandoInfo->database->creds->database);
    define('DB_USER', $aryLandoInfo->database->creds->user);
    define('DB_PASSWORD', $aryLandoInfo->database->creds->password);
    define('DB_HOST', $aryLandoInfo->database->internal_connection->host);
    define('DB_CHARSET', 'utf8');
    define('DB_COLLATE', '');
    define('WP_DEBUG', false);
    define('WP_DEBUG_LOG', false);
    define('WP_DEBUG_SCREEN', false);
} else {
    /**
     * Fill out if you are using a different development environment
     */
}