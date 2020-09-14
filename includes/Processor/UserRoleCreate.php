<?php
/**
 * Class UserRoleCreate.
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

/**
 * Class UserRoleCreate
 *
 * Processor class to create a user role.
 */
class UserRoleCreate extends Core\ProcessorEntity
{
    /**
     * @var array Details of the processor.
     *
     * {@inheritDoc}
     */
    protected $details = [
        'name' => 'User Role create',
        'machineName' => 'user_role_create',
        'description' => 'Create a role for a user.',
        'menu' => 'Admin',
        'input' => [
            'uid' => [
                'description' => 'The user id of the user.',
                'cardinality' => [1, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['integer'],
                'limitValues' => [],
                'default' => '',
            ],
            'accid' => [
                'description' => 'The account ID of user roles.',
                'cardinality' => [1, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => [],
                'limitValues' => [],
                'default' => '',
            ],
            'appid' => [
                'description' => 'The application ID of user roles.',
                'cardinality' => [1, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => [],
                'limitValues' => [],
                'default' => '',
            ],
            'rid' => [
                'description' => 'The user role ID of user roles.',
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

        $uid = $this->val('uid', true);
        $accid = $this->val('accid', true);
        $accid = !empty($accid) ? $accid : null;
        $appid = $this->val('appid', true);
        $appid = !empty($appid) ? $appid : null;
        $rid = $this->val('rid', true);

        if ($rid > 2 && empty($appid)) {
            $message = 'Only Administrator or Account manager roles can have NULL assigned to application';
            throw new Core\ApiException($message, 6, $this->id, 400);
        }
        if ($rid > 1 && empty($accid)) {
            throw new Core\ApiException('Only Administrator role can have NULL assigned to account', 6, $this->id, 400);
        }
        if ($rid < 3) {
            // Administrator or Account manager should not be assigned an appid.
            $appid = null;
        }
        if ($rid < 2) {
            // Administrator should not be assigned an accid.
            $accid = null;
        }

        $userRoleMapper = new Db\UserRoleMapper($this->db);

        $userRole = $userRoleMapper->findByFilter(['col' => [
            'uid' => $uid,
            'accid' => $accid,
            'appid' => $appid,
            'rid' => $rid,
        ]]);
        if (!empty($userRole)) {
            throw new Core\ApiException('User role already exists', 6, $this->id, 400);
        }

        $userRole = new Db\UserRole(null, $accid, $appid, $uid, $rid);
        return $userRoleMapper->save($userRole);
    }
}
