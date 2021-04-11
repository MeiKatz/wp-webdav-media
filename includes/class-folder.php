<?php
namespace WP_WebDAV;

use Sabre;
use Sabre\DAV;
use \WP_Term;
use \WP_Query;

class Folder extends DAV\Collection {
  /**
   * @var WP_Term
   */
  private $wp_term;

  /**
   * @param WP_Term $wp_term
   */
  public function __construct( WP_Term $wp_term ) {
    $this->wp_term = $wp_term;
  }

  /**
   * @return int
   */
  public function getID() {
    return $this->wp_term->term_id;
  }

  /**
   * return an array of all contained files
   *
   * @return WP_WebDAV\File[]
   */
  public function getChildren() {
    $children = array();

    $query = new WP_Query([
      'post_type' => 'attachment',
      'post_status' => 'inherit',
      'posts_per_page' => -1,
      'tax_query' => [
        [
          'taxonomy' => 'media_folder',
          'field' => 'term_taxonomy_id',
          'terms' => $this->wp_term->term_taxonomy_id,
        ]
      ],
    ]);

    $attachments = $query->get_posts();

    foreach ( $attachments as $attachment ) {
      array_push(
        $children,
        new File( $attachment )
      );
    }

    return apply_filters(
      'wp_webdav_nodes',
      $children,
      $this
    );
  }

  /**
   * return escaped folder name
   *
   * @return string
   */
  public function getName() {
    return $this->escapeFolderName( $this->wp_term->name );
  }

  /**
   * create a new file in this folder
   *
   * @param string $name
   * @param string $data (optional)
   * @return string
   */
  public function createFile( $name, $data = null ) {
    if ( ! $this->canCreateNode() ) {
      return parent::createFile(
        $name,
        $data
      );
    }

    $file = File::create(
      $name,
      $data
    );

    $this->add(
      $file
    );

    return $file->getETag();
  }

  /**
   * @param string $name
   * @return void
   * @throws Sabre\DAV\Exception\BadRequest
   */
  public function createDirectory( $name ) {
    if ( ! $this->canCreateNode() ) {
      return parent::createDirectory(
        $name
      );
    }

    $status = wp_insert_term(
      $name,
      'media_folder'
    );

    if ( is_wp_error( $status ) ) {
      throw new DAV\Exception\BadRequest(
        'could not create folder: ' . $status->get_error_message()
      );
    }
  }

  /**
   * Adds a file to this folder.
   *
   * @param WP_WebDAV\File $file
   * @return void
   * @throws Sabre\DAV\Exception\BadRequest
   */
  public function add( File $file ) {
    $status = wp_set_post_terms(
      $file->getID(),
      $this->getID(),
      'media_folder'
    );

    if ( $status === false ) {
      throw new DAV\Exception\BadRequest(
        'could not add file to folder'
      );
    }

    if ( is_wp_error( $status ) ) {
      throw new DAV\Exception\BadRequest(
        sprintf(
          'could not add file to folder: %s',
          $status->get_error_message()
        )
      );
    }
  }

  /**
   * remove all ASCII control characters
   *
   * @see https://docs.microsoft.com/en-us/windows/win32/fileio/naming-a-file
   *
   * @param string $string
   * @return string
   */
  private function removeControlChars( $string ) {
    return preg_replace(
      '/[\x00-\x1f]/',
      '',
      $string
    );
  }

  /**
   * replace all reserved characters by an underscore character
   *
   * @see https://docs.microsoft.com/en-us/windows/win32/fileio/naming-a-file
   *
   * @param string $string
   * @return string
   */
  private function replaceReservedChars( $string ) {
    return preg_replace(
      '/[<>:"\/\\|?*]/',
      '_',
      $string
    );
  }

  /**
   * replaces groups of tabulators, line feed, carriage return and spaces
   * by a single space character
   *
   * @param string $string
   * @return string
   */
  private function replaceWhitespaceChars( $string ) {
    return preg_replace(
      '/[\t\r\n\v ]+/',
      ' ',
      $string
    );
  }

  /**
   * escape the folder name (for details see definition of called methods)
   *
   * @param string $string
   * @return string
   */
  private function escapeFolderName( $string ) {
    return trim(
      $this->replaceReservedChars(
        $this->removeControlChars(
          $this->replaceWhiteSpaceChars( $string )
        )
      )
    );
  }

  /**
   * returns true if the current user can create a new node
   *
   * @return bool
   */
  private function canCreateNode() {
    return !!apply_filters(
      'wp_webdav_can_create_node',
      false,
      $this
    );
  }
}
