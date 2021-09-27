<?php
/**
 * Class VarPost.
 *
 * @package    ApiOpenStudio
 * @subpackage Processor
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 Naala Pty Ltd
 * @license    This Source Code Form is subject to the terms of the ApiOpenStudio Public License.
 *             If a copy of the license was not distributed with this file,
 *             You can obtain one at https://www.apiopenstudio.com/license/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Processor;

use ApiOpenStudio\Core;

/**
 * Class VarPost
 *
 * Processor class to return the post variables in a request.
 */
class VarPost extends Core\ProcessorEntity
{
    /**
     * {@inheritDoc}
     *
     * @var array Details of the processor.
     */
    protected array $details = [
        'name' => 'Var (Post)',
        'machineName' => 'var_post',
        'description' => 'A "post" variable. It fetches a variable from the post request.',
        'menu' => 'Primitive',
        'input' => [
            'key' => [
                'description' => 'The key or name of the POST variable.',
                'cardinality' => [1, 1],
                'literalAllowed' => true,
                'limitProcessors' => [],
                'limitTypes' => ['text'],
                'limitValues' => [],
                'default' => '',
            ],
            'nullable' => [
                'description' => 'Allow the processing to continue if the POST variable does not exist.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitProcessors' => [],
                'limitTypes' => ['boolean', 'integer'],
                'limitValues' => [],
                'default' => true,
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
    public function process(): Core\DataContainer
    {
        parent::process();

        $key = $this->val('key', true);
        $nullable = $this->val('nullable', true);
        $vars = $this->request->getPostVars();

        if (isset($vars[$key])) {
            return new Core\DataContainer($vars[$key]);
        } elseif ($nullable) {
            return new Core\DataContainer('', 'text');
        }

        throw new Core\ApiException("post variable ($key) not received", 6, $this->id, 400);
    }
}
