<?php
namespace WP_WebDAV;

final class Slug {
  private static $replacements = [
    'À' => 'A',
    'Á' => 'A',
    'Â' => 'A',
    'Ã' => 'A',
    'Ä' => 'A',
    'Å' => 'A',
    'Æ' => 'AE',
    'Ç' => 'C',
    'È' => 'E',
    'É' => 'E',
    'Ê' => 'E',
    'Ë' => 'E',
    'Ì' => 'I',
    'Í' => 'I',
    'Î' => 'I',
    'Ï' => 'I',
    'Ð' => 'D',
    'Ñ' => 'N',
    'Ò' => 'O',
    'Ó' => 'O',
    'Ô' => 'O',
    'Õ' => 'O',
    'Ö' => 'O',
    'Ø' => 'O',
    'Ù' => 'U',
    'Ú' => 'U',
    'Û' => 'U',
    'Ü' => 'U',
    'Ý' => 'Y',
    'ß' => 's',
    'à' => 'a',
    'á' => 'a',
    'â' => 'a',
    'ã' => 'a',
    'ä' => 'a',
    'å' => 'a',
    'æ' => 'ae',
    'ç' => 'c',
    'è' => 'e',
    'é' => 'e',
    'ê' => 'e',
    'ë' => 'e',
    'ì' => 'i',
    'í' => 'i',
    'î' => 'i',
    'ï' => 'i',
    'ñ' => 'n',
    'ò' => 'o',
    'ó' => 'o',
    'ô' => 'o',
    'õ' => 'o',
    'ö' => 'o',
    'ø' => 'o',
    'ù' => 'u',
    'ú' => 'u',
    'û' => 'u',
    'ü' => 'u',
    'ý' => 'y',
    'ÿ' => 'y',
    'A' => 'A',
    'a' => 'a',
    'C' => 'C',
    'c' => 'c',
    'D' => 'D',
    'd' => 'd',
    'E' => 'E',
    'e' => 'e',
    'G' => 'G',
    'g' => 'g',
    'H' => 'H',
    'h' => 'h',
    'I' => 'I',
    'i' => 'i',
    '?' => 'o',
    'J' => 'J',
    'j' => 'j',
    'K' => 'K',
    'k' => 'k',
    'L' => 'l',
    'l' => 'l',
    'N' => 'N',
    'n' => 'n',
    'O' => 'O',
    'o' => 'o',
    'Œ' => 'OE',
    'œ' => 'oe',
    'R' => 'R',
    'r' => 'r',
    'S' => 'S',
    's' => 's',
    'Š' => 'S',
    'š' => 's',
    'T' => 'T',
    't' => 't',
    'U' => 'U',
    'u' => 'u',
    'W' => 'W',
    'w' => 'w',
    'Y' => 'Y',
    'y' => 'y',
    'Ÿ' => 'Y',
    'Z' => 'Z',
    'z' => 'z',
    'Ž' => 'Z',
    'ž' => 'z',
    'ƒ' => 'f',
  ];

  private function __construct() {}

  /**
   * @param string $string
   * @return string
   */
  public static function fromString( $str ) {
    $str = trim( $str );

        return strtolower(
      preg_replace(
        array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'),
        array('', '-', ''),
        str_replace(
          array_keys( self::$replacements ),
          array_values( self::$replacements ),
          $str
        )
      )
    );
    }
}
