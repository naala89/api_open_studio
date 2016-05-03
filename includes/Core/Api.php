<?php

/**
 * This class processes and routes the rest request.
 * It cleans and stores all arguments, then class the correct class,
 * then calls the process() function on that class
 */

namespace Datagator\Core;
use Datagator\Config;
use Datagator\Processor;
use Datagator\Db;
use Datagator\Security;
use Datagator\Output;
use Datagator\Resource;
use Spyc;

Debug::setup((Config::$debugInterface == 'HTML' ? Debug::HTML : Debug::LOG), Config::$debug, Config::$errorLog);

//When I tasted WCC for the first time is 1985 I knew for the first time I was in love. Never before had a drink made me feel so.
//After my Uncle Bill went to jail in 1986, West Coast Cooler was my friend and got me through a really hard time.
//And now when I taste West Coast Cooler I remember my life and all the good times.

class Api
{
  private $cache;
  private $test = false; // false or filename in /yaml/test

  /**
   * Constructor
   *
   * @param mixed $cache
   *  type of cache to use
   *  @see Cache->setup($type)
   */
  public function __construct($cache=FALSE)
  {
    $this->cache = new Cache($cache);
  }

  /**
   * Process the rest request.
   *
   * @return mixed
   * @throws \Datagator\Core\ApiException
   */
  public function process()
  {
    // disseminate the request for processing
    $get = $_GET;
    $request = $this->_getData($get);

    // get the resource for the processing
    $result = $this->_getResource($request);
    $resource = $result->r;

    // validate user for the call, if required
    $this->_getValidation($resource, $request);

    // fetch the cache of the call, if it is not stale
    $cache = $this->_getCache($resource, $request);
    if ($cache !== false) {
      return $this->_getOutput($resource, $request, $cache);
    }

    // process the call
    if (!empty($resource->fragments)) {
      // replace all fragments with their computed value
      // need to place fragment results into request object, so that they are available to all processors
      $request->fragments = $this->_processFragments($resource->fragments, $request);
    };
    if (empty($resource->process)) {
      throw new ApiException('invalid resource - process section missing', 1);
    }
    $processor = new Processor\ProcessorBase($resource->process, $request);
    $data = $processor->process();

    // store the results in cache for next time
    if (is_object($data) && get_class($data) == 'Error') {
      Debug::message('Not caching, result is error object');
    } else {
      $cacheData = array('data' => $data);
      $ttl = empty($result->ttl) ? 0 : $result->ttl;
      $this->cache->set($this->_getCacheKey($request), $cacheData, $ttl);
    }

    return $this->_getOutput($resource, $request, $data);
  }

  /**
   * Process the request and request header into a meaningful array object.
   *
   * @param $get
   * @return bool|\stdClass
   * @throws \Datagator\Core\ApiException
   * @throws \Datagator\Core\Exception
   */
  private function _getData($get)
  {
    if (empty($get['request'])) {
      return FALSE;
    }

    $request = new \stdClass();
    $request->request = $get['request'];
    $args = explode('/', trim($request->request, '/'));
    if (sizeof($args) < 2) {
      // need at least noun and verb
      throw new ApiException('invalid request', 3);
    }

    //get request method
    $request->ip = $_SERVER['REMOTE_ADDR'];
    $request->method = $this->_getMethod();
    if($request->method == 'options') {
      // this is a preflight request - respond with 200 and empty payload
      die();
    }

    $request->appId = array_shift($args);
    $request->noun = array_shift($args);
    $request->verb = array_shift($args);
    $request->identifier = $request->noun . $request->verb;
    $request->args = $args;
    $header = getallheaders();
    $request->outFormat = $this->parseType($header, 'Accept', 'json');
    $request->vars = array_diff_assoc($get, array('request' => $request->request));
    $request->vars = $request->vars + $_POST;

    Debug::variable($request, 'request', 4);

    // set up DB interface
    $dsnOptions = '';
    if (sizeof(Config::$dboptions) > 0) {
      foreach (Config::$dboptions as $k => $v) {
        $dsnOptions .= sizeof($dsnOptions) == 0 ? '?' : '&';
        $dsnOptions .= "$k=$v";
      }
    }
    $dsnOptions = sizeof(Config::$dboptions) > 0 ? '?'.implode('&', Config::$dboptions) : '';
    $dsn = Config::$dbdriver . '://' . Config::$dbuser . ':' . Config::$dbpass . '@' . Config::$dbhost . '/' . Config::$dbname . $dsnOptions;
    $request->db = \ADONewConnection($dsn);
    if (!$request->db) {
      throw new ApiException('DB connection failed',2 , -1, 500);
    }
    $request->db->debug = Config::$debugDb;

    return $request;
  }

