<?php
/* 
Plugin Name: Kawaii AddThis
Plugin URI: http://www.salkodev.com/
Version: v1.00
Author: <a href="http://www.salkodev.com/">Andrew Salko</a>
Description: A helper AddThis plugin for a <a href="http://kawaii-mobile.com">http://kawaii-mobile.com</a>
*/

if (!class_exists("KawaiiAddThis")) 
{
	class KawaiiAddThis
	{

		function KawaiiAddThis() 
		{ //constructor
			
		}

		public static function GetScript()
		{
			$addContent='';	

			$addContent .= '<div class="addthis_toolbox addthis_floating_style addthis_32x32_style" style="left:10px; top:300px; background: transparent !important;">';
			$addContent .= '<a class="addthis_button_google_plusone_share"></a>';
			$addContent .= '<a class="addthis_button_facebook"></a>';
			$addContent .= '<a class="addthis_button_twitter"></a>';
			$addContent .= '<a class="addthis_button_pinterest_share"></a>';
			$addContent .= '<a class="addthis_button_linkedin"></a>';
			$addContent .= '<a class="addthis_button_favorites"></a>';
			$addContent .= '<a class="addthis_counter addthis_bubble_style"></a></div>';

			$addContent .= '<script type="text/javascript">var addthis_config = { "data_track_addressbar": false };</script>';
			$addContent .= '<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js#pubid=ra-52ac5aea586b25eb"></script>';
	
			return $addContent;
		}		

		function do_wp_footer()
		{						
			echo KawaiiAddThis::GetScript();
		}


	}//class

	if (class_exists("KawaiiAddThis")) 
	{
		$pluginKawaiiAddThis = new KawaiiAddThis();
	}

} //End Class KawaiiAddThis

//Actions and Filters	
if (isset($pluginKawaiiAddThis)) 
{    			
	add_filter('wp_footer', array('KawaiiAddThis', 'do_wp_footer'),1);
}