<?php
namespace WP_WebDAV;

use Sabre;
use Sabre\DAV;
use \WP_Post;

class File extends DAV\File {
  /**
   * @var WP_Post
   */
  private $post;

  /**
   * @param WP_Post $post
   */
  public function __construct( WP_Post $post ) {
    $this->post = $post;
  }

  /**
   * @return string
   */
  public function getName() {
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
   * @return string
   */
  public function getContentType() {
    return $this->post->post_mime_type;
  }

  /**
   * @return int
   */
  public function getLastModified() {
    return strtotime( $this->post->post_modified_gmt );
  }

  /**
   * @return string
   */
  private function getLocalPath() {
    return get_attached_file( $this->post->ID );
  }
}
