<?php

/**
 * Import, export and delete resources in JSON format.
 */

namespace Datagator\Processor;
use Datagator\Core;

class ResourceJson extends ResourceBase
{

  /**
   * Constructor. Store processor metadata and request data in object.
   *
   * @param $meta
   * @param $request
   */
  public function __construct($meta, $request)
  {
    $this->request['name'] = 'Resource (JSON)';
    $this->request['description'] = 'Create edit or fetch a custom API resource for the application in JSON form.';
    parent::__construct($meta, $request);
  }

  /**
   * @param $data
   * @return mixed
   * @throws \Datagator\Core\ApiException
   */
  protected function _importData($data)
  {
    $json = json_decode(json_encode($data), true);
    if (empty($json)) {
      throw new Core\ApiException('Invalid or no JSON supplied', 6, $this->id, 417);
    }
    return $json;
  }

  /**
   * @param array $data
   * @return string
   */
  protected function _exportData($data)
  {
    return json_encode($data);
  }
}
