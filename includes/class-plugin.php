<?php
namespace WP_WebDAV;

use Sabre\DAV;
use Sabre\DAV\Auth;
use \WP_User;

class Plugin {
  public static $METHODS = [
    'GET',
    'POST',
    'PUT',
    'PATCH',
    'DELETE',
    'HEAD',
    'TRACE',
    'CONNECT',
    'OPTIONS',
    'PROPFIND',
    'PROPPATCH',
    'MKCOL',
    'COPY',
    'MOVE',
    'LOCK',
    'UNLOCK',
  ];

  /**
   * @var bool
   */
  private $inited = false;

  /**
   * initializes the plugin
   *
   * @return void
   */
  public function init() {
    if ( $this->inited ) {
      return;
    }

    $this->registerRoutes();
    $this->loadTextDomain();

    $this->inited = true;
  }

  /**
   * callback hook for the endpoint
   *
   * @return void
   */
  public function action() {
    $rootDirectory = new RootFolder();

    // The server object is responsible for making sense out of the WebDAV protocol
    $server = new DAV\Server( $rootDirectory );

    // If your server is not on your webroot, make sure the following line has the
    // correct information
    $server->setBaseUri( '/wp-json/webdav/files/' );

    // This ensures that we get a pretty index in the browser, but it is
    // optional.
    $server->addPlugin(new DAV\Browser\Plugin());

    $authBackend = new Auth\Backend\BasicCallBack(function ($username, $password) {
      $ret = wp_authenticate(
        $username,
        $password
      );

      return (
        $ret instanceof WP_User
      );
    });

    $authBackend->setRealm( "WordPress Media Library" );

    $authPlugin = new Auth\Plugin( $authBackend );

    $server->addPlugin( $authPlugin );

    // All we need to do now, is to fire up the server
    $server->exec();
    die();
  }

  /**
   * @return void
   */
  private function registerRoutes() {
    $methods = self::$METHODS;
    $self = $this;

    add_action(
      'rest_api_init',
      function () use ( $self, $methods ) {

      register_rest_route( 'webdav', 'files', [
        'methods' => join( ",", $methods ),
        'callback'            => [ $self, 'action' ], // make sure it returns an XML string
        'permission_callback' => '__return_true',
      ]);

      register_rest_route( 'webdav', 'files/.*', [
        'methods' => join( ",", $methods ),
        'callback'            => [ $self, 'action' ], // make sure it returns an XML string
        'permission_callback' => '__return_true',
      ]);
    });
  }

  /**
   * @return void
   */
  private function loadTextDomain() {
    load_plugin_textdomain(
      'wp-webdav-media',
      false,
      plugin_basename( dirname( __FILE__ ) ) . '/../languages/'
    );
  }
}
