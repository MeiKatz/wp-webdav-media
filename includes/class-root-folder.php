<?php
namespace WP_WebDAV;

use Sabre\DAV;
use Sabre\DAV\Auth;

class RootFolder extends DAV\Collection {
  /**
   * @var WP_Term[]
   */
  private $folders;

  public function __construct() {
    $this->folders = get_terms([
      'taxonomy' => 'media_folder',
      'hide_empty' => false,
    ]);
  }

  /**
   * @return string
   */
  public function getID() {
    return '/';
  }

  /**
   * @return [File|Folder][]
   */
  public function getChildren() {
    $children = array();

    if ( $this->shouldShowAllFolder() ) {
      array_push(
        $children,
        new AllFolder()
      );
    }

    array_push(
      $children,
      new UnassignedFolder()
    );

    if ( $this->shouldShowReadmeFile() ) {
      array_push(
        $children,
        new AssetsFile( 'Readme.md' )
      );
    }

    foreach ( $this->folders as $folder ) {
      array_push(
        $children,
        new Folder( $folder )
      );
    }

    return apply_filters(
      'wp_webdav_nodes',
      $children,
      $this
    );
  }

  /**
   * @return string
   */
  public function getName() {
    return apply_filters(
      'wp_webdav_root_folder_name',
      ''
    );
  }

  /**
   * @param string $name
   * @return void
   */
  public function createDirectory( $name ) {
    if ( ! $this->canCreateNode() ) {
      return parent::createDirectory(
        $name
      );
    }

    if ( strpos( $name, '/' ) !== false ) {
      throw new DAV\Exception\BadRequest(
        'directory name contains invalid characters'
      );
    }

    $term = term_exists(
      $name,
      'media_folder'
    );

    if ( $term !== null && $term !== 0 ) {
      return;
    }

    $result = wp_insert_term(
      urldecode( $name ),
      'media_folder',
      [
        'parent' => 0, // currently no support for nested folders
        'slug' => Slug::fromString( $name ),
      ]
    );
  }

  /**
   * returns true if the "all folder" should be added to the root folder
   *
   * @return bool
   */
  private function shouldShowAllFolder() {
    return !!apply_filters(
      'wp_webdav_show_all_folder',
      true
    );
  }

  /**
   * returns true if the "unassigned folder" should be added to the root folder
   *
   * @return bool
   */
  private function shouldShowUnassignedFolder() {
    return !!apply_filters(
      'wp_webdav_show_unassigned_folder',
      true
    );
  }

  /**
   * returns true if the readme file should be added to the root folder
   *
   * @return bool
   */
  private function shouldShowReadmeFile() {
    return !!apply_filters(
      'wp_webdav_show_readme_file',
      true
    );
  }

  /**
   * returns true if the current user can create a new node,
   * otherwise false
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
