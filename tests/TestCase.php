<?php

namespace BunnyCDN\API\Test;

use \PHPUnit\Framework\TestCase AS BaseTestCase;
use \Dotenv\Dotenv;

class TestCase extends BaseTestCase
{
  public function setUp() {
    $this->assertTrue (file_exists('./src/.env'), "Can't find .env file.");
    if (file_exists('./src/.env')) {
        (new Dotenv('./src/'))->load();
    }
  }
}
