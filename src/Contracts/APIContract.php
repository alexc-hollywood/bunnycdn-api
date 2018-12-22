<?php

namespace BunnyCDN\API\Contracts;

interface APIContract {
  public function list ( string $remote_path = '' );
  public function exists ( string $remote_path );
  public function size ( string $remote_path );
  public function get ( string $remote_path );
  public function put ( string $local_file, $remote_path = '', $randomize_filename = false );
  public function purge ( string $remote_path );
  public function delete ( string $remote_path );
}
