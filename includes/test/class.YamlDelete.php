<?php

class YamlDelete {
  /**
   * get articles
   *
   * {"process":{"type":"yaml","meta":{"id":1,"func":"delete","method":"get","resource":"foo","action":"bar"}}}
   */
  public function get() {
    return array(
      'process' => array(
        'type' => 'yaml',
        'meta' => array(
          'id' => 1,
          'func' => 'delete',
          'method' => 'get',
          'resource' => 'foo',
          'action' => 'bar'

        )
      )
    );
  }
}
