<?php

/**
 * variables that are stored in the vars table in the session
 */

namespace Datagator\Processor;
use Datagator\Core;
use Datagator\Db;

class VarTemporary extends ProcessorBase
{
  protected $details = array(
    'name' => 'Var (Temporary)',
    'description' => 'A temporarily stored variable. This allows you to store a regularly used variable with a single value and fetch it at any time during your resource call. The value can be deleted, updated and fetched in future resource..',
    'menu' => 'Primitive',
    'application' => 'Common',
    'input' => array(
      'name' => array(
        'description' => 'The name of the variable.',
        'cardinality' => array(1, 1),
        'accepts' => array('function', 'literal')
      ),
      'value' => array(
        'description' => 'The value of the variable. This input is only used in save operations.',
        'cardinality' => array(0, 1),
        'accepts' => array('function', 'literal')
      ),
      'operation' => array(
        'description' => 'The operation to be performed on the variable.',
        'cardinality' => array(1, 1),
        'accepts' => array('function', '"save"', '"delete"', '"fetch"')
      ),
      'strict' => array(
        'description' => 'If set to 0 then return null if var does not exist. If set to 1 throw exception if var does not exist. Default is strict. Only used in fetch or delete operations.',
        'cardinality' => array(0, 1),
        'accepts' => array('function', '"0"', '"1"')
      ),
    ),
  );

  /**
   * @return bool|string
   * @throws \Datagator\Core\ApiException
   */
  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor VarTemporary', 4);

    $name = $this->val($this->meta->name);
    $strict = !empty($this->meta->strict) ? $this->val($this->meta->strict) : 1;
    $operation = $this->val($this->meta->operation);

    switch($operation) {
      case 'save':
        $_SESSION[$name] = $this->meta->value;
        return true;
        break;
      case 'delete':
        if (!isset($_SESSION[$name])) {
          if ($strict) {
            throw new Core\ApiException('could not delete variable, does not exist', 6, $this->id, 417);
          }
          return true;
        }
        unset($_SESSION[$name]);
        return true;
        break;
      case 'fetch':
        if ($strict && !isset($_SESSION[$name])) {
          throw new Core\ApiException('could not fetch variable, does not exist', 6, $this->id, 417);
        }
        return $_SESSION[$name];
        break;
      default:
        throw new Core\ApiException("invalid operation: $operation", 6, $this->id, 417);
        break;
    }
  }
}
