<?php
/**
 * Plugin: Displays a link list in a menu way
 *
 * Syntax: <dwmenu col="2" align="center" caption="headline">
 *           <dwitem name="name" description="description" link="link" image="image">
 *             <dwlink link="link" text="text" />
 *             <dwlink link="link" text="text" />
 *           </dwitem>
 *         </dwmenu>
 * DWMenu
 * col (opt)     The number of columns of the menu. Allowed are 1-4, default is 1
 * align (opt)   Alignment of the menu. Allowed are "left", "center" or "right", default is "left"
 * caption (opt) Headline of the menu, default is none
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Progi1984 <progi1984@gmail.com>
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');


/**
 *
 */
class syntax_plugin_dwmenu extends DokuWiki_Syntax_Plugin {
  /**
   * @var array
   */
  var $datas = array();

  /**
  * Get an associative array with plugin info.
  *
  * <p>
  * The returned array holds the following fields:
  * <dl>
  * <dt>author</dt><dd>Author of the plugin</dd>
  * <dt>email</dt><dd>Email address to contact the author</dd>
  * <dt>date</dt><dd>Last modified date of the plugin in
  * <tt>YYYY-MM-DD</tt> format</dd>
  * <dt>name</dt><dd>Name of the plugin</dd>
  * <dt>desc</dt><dd>Short description of the plugin (Text only)</dd>
  * <dt>url</dt><dd>Website with more information on the plugin
  * (eg. syntax description)</dd>
  * </dl>
  * @param none
  * @return Array Information about this plugin class.
  * @public
  * @static
  */
  function getInfo(){
    return array(
      'author' => 'Progi1984',
      'email'  => 'progi1984@gmail.com',
      'date'   => '2013-01-26',
      'name'   => 'DWMenu Plugin',
      'desc'   => 'Displays a link list in a menu way',
      'url'    => 'http://www.dokuwiki.org/plugin:dwmenu',
    );
  }

  /**
  * Get the type of syntax this plugin defines.
  *
  * The type of this plugin is "protected". It has a start and an end
  * token and no other wiki commands shall be parsed between them.
  *
  * @param none
  * @return String <tt>'protected'</tt>.
  * @public
  * @static
  */
  function getType(){
    return 'protected';
  }

  /**
  * Define how this plugin is handled regarding paragraphs.
  *
  * <p>
  * This method is important for correct XHTML nesting. It returns
  * one of the following values:
  * </p>
  * <dl>
  * <dt>normal</dt><dd>The plugin can be used inside paragraphs.</dd>
  * <dt>block</dt><dd>Open paragraphs need to be closed before
  * plugin output.</dd>
  * <dt>stack</dt><dd>Special case: Plugin wraps other paragraphs.</dd>
  * </dl>
  * @param none
  * @return String <tt>'block'</tt>.
  * @public
  * @static
  */
  function getPType(){
    return 'block';
  }

  /**
  * Where to sort in?
  *
  * Sort the plugin in just behind the formating tokens
  *
  * @param none
  * @return Integer <tt>135</tt>.
  * @public
  * @static
  */
  function getSort(){
    return 135;
  }

  /**
  * Connect lookup pattern to lexer.
  *
  * @param $aMode String The desired rendermode.
  * @return none
  * @public
  * @see render()
  */
  function connectTo($mode) {
     $this->Lexer->addEntryPattern('<dwmenu>(?=.*?</dwmenu.*?>)',$mode,'plugin_dwmenu');
     $this->Lexer->addEntryPattern('<dwmenu\s[^\r\n\|]*?>(?=.*?</dwmenu.*?>)',$mode,'plugin_dwmenu');
  }

  /**
   *
   */
  function postConnect() {
    $this->Lexer->addPattern('<dwitem\s[^\r\n\|]*?>(?=.*?</dwitem>)','plugin_dwmenu');
    $this->Lexer->addPattern('<dwlink\s[^\r\n\|]*?/>','plugin_dwmenu');
    $this->Lexer->addPattern('</dwitem>','plugin_dwmenu');
    $this->Lexer->addExitPattern('</dwmenu>','plugin_dwmenu');
  }

