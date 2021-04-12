<?php
namespace WP_WebDAV;

use Sabre\DAV;
use Sabre\DAV\Auth;
use Sabre\DAV\PropFind;
use Sabre\DAV\INode;
use \WP_User;
use \WP_Query;

class Plugin {
  const DEFAULT_REALM = 'WordPress Media Library';

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

    add_action(
      'init',
      [ $this, 'onInit' ]
    );

    register_activation_hook(
      __FILE__,
      [ $this, 'onActivate' ]
    );

    register_deactivation_hook(
      __FILE__,
      [ $this, 'onDeactivate' ]
    );

    $this->inited = true;
  }

  /**
   * @return void
   */
  public function onInit() {
    $this->registerRewriteRules();
    $this->loadTextDomain();
  }

  /**
   * Flush rewrites rules to add the rewrite rules
   * of this plugin.
   *
   * @return void
   */
  public function onActivate() {
    flush_rewrite_rules();
  }

  /**
   * Flush rewrites rules to remove the rewrite rules
   * of this plugin.
   *
   * @return void
   */
  public function onDeactivate() {
    flush_rewrite_rules();
  }

  /**
   * @param WP_Query $query
   * @return WP_Query
   */
  public function parseQuery( WP_Query $query ) {
    if ( ! isset( $query->query_vars['wp-webdav-path'] ) ) {
      return $query;
    }

    if ( $this->shouldRedirectToRoot( $query ) ) {
      wp_redirect(
        '/wp-webdav/',
        308
      );
      exit();
    }

    $this->handleRequests();
    exit();
  }

  /**
   * callback hook for the endpoint
   *
   * @return void
   */
  private function handleRequests() {
    $rootDirectory = new RootFolder();

    // The server object is responsible for making sense out of the WebDAV protocol
    $server = new DAV\Server( $rootDirectory );

    // If your server is not on your webroot, make sure the following line has the
    // correct information
    $server->setBaseUri( '/wp-webdav/' );

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

    $authBackend->setRealm(
      $this->getRealm()
    );

    $authPlugin = new Auth\Plugin( $authBackend );

    $server->addPlugin( $authPlugin );

    // All we need to do now, is to fire up the server
    $server->exec();
    exit();
  }

  /**
   * @return string
   */
  private function getRealm() {
    return apply_filters(
      'wp_webdav_realm',
      self::DEFAULT_REALM
    );
  }

  /**
   * @return bool
   */
  private function hasTrailingSlash() {
    return (
      substr( $_SERVER['REQUEST_URI'], -1, 1 ) === '/'
    );
  }

  /**
   * @return bool
   */
  private function hasQueryString() {
    return ! empty( $_SERVER['QUERY_STRING'] );
  }

  /**
   * @param WP_Query $query
   * @return bool
   */
  private function shouldRedirectToRoot( WP_Query $query ) {
    if ( ! empty( $query->query_vars[ 'wp-webdav-path' ] ) ) {
      return false;
    }

    if ( $this->hasTrailingSlash() ) {
      return false;
    }

    if ( $this->hasQueryString() ) {
      return false;
    }

    return true;
  }

  /**
   * @return void
   */
  private function registerRewriteRules() {
    add_rewrite_rule(
      '^wp-webdav(?:/(.*))?',
      'index.php?wp-webdav-path=$matches[1]',
      'top'
    );

    add_rewrite_tag(
      '%wp-webdav-path%',
      '.*'
    );

    add_action(
      'parse_query',
      [ $this, 'parseQuery' ]
    );
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
