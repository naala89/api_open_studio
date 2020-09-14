<?php
/**
 * Class AccountRead.
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
use Gaterdata\Db;
use Monolog\Logger;

/**
 * Class AccountRead
 *
 * Processor class to fetch an account.
 */
class AccountRead extends Core\ProcessorEntity
{
    /**
     * @var Db\AccountMapper
     */
    private $accountMapper;

    /**
     * @var Db\UserRoleMapper
     */
    private $userRoleMapper;

    /**
     * @var Db\UserMapper
     */
    private $userMapper;

    /**
     * @var array Details of the processor.
     *
     * {@inheritDoc}
     */
    protected $details = [
        'name' => 'Account read',
        'machineName' => 'account_read',
        'description' => 'Fetch a single or all accounts.',
        'menu' => 'Admin',
        'input' => [
            'token' => [
                // phpcs:ignore
                'description' => 'Request token of the user making the call. This is used to limit the accounts viewable whilst and still have access by all admin roles.',
                'cardinality' => [1, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['text'],
                'limitValues' => [],
                'default' => 0,
            ],
            'accid' => [
                'description' => 'Filter by accid. If empty then all accounts the user has access to will be returned.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['integer'],
                'limitValues' => [],
                'default' => 0,
            ],
            'keyword' => [
                // phpcs:ignore
                'description' => 'Keyword to filter by in the account name. This is only used iwhen getting "all" accounts.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['text'],
                'limitValues' => [],
                'default' => '',
            ],
            'order_by' => [
                'description' => 'Order by column. This is only used when getting "all" accounts.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['text'],
                'limitValues' => ['accid', 'name'],
                'default' => '',
            ],
            'direction' => [
                'description' => 'Order by direction. This is only used when getting "all" accounts.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['text'],
                'limitValues' => ['asc', 'desc'],
                'default' => '',
            ],
        ],
    ];

    /**
     * AccountRead constructor.
     *
     * @param mixed $meta Output meta.
     * @param mixed $request Request object.
     * @param \ADODB_mysqli $db DB object.
     * @param \Monolog\Logger $logger Logget object.
     */
    public function __construct($meta, &$request, \ADODB_mysqli $db, Logger $logger)
    {
        parent::__construct($meta, $request, $db, $logger);
        $this->accountMapper = new Db\AccountMapper($db);
        $this->userMapper = new Db\UserMapper($db);
        $this->userRoleMapper = new Db\UserRoleMapper($db);
    }

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

        $token = $this->val('token', true);
        $user = $this->userMapper->findBytoken($token);
        $accid = $this->val('accid', true);
        $keyword = $this->val('keyword', true);
        $orderBy = $this->val('order_by', true);
        $direction = $this->val('direction', true);

        $params = [];
        if (!empty($keyword)) {
            $params['filter'][] = [
                'keyword' => "%$keyword%",
                'column' => "a.name",
            ];
        }
        if (!empty($accid)) {
            $params['filter'][] = [
                'keyword' => $accid,
                'column' => 'a.accid',
            ];
        }
        if (!empty($orderBy)) {
            $params['order_by'] = $orderBy;
        }
        if (!empty($direction)) {
            $params['direction'] = $direction;
        }

        $accounts = $this->accountMapper->findAllForUser($user->getUid(), $params);

        if (empty($accounts)) {
            throw new Core\ApiException('No accounts found', 6, $this->id, 400);
        }

        $result = [];
        foreach ($accounts as $account) {
            $result[] = $account->dump();
        }

        return new Core\DataContainer($result, 'array');
    }
}
