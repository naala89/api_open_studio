<?php

/**
 * Import resources in Swagger YAML format.
 */

namespace Datagator\Processor;
use Datagator\Core;
use Datagator\Db\ResourceMapper;

class ResourceSwagger extends ResourceBase
{
  protected $details = array(
    'name' => 'Import Swagger',
    'description' => 'Create a custom API resource using a Swagger YAML document.',
    'menu' => 'Resource',
    'application' => 'All',
    'input' => array(
      'resource' => array(
        'description' => 'The resource string or file. This can be an attached file or a urlencoded GET var.',
        'cardinality' => array(1, 1),
        'accepts' => array('string', 'file')
      )
    )
  );

  private $paramCount;

  public function process()
  {
    $this->paramCount = 0;
    $resources = array();
    $swagger = $this->_importData();

    if (empty($swagger['paths'])) {
      throw new Core\ApiException('Missing paths element in swagger YAML', 1);
    }

    foreach ($swagger['paths'] as $path => $methods) {
      $pathParts = explode('/', trim($path, '/'));

      if (sizeof($pathParts) < 2) {
        throw new Core\ApiException('invalid path (must be at least noun/verb): ' . $path, 1);
      }

      $uriParams = array();
      if (sizeof($pathParts) > 2) {
        $uriParams = $this->_extractUriParams(array_slice($pathParts, 2));
      }
      $noun = $pathParts[0];
      $verb = $pathParts[1];

      foreach ($methods as $method => $definition) {
        $requestVars = $this->_extractParameters($definition['parameters'], $method);

        $resource = array();
        $resource['name'] = !empty($definition['operationId']) ? $definition['operationId'] : 'noName';
        $resource['description'] = !empty($definition['description']) ? $definition['description'] : 'noDescription';
        $resource['uri']['noun'] = $noun;
        $resource['uri']['verb'] = $verb;
        $resource['method'] = $method;
        $resource['process'] = '';
        $resource['parameters'] = array();
        $resource['parameters'] = array_merge($resource['parameters'], $uriParams);
        $resource['parameters'] = array_merge($resource['parameters'], $requestVars);

        $resources[] = array(
          'uri' => $resource['uri'],
          'method' =>$method,
          'appId' => $this->request->appId
        );

        //$this->save($resource);
      }
    }

    return $resources;
  }

  /**
   * Create or update a resource from YAML.
   * The Yaml is either post string 'yaml', or file 'yaml'.
   * File takes precedence over the string if both present.
   *
   * @param null $data
   * @return bool
   * @throws \Datagator\Core\ApiException
   */
  protected function save($data=NULL)
  {
    $this->_validateData($data);

    $name = $data['name'];
    $description = $data['description'];
    $method = $data['method'];
    $identifier = strtolower($data['uri']['noun']) . strtolower($data['uri']['verb']);
    $meta = array();
    $meta['security'] = $data['security'];
    $meta['process'] =  $data['process'];
    $ttl = !empty($data['ttl']) ? $data['ttl'] : 0;

    $mapper = new ResourceMapper($this->request->db);
    $resource = $mapper->findByAppIdMethodIdentifier($this->request->appId, $method, $identifier);
    if (empty($resource->getId())) {
      $resource->setAppId($this->request->appId);
      $resource->setMethod($method);
      $resource->setIdentifier($identifier);
    }
    $resource->setName($name);
    $resource->setDescription($description);
    $resource->setMeta(json_encode($meta));
    $resource->setTtl($ttl);
    return $mapper->save($resource);
  }

  /**
   * @return array|string
   * @throws \Datagator\Core\ApiException
   */
  protected function _importData($data)
  {
    return \Spyc::YAMLLoadString($data);
  }

  protected function _exportData($data)
  {

  }

  /**
   * @param $uriParams
   * @return array
   * @throws \Datagator\Core\ApiException
   */
  protected function _extractUriParams($uriParams)
  {
    $result = array();
    foreach ($uriParams as $key => $val) {
      if (!preg_match("/^\{[a-z0-9_-]*\}$/i", $val)) {
        throw new Core\ApiException("invalid URI element: $val", 1);
      }
      $result[] = array(
        'processor' => 'varUri',
        'meta' => array(
          'id' => $this->paramCount++,
          'index' => $key)
      );
    }
    return $result;
  }

  /**
   * @param $parameters
   * @param $method
   * @return array
   * @throws \Datagator\Core\ApiException
   */
  protected function _extractParameters($parameters, $method)
  {
    $result = array();
    foreach ($parameters as $parameter) {
      $p = array();
      $parameterCount = 1;
      switch ($parameter['in']) {
        case 'query':
          $p['processor'] = $method == 'get' ? 'varGet' : 'varPost';
          $p['meta']['id'] = $this->paramCount++;
          $p['meta']['name'] = $parameter['name'];
          break;
        case 'body':
          $p['processor'] = 'varBody';
          $p['meta']['id'] = $this->paramCount++;
          break;
      }
      // strongly typed
      if (!empty($parameter['items']['type'])) {
        Core\Debug::variable($parameter['items']['type'], 'strongly typed');
        $p = array(
          'meta' => array(
            'id' => $this->paramCount++,
            'value' => $p
          )
        );
        switch ($parameter['items']['type']) {
          case 'boolean':
            $p['processor'] = 'varBool';
            break;
          case 'float':
            $p['processor'] = 'varFloat';
            break;
          case 'integer':
            $p['processor'] = 'varInt';
            break;
          case 'number':
            $p['processor'] = 'varNum';
            break;
          case 'string':
            $p['processor'] = 'varStr';
            break;
          default:
            throw new Core\ApiException('unknown type: ' . $parameter['items']['type'], 1);
            break;
        }
      }
      $result[] = $p;
    }
    return $result;
  }
}
