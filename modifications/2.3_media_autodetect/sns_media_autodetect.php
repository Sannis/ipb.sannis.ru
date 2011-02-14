<?php

/**
* (SnS) Media autodetect
* for IP.Board 2.3.x
*
* @author    Oleg "Sannis" Efimov
* @copyright 2010 Sannis
* @link      http://sannis.ru
*
* Based on 2.1 mod http://forums.ibresource.ru/index.php?/topic/53449/
* @author    Alex "Arhar" Baranov
* @copyright 2009 Arhar
* @link      http://sannis.ru
*/

if ( !defined('IN_IPB') )
{
	print "<h1>Ошибка</h1> У Вас нет прямого доступа к этому файлу.";
	exit();
}

/**
* Media autodetect mod library class
*/
class media_autodetect
{
	# Global
	var $ipsclass;
	
	/**
	* Detect media from URL
	*
	* @return string HTML code of video object
	*/
	function detect_from_url($url)
	{
		$media_html = '';
		
		if( preg_match("#^http://rutube\.ru/tracks/(.+?)\.html\?v=(.+?)$#i", $url, $m) )
		{
			$media_html = '<object width="480px" height="360px"><param name="movie" value="http://video.rutube.ru/'.$m[2].'"></param>'.
					'<param name="wmode" value="window"></param><param name="allowfullscreen" value="true"></param>'.
					'<param name="allowscriptaccess" value="never">'.
					'<embed src="http://video.rutube.ru/'.$m[2].'" type="application/x-shockwave-flash" wmode="window" width="480px" height="360px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://video\.mail\.ru/(.+?)/(.+?)/(.+?)/(\d+)\.html.*$#i", $url, $m) )
		{
			$media_html = '<object width="480px" height="360px"><param name="flashvars" '.
					'value="imaginehost=video.mail.ru&perlhost=video.mail.ru&alias='.$m[1].'&username='.$m[2].'&albumid='.$m[3].'&id='.$m[4].'&catalogurl=http://video.mail.ru/catalog/misc/&tagurl=" />'.
					'<param name="allowscriptaccess" value="never" />'.
					'<param name="movie" value="http://img.mail.ru/r/video2/player_v2.swf?par=http://content.video.mail.ru/'.$m[1].'/'.$m[2].'/'.$m[3].'/$'.$m[4].'$0$0" />'.
					'<embed src="http://img.mail.ru/r/video2/player_v2.swf?par=http://content.video.mail.ru/'.$m[1].'/'.$m[2].'/'.$m[3].'/$'.$m[4].'$0$0" '.
					'type="application/x-shockwave-flash" width="480px" height="360px" '.
					'flashvars="imaginehost=video.mail.ru&perlhost=video.mail.ru&alias='.$m[1].'&username='.$m[2].'&albumid='.$m[3].'&id='.$m[4].'&catalogurl=http://video.mail.ru/catalog/misc/" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://smotri\.com/video/view/\?id=(.+?)$#i", $url, $m) )
		{
			$media_html = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="400px" height="330px">'.
					'<param name="movie" value="http://pics.smotri.com/scrubber_custom8.swf?file='.$m[1].'&bufferTime=3&autoStart=false&str_lang=eng&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color_lightaqua.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml" />'.
					'<param name="allowscriptaccess" value="never" /><param name="allowfullscreen" value="true" />'.
					'<param name="bgcolor" value="#ffffff" />'.
					'<embed src="http://pics.smotri.com/scrubber_custom8.swf?file='.$m[1].'&bufferTime=3&autoStart=false&str_lang=eng&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color_lightaqua.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml" '.
					'quality="high" type="application/x-shockwave-flash" wmode="window" width="400px" height="330px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://repka\.tv/video/(\d+?)[/]{0,1}$#i", $url, $m) )
		{
			$media_html = '<object width="538px" height="404px"><param name="movie" value="http://repka.tv/class/videoPage/p.swf"></param>'.
					'<param name="wmode" value="transparent"></param><param name="flashvars" value="url=http://repka.tv/video/get/'.$m[1].'/"></param>'.
					'<param name="allowscriptaccess" value="never" /><param name="allowfullscreen" value="true" />'.
					'<embed src="http://repka.tv/class/videoPage/p.swf" flashvars="url=http://repka.tv/video/get/'.$m[1].'/" '.
					'quality="high"  type="application/x-shockwave-flash" wmode="window" width="538px" height="404px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://(?:[a-z]{2,3}\.|)youtube\.com/watch\?v=(.+?)$#i", $url, $m)
		 or preg_match("#^http://(?:[a-z]{2,3}\.|)youtube\.com/v/(.+?)$#i", $url, $m) )
		{
			$media_html = '<object width="640px" height="385px"><param name="movie" value="http://www.youtube.com/v/'.$m[1].'&fs=1"></param>'.
					'<param name="wmode" value="transparent"></param><param name="allowfullscreen" value="true"></param><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://www.youtube.com/v/'.$m[1].'&fs=1" '.
					'type="application/x-shockwave-flash" wmode="window" width="640px" height="385px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if (preg_match("#^http://(?:www.|)vimeo\.com/(.+?)$#i", $url, $m))
		{
			$media_html = '<object type="application/x-shockwave-flash" width="640px" height="360px" data="http://www.vimeo.com/moogaloop.swf?clip_id='.$m[1].'">'.
					'<param name="quality" value="best" /><param name="allowfullscreen" value="true" /><param name="scale" value="showAll" />'.
					'<param name="movie" value="http://www.vimeo.com/moogaloop.swf?clip_id='.$m[1].'" />'.
					'<embed src="http://www.vimeo.com/moogaloop.swf?clip_id='.$m[1].'" '.
					'type="application/x-shockwave-flash" wmode="window" width="640px" height="360px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://ukrtube\.com\.ua/media/(\d+?)[/]{0,1}$#i", $url, $m) )
		{
			$media_html = '<object width="468px" height="385px"><param name="movie" value="http://ukrtube.com.ua/emb/'.$m[1].'/"></param>'.
					'<param name="wmode" value="transparent"></param><param name="flashvars" value=""></param><param name="allowScriptAccess" value="never" />'.
					'<embed src="http://ukrtube.com.ua/emb/'.$m[1].'/" type="application/x-shockwave-flash" wmode="window" width="468px" height="385px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://video\.bigmir\.net/show/(\d+?)[/]{0,1}$#i", $url, $m) )
		{
			$media_html = '<object width="625px" height="395px"><param name="movie" value="http://video.bigmir.net/extplayer/'.$m[1].'/"></param>'.
					'<param name="wmode" value="transparent"></param><param name="flashvars" value=""></param><param name="allowScriptAccess" value="never" />'.
					'<embed src="http://video.bigmir.net/extplayer/'.$m[1].'/" '.
					'type="application/x-shockwave-flash" wmode="window" width="625px" height="395px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://video\.online\.ua/(\d+?)[/]{0,1}$#i", $url, $m) )
		{
			$media_html = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="640px" height="400px"><param name="movie" value="http://i.online.ua/mplayer/player_logo.swf?file=http://video.online.ua/playlist/'.$m[1].'.xml&autoStart=false&str_lang=rus&xmldatasource=http://video.online.ua/playlist/'.$m[1].'.xml"></param>'.
					'<param name="wmode" value="transparent"></param><param name="flashvars" value=""></param><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://i.online.ua/mplayer/player_logo.swf?width=640&height=400&file=http://video.online.ua/playlist/'.$m[1].'.xml&autostart=false&javascriptid=media_player&enablejs=true" '.
					'type="application/x-shockwave-flash" wmode="window" width="640px" height="400px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://www\.u-tube\.ru/pages/video/(\d+?)[/]{0,1}$#i", $url, $m) )
		{
			$media_html = '<object width="400px" height="300px"><param name="movie" value="http://www.u-tube.ru/upload/others/flvplayer.swf"></param><param name="flashvars" value="file=http://www.u-tube.ru/playlist.php?id='.$m[1].'&width=400&height=300"></param>'.
					'<param name="wmode" value="transparent"></param><param name="flashvars" value="file=http://www.u-tube.ru/playlist.php?id='.$m[1].'&width=400&height=300"></param><param name="allowScriptAccess" value="never" />'.
					'<embed src="http://www.u-tube.ru/upload/others/flvplayer.swf" flashvars="file=http://www.u-tube.ru/playlist.php?id='.$m[1].'&width=400&height=300" '.
					'type="application/x-shockwave-flash" wmode="window" width="400px" height="300px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://kiwi\.kz/watch/watch/(.+?)$#i", $url, $m)
		 or preg_match("#^http://kiwi\.kz/watch/(.+?)$#i", $url, $m) )
		{
			$media_html = '<object width="450px" height="340px"><param name="movie" value="http://v.kiwi.kz/v/'.$m[1].'&fs=1"></param>'.
					'<param name="allowfullscreen" value="true"></param></param><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://v.kiwi.kz/v/'.$m[1].'" '.
					'type="application/x-shockwave-flash" wmode="window" width="450px" height="340px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://www\.dailymotion\.com/video/([^_]+?)(?:_.+?)$#i", $url, $m) )
		{
			$media_html = '<object width="480px" height="389px"><param name="movie" value="http://www.dailymotion.com/swf/'.$m[1].'"></param>'.
					'<param name="wmode" value="window" /><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://www.dailymotion.com/swf/'.$m[1].'" '.
					'type="application/x-shockwave-flash" wmode="window" width="480px" height="389px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://www\.liveleak\.com/view\?i=([^_]+?_\d+?)$#i", $url, $m) )
		{
			$media_html = '<object width="450px" height="370px"><param name="movie" value="http://www.liveleak.com/e/'.$m[1].'" />'.
					'<param name="wmode" value="window" /><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://www.liveleak.com/e/'.$m[1].'" '.
					'type="application/x-shockwave-flash" wmode="window" width="450px" height="370px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://www\.veoh\.com/(?:[^/]+?)/(?:[^/]+?)/watch/(.+?)$#i", $url, $m)
		 or preg_match("#^http://www\.veoh\.com/(?:[^/]+?)/(?:[^/]+?)/(?:[^/]+?)/(?:[^/]+?)/watch/(.+?)$#i", $url, $m) )
		{
			$media_html = '<object width="410px" height="341px"><param name="movie" value="http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.4.9.1004&permalinkId='.$m[1].'&player=videodetailsembedded&videoAutoPlay=0&id=anonymous" />'.
					'<param name="wmode" value="window" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.4.9.1004&permalinkId='.$m[1].'&player=videodetailsembedded&videoAutoPlay=0&id=anonymous" '.
					'type="application/x-shockwave-flash" wmode="window" width="410px" height="341px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://video\.yahoo\.com/watch/(\d+?)/(\d+?)[/]{0,1}$#i", $url, $m) )
		{
			$media_html = '<object width="512px" height="322px"><param name="movie" value="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" />'.
					'<param name="flashVars" value="id='.$m[2].'&vid='.$m[1].'&embed=1" />'.
					'<param name="wmode" value="window" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" flashvars="id='.$m[2].'&vid='.$m[1].'&embed=1"'.
					'type="application/x-shockwave-flash" wmode="window" width="512px" height="322px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://www\.gametrailers\.com/video/(?:[^/]+?)/(\d+?)(?:\?.+?)?$#i", $url, $m) )
		{
			$media_html = '<object width="480px" height="392px"><param name="movie" value="http://www.gametrailers.com/remote_wrap.php?mid='.$m[1].'" />'.
					'<param name="wmode" value="window" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://www.gametrailers.com/remote_wrap.php?mid='.$m[1].'" '.
					'type="application/x-shockwave-flash" quality="high" wmode="window" width="480px" height="392px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://video\.google\.com/videoplay\?docid=(.+?)\#?$#i", $url, $m) )
		{
			$media_html = '<object width="400px" height="326px"><param name="movie" value="http://video.google.com/googleplayer.swf?docid='.$m[1].'&fs=true" />'.
					'<param name="wmode" value="window" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://video.google.com/googleplayer.swf?docid='.$m[1].'&fs=true" '.
					'type="application/x-shockwave-flash" quality="high" wmode="window" width="400px" height="326px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://vids\.myspace\.com/index\.cfm\?fuseaction=.+?&amp;videoid=(\d+?)\#?$#i", $url, $m) )
		{
			$media_html = '<object width="425px" height="360px" ><param name="movie" value="http://mediaservices.myspace.com/services/media/embed.aspx/m='.$m[1].',t=1,mt=video"/>'.
					'<param name="wmode" value="window"/><param name="allowfullscreen" value="true"/><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://mediaservices.myspace.com/services/media/embed.aspx/m='.$m[1].',t=1,mt=video"'.
					'type="application/x-shockwave-flash" wmode="transparent" width="425px" height="360px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://www\.myvideo\.de/watch/(\d+?)/.*$#i", $url, $m) )
		{
			$media_html = '<object width="470px" height="285px" ><param name="movie" value="http://www.myvideo.de/movie/'.$m[1].'"/>'.
					'<param name="wmode" value="window"/><param name="allowfullscreen" value="true"/><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://www.myvideo.de/movie/'.$m[1].'"'.
					'type="application/x-shockwave-flash" wmode="transparent" width="470px" height="285px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( preg_match("#^http://play\.ukr\.net/videos/show/key/(.+?)/?$#i", $url, $m) )
		{
			$media_html = '<object width="585px" height="345px" ><param name="movie" value="http://play.ukr.net/player.swf?key=key/'.$m[1].'"/>'.
					'<param name="wmode" value="window"/><param name="allowfullscreen" value="true"/><param name="allowscriptaccess" value="never" />'.
					'<embed src="http://play.ukr.net/player.swf?key=key/'.$m[1].'"'.
					'type="application/x-shockwave-flash" wmode="transparent" width="585px" height="345px" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
		}
		
		if( $media_html == '' ) {
			return $url;
		}
		
		return "<!--media_s:".base64_encode($url)."-->".$media_html."<!--media_e-->";
	}
	
	/**
	* Detect media from tag content.
	* This may be URL or 'HTML code for blogs'
	*
	* @return string HTML code of video object
	*/
	function detect_from_tag($matches=array())
	{
		//---------------
		// Check for URL
		//---------------
				
		$media_html = $this->detect_from_url($matches[1]);
		
		if( $media_html != $matches[1] )
		{
			return $media_html;
		}
		
		$original_code = $matches[1];
		
		//----------------
		// Check for code
		//----------------
		
		// Find custom Youtube video
		$matches[1] = preg_replace_callback( "/&lt;object width=&quot;(\d+?)&quot; height=&quot;(\d+?)&quot;(.+?)&lt;param name=&quot;movie&quot; value=&quot;http:\/\/www\.youtube([\-nocookie]*?)\.com\/v\/(.+?)&quot;&gt;(.+?)&lt;\/object&gt;/is", array(&$this,"media_detect_youtube"), $matches[1]);
		
		// Find Yandex video
		$matches[1] = preg_replace_callback( "/&lt;object width=&quot;(\d+?)&quot; height=&quot;(\d+?)&quot;(?:.+?)&lt;param name=&quot;video&quot; value=&quot;http:\/\/(.+?)\.video\.yandex.ru\/lite\/([^\/]+?)\/([^\/]+?)[\/]{0,1}&quot;(?:.+?)&lt;\/object&gt;/is", array(&$this,"media_detect_yandex_video"), $matches[1]);
		
		// Find Repka.tv video
		$matches[1] = preg_replace_callback( "/&lt;embed(?:.+?)flashvars=&quot;url=http:\/\/repka\.tv\/externalPlayer\/([^\/]+?\/[^\/]+?\/[^\/]+?\/[^\/]+?\/[^\/]+?)&quot;(?:.+?)width=&quot;(\d+?)&quot; height=&quot;(\d+?)&quot;(?:.+?)&lt;\/embed&gt;/is", array(&$this,"media_detect_repkatv"), $matches[1]);
		
		// Find Vkadre video
		// FIXME
		$matches[1] = preg_replace( "/&lt;object (.+?)&lt;param name=&quot;movie&quot; value=&quot;http:\/\/vkadre\.ru\/swf\/VkadrePlayer\.swf\?1&quot; \/&gt;(.+?)&lt;param name=&quot;flashvars&quot; value=&quot;(.+?)&quot; \/&gt;(.+?)&lt;\/object&gt;/ies", "\$this->media_detect_vkadre(\"\\3\")", $matches[1]);
		
		// Find MySpace video
		$matches[1] = preg_replace_callback( "/^(?:.+?)&lt;object width=&quot;(\d+?px)&quot; height=&quot;(\d+?px)&quot;(?:.+?)&lt;param name=&quot;movie&quot; value=&quot;http:\/\/mediaservices\.myspace\.com\/services\/media\/embed\.aspx\/m=(\d+)(?:.+?)&quot;\/&gt;(?:.+?)&lt;\/object&gt;(?:.+?)$/ism", array(&$this,"media_detect_myspace_video"), $matches[1]);
		
		// Find VKontakte video
		$matches[1] = preg_replace_callback( "/^\&lt;iframe src=\&quot;http:\/\/(vkontakte\.ru|vk\.com)\/video_ext\.php\?oid=(\d+?)\&amp;id=(\d+?)\&amp;hash=([a-z\d]+?)\&quot; width=\&quot;(\d+?)\&quot; height=\&quot;(\d+?)\&quot;(?:.+?)\&gt;\&lt;\/iframe\&gt;$/is", array(&$this,"media_detect_vkontakte_video"), $matches[1]);
		
		// Find PlayUkrNet video
		$matches[1] = preg_replace_callback( "/&lt;object(?:.+?)param value=&#39;(?:.+?)key\/(.+?)&#39; name=&#39;movie&#39;(?:.+?)&lt;\/object&gt;/is", array(&$this,"media_detect_play_ukr_net"), $matches[1]);
		
		return "<!--media_s:".base64_encode($original_code)."-->".$matches[1]."<!--media_e-->";
	}
	
	function media_detect_youtube($vars)
	{
		$movie = str_replace("&amp;amp;", "&amp;", stripslashes($vars[5]));
		$nocookie = ($vars[4] ==' -nocookie') ? '-nocookie' : '';
		
		return '<object width="'.$vars[1].'" height="'.$vars[2].'"><param name="movie" value="http://www.youtube'.$nocookie.'.com/v/'.$movie.'"/>'.
			'<param name="allowscriptaccess" value="never"/><param name="allowfullscreen" value="true"/>'.
			'<embed src="http://www.youtube'.$nocookie.'.com/v/'.$movie.'" '.
			'type="application/x-shockwave-flash" allowscriptaccess="never" allowfullscreen="true" width="'.$vars[1].'" height="'.$vars[2].'"></embed></object>';
	}
	
	function media_detect_yandex_video($vars)
	{
		return '<object width="'.$vars[1].'" height="'.$vars[2].'"><param name="video" value="http://'.$vars[3].'.video.yandex.ru/lite/'.$vars[4].'/'.$vars[5].'/"/>'.
			'<param name="allowscriptaccess" value="never"/><param name="allowfullscreen" value="true"/><param name="scale" value="noscale"/>'.
			'<embed src="http://'.$vars[3].'.video.yandex.ru/lite/'.$vars[4].'/'.$vars[5].'/" '.
			'type="application/x-shockwave-flash" allowscriptaccess="never" allowfullscreen="true" scale="noscale" width="'.$vars[1].'" height="'.$vars[2].'"></embed></object>';
	}
	
	function media_detect_repkatv($vars)
	{
		return '<object width="'.$vars[2].'" height="'.$vars[3].'"><param name="movie" value="http://repka.tv/externalPlayer/p2.swf" /><param name="flashvars" value="url=http://repka.tv/externalPlayer/'.$vars[1].'" />'.
			'<param name="allowscriptaccess" value="never"/><param name="allowfullscreen" value="true"/><param name="quality" value="high"/>'.
			'<embed src="http://repka.tv/externalPlayer/p2.swf" flashvars="url=http://repka.tv/externalPlayer/'.$vars[1].'" '.
			'type="application/x-shockwave-flash" quality="high" wmode="window" allowfullscreen="true" allowscriptaccess="never" width="'.$vars[2].'" height="'.$vars[3].'"/></embed>';
	}
	
	function media_detect_vkadre($flashvars)
	{
		$flashvars = str_replace("&amp;amp;", "&amp;", stripslashes($flashvars));
		
		return '<object width="460" height="345" ><param name="movie" value="http://vkadre.ru/swf/VkadrePlayer.swf?1" /><param name="flashvars" value="'.$flashvars.'" />'.
			'<param name="allowscriptaccess" value="never"/><param name="allowfullscreen" value="true"/>'.
			'<embed src="http://vkadre.ru/swf/VkadrePlayer.swf?1" flashvars="'.$flashvars.'" '.
			'type="application/x-shockwave-flash" allowscriptaccess="never" allowfullscreen="true" width="460" height="345"/></object>';
	}
	
	function media_detect_myspace_video($vars)
	{
		return '<object width="'.$vars[1].'" height="'.$vars[2].'" ><param name="movie" value="http://mediaservices.myspace.com/services/media/embed.aspx/m='.$vars[3].',t=1,mt=video"/>'.
			'<param name="wmode" value="window"/><param name="allowfullscreen" value="true"/><param name="allowscriptaccess" value="never" />'.
			'<embed src="http://mediaservices.myspace.com/services/media/embed.aspx/m='.$vars[3].',t=1,mt=video"'.
			'type="application/x-shockwave-flash" wmode="transparent" width="'.$vars[1].'" height="'.$vars[2].'" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
	}
	
	function media_detect_play_ukr_net($vars)
	{
		return '<object width="585" height="345" ><param name="movie" value="http://play.ukr.net/player.swf?key=key/'.$vars[1].'"/>'.
			'<param name="wmode" value="window"/><param name="allowfullscreen" value="true"/><param name="allowscriptaccess" value="never" />'.
			'<embed src="http://play.ukr.net/player.swf?key=key/'.$vars[1].'"'.
			'type="application/x-shockwave-flash" wmode="transparent" width="585" height="345" allowfullscreen="true" allowscriptaccess="never"></embed></object>';
	}
	
	function media_detect_vkontakte_video($vars)
	{
		return '<iframe src="http://'.$vars[1].'/video_ext.php?oid='.$vars[2].'&id='.$vars[3].'&hash='.$vars[4].'" width="'.$vars[5].'" height="'.$vars[6].'" frameborder="0"></iframe>';
	}
}

?>
