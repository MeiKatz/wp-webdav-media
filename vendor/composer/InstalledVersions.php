<?php











namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => '0.1',
    'version' => '0.1.0.0',
    'aliases' => 
    array (
    ),
    'reference' => NULL,
    'name' => 'meikatz/wp-webdav-media',
  ),
  'versions' => 
  array (
    'meikatz/wp-webdav-media' => 
    array (
      'pretty_version' => '0.1',
      'version' => '0.1.0.0',
      'aliases' => 
      array (
      ),
      'reference' => NULL,
    ),
    'psr/log' => 
    array (
      'pretty_version' => '1.1.3',
      'version' => '1.1.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '0f73288fd15629204f9d42b7055f72dacbe811fc',
    ),
    'sabre/dav' => 
    array (
      'pretty_version' => '4.1.5',
      'version' => '4.1.5.0',
      'aliases' => 
      array (
      ),
      'reference' => 'c1afdc77a95efea6ee40c03c45f57c3c0c80ec22',
    ),
    'sabre/event' => 
    array (
      'pretty_version' => '5.1.2',
      'version' => '5.1.2.0',
      'aliases' => 
      array (
      ),
      'reference' => 'c120bec57c17b6251a496efc82b732418b49d50a',
    ),
    'sabre/http' => 
    array (
      'pretty_version' => '5.1.1',
      'version' => '5.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'd0aafede6961df6195ce7a8dad49296b0aaee22e',
    ),
    'sabre/uri' => 
    array (
      'pretty_version' => '2.2.1',
      'version' => '2.2.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'f502edffafea8d746825bd5f0b923a60fd2715ff',
    ),
    'sabre/vobject' => 
    array (
      'pretty_version' => '4.3.5',
      'version' => '4.3.5.0',
      'aliases' => 
      array (
      ),
      'reference' => 'd8a0a9ae215a8acfb51afc29101c7344670b9c83',
    ),
    'sabre/xml' => 
    array (
      'pretty_version' => '2.2.3',
      'version' => '2.2.3.0',
      'aliases' => 
      array (
      ),
      'reference' => 'c3b959f821c19b36952ec4a595edd695c216bfc6',
    ),
  ),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}