  /**
  * Handler to prepare matched data for the rendering process.
  *
  * <p>
  * The <tt>$aState</tt> parameter gives the type of pattern
  * which triggered the call to this method:
  * </p>
  * <dl>
  * <dt>DOKU_LEXER_ENTER</dt>
  * <dd>a pattern set by <tt>addEntryPattern()</tt></dd>
  * <dt>DOKU_LEXER_MATCHED</dt>
  * <dd>a pattern set by <tt>addPattern()</tt></dd>
  * <dt>DOKU_LEXER_EXIT</dt>
  * <dd> a pattern set by <tt>addExitPattern()</tt></dd>
  * <dt>DOKU_LEXER_SPECIAL</dt>
  * <dd>a pattern set by <tt>addSpecialPattern()</tt></dd>
  * <dt>DOKU_LEXER_UNMATCHED</dt>
  * <dd>ordinary text encountered within the plugin's syntax mode
  * which doesn't match any pattern.</dd>
  * </dl>
  * @param $aMatch String The text matched by the patterns.
  * @param $aState Integer The lexer state for the match.
  * @param $aPos Integer The character position of the matched text.
  * @param $aHandler Object Reference to the Doku_Handler object.
  * @return Integer The current lexer state for the match.
  * @public
  * @see render()
  * @static
  */
  function handle($match, $state, $pos, &$handler) {
    $match = trim($match);
    if(!empty($match)){
      switch ($state) {
        case DOKU_LEXER_ENTER:
          //echo 'DOKU_LEXER_ENTER';
          //echo '<pre>'.print_r(htmlentities($match), true).'</pre>';
          /* Remove < & > */
          $match = str_replace('<dwmenu', '', $match);
          if(substr($match, -1) == '>'){
            $match = substr($match, 0, -1);
          }
          $opts = $this->_parseOptions($match);

          if(isset($opts['align']) && in_array($opts['align'], array('left','center','right'))){
            $this->datas['align'] = $opts['align'];
          } else {
            $this->datas['align'] = 'left';
          }
          if(isset($opts['caption']) && is_string($opts['caption'])){
            $this->datas['caption'] = htmlentities($opts['caption']);
          } else {
            $this->datas['caption'] = '';
          }
          $this->datas['columns_data'] = array();
          if(isset($opts['col']) && is_numeric($opts['col'])){
            $this->datas['columns_num'] = $opts['col'];
          } else {
            $this->datas['columns_num'] = 1;
          }
          break;
        case DOKU_LEXER_MATCHED:
          //echo 'DOKU_LEXER_MATCHED';
          //echo '<pre>'.print_r(htmlentities($match), true).'</pre>';
          /* Remove < & > */
          if(substr($match, 0, 1) == '<'){
            $match = substr($match, 1);
          }
          if(substr($match, -1) == '>'){
            $match = substr($match, 0, -1);
          }
          /* XML Name */
          $arrXMLName = explode(' ', $match);
          if($arrXMLName[0] == 'dwitem'){
            $match = trim(substr($match, strlen($arrXMLName[0])));
            $opts = $this->_parseOptions($match);
            $iNumColumn = count($this->datas['columns_data']);
            if(isset($opts['name']) && is_string($opts['name'])){
              $this->datas['columns_data'][$iNumColumn]['name'] = $opts['name'];
            } else {
              $this->datas['columns_data'][$iNumColumn]['name'] = '';
            }
            if(isset($opts['description']) && is_string($opts['description'])){
              $this->datas['columns_data'][$iNumColumn]['description'] = $opts['description'];
            } else {
              $this->datas['columns_data'][$iNumColumn]['description'] = '';
            }
            if(isset($opts['link']) && is_string($opts['link'])){
              $this->datas['columns_data'][$iNumColumn]['link'] = $opts['link'];
            } else {
              $this->datas['columns_data'][$iNumColumn]['link'] = 'link';
            }
            if(isset($opts['image']) && is_string($opts['image'])){
              $this->datas['columns_data'][$iNumColumn]['image'] = $opts['image'];
            } else {
              $this->datas['columns_data'][$iNumColumn]['image'] = 'image.png';
            }
            $this->datas['columns_data'][$iNumColumn]['link_data'] = array();
          } elseif($arrXMLName[0] == 'dwlink'){
            $match = trim(substr($match, strlen($arrXMLName[0])));
            $opts = $this->_parseOptions($match);
            $iNumColumn = count($this->datas['columns_data']) - 1;
            $iNumLink = count($this->datas['columns_data'][$iNumColumn]['link_data']);
            if(isset($opts['link']) && is_string($opts['link'])){
              $this->datas['columns_data'][$iNumColumn]['link_data'][$iNumLink]['link'] = $opts['link'];
            } else {
              $this->datas['columns_data'][$iNumColumn]['link_data'][$iNumLink]['link'] = '';
            }
            if(isset($opts['text']) && is_string($opts['text'])){
              $this->datas['columns_data'][$iNumColumn]['link_data'][$iNumLink]['text'] = $opts['text'];
            } else {
              $this->datas['columns_data'][$iNumColumn]['link_data'][$iNumLink]['text'] = '';
            }
          } elseif($arrXMLName[0] == '/dwitem'){
          } else {}
          break;
        case DOKU_LEXER_EXIT:
          //echo 'DOKU_LEXER_EXIT';
          //echo '<pre>'.print_r(htmlentities($match), true).'</pre>';
          return $this->datas;
          break;
        case DOKU_LEXER_SPECIAL:
          //echo 'DOKU_LEXER_SPECIAL';
          //echo '<pre>'.print_r(htmlentities($match), true).'</pre>';
          break;
        case DOKU_LEXER_UNMATCHED:
          //echo 'DOKU_LEXER_UNMATCHED';
          //echo '<pre>'.print_r(htmlentities($match), true).'</pre>';
          break;
        default:
          //echo $state;
          //echo '<pre>'.print_r(htmlentities($match), true).'</pre>';
          break;
      }
    }
    return array();
  }

