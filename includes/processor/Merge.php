<?php

/**
 * Perform merge of multiple sources.
 */

namespace Datagator\Processor;
use Datagator\Core;

class Merge extends ProcessorBase
{
  private $_defaultType = 'union';
  protected $details = array(
    'name' => 'Merge',
    'description' => 'Merge multiple data-sets.',
    'menu' => 'Operation',
    'application' => 'All',
    'input' => array(
      'sources' => array(
        'description' => 'The data-sets to merge on.',
        'cardinality' => array(2, '*'),
        'accepts' => array('processor', 'array', 'literal')
      ),
      'mergeType' => array(
        'description' => 'The merge operation to perform. The default is union.',
        'cardinality' => array(1, 1),
        'accepts' => array('processor', '"union"', '"intersect"', '"difference"')
      ),
      'unique' => array(
        'description' => 'Filter out duplicate values. The default is false.',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', '"true"', '"false"')
      ),
    ),
  );

  public function process()
  {
    Core\Debug::variable($this->meta, 'processor Merge', 4);

    $sources = $this->val($this->meta->sources);
    $unique = !empty($this->meta->unique) ? $this->val($this->meta->unique) == 'true' : false;
    $mergeType = !empty($this->meta->mergeType) ? $this->val($this->meta->mergeType) : $this->_defaultType;
    $method = '_' . strtolower(trim($mergeType));

    if (!method_exists($this, $method)) {
      throw new Core\ApiException("invalid mergeType: $mergeType", 6, $this->id, 407);
    }

    if ($unique) {
      return array_unique($this->$method($sources));
    }
    return $this->$method($sources);
  }

  /**
   * @param $values
   * @return array|mixed
   */
  private function _union($values)
  {
    $result = array_shift($values);
    $result = is_array($result) ? $result : array($result);
    foreach ($values as $value) {
      $value = is_array($value) ? $value : array($value);
      $result = array_merge($result, $value);
    }
    return $result;
  }

  /**
   * @param $values
   * @return array|mixed
   */
  private function _intersect($values)
  {
    $result = array_shift($values);
    $result = is_array($result) ? $result : array($result);
    foreach ($values as $value) {
      $value = is_array($value) ? $value : array($value);
      $result = array_intersect($result, $value);
    }
    return $result;
  }

  private function _difference($values)
  {
    $result = array_shift($values);
    $result = is_array($result) ? $result : array($result);
    foreach ($values as $value) {
      $value = is_array($value) ? $value : array($value);
      $result = array_merge(array_diff($result, $value), array_diff($value, $result));
    }
    return $result;
  }
}
