<?php

namespace BunnyCDN\API;

use BunnyCDN\Contracts\API\APIContract;

use GuzzleHttp\Client AS Guzzle;
use GuzzleHttp\Psr7;

use BunnyCDN\Exceptions\API\AbsentFileException;
use BunnyCDN\Exceptions\API\APIResponseException;
use BunnyCDN\Exceptions\API\APIUnavailableException;
use BunnyCDN\Exceptions\API\InvalidConfigurationException;
use BunnyCDN\Exceptions\API\UploadFailureException;

class Helper implements APIContract {

  private $guzzle;
  private $config;
  protected $timeout = 30.0;
  protected $base_uri = 'https://bunnycdn.com/api/';

  public function __construct () {
    $this->guzzle = new Guzzle ([
      'base_uri'          => $this->base_uri,
      'Accept'            => 'application/json',
      'Content-Type'      => 'application/json',
      'accesskey'         => env ('BUNNYCDN_ACCESS_KEY'),
      'timeout'           => $this->timeout,
      'allow_redirects'   => false,
      'debug'             => env('BUNNYCDN_DEBUG', false),
    ]);
  }

  public function error ( $e )  {

  }

  public function exists ( string $remote_path ) : bool {
    return $this->guzzle->head ('http://example.com/')->getStatusCode() < 300 ? true : false;
  }

  public function get ( string $remote_path ) {
    $response = $this->guzzle->get (urlencode($remote_path));
    if ($response->getStatusCode() < 300) {
      header ("Content-type: application/octet-stream");
      header ("Content-Disposition: attachment; filename=".pathinfo(urlencode($remote_path), PATHINFO_BASENAME));
  	  echo $response['data'];
    }
    return false;
  }

  public function put ( string $local_file, string $remote_path ) : bool {
    $response = $this->guzzle->put (null, ['body' => Psr7\stream_for ( file_get_contents ($local_file) ) );
  }

  public function delete ( string $remote_path ) : bool {
    $response = $this->guzzle->delete (urlencode($remote_path));
  }

  public function purge ( string $uri ) : bool {
    $this->guzzle->post ($this->base_uri.'/purge?url='.urlencode($uri));
  }

}
