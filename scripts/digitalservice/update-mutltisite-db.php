<?php
/**
 * Created by PhpStorm.
 * User: gilzowp
 * Date: 3/12/19
 * Time: 1:41 PM
 * pattern we're using for domain matching : ^https:\/\/(marcom\.missouri\.edu([^\/]+))
 * first group is the whole domain matched against our production domain
 * second group is everything after the production domain
 * domain replacement patter: (marcom\.missouri\.edu[^\/]*)
 * match at the beginning or at after a forward slash
 * match marcom.missouri.edu.edu + anything that isnt a / zero or more times
 * @todo for some reason the wp find command is causing notices
 * PLATFORM_APP_DIR
 * LANDO_MOUNT
 */
/**
 * File name that contains the list of domains
 */
define('MULTISITE_DOMAINS_FILE', 'multisite-domains.json');

/**
 * Ensures the path ends in a directory separator
 * @param $strPath string path to evaluate
 * @return string path with an ensured ending separator
 */
function appendDirectorySeparator($strPath) {
    $strPattern = sprintf('/\%s$/', DIRECTORY_SEPARATOR);
    if (0 === preg_match($strPattern, $strPath)) {
        $strPath .= DIRECTORY_SEPARATOR;
    }

    return $strPath;
}

$boolMultisite = getenv('MULTISITE');
/**
 * We're testing for a platform-specific ENV, but since we *dont* want master, this should never be true for a local
 * development environment.
 */
if (filter_var($boolMultisite, FILTER_VALIDATE_BOOLEAN) && 'master' !== getenv('PLATFORM_BRANCH')) {
    echo "Beginning multisite database update check...\n";
    //ok, where are we?
    if (false !== $strRoutes = getenv('PLATFORM_ROUTES')) {
        //we're on platform
        $aryRoutes = json_decode(base64_decode($strRoutes), true);
        $aryNewDomains = array_keys($aryRoutes);
        $strAppPathENV = 'PLATFORM_APP_DIR';
        $strWebRootENV = 'PLATFORM_DOCUMENT_ROOT';
    } elseif (false !== $aryLandoInfo = getenv('LANDO_INFO')) {
        // We're on Lando
        $objLandoInfo = json_decode($aryLandoInfo);
        $aryNewDomains = $objLandoInfo->appserver_nginx->urls;
        $strAppPathENV = 'LANDO_MOUNT';
        $strWebRootENV = 'LANDO_WEBROOT';
    } else {
        /**
         * we aren't on platform, and we're not using Lando for local dev
         * You can create a update-multisite-db-local.php file with local configuration.
         * File must set:
         * $aryNewDomains - array of local domains you are using for your multisite domains
         * $strAppPathENV - the environmental variable that points to the application directory
         * $strWebRootENV - the environment variable that points to the web root directory
         */
        if (file_exists(dirname(__FILE__) . '/update-multisite-db-local.php')) {
            include(dirname(__FILE__) . '/update-multisite-db-local.php');
        }
    }

    /**
     * now we need to see if we have our multisite json file, but first do we need to add a directory seperator?
     */
    $strPathToApp = appendDirectorySeparator(getenv($strAppPathENV));
    echo "checking to see if our domains.json file exists...\n";
    if (file_exists($strPathToApp . MULTISITE_DOMAINS_FILE)) {
        // we have our file, let's proceed
        $aryProductionDomains = json_decode(file_get_contents($strPathToApp . MULTISITE_DOMAINS_FILE));
        if (is_array($aryProductionDomains) && count($aryProductionDomains) > 0) {
            //FINALLY we can work
            /**
             * @todo wp find on platform not working. If we can get it working, switch back to finding the the wp directory
             * instead of assuming where it is.
             *
             * strFindPathtoWP = exec("wp find \$$strAppPathENV --fields=wp_path --format=json --quiet");
             * $objPath = json_decode($strFindPathtoWP);
             * $strPathtoWP = $objPath[0]->wp_path;
             *
             */
            $strPathtoWP = appendDirectorySeparator(getenv($strWebRootENV)) . 'wp';
            //we also need the primary domain of the site
            // @todo what do we do if PRIMARY_DOMAIN has not been set?
            $strPrimaryDomain = getenv('PRIMARY_DOMAIN');
            echo "Our list of potential new domains:\n",var_export($aryNewDomains, true),"\n";

//now we need to get the list of domains from the database to see if we actually need to update them
            $strCurrentDomains = `wp site list --field=url --format=csv --no-header --path=$strPathtoWP --url=$strPrimaryDomain`;
            $aryCurrentDomains = str_getcsv($strCurrentDomains, "\n");

            echo "Our list of current domains from wp: \n",var_export($aryCurrentDomains, true), "\n";

            $aryDomainsToProcess = array_diff($aryNewDomains, $aryCurrentDomains);

            echo "Our domains to process from the diff:\n",var_export($aryDomainsToProcess, true), "\n";

            /**
             * Have all the domains already been converted (database was previously synced)?
             * If new domains is empty, then the diff will be empty
             */
            if (count($aryDomainsToProcess) > 0) {
                echo "There are domains that need to be updated in the database. Beginning conversion...\n";
                foreach ($aryProductionDomains as $strDomain) {
                    $strPattern = sprintf('/^https:\/\/(%s([^\/]+))/', $strDomain);
                    //now find all of the domains in our list that match
                    $aryMatchedDomains = preg_grep($strPattern, $aryDomainsToProcess);
                    echo 'For the domain ', $strDomain, " here are the matching local domains:\n", var_export($aryMatchedDomains, true),"\n";
                    if (count($aryMatchedDomains) > 0) {
                        preg_match($strPattern, reset($aryMatchedDomains), $aryMatchedDomain);
                        //echo 'Updating database for domain ', $strDomain, ' to the new local domain ', $aryMatchedDomain[1], "\n";
                        //prep our original domain
                        $strDomainPattern = sprintf('(%s[^\/]*)', str_replace('.', '\.', $strDomain));
                        //now update the database
                        echo "Replacing $strDomain with $aryMatchedDomain[1] in site and blogs using the pattern $strDomainPattern\n";
                        `wp search-replace '$strDomainPattern' $aryMatchedDomain[1] *_site *_blogs --regex --path=$strPathtoWP --url=$strDomain --verbose`;
                        $strDomainPattern = '(https?):\/\/'.$strDomainPattern;
                        echo "Replacing $strDomain with $aryMatchedDomain[1] in options and posts using the pattern $strDomainPattern\n";
                        `wp search-replace '$strDomainPattern' '\$1://$aryMatchedDomain[1]' '*_options' '*_posts' --skip-columns=guid --regex --path=$strPathtoWP --url=$aryMatchedDomain[1] --verbose`;
                    } else {
                        echo "$strDomain is already updated. Skipping.\n";
                    }
                }
                echo "Conversion of domains completed.\n";
            }

            echo "Multisite database update complete.\n";
        } else {
            echo 'It appears that ', MULTISITE_DOMAINS_FILE, ' is empty or not in the correct format. I can\'t remap';
            echo ' your domains without this information.';
        }
    } else {
        // well, crap
        echo "The file ", MULTISITE_DOMAINS_FILE, " is missing or not located in the APP root (", $strPathToApp,"). I can't remap your ";
        echo "domains without this file. Please create this file, add your domains and then try again.\n";
    }


}
