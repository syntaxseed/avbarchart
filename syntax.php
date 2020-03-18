<?php
/**
 * Plugin AVBarChart: Generates a very simple CSS/HTML bar chart
 *
 * USAGE: <barchart>MAXVAL|Label1:#,Label2:#,Label3:#</barchart>
 *
 * EXAMPLE: <barchart>100|A:55,B:5,C:23,D:38</barchart>
 *
 * With Colored Bars: <barchart>100|A:55:#FFCCCC,B:5,C:23#00FF00,D:38</barchart>
 *
 * @license    MIT (https://opensource.org/licenses/MIT)
 * @author     Sherri W. (http://syntaxseed.com)
 */

if (!defined('DOKU_INC')) {
    define('DOKU_INC', realpath(dirname(__FILE__).'/../../').'/');
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');
}
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_avbarchart extends DokuWiki_Syntax_Plugin
{
    // ********* CHART CONFIG SETTINGS *************
    public $barWidth = 25;         // Pixel width of chart bars.
    public $barColor = "#ccccff";  // Default color of graph bars.
    public $fontSize = "8pt;";     // Font size of labels and values.
    public $maxPxHeight = "200";   // Maximum height of the chart in pixels.
    // *********************************************

    /**
     * What kind of syntax are we?
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    public function getSort()
    {
        return 999;
    }


    /**
     * Connect pattern to lexer
     */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('\<barchart\>', $mode, 'plugin_avbarchart');
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('\</barchart\>', 'plugin_avbarchart');
    }


    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        switch ($state) {
          case DOKU_LEXER_ENTER:
            return array($state, '');
          case DOKU_LEXER_MATCHED:
            break;
          case DOKU_LEXER_UNMATCHED:

            $chart = "";
            list($maxRange, $data1) = preg_split("/\|/", $match);
            $maxRange = floatval($maxRange);

            if ($maxRange > 0 && !empty($data1)) {
                $values = preg_split("/,/", $data1);

                $chart = "";
                foreach ($values as $col) {
                    if (!empty($col)) {
                        list($label, $amount, $color) = preg_split("/:/", $col);
                        $amount = floatval($amount);
                        if (empty($label)) {
                            $label='&nbsp;';
                        }
                        if (empty($color)) {
                            $color = $this->barColor;
                        }
                        if ($amount >= 0) {
                            $height = round(($amount/$maxRange*$this->maxPxHeight));
                            $chart .= "<td valign='bottom' style='border:0;vertical-align:bottom;text-align:center;' align='center'><span style='font-size:".$this->fontSize.";'>".$amount."</span><br clear='all' /><table style='display:inline;border:0;' cellpadding='1' cellspacing='0'><tr><td height='".$height."' width='".$this->barWidth."' bgcolor='".$color."' valign='bottom'></td></tr></table><br clear='all' /><span style='font-size:".$this->fontSize.";'><b>".$label."</b></span></td>";
                        }
                    }
                }
            }

            $match = $chart;
            return array($state, $match);

          case DOKU_LEXER_EXIT:
            return array($state, '');
          case DOKU_LEXER_SPECIAL:
            break;
        }
        return array();
    }


    /**
     * Create output
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode == 'xhtml') {
            list($state, $match) = $data;

            switch ($state) {
          case DOKU_LEXER_ENTER:
            $renderer->doc .= "<table border='0' cellspacing='2' style='border:0;'><tr>";
            break;

          case DOKU_LEXER_MATCHED:
            break;

          case DOKU_LEXER_UNMATCHED:

            $renderer->doc .= $match; break;

          case DOKU_LEXER_EXIT:
            $renderer->doc .= "</tr></table>";
            break;

          case DOKU_LEXER_SPECIAL:
            break;
        }
            return true;
        }
        return false;
    }
} // End class
