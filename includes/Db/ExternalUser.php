<?php
/**
 * Class ExternalUser.
 *
 * @package Gaterdata
 * @subpackage Db
 * @author john89 (https://gitlab.com/john89)
 * @copyright 2020-2030 GaterData
 * @license This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 *      If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 * @link https://gaterdata.com
 */

namespace Gaterdata\Db;

/**
 * Class ExternalUser.
 *
 * DB class for for storing external_user row data.
 */
class ExternalUser
{
    /**
     * @var integer External entity ID.
     */
    protected $id;

    /**
     * @var integer Application ID.
     */
    protected $appId;

    /**
     * @var mixed External ID.
     */
    protected $externalId;

    /**
     * @var mixed External entity.
     */
    protected $externalEntity;

    /**
     * @var mixed Data field 1
     */
    protected $dataField1;

    /**
     * @var mixed Data field 2
     */
    protected $dataField2;

    /**
     * @var mixed Data field 3
     */
    protected $dataField3;

    /**
     * ExternalUser constructor.
     *
     * @param integer $id External entity ID.
     * @param integer $appId Application ID.
     * @param integer $externalId External ID.
     * @param string $externalEntity External entity name.
     * @param string $dataField1 Spare data field 1.
     * @param string $dataField2 Spare data field 2.
     * @param string $dataField3 Spare data field 3.
     */
    public function __construct(
        int $id = null,
        int $appId = null,
        int $externalId = null,
        string $externalEntity = null,
        string $dataField1 = null,
        string $dataField2 = null,
        string $dataField3 = null
    ) {
        $this->id = $id;
        $this->appId = $appId;
        $this->externalId = $externalId;
        $this->externalEntity = $externalEntity;
        $this->dataField1 = $dataField1;
        $this->dataField2 = $dataField2;
        $this->dataField3 = $dataField3;
    }

    /**
     * Get External user ID.
     *
     * @return integer External user ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set External user ID.
     *
     * @param integer $id External user ID.
     *
     * @return void
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * Get Application ID.
     *
     * @return integer Application ID.
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Set application ID.
     *
     * @param integer $appId Application ID.
     *
     * @return void
     */
    public function setAppId(int $appId)
    {
        $this->appId = $appId;
    }

    /**
     * Get External ID.
     *
     * @return mixed External ID.
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set External ID.
     *
     * @param mixed $externalId External ID.
     *
     * @return void
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
    }

    /**
     * Get External entity.
     *
     * @return mixed External entity.
     */
    public function getExternalEntity()
    {
        return $this->externalEntity;
    }

    /**
     * Set External entity.
     *
     * @param string $externalEntity External entity.
     *
     * @return void
     */
    public function setExternalEntity(string $externalEntity)
    {
        $this->externalEntity = $externalEntity;
    }

    /**
     * Get data field 1.
     *
     * @return mixed Data field 1.
     */
    public function getDataField1()
    {
        return $this->dataField1;
    }

    /**
     * Set data field 1.
     *
     * @param mixed $dataField1 Data field 1.
     *
     * @return void
     */
    public function setDataField1($dataField1)
    {
        $this->dataField1 = $dataField1;
    }

    /**
     * Get data field 2.
     *
     * @return mixed Data field 2.
     */
    public function getDataField2()
    {
        return $this->dataField2;
    }

    /**
     * Set data field 2.
     *
     * @param mixed $dataField2 Data field 2.
     *
     * @return void
     */
    public function setDataField2($dataField2)
    {
        $this->dataField2 = $dataField2;
    }

    /**
     * Get data field 3.
     *
     * @return mixed Data field 3.
     */
    public function getDataField3()
    {
        return $this->dataField3;
    }

    /**
     * Set data field 3.
     *
     * @param mixed $dataField3 Data field 3.
     *
     * @return void
     */
    public function setDataField3($dataField3)
    {
        $this->dataField3 = $dataField3;
    }

    /**
     * Return the values as an associative array.
     *
     * @return array External user.
     */
    public function dump()
    {
        return [
            'uid' => $this->uid,
            'appId' => $this->appId,
            'externalId' => $this->externalId,
            'externalEntity' => $this->externalEntity,
            'dataField1' => $this->dataField1,
            'dataField2' => $this->dataField2,
            'dataField3' => $this->dataField3,
        ];
    }
}
