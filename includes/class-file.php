<?php
namespace WP_WebDAV;

use \WP_Post;
use Sabre;
use Sabre\DAV;

class File extends DAV\File {
  /**
   * @var WP_Post
   */
  private $wp_post;

  /**
   * Create a file by its name and its contents.
   *
   * @param string $name
   * @param resource|string $data
   * @return WP_WebDAV\File
   */
  public static function create(
    $name,
    $data
  ) {
    $contents = self::readContents( $data );
    $fileinfo = self::uploadContents(
      $name,
      $contents
    );

    $post = self::insertAttachment(
      $name,
      $fileinfo
    );

    self::generateAttachmentMetadata(
      $post,
      $fileinfo['file']
    );

    return new self( $post );
  }

  /**
   * @param WP_Post $wp_post
   */
  public function __construct( WP_Post $wp_post ) {
    $this->wp_post = $wp_post;
  }

  /**
   * @return int
   */
  public function getID() {
    return $this->wp_post->ID;
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
      $this->wp_post,
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
    return $this->wp_post->post_mime_type;
  }

  /**
   * @return int
   */
  public function getLastModified() {
    return strtotime( $this->wp_post->post_modified_gmt );
  }

  /**
   * @return void
   * @throws Sabre\DAV\Exception\BadRequest
   */
  public function delete() {
    if ( ! $this->canDeleteNode() ) {
      throw new DAV\Exception\Forbidden(
        'cannot delete file: user is not permitted to'
      );
    }

    $status = wp_delete_attachment(
      $this->wp_post->ID
    );

    if ( $status === null || $status === false ) {
      throw new DAV\Exception\BadRequest(
        'could not delete file'
      );
    }
  }

  /**
   * @param resource|string $data
   * @return string
   */
  private static function readContents( $data ) {
    if ( !is_resource( $data ) ) {
      return $data;
    }

    return stream_get_contents( $data );
  }

  /**
   * @param string $name
   * @param string $data
   * @return array (information about the newly-uploaded file)
   */
  private static function uploadContents( $name, $data ) {
    $fileinfo = wp_upload_bits(
      $name,
      null,
      $data
    );

    if ( $fileinfo['error'] ) {
      throw new DAV\Exception\BadRequest(
        'could not create file: ' . $fileinfo['error']
      );
    }

    unset( $fileinfo['error'] );

    return $fileinfo;
  }

  /**
   * @param string $filename
   * @param array $fileinfo
   * @return WP_Post
   */
  private static function insertAttachment(
    $filename,
    array $fileinfo
  ) {
    $wp_post_id = wp_insert_post([ # @todo use wp_insert_attachment
      #'post_author' => '@todo',
      'post_mime_type' => $fileinfo['type'],
      'post_name' => self::extractTitle( $filename ),
      'post_status' => 'inherit',
      'post_title' => self::extractTitle( $fileinfo['file'] ),
      'post_type' => 'attachment',
      'guid' => $fileinfo['url'],
    ], true);

    if ( is_wp_error( $wp_post_id ) ) {
      throw new DAV\Exception\BadRequest(
        'could not create file: ' . $wp_post_id->get_error_message()
      );
    }

    # register attachment path in database
    update_post_meta(
      $wp_post_id,
      '_wp_attached_file',
      _wp_relative_upload_path(
        $fileinfo['file']
      )
    );

    return get_post(
      $wp_post_id
    );
  }

  /**
   * Create and add meta data for file.
   * For images also add sub-sizes.
   *
   * @param WP_Post $wp_post
   * @param string $filename
   * @return void
   */
  private static function generateAttachmentMetadata(
    WP_Post $wp_post,
    $filename
  ) {
    # assure function exists
    if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
      include_once( ABSPATH . 'wp-admin/includes/image.php' );
    }

    wp_generate_attachment_metadata(
      $wp_post->ID,
      $filename
    );
  }

  /**
   * drop dir name and file extension
   *
   * @param string $path
   * @return string
   */
  private static function extractTitle( $path ) {
    $basename = basename( $path );

    # find position of file extension
    $index = strrpos(
      $basename,
      '.'
    );

    # has no file extension
    if ( $index === false ) {
      return $basename;
    }

    # drop file extension
    return substr(
      $basename,
      0,
      $index
    );
  }

  /**
   * @return string
   */
  private function getLocalPath() {
    return get_attached_file( $this->wp_post->ID );
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

  /**
   * @return bool
   */
  private function canDeleteNode() {
    return !!apply_filters(
      'wp_webdav_can_delete_node',
      false,
      $this
    );
  }
}
