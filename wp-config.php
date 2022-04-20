<?php

require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define('PLATFORM_VARIABLES', json_decode($_ENV['PLATFORM_VARS'], true));

// Set host values
$site_scheme = 'http';
$site_host = 'localhost';
$strDomainRequest = "SERVER_NAME";

if (isset($_SERVER['HTTP_HOST'])) {
    $site_host = $_SERVER['HTTP_HOST'];
    $site_scheme = !empty($_SERVER['https']) ? 'https' : 'http';
}

//do we have a multisite?
if (false !== getenv('MULTISITE') && filter_var(getenv('MULTISITE'), FILTER_VALIDATE_BOOLEAN)) {
    /**
     * Set up all the constants that we need for multisite
     */
    define('MULTISITE', true);
    define('WP_ALLOW_MULTISITE', true);
    define('PATH_CURRENT_SITE', '/');
    define('SITE_ID_CURRENT_SITE', 1);
    define('BLOG_ID_CURRENT_SITE', 1);
    //DOMAIN_CURRENT_SITE intentionally not defined yet
    /**
     * Do we have a multi/sub-domain set up or subdirectory?
     */
    $boolMultiDomain = false; //assume default of subdirectory
    if (false !== getenv('MULTISITE_MULTIDOMAIN') && filter_var(getenv('MULTISITE_MULTIDOMAIN'), FILTER_VALIDATE_BOOLEAN)) {
        $boolMultiDomain = true;
    }

    define('SUBDOMAIN_INSTALL', $boolMultiDomain);
} else {
    define('MULTISITE', false);
    define('SUBDOMAIN_INSTALL', false);
}
//are we on platform?
if (false !== $strRelationships = getenv('PLATFORM_RELATIONSHIPS')) {
    $site_scheme = 'https';//assume all sites on platform should be https
    // This is where we get the relationships of our application dynamically from Platform.sh

    // set session path to /tmp in cas we are using wp-cli to avoid notices
    if (php_sapi_name() === 'cli') {
        session_save_path("/tmp");
    }

    $relationships = json_decode(base64_decode(getenv('PLATFORM_RELATIONSHIPS')), true);

    // We are using the first relationship called "database" found in your
    // relationships. Note that you can call this relationship as you wish
    // in you .platform.app.yaml file, but 'database' is a good name.
    define('DB_NAME', $relationships['database'][0]['path']);
    define('DB_USER', $relationships['database'][0]['username']);
    define('DB_PASSWORD', $relationships['database'][0]['password']);
    define('DB_HOST', $relationships['database'][0]['host']);
    define('DB_CHARSET', 'utf8');
    define('DB_COLLATE', '');

    //we need routes for both multi and standard
    $aryRoutes = array();//assume we dont have it
    if (false !== $strRoutes = getenv('PLATFORM_ROUTES')) {
        $aryRoutes = json_decode(base64_decode($strRoutes), true);
    }

    if (MULTISITE) {
        if (false === $strPrimaryDomain = getenv('PRIMARY_DOMAIN')) {
            //@todo we need a way to fail here
            echo "This is a multidomain multisite but the primary domain ENV is missing.\n";
        }

        if ('master' == getenv('PLATFORM_BRANCH')) {
            //use MULTISITE_PRIMARY_DOMAIN
            //use the default site_schema of https
            $site_host = $strPrimaryDomain;
        } else {
            //we have to find the correct URL to use
            //first escape any periods
            $strLookForDomain = str_replace('.', '\.', $strPrimaryDomain);
            $strPattern = sprintf('/^https:\/\/(%s[^\/]+)/', $strLookForDomain);
            $aryMatched = preg_grep($strPattern, array_keys($aryRoutes));
            if (1 === count($aryMatched)) {
                //now we have the _WHOLE_ match, but we need just the domain
                preg_match($strPattern, reset($aryMatched), $aryMatches);
                //@todo this assumes 1 exists without checking first
                $site_host = $aryMatches[1];
            } else {
                //@todo throw an error?
                //echo '<p>I found too many matches for our primary domain:</p><pre>',var_export($aryMatched,true),'</pre>';exit();
            }
        }
    } else {
        /**
         * we'll re-use platform's original code, with some minor modifications. Check whether a route is defined for
         * this application in the Platform.sh routes. Use it as the site hostname if so (it is never ideal to trust
         * HTTP_HOST).
         */
        $strPlatformAppName = getenv('PLATFORM_APPLICATION_NAME');
        foreach ($aryRoutes as $strURL => $aryRoute) {
            if ('upstream' === $aryRoute['type'] && $aryRoute['upstream'] === $strPlatformAppName) {
                // Pick the first hostname, or the first HTTPS hostname if one exists.
                $strHost = parse_url($strURL, PHP_URL_HOST);
                $strScheme = parse_url($strURL, PHP_URL_SCHEME);

                if (false !== $strHost && $strScheme == $site_scheme) { //i.e. is https
                    $site_host = $strHost;
                }
            }
        }
    }

    // Debug mode should be disabled on Platform.sh. Set this constant to true
    // in a wp-config-local.php file to skip this setting on local development.
    if (!defined('WP_DEBUG')) {
        define('WP_DEBUG', false);
    }

    // Set all of the necessary keys to unique values, based on the Platform.sh
    // entropy value.
    if (getenv('PLATFORM_PROJECT_ENTROPY')) {
        $keys = [
            'AUTH_KEY', 'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY', 'LOGGED_IN_SALT',
            'NONCE_KEY', 'NONCE_SALT',
            'AUTH_SALT', 'SECURE_AUTH_SALT',
        ];
        $entropy = getenv('PLATFORM_PROJECT_ENTROPY');
        foreach ($keys as $key) {
            if (!defined($key)) {
                define($key, $entropy . $key);
            }
        }
    }
} else {
    // You can create a wp-config-local.php file with local configuration.
    if (file_exists(dirname(__FILE__) . '/wp-config-local.php') ) {
        include dirname(__FILE__) . '/wp-config-local.php';
    }
}

