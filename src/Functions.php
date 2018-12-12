<?php

use BunnyCDN\API\Helper AS BunnyCDN;

if ( !function_exists ('bunnycdn_list') ) {
  function bunnycdn_list ( string $remote_path ) {
    (new BunnyCDN)->list ( $remote_path );
  }
}

if ( !function_exists ('bunnycdn_exists') ) {
  function bunnycdn_exists ( string $remote_path ) {
    return (new BunnyCDN)->exists ( $remote_path );
  }
}

if ( !function_exists ('bunnycdn_get') ) {
  function bunnycdn_get ( string $remote_path ) {
    return (new BunnyCDN)->get ( $remote_path );
  }
}

if ( !function_exists ('bunnycdn_put') ) {
  function bunnycdn_put ( string $local_file, string $remote_path ) {
    return (new BunnyCDN)->put ( $local_file, $remote_path );
  }
}

if ( !function_exists ('bunnycdn_purge') ) {
  function bunnycdn_purge ( string $uri ) {
    return (new BunnyCDN)->purge ( $uri );
  }
}

if ( !function_exists ('bunnycdn_delete') ) {
  function bunnycdn_delete (string $remote_path) {
    function bunnycdn_delete ( string $remote_path) {
      return (new BunnyCDN)->delete ( $remote_path );
    }
  }
}