  /**
   * Get the requested resource and TTL from the DB.
   *
   * @param $request
   * @return \stdClass
   * @throws \Datagator\Core\ApiException
   */
  private function _getResource(&$request)
  {
    $mapper = new Db\ResourceMapper($request->db);

    $result = new \stdClass();
    if (!$this->test) {
      $resource = $mapper->findByAppIdMethodIdentifier($request->appId, $request->method, $request->identifier);

      if ($resource->getId() === NULL) {
        throw new ApiException('resource or client not defined', 3, -1, 404);
      }

      $result->r = json_decode($resource->getMeta());
      $result->ttl = $resource->getTtl();
    } else {
      $filepath = $_SERVER['DOCUMENT_ROOT'] . Config::$dirYaml . 'test/' . $this->test;
      if (!file_exists($filepath)) {
        throw new ApiException("invalid test yaml: $filepath", 1 , -1, 400);
      }
      $array = Spyc::YAMLLoad($filepath);
      $result = new \stdClass();
      $result->r = new \stdClass();
      $result->r->process = $this->_arrayToObject($array['process']);
      if (!empty($array['security'])) {
        $result->r->security = $this->_arrayToObject($array['security']);
      }
      if (!empty($array['output'])) {
        $result->r->output = $this->_arrayToObject($array['output']);
      }
      if (!empty($array['fragments'])) {
        $result->r->fragments = $this->_arrayToObject($array['fragments']);
      }
      $result->ttl = $array['ttl'];
      $request->method = $array['method'];
      $request->identifier = strtolower($array['uri']['noun']) . strtolower($array['uri']['verb']);
    }

    Debug::variable($result, 'resource', 3);

    return $result;
  }

  /**
   * Perform api request auth if defined in the meta.
   *
   * @param $resource
   * @param $request
   * @return bool
   * @throws \Datagator\Core\ApiException
   */
  private function _getValidation($resource, $request)
  {
    if (empty($resource->security)) {
      return;
    }
    $class = 'Datagator\\Security\\' . ucfirst($this->_cleanData($resource->security->processor));
    $security = new $class($resource->security->meta, $request);
    if (!$security->process()) {
      throw new ApiException('unauthorized', 4, $resource->security->meta->id, 401);
    }
    return;
  }

  /**
   * Check cache for any results.
   *
   * @param $resource
   * @param $request
   * @return bool
   */
  private function _getCache($resource, $request)
  {
    if (!$this->cache->cacheActive()) {
      Debug::message('not searching for cache - inactive', 3);
      return FALSE;
    }

    $cacheKey = $this->_getCacheKey($request);
    Debug::variable($cacheKey, 'cache key', 4);
    $data = $this->cache->get($cacheKey);

    if (!empty($data)) {
      Debug::variable($data, 'from cache', 4);
      return $this->_getOutput($resource, $request, $data);
    }

    Debug::message('no cache entry found', 3);
    return FALSE;
  }

  /**
   * Get the cache key for a request.
   *
   * @param $request
   * @return array|string
   */
  private function _getCacheKey($request)
  {
    return $this->_cleanData($request->method . '_' . $request->request);
  }

  /**
   * Porcess the fragments meta and
   *
   * @param $fragments
   * @param $request
   * @return \stdClass
   * @throws \Datagator\Core\ApiException
   */
  private function _processFragments($fragments, $request) {
    $result = new \stdClass();
    foreach ($fragments as $fragment) {
      if (empty($fragment->fragment) || !isset($fragment->meta)) {
        throw new ApiException('bad fragment definition');
      }
      if (is_string($fragment->meta)) {
        // fragment is a constant
        $result->{$fragment->fragment} = $fragment->meta;
      } elseif (!empty($fragment->meta->processor) && !empty($fragment->meta->meta)) {
        // fragment is a processor
        $class = '\\Datagator\\Processor\\' . ucfirst(trim($fragment->meta->processor));
        if (!class_exists($class)) {
          $class = '\\Datagator\\Endpoint\\' . ucfirst(trim($fragment->meta->processor));
          if (!class_exists($class)) {
            $class = '\\Datagator\\Output\\' . ucfirst(trim($fragment->meta->processor));
            if (!class_exists($class)) {
              $class = '\\Datagator\\Security\\' . ucfirst(trim($fragment->meta->processor));
              if (!class_exists($class)) {
                throw new ApiException('unknown processor in fragment: ' . ucfirst(trim($fragment->meta->processor)), 1);
              }
            }
          }
        }
        $processor = new $class($fragment->meta->meta, $request);
        $result->{$fragment->fragment} = $processor->process();
      } else {
        throw new ApiException('invalid fragment meta',1);
      }
    }
    return $result;
  }

