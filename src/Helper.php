<?php

namespace BunnyCDN\API;

use BunnyCDN\Contracts\API\APIContract;

use GuzzleHttp\Client AS Guzzle;
use GuzzleHttp\Psr7;

use BunnyCDN\Exceptions\API\AbsentFileException;
use BunnyCDN\Exceptions\API\APIResponseException;
use BunnyCDN\Exceptions\API\APIUnavailableException;
use BunnyCDN\Exceptions\API\EmptyPathException;
use BunnyCDN\Exceptions\API\InaccessibleLocalFileException;
use BunnyCDN\Exceptions\API\InvalidConfigurationException;
use BunnyCDN\Exceptions\API\UploadFailureException;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;

/**
 * Provides a simplistic interface to the basic file functions of the BunnyCDN API.
 *
 * A crucial thing to understand about the API is it requires MULTIPLE sets of keys.
 * Firstly, you need your global API key, which is used for administrative operations on your account.
 * Then, for each storage zone you have, you need the "password" for it as the key to authorize with.
 *
 * If you had 5 storage zones, you would need 6 keys (1 global admin API key, 5 sotrage zone keys).
 * This class is designed to be used with a SINGLE storage zone, but you can override it if you're smart.
 *
 * @link https://bunnycdn.docs.apiary.io/
 */
class Helper implements APIContract {

  /** @var Client Container for the HTTP client */
  private $guzzle;

  /** @var float The amount of time allowed for an HTTP request to complete */
  protected $timeout = 30.0;

  /** @var string URL to the API for admin functions, like billing, stats etc. */
  protected $api_url = 'https://bunnycdn.com/api/';

  /** @var string URL for the storage zone-based functions, such as file uploading/deleting etc. In the form of storage.bunnycdn.com/mystoragezone */
  protected $storage_url = 'https://storage.bunnycdn.com/';

  /** @var string URL suffix for the public CDN path uploaded files can be accesed from. */
  protected $cdn_url = 'b-cdn.net';

  /**
   * Initializes the helper class with confg and Guzzle client.
   *
   * Checks if env settings have been correctly specified, then creates a basic
   * REST client for use across all functions. Makes a simple request to the API
   * to see if it's accessible (e.g. offline FS or 503 error).
   *
   * @param bool        $test_api Whether or not to make a test call to the API.
   *
   * @throws RequestException
   * @throws InvalidConfigurationException
   * @throws APIUnavailableException
   * @throws Exception
   * @return void
   */
  public function __construct ( bool $test_api = true ) {

    if ( !env ('BUNNYCDN_API_KEY') || empty ( env ('BUNNYCDN_API_KEY')) ) {
      throw new InvalidConfigurationException ("You need to set BUNNYCDN_API_KEY in your environmental variables.");
    }

    if ( !env ('BUNNYCDN_STORAGE_KEY') || empty ( env ('BUNNYCDN_STORAGE_KEY')) ) {
      throw new InvalidConfigurationException ("You need to set BUNNYCDN_STORAGE_KEY in your environmental variables.");
    }

    if ( !env ('BUNNYCDN_PULLZONE') || empty ( env ('BUNNYCDN_PULLZONE')) ) {
      throw new InvalidConfigurationException ("You need to set BUNNYCDN_PULLZONE in your environmental variables.");
    }

    $this->guzzle = new Guzzle ([
      'timeout'           => $this->timeout,
      'allow_redirects'   => false,
      'debug'             => env('BUNNYCDN_DEBUG', false),
    ]);

    if ( $test_api ) {
      try {
        $this->ping ();
      } catch ( \Exception $e ) {
        throw new APIUnavailableException ("Could not connect to BunnyCDN: ".$e->getMessage ());
      }
    }
  }

  /**
   * Places a HEAD request to the API statistics endpoint to see if the API is ava
   * is online and available.
   *
   * @see __construct()
   * @throws RequestException
   * @return bool
   */
  public function ping () : bool {
    return $this->guzzle->head ($this->api_url . 'statistics', [
      'headers' => [
        'Accept'            => 'application/json',
        'accesskey'         => env ('BUNNYCDN_API_KEY'),
      ]
    ])->getStatusCode() < 300 ? true : false;
  }

  /**
   * Queries the existence of a file in the specified storage zone.
   *
   * Performs a speedy HEAD request to the URL to look for a HTTP 200 response.
   *
   * @param string        $remote_path Path to the file, e.g. folder1/something/image.jpg
   * @throws EmptyPathException
   * @throws RequestException
   * @return bool
   */
  public function exists ( string $remote_path ) : bool {

    if ( !$remote_path || empty ($remote_path) ) {
      throw new EmptyPathException ("Remote path cannot be blank. String given: " . $remote_path);
    }

    return $this->guzzle->head ( $this->storage_url . env('BUNNYCDN_PULLZONE') . ($remote_path ?? ''), [
      'headers' => [
        'Accept'            => 'application/json',
        'accesskey'         => env ('BUNNYCDN_STORAGE_KEY'), // NB: uses storage zone key/password, NOT the global API key
      ]
    ])->getStatusCode() < 300 ? true : false;

  }

