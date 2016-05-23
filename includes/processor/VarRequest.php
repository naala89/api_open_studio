<?php

/**
 * Request variable
 */

namespace Datagator\Processor;
use Datagator\Core;

class VarRequest extends VarMixed
{
  protected $details = array(
    'name' => 'Var (Request)',
    'description' => 'A "get" or "post" variable. It fetches a variable from the get or post requests.',
    'menu' => 'Primitive',
    'application' => 'All',
    'input' => array(
      'name' => array(
        'description' => 'The name of the variable.',
        'cardinality' => array(1, 1),
        'accepts' => array('processor', 'string')
      ),
    ),
  );

  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor VarRequest', 4);
    $name = $this->val($this->meta->name);

    if (isset($this->request->vars[$name])) {
      return $this->request->vars[$name];
    }

    return null;
  }
}
