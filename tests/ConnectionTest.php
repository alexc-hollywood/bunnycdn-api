<?php

namespace BunnyCDN\API\Test;

use BunnyCDN\API\APIClient;

class ConnectionTest extends TestCase
{
  public function setUp() {
    parent::setUp();
  }

  public function testConstructor () {
      $api = new APIClient (false);
      $this->assertTrue ($api->ping());
  }

}
