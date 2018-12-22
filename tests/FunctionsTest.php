<?php

namespace BunnyCDN\API\Test;

use BunnyCDN\API\APIClient;

class FunctionsTest extends TestCase
{
  private $api;

  public function setUp() {
    parent::setUp();
    $this->api = new APIClient ();
  }

  public function testShortcutFunctions () {
    $this->assertTrue (function_exists ('bunnycdn_list'));
    $result = bunnycdn_list ();
    $this->assertTrue (is_array($result), 'API result does not appear to be an array.');
    $this->assertTrue (count($result) > 0, 'API result does not have any files.');

    $this->assertTrue (function_exists ('bunnycdn_exists'));
    $result = bunnycdn_exists ('test.jpg');
    $this->assertTrue ($result);

    $this->assertTrue (function_exists ('bunnycdn_size'));
    $result = bunnycdn_size ('test.jpg');
    $this->assertTrue ($result > 0);

    $fn = 'test-'.time().'.jpg';
    copy ('./test.jpg', './'.$fn);

    $this->assertTrue (function_exists ('bunnycdn_put'));
    $result = bunnycdn_put ('./'.$fn, $fn);
    $this->assertNotNull ($result);

    unlink ('./'.$fn);

    $this->assertTrue (function_exists ('bunnycdn_delete'));
    $result = bunnycdn_delete ($fn);
    $this->assertTrue ($result);
  }

}
