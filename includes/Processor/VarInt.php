<?php
/**
 * Class VarInt.
 *
 * @package Gaterdata
 * @subpackage Processor
 * @author john89
 * @copyright 2020-2030 GaterData
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0-or-later
 * @link https://gaterdata.com
 */

namespace Gaterdata\Processor;

use Gaterdata\Core;

/**
 * Class VarInt
 *
 * Processor class to define an integer variable.
 */
class VarInt extends Core\ProcessorEntity
{
    /**
     * @var array Details of the processor.
     *
     * {@inheritDoc}
     */
    protected $details = [
    'name' => 'Var (Integer)',
        'machineName' => 'var_int',
        'description' => 'An integer variable. It validates the input and returns an error if it is not a integer.',
        'menu' => 'Primitive',
        'input' => [
            'value' => [
                'description' => 'The value of the variable.',
                'cardinality' => [1, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['integer'],
                'limitValues' => [],
                'default' => '',
            ],
        ],
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

        $result = $this->val('value');
        if (!$this->isDataContainer($result)) {
            $result = new Core\DataContainer($result, 'integer');
        }
        $integer = filter_var($result->getData(), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        if (is_null($integer)) {
            throw new Core\ApiException($result->getData() . ' is not integer', 0, $this->id);
        }
        $result->setData($integer);
        $result->setType('integer');
        return $result;
    }
}
