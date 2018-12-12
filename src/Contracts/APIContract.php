<?php

namespace BunnyCDN\Contracts\API;

interface APIContract {
  public function list();
  public function exists();
  public function get();
  public function put();
  public function purge();
  public function delete();
}