// Define wp-content directory outside of WordPress core directory
define('WP_HOME', $site_scheme . '://' . $site_host);
define('WP_SITEURL', WP_HOME . '/wp');

define('WP_CONTENT_DIR', dirname(__FILE__) . '/web/wp-content');

$strContentURL =  WP_HOME . '/wp-content';
if (MULTISITE) {
    define('DOMAIN_CURRENT_SITE', $site_host);
    /**
     * We need to define the cookie domain constant if we're on a multi domain multisite
     */
    if (SUBDOMAIN_INSTALL) {
        $strContentURL = '/wp-content';
        //we'll set the cookie_domain constant to the correct requested domain
        if (isset($_SERVER[$strDomainRequest])) {
            $strDomainPattern = '/^(?:www.)?((?:[A-Za-z0-9_\-]+\.){1,6}[A-Za-z0-9_\-]{2,})$/';
            if (1 === preg_match($strDomainPattern, $_SERVER[$strDomainRequest], $aryMatches)) {
                define('COOKIE_DOMAIN', $aryMatches[1]);
            }
        }
    }
}

define('WP_CONTENT_URL', $strContentURL);

// Since you can have multiple installations in one database, you need a unique
// prefix.
$table_prefix  = 'wp_';

/**
 * some plugins require constants be added to the wp-config.php file. Since this file is not changeable on a site-by-site
 * basis, will include a secondary file that is site-editable, allowing for additional constants or overriding of any
 * variables that have already been set (e.g. $table_prefix)
 */

if (file_exists(dirname(__FILE__) . '/wp-config-extras.php')) {
    include dirname(__FILE__) . '/wp-config-extras.php';
}

// Default PHP settings.
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.gc_maxlifetime', 200000);
ini_set('session.cookie_lifetime', 2000000);
ini_set('pcre.backtrack_limit', 200000);
ini_set('pcre.recursion_limit', 200000);

/**
 * Absolute path to the WordPress directory. 
*/
if (!defined('ABSPATH') ) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

/**
 * Sets up WordPress vars and included files.
 * Moved to ./web/wp-config.php
 *
 * @see https://github.com/wp-cli/wp-cli/issues/1218
 */
//require_once(ABSPATH . 'wp-settings.php');
