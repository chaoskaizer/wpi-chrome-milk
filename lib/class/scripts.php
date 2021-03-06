<?php
if ( !defined('KAIZEKU') ) exit(42);
/**
 * WP-iStalker Chrome Milk 
 * Client scripts
 * 
 * @package	WordPress
 * @subpackage	wp-istalker-chrome
 * 
 * @category	Template
 * @author	Avice (ChaosKaizer) De'vereux <ck+wp-istalker-chrome@istalker.net>
 * @copyright 	2006 - 2009 Avice De'vereux
 * @license 	http://www.opensource.org/licenses/mit-license.php MIT License
 * @version 	CVS:$Id$
 * @since 	1.2
 */

/**
 * wpiScripts
 * @uses $wp_scripts WP_scripts object (BackPress Scripts enqueue)
 * @since 1.2
 */
 	
class wpiScripts{
	
	const SEP = ',';
	
	public $wp_filter_id;
	
	public $tag = array();
	
	public $head = array();
	
	public $footer = array();
	
	public $path;
	
	public $js;
	
	
	public function __construct()
	{
		$this->path = WPI_JS_DIR;
	}
	
	public function setScripts()
	{
		//$this->register('jquery','head');
		
		wp_deregister_script('jquery');
		wp_deregister_script('jquery-ui');		
	}
	
	public function register($tag = 'jquery', $type = 'head')
	{	
		$prop = array();
		if (file_exists(WPI_JS_DIR.$tag.'.js')){
			
			switch ($type){
				case 'head':
					$this->head[] = $tag;
				break;
				case 'footer':
					$this->footer[] = $tag;
				break;
				default:
					$this->tag[] = $tag;
				break;
			}				
			
		} else {
			return false;
		}
	}
	
	public function flushJs()
	{
		$this->js = null;
	}
	
	public function getScripts($type = 'tag')
	{
		$this->flushJs();
		if (has_count($this->$type)){
			
			$this->$type = array_unique($this->$type);
						
			$this->js = join(self::SEP,$this->$type);
						
		}
	}
	
	public function printHead()
	{	global $wp_query;
		
		$this->type = 'head';
		
		$this->flushJs();
		
		$this->setExtraJS();
		
		if (has_count($this->head)){
			
			$this->head = array_unique($this->head);
						
			$this->js = join(self::SEP,$this->head);
						
		}
		
		/**
		 * http://ajax.googleapis.com
		 * @link http://code.google.com/apis/ajaxlibs/documentation/index.html#jquery AJAX Libraries API 
		 * takes load of server and increases the chance that a client already has these files cached
		 */
		echo PHP_T;		
		t('script','',array(
					'id'=>'googleapis-jquery',
					'type'=>'text/javascript',					
					'src'=> 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.1/jquery.min.js',
					'charset'=>'utf-8') );					
		if ($wp_query->is_singular){
		echo PHP_T;
		t('script','',array(
					'id'=>'googleapis-jquery-ui',
					'type'=>'text/javascript',					
					'src'=> 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.5.3/jquery-ui.min.js',
					'charset'=>'utf-8') );
		}
		
		/**
	 	 * combine scripts 
		 */		
		$this->printScripts();			
	}
	
	
	public function printFooter()
	{
		$this->type = 'footer';
		
		$this->flushJs();
		
		if (has_count($this->footer)){
			
			$this->head = array_unique($this->footer);
						
			$this->js = join(self::SEP,$this->footer);
						
		}
		
		$this->printScripts();	
	}
	
	
	public function printScripts()
	{
		echo PHP_EOL.PHP_T;								
		t('script','',array(
					'id'=>'wpi-js-'.$this->type,
					'type'=>'text/javascript',					
					'src'=> wpi_theme_content_url($this->js,'.js'),
					'charset'=>'utf-8') );
	}
	
	public function getHeaderScripts(){
		global $wp_query;
		
		$this->getScripts('head');			
		$this->printScripts();
	}
	
	
	public function setExtraJS()
	{	global $wp_query;
	
		$js = array();
		
		if ($wp_query->is_singular){
			
		 	//$js['jquery-ui'] = 'head';
		 	
		 	if (wpi_option('widget_dynacloud')){
				$js['dynacloud'] = 'head';				
			}
			
			
		}	
		
		
		if (has_count($js)){
			foreach($js as $tag => $section) $this->register($tag,$section);
		}	
		
		unset($js);
	}
	