  /**
  * Handle the actual output creation.
  *
  * <p>
  * The method checks for the given <tt>$aFormat</tt> and returns
  * <tt>FALSE</tt> when a format isn't supported. <tt>$aRenderer</tt>
  * contains a reference to the renderer object which is currently
  * handling the rendering. The contents of <tt>$aData</tt> is the
  * return value of the <tt>handle()</tt> method.
  * </p>
  * @param $aFormat String The output format to generate.
  * @param $aRenderer Object A reference to the renderer object.
  * @param $aData Array The data created by the <tt>handle()</tt>
  * method.
  * @return Boolean <tt>TRUE</tt> if rendered successfully, or
  * <tt>FALSE</tt> otherwise.
  * @public
  * @see handle()
  */
  function render($mode, &$renderer, $data) {
    if (empty($data)) return false;
    if($mode == 'xhtml'){
      // Column Size
      if($data['columns_num'] > 10){
        $data['columns_num'] = 10;
      }

      $renderer->doc .= '<div class="dwmenu '.$data['align'].'">'."\n";
      if (isset($data['caption'])){
        $renderer->doc .= '<p class="dwmenu_caption">'.$data['caption'].'</p>'."\n";
      }
      foreach($data['columns_data'] as $item_colum) {
        $renderer->doc .= '<div class="dwmenu_item column'.$data['columns_num'].'">'."\n";
        // Image
        if(!empty($item_colum['image'])){
          $dwImg = Doku_Handler_Parse_Media($item_colum['image']);
          list($ext,$mime,$dl) = mimetype($dwImg['src']);
          $renderer->doc .= $renderer->_media($dwImg['src'],'', null,$dwImg['width'], $dwImg['height'], $dwImg['cache']);
        }
        // Item
        $renderer->doc .= '<div class="dwmenu_itemhead">'."\n";
        // Title
        $link = $this->_getWikiLink($item_colum['link'], $item_colum['name'], $renderer);
        $link['title'] = $item_colum['name'];
        $link['name'] = $item_colum['name'];
        $renderer->doc .= $renderer->_formatLink($link)."\n";
        $renderer->doc .= '</div>'."\n";
        // Description
        $renderer->doc .= '<div class="dwmenu_itemdesc">'.$item_colum['description'].'</div>'."\n";
        if(!empty($item_colum['link_data'])){
          $renderer->doc .= '<div class="dwmenu_itemlink">'."\n";
          foreach($item_colum['link_data'] as $iKey => $item_link){
            if($iKey > 0){
              $renderer->doc .= '<small>•</small>'."\n";
            }
            $link = $this->_getWikiLink($item_link['link'], $item_link['text'], $renderer);
            $link['title'] = $item_link['text'];
            $link['name'] = $item_link['text'];

            $renderer->doc .= $renderer->_formatLink($link)."\n";
          }
          $renderer->doc .= '</div>'."\n";
        }
        $renderer->doc .= '</div>'."\n";
      }
	  $renderer->doc .= '</div>'."\n";
      if($data['align'] == 'left' || $data['align'] == 'right'){
        $renderer->doc .= '<p style="clear:both;" />';
      }
      return true;
    }
    return false;
  }

