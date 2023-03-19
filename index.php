<?php

/**
 * Double dingbat multi-layer icons
 *
 * @author       Xander Bass
 * @copyright    Xander Bass
 * @license      https://opensource.org/licenses/LGPL-2.1 LGPL-2.1
 *
 * @description  Main viewer
 */

namespace XanderBass\DDMLIcons;

require_once 'src/compiler.php';

$path    = rtrim(strtr(__DIR__, '\\', '/'), '/') . '/';
$command = strtolower(empty($_SERVER['argv'][1]) ? '' : strtolower($_SERVER['argv'][1]));
$is_cli  = !empty($command);
if (empty($command) && !empty($_GET['command'])) {
    $command = strtolower($_GET['command']);
}
$cssurl = $is_cli ? 'style.css' : 'index.php?command=style';

if (empty($command) || ($command === 'compile')) :
    ob_start();
?><!DOCTYPE html>
<html><head>
    <meta charset="utf-8">
    <title>DDMLIcons viewer</title>
    <link rel="stylesheet" href="<?=$cssurl?>">
    <link rel="stylesheet" href="src/viewer.css">
    <script type="text/javascript" src="src/viewer.js"></script>
</head><body>
<aside>
    <nav class="select">
        <a href="#" data-type="icon">Select icon</a>
        <a href="#" data-type="ding">Select ding</a>
    </nav>
    <section class="icons">
        <div class="big icon" data-size="big"></div>
        <div class="medium icon" data-size="medium"></div>
        <div class="small icon" data-size="small"></div>
        <nav class="pos values" data-type="ding" data-property="pos">
            <a href="#" data-value="top" class="ddml-ding-circle ddml-pos-top ddml-filled-ding"></a>
            <a href="#" data-value="middle" class="ddml-ding-circle ddml-pos-middle ddml-filled-ding"></a>
            <a href="#" data-value="" class="active ddml-ding-circle ddml-pos-bottom ddml-filled-ding"></a>
        </nav>
    </section>
    <nav class="color values">
        <?php
        foreach (array('icon', 'ding') as $t) {
            echo '<div data-type="' . $t . '" data-property="color">';
            foreach (array('default', 'notice', 'success', 'warning', 'error') as $i => $k) {
                $classes = join(' ', array(
                    'ddml-icon-circle',
                    empty($i) ? 'active' : '',
                    'ddml-filled-icon',
                    'ddml-color-icon-' . $k
                ));
                echo '<a href="#" data-value="' . $k . '" class="' . $classes . '"></a>' . "\r\n";
            }
            echo "</div>\r\n";
        }
        ?>
    </nav>
    <nav class="flags">
        <a href="#" data-type="icon" data-property="disabled">Disabled</a>
    </nav>
    <nav class="flags">
        <a href="#" data-type="icon" data-property="filled">Filled icon</a>
        <a href="#" data-type="ding" data-property="filled">Filled ding</a>
        <a href="#" data-type="icon" data-property="animated">Animated icon</a>
        <a href="#" data-type="ding" data-property="animated">Animated ding</a>
    </nav>
    <nav class="orientation values">
        <?php
        foreach (array('icon', 'ding') as $t) {
            echo '<div data-type="' . $t . '" data-property="orientation">';
            foreach (array('nw', 'n', 'ne', 'w', '', 'e', 'sw', 's', 'se') as $k) {
                $classes = join(' ', array(
                    empty($k) ? 'ddml-icon-circle' : 'ddml-icon-navigation',
                    empty($k) ? 'active' : 'ddml-orientation-icon-' . $k,
                    'ddml-filled-icon'
                ));
                echo '<a href="#" data-value="' . $k . '" class="' . $classes . '"></a>' . "\r\n";
            }
            echo "</div>\r\n";
        }
        ?>
    </nav>
    <div id="classlist"></div>
</aside>
<main>
    <ul>
        <?php
        foreach (array('normal' => '', 'symbols' => 'sym-') as $part => $pref) {
            $items = json_decode(file_get_contents($path . $part . '/font.json'), true);
            foreach ($items['glyphs'] as $item) {
                $letter = '&#' . $item['code'];
                $code   = dechex($item['code']);
                echo <<<html
<li data-name="{$pref}{$item['css']}" class="{$pref}icons">
    <span class="letter">{$letter}</span>
    <span class="code">{$code}</span>
</li>

html;
            }
        }
        ?>
    </ul>
</main>
</body></html><?php
    $html = ob_get_clean();
    if ($is_cli) {
        if (file_put_contents('index.html', $html)) {
            echo "HTML compiled successfully\r\n";
        }
        try {
            if (Compiler::compileCSS()) {
                echo "CSS compiled successfully";
            }
        } catch (\Exception $e) {
            echo "CSS compile error: " . $e->getMessage();
        }
    } else {
        echo $html;
    }
else :
    try {
        switch ($command) {
            case 'compile/font':
                exit(Compiler::compileFontSource());
                break;
            case 'compile/css':
                if (Compiler::compileCSS()) {
                    exit('CSS compiled successfully');
                } else {
                    exit('CSS is not compiled');
                }
                break;
            case 'style':
                $css = Compiler::compileCSS(true);
                header('Content-type: text/css; charset=utf-8');
                exit($css);
        }
    } catch (\Exception $e) {
        exit($e->getMessage());
    }
endif;