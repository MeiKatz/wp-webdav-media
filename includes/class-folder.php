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
  private $term;

  /**
   * @param WP_Term $term
   */
  public function __construct( WP_Term $term ) {
    $this->term = $term;
  }

  /**
   * @return int
   */
  public function getID() {
    return $this->term->term_id;
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
          'terms' => $this->term->term_taxonomy_id,
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

    return $children;
  }

  /**
   * return escaped folder name
   *
   * @return string
   */
  public function getName() {
    return $this->escapeFolderName( $this->term->name );
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

    $info = wp_upload_bits(
      $name,
      null,
      $data
    );

    if ( $info['error'] ) {
      // mach was mit dem Fehler
    }

    // ansonsten behandle die Daten. muss es noch hochgeladen werden? ETag zurückgeben?
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
