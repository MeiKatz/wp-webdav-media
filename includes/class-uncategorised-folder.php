<?php
namespace WP_WebDAV;

use Sabre;
use Sabre\DAV;
use \WP_Query;

class UncategorisedFolder extends DAV\Collection {
	public function getChildren() {
		$children = array();
		
		$query = new WP_Query([
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => -1,
			'tax_query' => [
				[
					'taxonomy' => 'media_folder',
					'operator' => 'NOT EXISTS',
				]
				
			]
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
		return '[Ohne Kategorie]';
	}
}
