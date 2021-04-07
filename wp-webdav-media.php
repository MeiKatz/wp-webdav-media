<?php
/*
Plugin Name: WP-WebDAV-Media
Version: 1.0
Description: Makes media library accessable via WebDAV
Author: Gregor Mitzka
Author URI: https://github.com/MeiKatz
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
namespace WP_WebDAV;

require dirname( __FILE__ ) . '/vendor/autoload.php';

$webdav = new Plugin();
$webdav->init();
