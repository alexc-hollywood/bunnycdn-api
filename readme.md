# BunnyCDN API: Lite Edition

This package provides a very basic Flysystem-like interface to the BunnyCDN API(s). They're not particularly well documented or easy to use as-is.

Documentation:
- https://bunnycdn.docs.apiary.io/
- https://bunnycdnstorage.docs.apiary.io

**Important:**

The API works in a quirky way with multiple account keys. Firstly, you will need your *global* API account key to perform administrative functions (e.g. billing, statistics, purging, managing zones etc). This is in your account dashboard.

Secondly, you will need your password (s) for *each storage zone*. This is also the *FTP password* found in the settings for the individual storage zone itself, and functions as an API key.

As such, the "authentication key" you need to provide to the API varies according to what you want to do. For global API methods, you need the main API key. For file operations, the key is the "storage* password.

If you have 4 storage zones, you will need **5** keys.

*This package is designed for use with a single storage zone.*

# Prerequisites
The package assumes you are using the `dotenv` library for storing sensitive passwords. Keys and other info that should be withheld from public view are assumed to be in a single `.env` file.

Example .env configuration
```
BUNNYCDN_PULLZONE=SUBDOMAINFORPULLZONE
BUNNYCDN_API_KEY=GLOBALAPIKEYFROMYOURACCOUNTDASHBOARD
BUNNYCDN_STORAGE_KEY=FTPPASSWORDFORYOURSTORAGEZONE
BUNNYCDN_DEBUG=true
```
The API client does **not auto-create directories**. The folder you are uploading to must already exist.

Only a **limited set of features** are implemented here. For example, you cannot create pullzones or purge the whole cache. That's simply because a further edit should see it produced as a standardised **Flysystem** driver.

# Usage

The package is primarily about manipulating files, in Flysystem-fashion.

## List contents of a directory

By instantiating the class directly:
```php
$client = new BunnyCDN\API\APIClient();

// storage.bunnycdn.com/storage-zone/images/thumbnails
$json_object = $client->list ('images/thumbnails');
```
Using the helper function:
```php
bunnycdn_list ('images/thumbnails');
```
## Test existence of a file

By instantiating the class directly:
```php
$client = new BunnyCDN\API\APIClient();

// storage.bunnycdn.com/storage-zone/video.mp4
if ($client->exists ('video.mp4') ) {
   // do something
}
```
Using the helper function:
```php
echo bunnycdn_exists ('video.mp4');
```

## Retrieve a file

By instantiating the class directly:
```php
$client = new BunnyCDN\API\APIClient();

// storage.bunnycdn.com/storage-zone/docs/test.xls
file_put_contents ('download.xls', $client->get('docs/test.xls');
```
Using the helper function:
```php
Storage::local()->save ( bunnycdn_get ('docs/test.xls') );
```
## Upload a file

By instantiating the class directly:
```php
$client = new BunnyCDN\API\APIClient();

// resolves to storage.bunnycdn.com/storage-zone/images/upload.jpg or storage-zone.bunnycdn.com/images.upload.jpg
$client->put ('/var/www/uploads/something.jpg', 'images/upload.jpg');

if ( $client->exists ('images/upload.jpg') ) {
    // rock n' roll
}
```
Using the helper function:
```php
bunnycdn_put ('storage/files/video.mp4', 'videos/uploads/1234.mp4')
```
## Purge a cached file

By instantiating the class directly:
```php
$client = new BunnyCDN\API\APIClient();

// bunnycdn.com/api/purge?url=http://storage-zone.b-cdn.net/images/old.jpg
$client->purge ('images/old.jpg');
```
Using the helper function:
```php
bunnycdn_purge ('fonts/Roboto.ttf');
```

## Delete a file

By instantiating the class directly:
```php
$client = new BunnyCDN\API\APIClient();

if ( $client->delete('test_video.mpg') {
    // celebrate
}
```
Using the helper function:
```php
bunnycdn_delete ('path/to/stored_file.mp4');
```