  /**
   * Get the formatted output r
   * @param $resource
   * @param $request
   * @param $data
   * @return string
   * @throws \Datagator\Core\ApiException
   */
  private function _getOutput($resource, $request, $data)
  {
    $result = 'yamlImport';//'true';

    // default to response output if no output defined
    if (empty($resource->output)) {
      Debug::message('no output section defined - returning the result in the response');
      // translate the output to the correct format as requested in header and return in the response
      $outFormat = ucfirst($this->_cleanData($request->outFormat));
      $outFormat = $outFormat == '**' ? 'Json' : $outFormat;
      $class = 'Datagator\\Output\\' . $outFormat;
      if (!class_exists($class)) {
        throw new ApiException('output processor undefined: ' . $outFormat, 1);
      }
      $obj = new $class($data, 200);
      $result = $obj->process();
      $obj->setStatus();
      $obj->setHeader();
    } else {
      foreach ($resource->output as $index => $output) {
        if (is_string($output) && $output == 'response') {
          // translate the output to the correct format as requested in header and return in the response
          $outFormat = ucfirst($this->_cleanData($request->outFormat));
          $outFormat = $outFormat == '**' ? 'Json' : $outFormat;
          $class = 'Datagator\\Output\\' . $outFormat;
          if (!class_exists($class)) {
            throw new ApiException('output processor undefined: ' . $outFormat, 1);
          }
          $obj = new $class($data, 200);
          $result = $obj->process();
          $obj->setStatus();
          $obj->setHeader();
        } else {
          // treat as a multiple output and let the class take care of the output.
          foreach ($output as $type=> $meta) {
            $class = 'Datagator\\Output\\' . $type;
            if (!class_exists($class)) {
              throw new ApiException('output processor undefined: ' . $type, 1);
            }
            $obj = new $class($data, 200, $meta);
            $obj->process();
          }
        }
      }
    }

    return $result;
  }

  /**
   * Utility function to get the REST method from the $_SERVER var.
   *
   * @return string
   * @throws \Datagator\Core\ApiException
   */
  private function _getMethod()
  {
    $method = strtolower($_SERVER['REQUEST_METHOD']);
    if ($method == 'post' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
      if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
        $method = 'delete';
      } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
        $method = 'put';
      } else {
        throw new ApiException("unexpected header", 3, -1, 406);
      }
    }
    return $method;
  }

  /**
   * Calculate a format from string of header Content-Type or Accept.
   *
   * @param $header
   * @param $key
   * @param bool|FALSE $default
   * @return bool|string
   */
  public function parseType($header, $key, $default=null)
  {
    $key = strtolower($key);
    $result = $default;
    if (!empty($header[strtolower($key)])) {
      $parts = preg_split('/\,|\;/', $header[$key]);
      foreach ($parts as $part) {
        $result = preg_replace("/(application||text)\//i",'',$part);
        $result = trim($result);
        $class = "\\Datagator\\Output\\" . $result;
        if (class_exists($class)) {
          return $result;
        }
      }
    }
    return ($result == '*' || $result == '**') ? $default : $result;
  }

  /**
   * Utility recursive function to clean vars for processing.
   *
   * @param $data
   * @return array|string
   */
  private function _cleanData($data)
  {
    $cleaned = Array();
    if (is_array($data)) {
      foreach ($data as $k => $v) {
        $cleaned[$k] = $this->_cleanData($v);
      }
    } else {
      $cleaned = trim(strip_tags($data));
    }
    return $cleaned;
  }

  private function _arrayToObject($array)
  {
    $json = json_encode($array);
    $object = json_decode($json);
    return $object;
  }
}
