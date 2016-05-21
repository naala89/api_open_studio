<?php

/**
 * Parent class for mixed variable types
 */

namespace Datagator\Processor;
use Datagator\Core;

class VarMixed extends ProcessorBase
{
  protected $details = array(
    'name' => 'Var (Mixed)',
    'description' => 'A variable of any type.',
    'menu' => 'Primitive',
    'application' => 'All',
    'input' => array(
      'value' => array(
        'description' => 'The value of the variable.',
        'cardinality' => array(1, 1),
        'accepts' => array('processor', 'literal')
      ),
    ),
  );

  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor VarMixed', 4);

    return $this->val($this->meta->value);
  }
}