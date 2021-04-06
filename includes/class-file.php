<?php
namespace WP_WebDAV;

use Sabre;
use Sabre\DAV;
use \WP_Post;

class File extends DAV\File {
	private $post;
	
	public function __construct( WP_Post $post ) {
		$this->post = $post;
	}
	
	public function getName() {
		return basename( $this->getLocalPath() );
	}
	
	public function getLocalPath() {
		return get_attached_file( $this->post->ID );
	}
	
	public function get() {
		return fopen(
			$this->getLocalPath(),
			'r'
		);
	}
	
	public function getSize() {
		return filesize( $this->getLocalPath() );
	}
	
	public function getETag() {
		return sprintf(
			'"%s"',
			md5( $this->getLocalPath() . $this->getLastModified() )
		);
	}
	
	public function getContentType() {
		return $this->post->post_mime_type;
	}
	
	public function getLastModified() {
        	return strtotime( $this->post->post_modified_gmt );
   	}
}
