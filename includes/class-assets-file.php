<?php
namespace WP_WebDAV;

use Sabre;
use Sabre\DAV;

class AssetsFile extends DAV\File {
  /**
   * @var string
   */
  private $path;

  /**
   * @var string
   */
  private $displayName = null;

  /**
   * @param string $path
   * @param string $displayName (optional)
   */
  public function __construct( $path, $displayName = null ) {
    $this->path = $path;
    $this->displayName = $displayName;
  }

  /**
   * @return string
   */
  public function getName() {
    if ( $this->displayName !== null ) {
      return $this->displayName;
    }

    return basename( $this->getLocalPath() );
  }

  /**
   * @return resource
   */
  public function get() {
    return fopen(
      $this->getLocalPath(),
      'r'
    );
  }

  /**
   * @return int
   */
  public function getSize() {
    return filesize( $this->getLocalPath() );
  }

  /**
   * @return string
   */
  public function getETag() {
    return sprintf(
      '"%s"',
      md5( $this->getLocalPath() . $this->getLastModified() )
    );
  }

  /**
   * @return null
   */
  public function getContentType() {
    return null;
  }

  /**
   * @return int
   */
  public function getLastModified() {
    return filemtime( $this->getLocalPath() );
  }

  /**
   * @return string
   */
  private function getLocalPath() {
    return realpath(
      sprintf(
        '%s/wp-webdav-media/assets/%s',
        WP_PLUGIN_DIR,
        $this->path
      )
    );
  }
}
