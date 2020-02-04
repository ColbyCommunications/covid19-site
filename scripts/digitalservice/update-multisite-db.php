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
 * @todo this whole thing needs to be refactored
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
function appendDirectorySeparator($strPath)
{
    $strPattern = sprintf('/\%s$/', DIRECTORY_SEPARATOR);
    if (0 === preg_match($strPattern, $strPath)) {
        $strPath .= DIRECTORY_SEPARATOR;
    }

    return $strPath;
}

/**
 * Attempts to find the local/temporary URL based on the list of local domains and the project primary domain
 * @param array $aryLocalDomains list of local/temp URLs
 * @param string $strPrimaryDomain the primary/root domain for the project
 * @param string regex pattern to use for matching against the domains. Default is '/^https:\/\/(%s([^\/]+))/'
 * @return bool|array
 */
function findLocalPrimaryDomain(array $aryLocalDomains, string $strPrimaryDomain, $strPattern='/^https:\/\/(%s([^\/]+))/')
{
    $return = false;
    $strPrimaryDomainPattern = sprintf($strPattern, preg_quote($strPrimaryDomain));
    $aryPrimaryDomain = preg_grep($strPrimaryDomainPattern, $aryLocalDomains);
    if (1 == count($aryPrimaryDomain)) {
        $return = parse_url(reset($aryPrimaryDomain), PHP_URL_HOST);
    }

    return $return;
}

function searchReplace(string $strSearch, string $strReplace, array $aryIncludeTables, array $aryIncludeColumns, string $strPath, string $strUrl)
{
    $strTables = implode(' ', $aryIncludeTables);
    $strColumns = implode(',', $aryIncludeColumns);
    `wp search-replace '$strSearch' '$strReplace' $strTables --regex --include-columns=$strColumns --path=$strPath --url=$strUrl --verbose`;
}

/**
 * Are we running a multisite? I mean, we _should_ be if we're running this file, but let's double-check
 */
$boolMultisite = getenv('MULTISITE');
/**
 * ok, what _type_ of multisite: domain or directory?
 */
$boolMultiDomain = filter_var(getenv('MULTISITE_MULTIDOMAIN'), FILTER_VALIDATE_BOOLEAN);
/**
 * We're testing for a platform-specific ENV, but since we *dont* want master, this should never be true for a local
 * development environment.
 */
