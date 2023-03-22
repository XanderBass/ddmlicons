<?php

/**
 * Double dingbat multi-layer icons
 *
 * @author       Xander Bass
 * @copyright    Xander Bass
 * @license      https://opensource.org/licenses/LGPL-2.1 LGPL-2.1
 *
 * @description  Compiler class
 */

namespace XanderBass\DDMLIcons;

class Compiler
{
    /**
     * Correct data by "NORMAL"
     * @param array $normal  "NORMAL" data
     * @param array $data    data
     * @return mixed
     */
    protected static function _correct($normal, $data = null)
    {
        if ($data === null) $data = $normal;
        $result = $normal;
        $code   = 0xf000;
        $names  = array();
        foreach ($data['glyphs'] as $key => $glyph) {
            $name = preg_replace('~^(\d+)(\-+)(.+)$~si', '\3', $normal['glyphs'][$key]['css']);
            $css  = $name;
            if (empty($names[$name])) {
                $names[$name] = 1;
            } else {
                $names[$name]++;
                $css .= ($names[$name] - 1);
            }
            $result['glyphs'][$key]['css']  = $css;
            $result['glyphs'][$key]['code'] = $code;
            $result['glyphs'][$key]['uid']  = $glyph['uid'];
            if (!empty($glyph['svg']))    $result['glyphs'][$key]['svg']    = $glyph['svg'];
            if (!empty($glyph['search'])) $result['glyphs'][$key]['search'] = array($name);
            $code++;
        }
        return $result;
    }

    /**
     * Get data from iconset file
     * @param string $name  Iconset name
     * @param $file  $file  Iconset file
     * @param bool   $err   Drop errors
     * @return mixed
     * @throws \Exception
     */
    protected static function _getJSON($name, $file, $err = true)
    {
        static $path = null;
        if (empty($path)) {
            $path = self::path(dirname(__DIR__));
        }
        if (!in_array($name, array('normal', 'bold', 'symbols')) && $err) {
            throw new \Exception('Incorrect iconset');
        }
        $filename = $path . $name . '/' . $file . '.json';
        if (!file_exists($filename)) {
            if ($err) {
                throw new \Exception("Iconset source for '{$name}' does not exists");
            } else {
                return array();
            }
        }
        return json_decode(file_get_contents($filename), true);
    }

    /**
     * Compile CSS
     * @param bool $ret  Return result
     * @return bool|int
     * @throws \Exception
     */
    public static function compileCSS($ret = false)
    {
        $path   = self::path(dirname(__DIR__));
        $result = array(file_get_contents($path . 'min-style.css'));
        foreach (array('normal' => '', 'symbols' => 'sym-') as $part => $pref) {
            $data  = self::_getJSON($part, 'font');
            $icons = array();
            foreach ($data['glyphs'] as $glyph) {
                $name    = $glyph['css'];
                $code    = '\\' . dechex($glyph['code']);
                $icons[] = ".ddml-icon-{$pref}{$name}:before, .ddml-ding-{$pref}{$name}:after { content: '{$code}'; }";
            }
            if (!empty($icons)) {
                $result[] = '/** ' . strtoupper($part) . ' **/';
                $result[] = implode("\r\n", $icons);
            }
        }
        foreach (array('macros', 'ui', 'corrections') as $k) {
            $file = self::path() . $k . '.css';
            if (file_exists($file)) {
                $file = trim(file_get_contents($file));
                if (!empty($file)) {
                    $result[] = '/** ' . strtoupper($k) . ' **/';
                    $result[] = $file;
                }
            }
        }
        $result = join("\r\n\r\n", $result);
        return $ret ? $result : file_put_contents($path . 'style.css', $result);
    }

    /**
     * Compile font source from configuration files
     * @throws \Exception
     */
    public static function compileFontSource()
    {
        $path    = self::path(dirname(__DIR__));
        $opts    = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $normal  = self::_getJSON('normal' , 'config');
        $bold    = self::_getJSON('bold'   , 'config');
        $symbols = self::_getJSON('symbols', 'config');
        $result  = array();
        if (file_put_contents($path . 'normal/font.json', json_encode(self::_correct($normal), $opts))) {
            $result[] = 'Normal glyphs: ok';
        } else {
            $result[] = 'Normal glyphs: error';
        }
        if (file_put_contents($path . 'bold/font.json', json_encode(self::_correct($normal, $bold), $opts))) {
            $result[] = 'Filled glyphs: ok';
        } else {
            $result[] = 'Filled glyphs: error';
        }
        if (file_put_contents($path . 'symbols/font.json', json_encode(self::_correct($symbols), $opts))) {
            $result[] = 'Simple symbols: ok';
        } else {
            $result[] = 'Simple symbols: error';
        }
        return join("\r\n", $result);
    }

    /**
     * Correct path
     * @param string $path  Path
     * @return string
     */
    public static function path($path = __DIR__)
    {
        return rtrim(strtr($path, '\\', '/'), '/') . '/';
    }
}