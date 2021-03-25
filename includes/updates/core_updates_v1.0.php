<?php

/**
 * Update functions for ApiOpenStudio v1.0
 *
 * @package    ApiOpenStudio
 * @subpackage Updates
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 Naala Pty Ltd
 * @license    This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 *             If a copy of the MPL was not distributed with this file,
 *             You can obtain one at https://mozilla.org/MPL/2.0/.
 * @link       https://www.apiopenstudio.com
 */

/**
 * Example update function.
 *
 * @param ADODB_mysqli $db
 *
 * @version V0.0.0
 */
function example_update(ADODB_mysqli $db)
{
    // Do Something
}

/**
 * Update all resource meta to use the 'processor' keyword instead of 'function'.
 *
 * Part 1 - Update meta function key.
 *
 * @param ADODB_mysqli $db
 *
 * @version V1.0.0-alpha2
 *
 * @see https://gitlab.com/john89/api_open_studio/-/issues/54
 */
function update_all_resources_54_part_1(ADODB_mysqli $db)
{
    echo "Updating the meta for all resources...\n";

    $sql = "SELECT * FROM resource";
    $resources = $db->execute($sql);
    while ($resource = $resources->fetchRow()) {
        $resid = $resource['resid'];
        $meta_old = $resource['meta'];
        $name = $resource['name'];
        echo "Checking resource $resid: $name.\n";
        $meta_new = str_ireplace('"function":', '"processor":', $meta_old);
        if ($meta_new == $meta_old) {
            echo "Nothing to update.\n";
        } else {
            echo "Updating resource $resid: $name\n";
            $sql = "UPDATE resource SET meta = '$meta_new' WHERE resid = $resid";
            $db->execute($sql);
        }
    }
}

/**
 * Update all resource meta to use the 'processor' keyword instead of 'function'.
 *
 * Part 2 - Update the functions processor.
 *
 * @param ADODB_mysqli $db
 *
 * @version V1.0.0-alpha2
 *
 * @see https://gitlab.com/john89/api_open_studio/-/issues/54
 */
function update_all_resources_54_part_2(ADODB_mysqli $db)
{
    echo "Updating the Core 'Functions' resource\n";

    $config = new \ApiOpenStudio\Core\Config();
    $coreAccount = $config->__get(['api', 'core_account']);
    $coreApplication = $config->__get(['api', 'core_application']);
    $basePath = $config->__get(['api', 'base_path']);
    $dirResources = $config->__get(['api', 'dir_resources']);

    // Find the old Functions processor in the DB.
    $sql = "SELECT res.* FROM resource AS res ";
    $sql .= "INNER JOIN application AS app ON res.appid = app.appid ";
    $sql .= "INNER JOIN account AS acc ON app.accid = acc.accid ";
    $sql .= "WHERE acc.name = '$coreAccount' ";
    $sql .= "AND app.name = '$coreApplication' ";
    $sql .= "AND res.name = 'Functions'";
    $resources = $db->execute($sql);
    if ($resources->recordCount() === 0) {
        $message = "Error: unable to find the 'Functions' resource for $coreAccount, $coreApplication.";
        $message .= " Please validate the SQL: $sql\n";
        echo $message;
        exit();
    }

    // Load the data from the new Processors processor file.
    $file = $basePath . $dirResources . 'processors.yaml';
    $yaml = $name = $description = $uri = $method = $appid = $ttl = $meta = '';
    if (!$contents = file_get_contents($file)) {
        echo "Error: unable to find the new $file file!\n";
        exit();
    }
    $yaml = \Spyc::YAMLLoadString($contents);
    $name = $yaml['name'];
    $description = $yaml['description'];
    $uri = $yaml['uri'];
    $method = $yaml['method'];
    $appid = $yaml['appid'];
    $ttl = $yaml['ttl'];
    $meta = [];
    if (!empty($yaml['security'])) {
        $meta[] = '"security": ' . json_encode($yaml['security']);
    }
    if (!empty($yaml['process'])) {
        $meta[] = '"process": ' . json_encode($yaml['process']);
    }
    $meta = '{' . implode(', ', $meta) . '}';

    // Delete the old Functions processor.
    while ($resource = $resources->fetchRow()) {
        $resid = $resource['resid'];
        echo "Deleting $resid: " . $resource['name'] . "'\n";
        $sql = "DELETE FROM resource WHERE resid = $resid";
        $db->execute($sql);
    }

    // Insert the new Processors processor.
    echo "Inserting new Processors processor\n";
    $sql = 'INSERT INTO resource (`appid`, `name`, `description`, `method`, `uri`, `meta`, `ttl`)';
    $sql .= "VALUES ($appid, '$name', '$description', '$method', '$uri', '$meta', $ttl)";
    if (!($db->execute($sql))) {
        echo "$sql\n";
        echo "Error: insert resource `$name` failed, please check your logs.\n";
        exit;
    }
}