if (filter_var($boolMultisite, FILTER_VALIDATE_BOOLEAN) && 'master' !== getenv('PLATFORM_BRANCH')) {
    /**
     * Only search columns we know we need to change.
     * `option_name` is not included. Do we need to include it?
     */
    $aryIncludeColumnsPostOptions = array(
        'option_value',
        'post_content',
        'post_excerpt',
        'post_content_filtered'
    );

    /**
     * Should only need the one column: domain
     *
     */
    $strIncludeColumnsSiteBlogs = 'domain';

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
    echo "\e[1;34mChecking to see if our domains.json file exists...\e[0m";
    if (file_exists($strPathToApp . MULTISITE_DOMAINS_FILE)) {
        echo "\e[1;31m Check.\e[0m\n";
        // we have our file, let's proceed
        $aryProductionDomains = json_decode(file_get_contents($strPathToApp . MULTISITE_DOMAINS_FILE));
        if (is_array($aryProductionDomains) && count($aryProductionDomains) > 0) {
            //FINALLY we can work
            /**
             * @todo wp find on platform not working. If we can get it working, switch back to finding the the wp
             * directory instead of assuming where it is.
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
            //and the table prefix
            $strTablePrefix = rtrim(`wp config get table_prefix --path=$strPathtoWP`);
            //and the new local primary domain
            $strLocalPrimaryDomain = findLocalPrimaryDomain($aryNewDomains, $strPrimaryDomain);

            if (false === $strLocalPrimaryDomain && false === $boolMultiDomain) {
                /**
                 * We can have a situation where a directory-based multisite has not had a domain added to its project
                 * yet, but we still want to use it for testing.
                 */
                unset($strLocalPrimaryDomain);
                //pattern is match any domain name that is https and does not start with "www."
                $strLocalPrimaryDomain = findLocalPrimaryDomain($aryNewDomains,$strPrimaryDomain,'/^https:\/\/((?!w{3}\.)[^\/]+)/');
            }


            /**
             * @todo we need to check and make sure that localPrimaryDomain isnt false before continuing
             */
            if (false === $strLocalPrimaryDomain) {
                /**
                 * @todo I do not like at all throwing an exit here. refactor this spaghetti
                 */
                echo "\e[1;31mI'm not able to determine the local domain(s). ";
                echo " Without it, I'm unable to continue. Exiting. .\e[0m\n";
                exit();
            }
            //now we need to get the list of domains from the database to see if we actually need to update them
            $strSiteListCmdPattern = "wp site list --fields=blog_id,url --format=csv --no-header --path=%s --url=%s";
            exec(sprintf($strSiteListCmdPattern, $strPathtoWP, $strPrimaryDomain), $aryCurrentDomainsRows, $intSuccess);

            if (1 === $intSuccess) {
                unset($aryCurrentDomainsRows,$intSuccess);
                echo "\e[1;33mIt appears that the PRIMARY_DOMAIN has already been processed. Trying to find the new local domain...\e[0m\n";
                //ok, it's possible they already processed the primary domain, but others still need to be converted
                //let's see if we can find the local domain that matches primary domain

                if (false !== $strLocalPrimaryDomain) {
                    //lets try again
                    exec(sprintf($strSiteListCmdPattern,$strPathtoWP,$strLocalPrimaryDomain), $aryCurrentDomainsRows, $intSuccess);
                    //I give up.
                    if(1 === $intSuccess) {
                        echo "\e[1;31mNeither the PRIMARY_DOMAIN or the local version $strLocalPrimaryDomain appear to be set in the database";
                        echo " Without the primary domain, I'm unable to continue. . Exiting. .\e[0m\n";
                        exit();
                    }
                } else {
                    echo "\e[1;31mIt appears that the PRIMARY_DOMAIN has already been converted and I am unable to locate a local version. Exiting. .\e[0m\n";
                    exit();
                }
            }

            $aryCurrentDomains = array();
            if (count($aryCurrentDomainsRows) > 0) {
                foreach ($aryCurrentDomainsRows as $strRow) {
                    $aryRow = str_getcsv($strRow);
                    if ('blog_id' != $aryRow[0]) {
                        if ($boolMultiDomain) {
                            $strSite = parse_url($aryRow[1], PHP_URL_HOST);
                        } else {
                            $strSite = $aryRow[1];
                        }
                        $aryCurrentDomains[$aryRow[0]] = $strSite;
                    }
                }
            }

            if (!$boolMultiDomain) {
                #now we need all of the sites that have not been adjusted to the local domain
                $strNotLocalPattern = "/^((?!%s).)*$/";
                $aryDomainsToProcess = preg_grep(sprintf($strNotLocalPattern,preg_quote($strLocalPrimaryDomain)), $aryCurrentDomains);
            } else {
                $aryDomainsToProcess = array_diff($aryNewDomains, $aryCurrentDomains);
            }


            /**
             * Have all the domains already been converted (database was previously synced)?
             * If new domains is empty, then the diff will be empty
             */
            if (count($aryDomainsToProcess) > 0) {
                echo "\e[1;34mThere are domains that need to be updated in the database. Beginning conversion...\e[0m\n";

                /**
                 * Now is where it gets fun. with multidomain, we want to loop over each production domain name, locate the
                 * id, then find its corresponding local version. with subdirectory, we want to loop over each site in the
                 * site list since the root domain is the same for all.
                 */
                if (!$boolMultiDomain) {
                    /*
                     * We need to find what piece of the local/temp URL differs from the production URL so we don't
                     * double change a url:
                     * (e.g. foo.missouri.edu --> foo.missouri.edu.lndo.site --> foo.missouri.edu.lndo.site.lndo.site
                     */
                    $strNoReplacePattern='/%s(.*)/';
                    $intDomainMatch = preg_match(sprintf($strNoReplacePattern, preg_quote($strPrimaryDomain)), $strLocalPrimaryDomain, $aryMatches);
                    /**
                     * @todo I dont like this, refactor
                     */
                    if (!isset($aryMatches[1]) || 1 !== $intDomainMatch) {
                        echo "\e[1;37;41mUnable to find difference in Domains!\e[0m\n";
                        echo "I'm trying to update a sub-directory based multisite from the production domain of ";
                        echo $strPrimaryDomain, " to the local \ndomain of ", $strLocalPrimaryDomain, " but am unable ";
                        echo "to determine the difference between the two. I am unable to continue. Exiting.\n";
                        exit();
                    } else {
                        $strDontMatch = $aryMatches[1];
                        $strNegLookAheadPtrn = '(%s(?!%s))';
                        $strDomainSearch = sprintf($strNegLookAheadPtrn,preg_quote($strPrimaryDomain),preg_quote($strDontMatch));
                    }
                }

                if ($boolMultiDomain) {
                    $aryLoopDomains = $aryProductionDomains;
                } else {
                    $aryLoopDomains = $aryDomainsToProcess;
                }

                /**
                 * The tables needed for updating site and blog
                 */
                $arySiteBlogTbls = array(
                    $strSiteTable = $strTablePrefix . 'site',
                    $strBlogTable = $strTablePrefix . 'blogs',
                );

                foreach ($aryLoopDomains as $strDomain) {
                    //is this domain even in our list of domains in the site for us to change?
                    $intBlogID = array_search($strDomain, $aryCurrentDomains);
                    if (false !== $intBlogID) {
                        /**
                         * Table names for updating posts and options
                         */
                        $aryPostOptionsTbls = array(
                            $strTablePrefix.((1 === $intBlogID) ? '' : $intBlogID . '_').'options',
                            $strTablePrefix.((1 === $intBlogID) ? '' : $intBlogID . '_').'posts',
                        );

                        if ($boolMultiDomain) {
                            $strPattern = sprintf('/^https:\/\/(%s([^\/]+))/', $strDomain);
                            //now find all of the domains in our list that match
                            $aryMatchedDomains = preg_grep($strPattern, $aryDomainsToProcess);
                            $intContinue = count($aryMatchedDomains);
                        } else {
                            $strNoReplacePattern='/%s(.*)/';
                            $intContinue = preg_match(sprintf($strNoReplacePattern,preg_quote($strPrimaryDomain)), $strLocalPrimaryDomain, $aryMatches);
                        }

                        //if preg_match for dir returned a 1, or we have at least one match for domain
                        if ($intContinue > 0) {
                            if ($boolMultiDomain) {
                                preg_match($strPattern, reset($aryMatchedDomains), $aryMatchedDomain);

                                //prep our original domain
                                $strDomainPattern = sprintf('(%s[^\/]*)', str_replace('.', '\.', $strDomain));
                                $strDomainSearch = '(https?):\/\/'.$strDomainPattern;
                                $strDomainReplace = "\$1://$aryMatchedDomain[1]";
                                $strSearchReplaceDomain = $aryMatchedDomain[1];

                                /*
                                 * domain-based multisite needs to update blog/site tables _before_ we update posts/options
                                 * directory-based is the opposite bcuz WordPress
                                 */
                                echo "\e[1;34mUpdating the domain in the database from domain \e[1;37m\e[1m\e[4m", $strDomainSearch, "\e[0m\e[1;34m to the new local domain \e[1;37m\e[1m\e[4m", $strSearchReplaceDomain;
                                echo "\e[1;34m for the tables blogs and site.", "\e[0m\n";
                                searchReplace($strDomainPattern, $aryMatchedDomain[1], $arySiteBlogTbls, array($strIncludeColumnsSiteBlogs), $strPathtoWP, $strDomain);
                            } else {
                                $strDomainReplace = $strLocalPrimaryDomain;
                                $strSearchReplaceDomain = $strDomain;
                            }

                            echo "\e[1;34mUpdating the domain in the database from domain \e[1;37m\e[1m\e[4m", $strSearchReplaceDomain, "\e[0m\e[1;34m to the new local domain \e[1;37m\e[1m\e[4m", $strDomainReplace;
                            echo "\e[0m\e[1;34m for the tables posts and options.", "\e[0m\n";
                            searchReplace($strDomainSearch,$strDomainReplace, $aryPostOptionsTbls, $aryIncludeColumnsPostOptions,$strPathtoWP, $strSearchReplaceDomain);
                        } else {
                            echo "\e[1;32m$strDomain is already updated. Skipping.\e[0m\n";
                        }
                    } else {
                        echo "\e[1;32m$strDomain is not an active site in this multisite. Skipping.\e[0m\n";
                    }
                }

                /**
                 * Now that the we've looped through everything, the directory-based multisite can update the
                 * blogs/site tables
                 */
                if (!$boolMultiDomain) {
                    echo "\e[1;34mUpdating the domain in the database from domain \e[1;37m\e[1m\e[4m", $strPrimaryDomain, "\e[0m\e[1;34m to the new local domain \e[1;37m\e[1m\e[4m", $strLocalPrimaryDomain;
                    echo "\e[0m\e[1;34m for the tables blogs and site.", "\e[0m\n";
                    searchReplace($strPrimaryDomain, $strLocalPrimaryDomain, $arySiteBlogTbls, array($strIncludeColumnsSiteBlogs), $strPathtoWP,$strPrimaryDomain);
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
