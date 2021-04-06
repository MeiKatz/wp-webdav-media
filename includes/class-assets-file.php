<?php
namespace WP_WebDAV;

use Sabre;
use Sabre\DAV;

class AssetsFile extends DAV\File {
	private $path;
	private $displayName = null;
	
	public function __construct( $path, $displayName = null ) {
		$this->path = $path;
		$this->displayName = $displayName;
	}
	
	public function getName() {
		if ( $this->displayName !== null ) {
			return $this->displayName;
		}
		
		return basename( $this->getLocalPath() );
	}
	
	public function getLocalPath() {
		return realpath(
			sprintf(
				'%s/wp-webdav-media/assets/%s',
				WP_PLUGIN_DIR,
				$this->path
			)
		);
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
		return null;
	}
	
	public function getLastModified() {
        return filemtime( $this->getLocalPath() );
    }
}
