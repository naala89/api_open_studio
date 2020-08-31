<?php
/**
 * Class InviteDelete.
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
use Gaterdata\Db;
use Monolog\Logger;

/**
 * Class InviteDelete
 *
 * Processor class delete an invite.
 */
class InviteDelete extends Core\ProcessorEntity
{
    /**
     * @var Db\InviteMapper
     */
    private $inviteMapper;

    /**
     * @var array Details of the processor.
     *
     * {@inheritDoc}
     */
    protected $details = [
        'name' => 'Invite delete',
        'machineName' => 'invite_delete',
        'description' => 'Delete an invite by ID.',
        'menu' => 'Admin',
        'input' => [
            'iid' => [
                'description' => 'Invite ID.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['integer'],
                'limitValues' => [],
                'default' => '',
            ],
        ],
    ];

    /**
     * InviteDelete constructor.
     *
     * @param mixed $meta Output meta.
     * @param mixed $request Request object.
     * @param \ADODB_mysqli $db DB object.
     * @param \Monolog\Logger $logger Logget object.
     */
    public function __construct($meta, &$request, \ADODB_mysqli $db, Logger $logger)
    {
        parent::__construct($meta, $request, $db, $logger);
        $this->inviteMapper = new Db\InviteMapper($db);
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

        $iid = $this->val('iid', true);

        $invite = $this->inviteMapper->findByIid($iid);

        if (empty($invite->getIid())) {
            throw new Core\ApiException('Invalid iid: ' . $iid);
        }

        $result = $this->inviteMapper->delete($invite);
        return new Core\DataContainer('Deleted user invite for ' . $invite->getEmail(), 'text');
    }
}