	public function embedScript()
	{	global $wp_query;	
		
		$defer = false;
		
		// execute after page load
		$domready = array();
		
		// default script prop attributes 		
		$attribs = array('id'=>'wp-js-head-embed','type'=>'text/javascript','defer'=>'defer','charset'=>'utf-8');
		
		list($lang, $locale) = explode('-',get_bloginfo('language'));
		
		// post id (singular section only)
		$pid = (isset($wp_query->post->ID)) ? $wp_query->post->ID : 0;
		$section = is_at();
		
				
		$js = PHP_EOL.PHP_T;
		$js .= '/*<![CDATA[*/'.PHP_EOL.PHP_T.PHP_T;	
		$js .= 'var wpi = {url:'.json_encode(WPI_URL_SLASHIT);
		$js .= ',home_url:'.json_encode(WPI_HOME_URL_SLASHIT);
		$js .= ',id:'.json_encode(wpiTemplate::bodyID());
		$js .= ',blogname:'.json_encode(WPI_BLOG_NAME);
		$js .= ',theme_url:'.json_encode(WPI_THEME_URL);
		$js .= ',lang:{ search:'.json_encode(__('search',WPI_META)).'}';
		$js .= ',section:'.json_encode($section);
		$js .= ',widget:{uri:'.json_encode(wpi_get_public_widget_url());
		$js .= ',request:[]';		
		$js .=',keywords:'.json_encode(wpi_option('widget_dynacloud') ? true : false);
		$js .= ',flickrrss:'.json_encode(wpi_option('widget_dynacloud') ? true : false);
		$js .= '}';
		$js .= ',permalink:'.json_encode(trailingslashit(self_uri()));
		
		$jspath = json_encode(rel(WPI_THEME_URL.'public/scripts/') );
		$jsurl  = json_encode(wpi_get_scripts_url('%s'));	
		
		$js .= ',script:{path:'.$jspath.',url:'.$jsurl.'}';
		
		if ( wpi_option('client_width')){
			$js .= ',cl_width: function(){if (window.innerWidth){ return window.innerWidth;}else if (document.documentElement && document.documentElement.clientWidth != 0){return document.documentElement.clientWidth;} else if (document.body){return document.body.clientWidth;} return 0;}';
			
			$domready[] = '/* Client width */ if( jQuery(wpi.bid).hasClass(wpi.clw) == false){ jQuery(wpi.bid).addClass(wpi.clw);};';
			$defer = 1;	
		}
		
		if (wpi_option('client_time_styles')){
			$js .= ',pid:'.$pid.',cl_type:td};'.PHP_EOL;
			$js .= PHP_T.PHP_T.'jQuery.cookie(\''.wpiTheme::CL_COOKIE_TIME.'\',wpi.cl_type,{duration: 1/24,path: "/"});'.PHP_EOL;
			$domready[] = '/* Client time	*/ if( jQuery(wpi.bid).hasClass(wpi.cl_type) == false){ jQuery(wpi.bid).removeClass(\'dw dy dk nt\');jQuery(wpi.bid).addClass(wpi.cl_type);};';
		} else {
			$js .= ',pid:'.$pid.'};'.PHP_EOL;
		}
		
		if ( wpi_option('client_width')){
			$js .= PHP_T.PHP_T.'wpi.clw = \'cl-width-\'+ wpi.cl_width();jQuery.cookie(\''.wpiTheme::CL_COOKIE_WIDTH.'\',wpi.clw,{duration: 1/24,path: "/"});'.PHP_EOL;
		}	
		
		// load custom widget (ajax response)
		$js .= PHP_T.PHP_T.'wpi.getWidget = function(wid,elm){if (jQuery(elm).length >= 1){jQuery.get(wpi.widget.uri.replace(/%s/, wid),function(data){jQuery(elm).replaceWith(data); wpi.rysncf(elm);});};};'.PHP_EOL;
		
		/**
		 * Restore WPI's func
		 */
		$js .= PHP_T.PHP_T.'wpi.rysncf = function(elm){if(\'home\' == wpi.section) { wpi.gridSidebarFilter(2,3);};jQuery(elm+\' .widget-title\').click(function(){wpi.toggle(jQuery(this).next());});};'.PHP_EOL;
		
		if (wpi_option('iframe_breaker') && !$wp_query->is_preview){
			$js .= PHP_T.PHP_T.'if(top.location!=location){top.location.href=document.location.href;};'.PHP_EOL;
			$defer = 1;
		}
		
		if (wpi_option('overwrite_flickrrss') && $section == 'home'){	
			$domready[] = '/* flickr RSS	*/ wpi.widget.request.push({"n":"#flickrrss","c":\'df695b32187596617d0beaa25760a8a0\'});';			
			$defer = 1;
		}
		
		if (wpi_option('overwrite_recent_comments') & $section == 'home'){	
			$domready[] = '/* recent comments	*/ wpi.widget.request.push({"n":"#recent-comments","c":\'b47bdb6bde262b0537f6f2a7fbfe825f\'});';			
			$defer = 1;
		}
		
		// gd blogname
		if (wpi_option('gd_blogname')){
			$domready[] = '/* GD Blog Title		*/ jQuery(\'#blog-title a\').css(\'backgroundImage\', \'url(\'+wpi.home_url + \'wpi-public/webfont,blog-name/)\');';	
			$defer = 1;	
		}								
		
		if ($defer) unset($attribs['defer']);
		
		if (has_count($domready)){
			$domready = apply_filters(wpiFilter::FILTER_JS_DOM_READY,$domready);
			
			$js .= PHP_T.PHP_T.'jQuery(document).ready(function(){ '.PHP_EOL;
			$js .= stab(1).'wpi.bid = document.body; '.PHP_EOL.stab(1).join(PHP_EOL.stab(1),$domready).PHP_EOL;
			$js .= stab(1).'if(wpi.widget.request.length >= 0){jQuery.each(wpi.widget.request, function(index, v){ wpi.getWidget(v.c,v.n);});}'.PHP_EOL;
			$js .= PHP_T.PHP_T.'});'.PHP_EOL;
		}
		
		// google webmaster 404 widget		
		if ($wp_query->is_search || $wp_query->is_404){		
			$js .= PHP_T.PHP_T.'var GOOG_FIXURL_LANG = \''.$lang.'\';var GOOG_FIXURL_SITE = wpi.url;'.PHP_EOL;
		}
		
		$js .= PHP_T.'/*]]>*/'.PHP_EOL.PHP_T;
				
		echo PHP_T;		
		
		t('script',$js,$attribs);
	}
}