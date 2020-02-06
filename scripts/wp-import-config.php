<?php
/**
 * Automates the process of importing/pulling configuration bundles into the system on deploy
 */

exec('wp config bundles --format=json',$aryJSONBunles,$intSuccess);

if (0 === $intSuccess) {
    $aryBundles = json_decode(reset($aryJSONBunles));
    if (!is_null($aryBundles)) {
        foreach ($aryBundles as $objBundle) {
            if (!filter_var($objBundle->is_db, FILTER_VALIDATE_BOOLEAN)) {
                echo "$objBundle->name configuration bundle is not in the database. Importing...";
                exec('wp config pull '.$objBundle->name, $aryOutPut, $intPullSuccess);
                if (0 === $intPullSuccess) {
                    echo " Success!\n";
                } else {
                    echo "\nFailed to import the bundle $objBundle->name \n";
                }
            }
        }
    }
} else {
    echo "There was an issue running the `wp config bundles` command... \n";
}