  /**
   * @param $match
   * @param $title
   * @param $renderer
   * @return array
   */
  private function _getWikiLink($match, $title, &$renderer) {
    global $ID;
    global $conf;

    // Strip the opening and closing markup
    $link = preg_replace(array('/^\[\[/','/\]\]$/u'),'',$match);

    // Split title from URL
    $link = explode('|',$link,2);
    $ref  = trim($link[0]);

    //decide which kind of link it is
    if ( preg_match('/^[a-zA-Z0-9\.]+>{1}.*$/u',$ref) ) {
      // Interwiki
      $interwiki = explode('>',$ref,2);
      $type = 'interwikilink';
      $args = array($ref,$title,strtolower($interwiki[0]),$interwiki[1]);
    } elseif ( preg_match('/^\\\\\\\\[\w.:?\-;,]+?\\\\/u',$ref) ) {
      // Windows Share
      $type = 'windowssharelink';
      $args = array($ref,$title);
    } elseif ( preg_match('#^([a-z0-9\-\.+]+?)://#i',$ref) ) {
      // external link (accepts all protocols)
      $type = 'externallink';
      $args = array($ref,$title);
    } elseif ( preg_match('<'.PREG_PATTERN_VALID_EMAIL.'>',$ref) ) {
      // E-Mail (pattern above is defined in inc/mail.php)
      $type = 'emaillink';
      $args = array($ref,$title);
    } elseif ( preg_match('!^#.+!',$ref) ) {
      // local link
      $type = 'locallink';
      $args = array(substr($ref,1),$title);
    } else {
      // internal link
      $type = 'internallink';
      $args = array($ref,$title);
    }

    $link = array();
    $link['class']  = '';
    $link['style']  = '';
    $link['pre']    = '';
    $link['suf']    = '';
    $link['more']   = '';
    $link['title']  = '';
    $link['name']   = '';

    $check = false;
    $exists = false;

    switch ($type) {
      case 'interwikilink':
        $link['url']  = $renderer->_resolveInterWiki($args[2],$args[3]);
        $link['target'] = $conf['target']['interwiki'];
        if (strpos($link['url'],DOKU_URL) === 0) {
          //we stay at the same server, so use local target
          $link['target'] = $conf['target']['wiki'];
        }
        break;
      case 'windowssharelink':
        $link['url']  = 'file:///'.str_replace('\\','/',$args[0]);
        $link['target'] = $conf['target']['windows'];
        break;
      case 'externallink':
        $link['url'] = $args[0];
        $link['target'] = $conf['target']['extern'];
        break;
      case 'emaillink':
        $address = $renderer->_xmlEntities($args[0]);
        $address = obfuscate($address);
        if ($conf['mailguard'] == 'visible')
            $address = rawurlencode($address);
        $link['url'] = 'mailto:'.$address;
        $link['target'] = '';
        $link['class'] = 'JSnocheck';
        break;
      case 'locallink':
        $link['url'] = '#'.sectionID($args[0], $check);
        $link['target'] = '';
        $link['class'] = "wikilink1";
        break;
      case 'internallink':
        resolve_pageid(getNS($ID),$args[0],$exists);
        $link['url']  = wl($args[0]);
        list($id,$hash) = explode('#',$args[0],2);
        if (!empty($hash)) $hash = sectionID($hash, $check);
        if ($hash) $link['url'] = wl($id).'#'.$hash;    //keep hash anchor

        $link['target'] = $conf['target']['wiki'];
        $link['class'] = $exists ? 'wikilink1' : 'wikilink2';
        break;
      case 'internalmedia':
        resolve_mediaid(getNS($ID),$args[0], $exists);
        $link['url']  = ml($args[0],array('id'=>$ID,'cache'=>$args[5]),true);
        $link['target'] = '';
        if (!$exists) $link['class'] = 'wikilink2';
        break;
      case 'externalmedia':
        $link['url']  = ml($args[0],array('cache'=>$args[5]));
        $link['target'] = '';
        break;
    }
    return $link;
  }

  /**
   * Parse options
   * @param $string
   * @return array
   */
  private function _parseOptions($string){
      $arrOptions = array();
      $string = trim($string);

      $arrString = explode('" ', $string.' ');
      foreach($arrString as $item){
          $arrItem = explode('="', $item);
          if(!empty($arrItem[0])){
              $arrOptions[$arrItem[0]] = $arrItem[1];
          }
          unset($arrItem);
      }
      return $arrOptions;
  }
}

?>
