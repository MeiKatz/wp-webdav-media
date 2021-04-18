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

  const DEFAULT_REWRITE_TAG = 'webdav_route';

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
  public function intersectQuery( WP_Query $query ) {
    if ( ! $this->isResponsible( $query ) ) {
      return $query;
    }

    if ( $this->shouldRedirectToRoot() ) {
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
   * @return string
   */
  private function getRoutePath() {
    $value = get_query_var(
      self::DEFAULT_REWRITE_TAG
    );

    if ( ! empty( $value ) ) {
      return $value;
    }

    $match = preg_match(
      '/\/$|\/\?/',
      $_SERVER['REQUEST_URI']
    );

    if ( $match === 1 ) {
      return '/';
    }

    return '';
  }

  /**
   * @return bool
   */
  private function shouldRedirectToRoot() {
    return (
      $this->getRoutePath() === ''
    );
  }

  /**
   * @param WP_Query $query
   * @return bool
   */
  private function isResponsible( WP_Query $query ) {
    return isset(
      $query->query_vars[ self::DEFAULT_REWRITE_TAG ]
    );
  }

  /**
   * @return void
   */
  private function registerRewriteRules() {
    add_rewrite_rule(
      '^wp-webdav$',
      sprintf(
        'index.php?%s=',
        self::DEFAULT_REWRITE_TAG
      ),
      'top'
    );

    add_rewrite_rule(
      '^wp-webdav/(.*)?',
      sprintf(
        'index.php?%s=/$matches[1]',
        self::DEFAULT_REWRITE_TAG
      ),
      'top'
    );

    add_rewrite_tag(
      sprintf(
        '%%%s%%',
        self::DEFAULT_REWRITE_TAG
      ),
      '.*'
    );

    add_action(
      'parse_query',
      [ $this, 'intersectQuery' ]
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
