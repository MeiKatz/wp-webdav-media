<?php
namespace WP_WebDAV;

use Sabre\DAV;
use Sabre\DAV\Auth;

class RootFolder extends DAV\Collection {
	private $folders;
	
	public function __construct() {
		$this->folders = get_terms([
			'taxonomy' => 'media_folder',
			'hide_empty' => false,
		]);
	}
	
	public function getChildren() {
		$children = array();
		
		array_push(
			$children,
			new AllFolder()
		);
		
		array_push(
			$children,
			new UncategorisedFolder()
		);
		
		array_push(
			$children,
			new AssetsFile( 'Readme.md' )
		);
		
		foreach ( $this->folders as $folder ) {
			array_push(
				$children,
				new Folder( $folder )
			);
		}
				
		return $children;
	}
	
	public function getName() {
		return '';
	}
	
	public function createDirectory( $name ) {
		if ( strpos( $name, '/' ) !== false ) {
			throw new DAV\Exception\BadRequest( 'directory name contains invalid characters' );
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
}
