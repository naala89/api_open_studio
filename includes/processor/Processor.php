<?php

/**
 * Class Processor
 *
 * Base class for all Processors.
 * This is called by Api, and will start the recursive processing of thr metadata.
 */

namespace Datagator\Processors;
use Datagator\Core;

class Processor
{
  /**
   * Processor ID.
   *
   * @var integer
   */
  protected $id = '';
  /**
   * Meta describing the resource (generated by frontend and stored in DB).
   *
   * @var integer
   */
  protected $meta;
  /**
   * All of the request details.
   *
   * @var stdClass
   */
  protected $request;
  /**
   * Required inputs to the processor.
   *
   * @var array
   */
  protected $required = array();
  /**
   * An array of details of the processor, used to configure the frontend GUI and metadata construction.
   *
   * Indexes:
   *  name: name of the processor
   *
   *  description: description of the processor
   *
   *  menu: lists the immediate menu parents
   *
   *    examples:
   *      'menu' => 'menu1' - belongs to menu1
   *      'menu' => array('menu1', 'menu2') - belongs to menu1, and menu2
   *
   *  input: list the input nodes for this processor
   *    This is an array with the following indexes:
   *    description (string): description of what the processor does
   *    cardinality (array): first value is the min of values that this input will accept, the second os the max. * indicates infinite
   *    type (array): an array of input type this processor will accept (i.e. str, int, processor, float, mixed, etc)
   *
   *    examples:
   *      input => array(
   *        'sources' => array('description' => 'desc1', 'cardinality' => array(1, '*'), type => array('processor', 'string'))
   *      )
   *          This processor has only one input, called sources.
   *          Sources must contain at least one value.
   *          The inputs can only be string or another processor.
   *
   *      input => array(
   *        'method' => array('description' => 'desc1', 'cardinality' => array(1, 1), 'accepts' => array('string' => array('get', 'post'))),
   *        'auth' => array('description' => 'desc2', 'cardinality' => array(0, 1), 'accepts' => array('processor'),
   *        'vars' => array('description' => 'desc3', 'cardinality' => array(1, '*'), type => array('processor', 'integer'))
   *      )
   *          This Processor has 3 inputs:
   *          method, which has only one sub-input, of type string, with only 2 possible values ('get' and 'post')
   *          auth, which has only one value, of type processor
   *          vars, which can contain an infinite number of values, of type processor or integer, with no limit on value.
   *
   * @var array
   */
  protected $details = array();

  /**
   * Constructor. Store processor metadata and request data in object.
   *
   * If this method is overridden by any derived classes, don't forget to call parent::__construct()
   *
   * @param $meta
   * @param $request
   */
  public function __construct($meta, $request)
  {
    Core\Debug::message('Processor base class loaded', 4);
    Core\Debug::variable($meta);

    $this->meta = $meta;
    $this->request = $request;
    if (isset($meta->id)) {
      $this->id = $meta->id;
    }
  }

  /**
   * Main processor function.
   *
   * This is where the magic happens, and should be overridden by all derived classes.
   *
   * Fetches and process the processor described in the metadata.
   * It is also the 1st stop to recursive processing of processors, so the place validate user credentials.
   *
   * @return array|Error
   */
  public function process()
  {
    Core\Debug::message('Processor');
    $processor = $this->getProcessor($this->meta);
    Core\Debug::message('hi');
    return $processor->process();
  }

  /**
   * Return details for processor, for frontend application.
   *
   * @return mixed
   */
  public function details()
  {
    return $this->details;
  }

  /**
   * Validate that the required fields are in the metadata
   *
   * @return bool
   * @throws \Datagator\Core\ApiException
   */
  protected function validateRequired()
  {
    $result = array();
    foreach ($this->required as $required) {
      if (!isset($this->meta->$required)) {
        $result[] = $required;
      }
    }
    if (empty($result)) {
      return TRUE;
    }
    throw new Core\ApiException('missing required meta: ' . implode(', ', $result), -1, $this->id, 417);
  }

  /**
   * Evaluate an object to see if it's a processor.
   *
   * @param $obj
   * @return bool
   */
  protected function isProcessor($obj)
  {
    return (is_object($obj) && (isset($obj->type) || isset($obj->meta)));
  }

  /**
   * Process a variable into a final result for the processor.
   *
   * This method can be used to process a value in it's meta to return a final result that it can use.
   * If the object is a processor, then it will process that down to a final return value,
   * or if the obj is a simple value, then it will return that. Anything else will return an error object.
   *
   * TODO: Add validation of var type result. This can be declared in $this->required
   *
   * @param $obj
   * @return array
   * @throws \Datagator\Core\ApiException
   */
  protected function getVar($obj)
  {
    $result = $obj;

    if ($this->isProcessor($obj)) {
      // this is a processor
      $processor = $this->getProcessor($obj);
      $result = $processor->process();
    } elseif (is_array($obj)) {
      // this is an array of processors or values
      $result = array();
      foreach ($obj as $o) {
        $val = $this->getVar($o);
        $result[] = $val;
      }
    } elseif (!is_string($obj) && !is_numeric($obj) && !is_bool($obj)) {
      // this is an invalid value
      throw new Core\ApiException('invalid var value', -1, $this->id, 417);
    }

    return $result;
  }

  /**
   * Fetch the processor defined in the obj (from meta), or return an error.
   *
   * @param bool|FALSE $obj
   * @return mixed
   */
  protected function getProcessor($obj = FALSE)
  {
    Core\Debug::variable($obj);
    $obj = ($obj === FALSE ? $this->meta : $obj);
    $class = 'Datagator\\Processors\\' . ucfirst(trim($obj->type));
    return new $class($obj->meta, $this->request);
  }
}
