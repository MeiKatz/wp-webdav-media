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
   * @param string $name
   */
  public function setName( $name ) {
    if ( ! $this->canRenameNode() ) {
      return parent::setName( $name );
    }

    $dirname = dirname(
      $this->getLocalPath()
    );

    $name = basename( $name );

    $new_path = sprintf(
      '%s/%s',
      $dirname,
      $name
    );

    if ( file_exists( $new_path ) ) {
      throw new DAV\Exception\BadRequest(
        'cannot rename file: duplicate file name'
      );
    }

    if ( ! rename( $this->getLocalPath(), $new_path ) ) {
      throw new DAV\Exception\BadRequest(
        'could not rename file'
      );
    }

    $success = update_attached_file(
      $this->post,
      $new_path
    );

    if ( ! $success ) {
      throw new DAV\Exception\BadRequest(
        'could not rename file'
      );
    }
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

  /**
   * @return bool
   */
  private function canRenameNode() {
    return !!apply_filters(
      'wp_webdav_can_rename_node',
      false,
      $this
    );
  }
}
