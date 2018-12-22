<?php

namespace BunnyCDN\API\Test;

use BunnyCDN\API\APIClient;

class WriteTest extends TestCase
{
  private $api;

  public function setUp() {
    parent::setUp();
    $this->api = new APIClient ();
  }

  public function testUploadExampleFile () {
    $fn = 'test-'.time().'.jpg';

    copy ('./test.jpg', './'.$fn);

    $response = $this->api->put ('./'.$fn, $fn);

    unlink ('./'.$fn);

    $this->assertNotNull ($response, $this->api->stream);
    $this->assertTrue ($this->api->exists ($fn));
  }

  public function testDeleteUploadedFile () {
    $fn = 'test-'.time().'.jpg';

    copy ('./test.jpg', './'.$fn);

    $upload = $this->api->put ('./'.$fn, $fn);

    unlink ('./'.$fn);

    $response = $this->api->delete ($fn);

    $this->assertTrue ($response);
  }


}
