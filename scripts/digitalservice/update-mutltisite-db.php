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
 */
$boolMultisite = getenv('MULTISITE');
/**
 * We're testing for a platform-specific ENV, but since we *dont* want master, this should never be true for a local
 * development environment.
 */
if (filter_var($boolMultisite, FILTER_VALIDATE_BOOLEAN) && 'master' !== getenv('PLATFORM_BRANCH')) {
    echo "Beginning multisite database update check...\n";
    //set defaults
    $aryNewDomains = array();
    $strPathToApp = '';
    //we need a list of all the domains we have to deal with
    // @todo assumes everyone has installed the platform phar and placed it in the same location
    $strProductionDomains = `platform domain:list --format=csv --no-header --columns=Name`;
    $aryProductionDomains = str_getcsv($strProductionDomains, "\n");
    /*
     * now we need our list of current URLs
     * First, are we on platform?
     */
    if (false !== $strRoutes = getenv('PLATFORM_ROUTES')) {
        $aryRoutes = json_decode(base64_decode($strRoutes), true);
        $aryNewDomains = array_keys($aryRoutes);
        $strAppPathENV = 'PLATFORM_DOCUMENT_ROOT';
    } elseif (false !== $aryLandoInfo = getenv('LANDO_INFO')) {
        // We're on Lando
        $objLandoInfo = json_decode($aryLandoInfo);
        $aryNewDomains = $objLandoInfo->appserver_nginx->urls;
        $strAppPathENV = 'LANDO_MOUNT';
    } else {
        /**
         * we aren't on platform, and we're not using Lando for local dev
         * You can create a update-multisite-db-local.php file with local configuration.
         * File must set:
         * $aryNewDomains - array of local domains you are using for your multisite domains
         * $strAppPathENV - the environmental variable that points to the application directory
         */
        if (file_exists(dirname(__FILE__) . '/update-multisite-db-local.php')) {
            include(dirname(__FILE__) . '/update-multisite-db-local.php');
        }
    }


    /**
     * @todo wp find on platform not working. If we can get it working, switch back to finding the the wp directory
     * instead of assuming where it is.
     *
     * strFindPathtoWP = exec("wp find \$$strAppPathENV --fields=wp_path --format=json --quiet");
     * $objPath = json_decode($strFindPathtoWP);
     * $strPathtoWP = $objPath[0]->wp_path;
     *
     */
    if (false !== $strFindPathtoWP = getenv($strAppPathENV)) {
        if (0 !== strpos(strrev($strFindPathtoWP), DIRECTORY_SEPARATOR)) {
            $strFindPathtoWP .= DIRECTORY_SEPARATOR;
        }
        $strPathtoWP = $strFindPathtoWP . 'wp';
    }

    echo "Our list of potential new domains:\n",var_export($aryNewDomains,true),"\n";

    //now we need to get the list of domains from the database to see if we actually need to update them
    $strCurrentDomains = `wp site list --field=url --format=csv --no-header --path=$strPathtoWP`;
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
}