  /**
   * Retrieves a listing of files and folders in the specific storage zone, or a subpath.
   *
   * @param string        $remote_path Path on the filesystem, e.g. images/
   * @throws RequestException
   * @return mixed
   */
  public function list ( string $remote_path = '' ) {
    $response = $this->guzzle->get ( $this->storage_url . env('BUNNYCDN_PULLZONE') . ($remote_path ?? ''), [
      'headers' => [
        'Accept'            => 'application/json',
        'accesskey'         => env ('BUNNYCDN_STORAGE_KEY'), // NB: uses storage zone key/password, NOT the global API key
      ]
    ])->getBody();

    return json_decode ((string) $response);
  }

  /**
   * Retrieves the raw content of a file stored in the storage zone.
   *
   * NB: does not get the associated json or meta-info. Returns the file content.
   *
   * @param string        $remote_path Path to the file, e.g. folder1/something/image.jpg
   * @throws AbsentFileException
   * @throws APIResponseException
   * @throws EmptyPathException
   * @throws RequestException
   * @return mixed
   */
  public function get ( string $remote_path ) {

    if ( !$remote_path || empty ($remote_path) ) {
      throw new EmptyPathException ("Remote path cannot be blank. String given: " . $remote_path);
    }

    $response = $this->guzzle->get ( $this->storage_url . env('BUNNYCDN_PULLZONE') . urlencode($remote_path), [
      'headers' => [
        'accesskey'         => env ('BUNNYCDN_STORAGE_KEY'), // NB: uses storage zone key/password, NOT the global API key
      ]
    ]);

    if ( $response->getStatusCode() >= 400) {
      if ( $response->getStatusCode() == 404 ) {
        throw new AbsentFileException;
      }
      throw new APIResponseException;
    }

    return $response->getBody()->getContents();
  }

  /**
   * Uploads the contents of a file from the local filesystem to the storage zone.
   *
   * IMPORTANT: THE PARENT FOLDER MUST ALREADY EXIST. DOES NOT AUTO-CREATE THE PATH.
   *
   * @param string        $local_file Path to the local file, e.g. /var/www/uploads/image.jpg
   * @param string        $remote_path Path to the file, e.g. folder1/my-cdn/image.jpg
   * @param bool        $randomize_filename Whether or not to randomize the basename of the file to avoid conflicts.
   * @throws EmptyPathException
   * @throws InaccessibleLocalFileException
   * @throws FileUploadException
   * @throws RequestException
   * @return mixed
   */
  public function put ( string $local_file, string $remote_path, $randomize_filename = false ) {

    if ( !$remote_path || empty ($remote_path) ) {
      throw new EmptyPathException ("Remote path cannot be blank. String given: " . $remote_path);
    }

    if ( !$local_file
      || empty ($local_file)
      || !file_exists ($local_file)
      || !filesize ($local_file)
      || !is_readable ($local_file)
    ) {
      throw new InaccessibleLocalFileException ( $local_file . " does not exist, is not readable, or is not valid." );
    }

    $file_path_to_store_to = urlencode ($remote_path);

    if ( $randomize_filename ) {
      $file_path_to_store_to =  pathinfo ($remote_path, PATHINFO_DIRNAME) . md5 (pathinfo($remote_path, PATHINFO_BASENAME)) . '-'. uniqId() . '.' . pathinfo ($remote_path, PATHINFO_EXTENSION);
    }

    $response = $this->guzzle->put ($this->storage_url . env('BUNNYCDN_PULLZONE') . $file_path_to_store_to, [
        'Accept'            => 'application/json',
        'accesskey'         => env ('BUNNYCDN_STORAGE_KEY'),  // NB: uses storage zone key/password, NOT the global API key
        'body'              => Psr7\stream_for ( file_get_contents ($local_file) )
      ],
    );

    if ( $response->getStatusCode() >= 400) {
      throw new FileUploadException;
    }

    return json_decode ((string) $response);

  }

  /**
   * Deletes a file or folder from the storage zone.
   *
   * NB: File must exception of you're gonna have a bad time.
   *
   * @param string        $remote_path Path to the file, e.g. folder1/something/image.jpg
   * @throws EmptyPathException
   * @throws RequestException
   * @return mixed
   */
  public function delete ( string $remote_path ) : mixed {

    if ( !$remote_path || empty ($remote_path) ) {
      throw new EmptyPathException ("Remote path cannot be blank. String given: " . $remote_path);
    }

    $response = $this->guzzle->delete ( $this->storage_url . env('BUNNYCDN_PULLZONE') . urlencode($remote_path), [
      'headers' => [
        'Accept'            => 'application/json',
        'accesskey'         => env ('BUNNYCDN_STORAGE_KEY'),  // NB: uses storage zone key/password, NOT the global API key
      ]
    ]);

    return json_decode ((string) $response);

  }

  /**
   * Purges a specific IRL globally from any or all storage zones.
   *
   * NB: This is not contained to a storage zone. The URL built is http://mystoragezone.b-cdn.net/path/to/file.jpg
   * Do not specify a full URL.
   *
   * @param string        $remote_path Path to the file, e.g. folder1/something/image.jpg
   * @throws EmptyPathException
   * @throws RequestException
   * @return mixed
   */
  public function purge ( string $remote_path ) {

    if ( !$remote_path || empty ($remote_path) ) {
      throw new EmptyPathException ("Remote path cannot be blank. String given: " . $remote_path);
    }

    $this->guzzle->post ( $this->api_url . '/purge?url=' . 'http://'.env('BUNNYCDN_PULLZONE'). '.'. $this->cdn_url . urlencode($remote_path), [
      'Accept'            => 'application/json',
      'accesskey'         => env ('BUNNYCDN_API_KEY'),
    ]);

    return json_decode ((string) $response);
  }

}
