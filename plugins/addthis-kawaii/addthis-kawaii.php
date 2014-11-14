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

		public static function GetInnerButtons()
		{
			$addContent='';
			$addContent .= '<a class="addthis_button_google_plusone_share"></a>';
			$addContent .= '<a class="addthis_button_facebook"></a>';
			$addContent .= '<a class="addthis_button_twitter"></a>';
			$addContent .= '<a class="addthis_button_pinterest_share"></a>';
			$addContent .= '<a class="addthis_button_tumblr"></a>';
			$addContent .= '<a class="addthis_button_linkedin"></a>';
			$addContent .= '<a class="addthis_button_favorites"></a>';
			$addContent .= '<a class="addthis_counter addthis_bubble_style"></a>';
			return $addContent;
		}

		public static function GetScript($standartButtons)
		{
			$addContent='';	

			if($standartButtons===TRUE)
			{
				//standart style, mobile or small window
				$addContent .= '<div class="addthis_toolbox addthis_default_style addthis_16x16_style kawaii_sharing_standart">';
			}
			else
			{
				//float style,for desktop
				$addContent .= '<div class="addthis_toolbox addthis_floating_style addthis_32x32_style kawaii_sharing_float">';
			}

			$addContent .= KawaiiAddThis::GetInnerButtons();
			$addContent .= '</div>';
			return $addContent;
		}

		function GetAddThisScriptLoader()
		{
			$addContent='';	
			$addContent .= '<script type="text/javascript">var addthis_config = { "data_track_addressbar": false };</script>';
			$addContent .= '<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js#pubid=ra-52ac5aea586b25eb"></script>';
			return $addContent;
		}

		function do_wp_footer()
		{	
			//add floating bar (vertical)					
			echo KawaiiAddThis::GetScript(FALSE);			
			echo KawaiiAddThis::GetAddThisScriptLoader();
		}

		function do_add_stylesheet()
		{		  
			wp_register_style( 'kawaiisharing', plugins_url('kawaiisharing.css', __FILE__) );
			wp_enqueue_style( 'kawaiisharing' );			
		}

		function do_content($content)
		{
			//add standart bar to content
			$addContent=KawaiiAddThis::GetScript(TRUE);

			return  $content . $addContent;
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

	add_action('wp_enqueue_scripts', array('KawaiiAddThis','do_add_stylesheet'), 1);

	add_filter('the_content', array('KawaiiAddThis', 'do_content'),1);
}