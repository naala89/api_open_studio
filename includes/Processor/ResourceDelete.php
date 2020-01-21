<?php

/**
 * Delete a resource.
 */

namespace Gaterdata\Processor;

use Gaterdata\Core\Config;
use Gaterdata\Core;
use Gaterdata\Db\AccountMapper;
use Gaterdata\Db\ApplicationMapper;
use Gaterdata\Db\ResourceMapper;

class ResourceDelete extends Core\ProcessorEntity
{
    /**
     * @var Config
     */
    private $settings;

    /**
     * @var ResourceMapper
     */
    private $resourceMapper;

    /**
     * @var AccountMapper
     */
    private $accountMapper;

    /**
     * @var ApplicationMapper
     */
    private $applicationMapper;

    /**
     * {@inheritDoc}
     */
    protected $details = [
        'name' => 'Resource delete',
        'machineName' => 'resource_delete',
        'description' => 'Delete a resource.',
        'menu' => 'Admin',
        'input' => [
            'resid' => [
                'description' => 'The resource ID.',
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
     */
    public function __construct($meta, &$request, $db)
    {
        parent::__construct($meta, $request, $db);
        $this->applicationMapper = new ApplicationMapper($db);
        $this->accountMapper = new AccountMapper($db);
        $this->resourceMapper = new ResourceMapper($db);
        $this->settings = new Config();
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        Core\Debug::variable($this->meta, 'Processor ' . $this->details()['machineName'], 2);

        $resid = $this->val('resid', true);

        $resource = $this->resourceMapper->findId($resid);
        if (empty($resource->getResid())) {
            throw new Core\ApiException("Invalid resource: $resid", 6, $this->id, 400);
        }
        $appid = $resource->getAppid();

        $application = $this->applicationMapper->findByAppid($appid);
        $account = $this->accountMapper->findByAccid($application->getAccid());
        if (
            $account->getName() == $this->settings->__get(['api', 'core_account'])
            && $application->getName() == $this->settings->__get(['api', 'core_application'])
            && $this->settings->__get(['api', 'core_resource_lock'])
        ) {
            throw new Core\ApiException("Unauthorised: this is a core resource", 6, $this->id, 400);
        }

        return new Core\DataContainer($this->resourceMapper->delete($resource) ? 'true' : 'false');
    }
}
