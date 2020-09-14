<?php
/**
 * Class Functions.
 *
 * @package Gaterdata
 * @subpackage Processor
 * @author john89 (https://gitlab.com/john89)
 * @copyright 2020-2030 GaterData
 * @license This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 *      If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 * @link https://gaterdata.com
 */

namespace Gaterdata\Processor;

use Gaterdata\Core;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

/**
 * Class Functions
 *
 * Processor class to list all processor/functions.
 */
class Functions extends Core\ProcessorEntity
{
    /**
     * @var array Details of the processor.
     *
     * {@inheritDoc}
     */
    protected $details = [
        'name' => 'Functions',
        'machineName' => 'functions',
        'description' => 'Fetch data on a single or all Functions.',
        'menu' => 'System',
        'input' => [
            'machine_name' => [
                'description' => 'The resource machine_name or "all" for all functions.',
                'cardinality' => [1, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['text'],
                'limitValues' => [],
                'default' => '',
            ],
        ],
    ];

    /**
     * @var array list of namespaces to fetch.
     */
    private $namespaces = [
        'Endpoint',
        'Output',
        'Processor',
        'Security',
    ];

    /**
     * {@inheritDoc}
     *
     * @return Core\DataContainer Result of the processor.
     *
     * @throws Core\ApiException Exception if invalid result.
     */
    public function process()
    {
        $this->logger->info('Processor: ' . $this->details()['machineName']);

        $machineName = $this->val('machine_name', true);

        $details = [];
        foreach ($this->namespaces as $namespace) {
            $classNames = $this->_getClassList($namespace);
            foreach ($classNames as $className) {
                $detail = $this->_getDetails($namespace, $className);
                if ($detail !== false) {
                    $details[$detail['machineName']] =  $detail;
                }
            }
        }
        sort($details);

        if ($machineName == 'all') {
            return $details;
        }

        if (!isset($details[$machineName])) {
            throw new Core\ApiException("Invalid machine name: $machineName", 6, $this->id, 401);
        }
        return $details[$machineName];
    }

    /**
     * Get a list of classes from a directory.
     *
     * @param string $namespace The subdirectory/namespace.
     *
     * @return array The list of class names.
     */
    private function _getClassList(string $namespace)
    {
        $iterator = new RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/../' . $namespace));
        $objects = new RegexIterator($iterator, '/[a-z0-9]+\.php/i', \RecursiveRegexIterator::GET_MATCH);
        $result = [];
        foreach ($objects as $name => $object) {
            preg_match('/([a-zA-Z0-9]+)\.php$/i', $name, $className);
            $result[] = $className[1];
        }
        return $result;
    }

    /**
     * Return the default details attributed from a class.
     *
     * @param string $namespace The namespace that the class belongs to.
     * @param string $className The classname.
     *
     * @return array The details array.
     *
     * @throws \ReflectionException Error.
     */
    private function _getDetails(string $namespace, string $className)
    {
        $reflector = new ReflectionClass("\\Gaterdata\\$namespace\\$className");
        if (!$reflector->isAbstract()) {
            $properties = $reflector->getDefaultProperties();
            return $properties['details'];
        }

        return false;
    }
}
