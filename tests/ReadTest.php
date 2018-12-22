<?php

namespace BunnyCDN\API\Test;

use BunnyCDN\API\APIClient;

class ReadTest extends TestCase
{
  private $api;

  public function setUp() {
    parent::setUp();
    $this->api = new APIClient ();
  }

  public function testListRootDirectoryFiles () {
    $data = $this->api->list ();

    $this->assertNotNull ($data, 'API result appears to be NULL.');
    $this->assertFalse (!is_array($data), 'API result does not appear to be an array.');
    $this->assertTrue (count($data) > 0, 'API result does not have any files.');
  }

  public function testExampleFileExists () {
    $this->assertTrue ($this->api->exists ('test.jpg'), "Can't find test.jpg file in root directory.");
    $this->assertTrue ($this->api->size ('test.jpg') > 0);
  }

  public function testGetExampleFileContents () {
    $this->assertNotNull ($this->api->get ('test.jpg'), "Can't get test.jpg file contents.");
  }

}
