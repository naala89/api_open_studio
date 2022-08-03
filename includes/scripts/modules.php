#!/usr/bin/env php
<?php

/**
 * Script to install a plugin or processor module in ApiOpenStudio.
 *
 * @package   ApiOpenStudio
 * @license   This Source Code Form is subject to the terms of the ApiOpenStudio Public License.
 *            If a copy of the license was not distributed with this file,
 *            You can obtain one at https://www.apiopenstudio.com/license/.
 * @author    john89 (https://gitlab.com/john89)
 * @copyright 2020-2030 Naala Pty Ltd
 * @link      https://www.apiopenstudio.com
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use ApiOpenStudio\Cli\Modules;

global $argv;

$modules = new Modules();
$modules->exec($argv);

exit;
