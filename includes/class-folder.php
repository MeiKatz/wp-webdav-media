<?php
namespace WP_WebDAV;

use Sabre;
use Sabre\DAV;
use \WP_Term;
use \WP_Query;

class Folder extends DAV\Collection {
	private $term;
	
	public function __construct( WP_Term $term ) {
		$this->term = $term;
	}
	
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
	
	public function getName() {
		return $this->escapeName( $this->term->name );
	}
	
	public function createFile( $name, $data = null ) {
		return parent::createFile( $name, $data );
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
	
	# @see https://docs.microsoft.com/en-us/windows/win32/fileio/naming-a-file
	private function removeControlChars( $string ) {
		return preg_replace(
			'/[\x00-\x1f]/',
			'',
			$string
		);
	}
	
	# @see https://docs.microsoft.com/en-us/windows/win32/fileio/naming-a-file
	private function replaceReservedChars( $string ) {
		return preg_replace(
			'/[<>:"\/\\|?*]/',
			'_',
			$string
		);
	}
	
	private function escapeName( $string ) {
		return trim(
			$this->replaceReservedChars(
				$this->removeControlChars( $string )
			)
		);
	}
}
