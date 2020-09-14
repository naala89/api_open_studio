<?php
/**
 * Class ResourceValidator.
 *
 * @package Gaterdata
 * @subpackage Core
 * @author john89 (https://gitlab.com/john89)
 * @copyright 2020-2030 GaterData
 * @license This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 *      If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 * @link https://gaterdata.com
 */

namespace Gaterdata\Core;

use Monolog\Logger;

/**
 * Class ResourceValidator
 *
 * Validate a resource definition.
 */
class ResourceValidator
{
    /**
     * @var ProcessorHelper
     */
    protected $helper;

    /**
     * @var \ADODB_mysqli
     */
    private $db;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * Constructor. Store processor metadata and request data in object.
     *
     * If this method is overridden by any derived classes, don't forget to call parent::__construct()
     *
     * @param \ADODB_mysqli $db Database.
     * @param \Monolog\Logger $logger Logger.
     */
    public function __construct(\ADODB_mysqli $db, Logger $logger)
    {
        $this->helper = new ProcessorHelper();
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Validate input data is well formed.
     *
     * @param array $data Resource metadata array.
     *
     * @return void
     *
     * @throws Core\ApiException Input data not well formnd.
     */
    public function validate(array $data)
    {
        $this->logger->notice('Validating the new resource...');
        // check mandatory elements exists in data
        if (empty($data)) {
            throw new Core\ApiException("empty resource uploaded", 6, -1, 406);
        }
        if (!isset($data['process'])) {
            throw new Core\ApiException("missing process in new resource", 6, -1, 406);
        }

        // validate for identical IDs
        $this->validateIdenticalIds($data);

        // validate dictionaries
        if (isset($data['security'])) {
            $this->validateDetails($data['security']);
        }
        if (isset($data['output'])) {
            $this->validateDetails($data['output']);
        }
        if (!empty($data['fragments'])) {
            if (!Core\Utilities::is_assoc($data['fragments'])) {
                throw new Core\ApiException("invalid fragments structure in new resource", 6, -1, 406);
            }
            foreach ($data['fragments'] as $fragKey => $fragVal) {
                $this->validateDetails($fragVal);
            }
        }
        $this->validateDetails($data['process']);
    }

    /**
     * Search for identical IDs.
     *
     * @param array $meta Resource metadata array.
     *
     * @return boolean
     *
     * @throws Core\ApiException Identical ID found.
     */
    private function validateIdenticalIds(array $meta)
    {
        $id = [];
        $stack = [$meta];

        while ($node = array_shift($stack)) {
            if ($this->helper->isProcessor($node)) {
                if (in_array($node['id'], $id)) {
                    throw new Core\ApiException('identical ID in new resource: ' . $node['id'], 6, -1, 406);
                }
                $id[] = $node['id'];
            }
            if (is_array($node)) {
                foreach ($node as $item) {
                    array_unshift($stack, $item);
                }
            }
        }

        return true;
    }

    /**
     * Validate a resource section.
     *
     * @param array $meta Resource metadata array.
     *
     * @return void
     *
     * @throws Core\ApiException Error found in validating the resource.
     */
    private function validateDetails(array $meta)
    {
        $stack = array($meta);

        while ($node = array_shift($stack)) {
            if ($this->helper->isProcessor($node)) {
                $classStr = $this->helper->getProcessorString($node['function']);
                $class = new $classStr($meta, new Request(), $this->db, $this->logger);
                $details = $class->details();
                $id = $node['id'];
                $this->logger->notice('Validating: ' . $id);

                foreach ($details['input'] as $inputKey => $inputDef) {
                    $min = $inputDef['cardinality'][0];
                    $max = $inputDef['cardinality'][1];
                    $literalAllowed = $inputDef['literalAllowed'];
                    $limitFunctions = $inputDef['limitFunctions'];
                    $limitTypes = $inputDef['limitTypes'];
                    $limitValues = $inputDef['limitValues'];
                    $count = 0;

                    if (!empty($node[$inputKey])) {
                        $input = $node[$inputKey];

                        if ($this->helper->isProcessor($input)) {
                            if (!empty($limitFunctions) && !in_array($input['function'], $limitFunctions)) {
                                $message = 'processor ' . $input['id'] . ' is an invalid function type (only "'
                                    . implode('", ', $limitFunctions) . '" allowed)';
                                throw new Core\ApiException($message, 6, $id, 406);
                            }
                            array_unshift($stack, $input);
                            $count = 1;
                        } elseif (is_array($input)) {
                            foreach ($input as $item) {
                                if ($this->helper->isProcessor($item)) {
                                    array_unshift($stack, $item);
                                } else {
                                    $this->validateTypeValue($item, $limitTypes, $id);
                                }
                            }
                            $count = sizeof($input);
                        } elseif (!$literalAllowed) {
                            $message = "literals not allowed as input for '$inputKey' in function: $id";
                            throw new Core\ApiException($message, 6, $id, 406);
                        } else {
                            if (!empty($limitValues) && !in_array($input, $limitValues)) {
                                $message = "invalid value type for '$inputKey' in function: $id";
                                throw new Core\ApiException($message, 6, $id, 406);
                            }
                            if (!empty($limitTypes)) {
                                $this->validateTypeValue($input, $limitTypes, $id);
                            }
                            $count = 1;
                        }
                    }

                    // validate cardinality
                    if ($count < $min) {
                        // check for nothing to validate and if that is ok.
                        $message = "input '$inputKey' in function '" . $node['id'] . "' requires min $min";
                        throw new Core\ApiException($message, 6, $id, 406);
                    }
                    if ($max != '*' && $count > $max) {
                        $message = "input '$inputKey' in function '" . $node['id'] . "' requires max $max";
                        throw new Core\ApiException($message, 6, $id, 406);
                    }
                }
            } elseif (is_array($node)) {
                foreach ($node as $key => $value) {
                    array_unshift($stack, $value);
                }
            }
        }
    }

    /**
     * Compare an element type and possible literal value or type in the input resource with the definition in the
     * Processor it refers to. If the element type is processor, recursively iterate through, using the calling
     * function _validateProcessor().
     *
     * @param mixed $element Literal value in a resource to validate against $accepts.
     * @param array $accepts Array of types the processor can accept.
     * @param string|integer $id Processor ID.
     *
     * @return boolean
     *
     * @throws Core\ApiException Invalid $element.
     */
    private function validateTypeValue($element, array $accepts, $id)
    {
        if (empty($accepts)) {
            return true;
        }
        $valid = false;

        foreach ($accepts as $accept) {
            if ($accept == 'file') {
                $valid = true;
                break;
            } elseif ($accept == 'literal' && (is_string($element) || is_numeric($element))) {
                $valid = true;
                break;
            } elseif ($accept == 'boolean'
                && filter_var($element, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null) {
                $valid = true;
                break;
            } elseif ($accept == 'integer'
                && filter_var($element, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) !== null) {
                $valid = true;
                break;
            } elseif ($accept == 'text' && is_string($element)) {
                $valid = true;
                break;
            } elseif ($accept == 'float'
                && filter_var($element, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) !== null) {
                $valid = true;
                break;
            } elseif ($accept == 'array' && is_array($element)) {
                $valid = true;
                break;
            }
        }
        if (!$valid) {
            $message = 'invalid literal in new resource (' . print_r($element) . '. only "' .
                implode("', '", $accepts) . '" accepted';
            throw new Core\ApiException($message, 6, $id, 406);
        }
        return $valid;
    }
}
