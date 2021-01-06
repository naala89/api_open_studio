<?php

/**
 * Class Fragment.
 *
 * @package    ApiOpenStudio
 * @subpackage Processor
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 ApiOpenStudio
 * @license    This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 *             If a copy of the MPL was not distributed with this file,
 *             You can obtain one at https://mozilla.org/MPL/2.0/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Processor;

use ApiOpenStudio\Core;

/**
 * Class Fragment
 *
 * Processor class to define a fragment.
 * This is a like a routine that can be called multiple times in a resource.
 */
class Fragment extends Core\ProcessorEntity
{
    /**
     * {@inheritDoc}
     *
     * @var array Details of the processor.
     */
    protected $details = [
        'name' => 'Fragment',
        'machineName' => 'fragment',
        'description' => 'Insert the result of a fragment declaration.',
        'menu' => 'Logic',
        'input' => [
          'name' => [
            'description' => 'The name of the fragment',
            'cardinality' => [1, 1],
            'literalAllowed' => true,
            'limitFunctions' => [],
            'limitTypes' => [],
            'limitValues' => [],
            'default' => ''
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

        $name = $this->val('name');
        $fragments = $this->request->getFragments();
        if (empty($fragments) || empty($fragments->$name)) {
            throw new Core\ApiException("invalid fragment name: $name", $this->id);
        }

        return $fragments->$name;
    }
}
