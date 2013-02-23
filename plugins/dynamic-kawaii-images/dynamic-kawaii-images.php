<?php
/* 
Plugin Name: Dynamic Kawaii Images
Plugin URI: http://www.salkodev.com/
Version: v1.00
Author: <a href="http://www.salkodev.com/">Andrew Salko</a>
Description: A helper plugin for a <a href="http://kawaii-mobile.com">http://kawaii-mobile.com</a>
*/

if (!class_exists("DynamicKawaiiImages")) 
{
	include ('kawaii-resolution.php');
	include ('encryptor-kawaii.php');
	include ('simpleimage.php');

	class DynamicKawaiiImages
	{

		function DynamicKawaiiImages() 
		{ //constructor
			
		}

		public static function SendBaseImageHeaders($fileName)
		{
			header("HTTP/1.0 200 OK");
			$timeOffset = 60 * 60;//1 hour
			header("Expires: " . gmdate("D, d M Y H:i:s", time() + $timeOffset) . " GMT");
			header("Cache-Control: max-age=".$timeOffset.", must-revalidate"); 
			header("Pragma:");

			$fileExt=pathinfo($fileName, PATHINFO_EXTENSION);
			if($fileExt=='jpg' || $fileExt=='jpeg')
			{
				header("Content-Type: image/jpeg");
			}

			if($fileExt=='png')
			{
				header("Content-Type: image/png");
			}
		}

		public static function GetContentType($fileName)
		{
			$fileExt=pathinfo($fileName, PATHINFO_EXTENSION);
			if($fileExt=='jpg' || $fileExt=='jpeg')
			{
				return 'Content-Type: image/jpeg';				
			}

			if($fileExt=='png')
			{
				return 'Content-Type: image/png';				
			}	
			return '';				
		}

		// Save file to cache-dir and output it to browser.
		// The $image object will be cleaned.
		public static function OutputFileToBrowser($image, $fileNameForSave)
		{
			$fileExt=pathinfo($fileNameForSave, PATHINFO_EXTENSION);
			if($fileExt=='jpg' || $fileExt=='jpeg')
			{
				$image->save($fileNameForSave, IMAGETYPE_JPEG, 85);
			}

			if($fileExt=='png')
			{
				$image->save($fileNameForSave, IMAGETYPE_PNG);
			}	
        	
			$image->free();

        	//вывод в поток браузера того что сохранилось:
			ob_end_clean();
			readfile($fileNameForSave);
			flush(); 			
	        exit();			
		}		

		// Creates fake attach file name from real post ID
		// Returns FALSE if failed
		// Out params:
		public static function CreateImageElement($imageID, $resolution, &$shortDescription)
		{
			$imgURL=wp_get_attachment_url($imageID);

			//full URL to attach page
			$postPermLink=post_permalink($imageID);			
			$fileName = basename($imgURL);

			//parts of file name..we need it later
			//Madlax.Margaret-Burton.Elenore-Baker.Madlax-HTC-Cha-Cha-wallpaper.Vanessa-Rene.320x480.jpg
			$nameParts=explode('.', $fileName);			
			$shortFileName=$nameParts[0];

			$namePartsCount=count($nameParts);
			if($namePartsCount>2)
			{
				$shortFileName='';
				//если можно, отрежем часть с разрешением
				for($q=0; $q<$namePartsCount-2; $q++)
				{
					if(strlen($shortFileName)>0)
					{
						$shortFileName.= '.';
					}

					$shortFileName.=$nameParts[$q];					
				}				
			}
			
			$fileExt=end($nameParts);//extension (jpg)

			$fileNameGood='kawaii-mobile.com.'.$shortFileName.'.'.$resolution.'.'.$fileExt;

			$nowTime = (string)time();
			$encryptor=new EncryptorKawaii();
			$secretCode=$encryptor->Encode($nowTime);
            			
			$imgLink=$postPermLink.'custom/'.$fileNameGood.'?newsize='.$resolution.'&amp;code='.$secretCode.'&amp;id='.$imageID;

			$resParts=explode('x',$resolution);
			if(count($resParts)!=2)
			{
				return FALSE;
			}

			$imgWidth=$resParts[0];
			$imgHeight=$resParts[1];

			$resDetector=new KawaiiResolutionDetector();
			$linkNameCurrent=$resDetector->GetResolutionDescription($imgWidth, $imgHeight);

			//prepare ALT text for image. Just use fileName,replace '-' chars with spaces
			$fileNameForALT=str_replace('-', ' ', $shortFileName);

			$imgAlt=$fileNameForALT .' '. $linkNameCurrent;
			
			$imgNode='<a href="'.$imgLink.'"><img src="'. $imgLink .'" alt="'. $imgAlt .'" title="'. $imgAlt. '" width="'.$imgWidth.'" height="'.$imgHeight.'"></img></a>';

			$shortDescription=$imgAlt;

			return $imgNode;

		}//CreateImageElement


		function do_template_redirect()
		{	
			global $wp_query;

			/*					
		    if(is_404()===FALSE)
			{
				return;	
			}*/

			$url = $_SERVER['REQUEST_URI'];

						
			if (strpos($url,'/custom-image/') == true) 
			{												
				//here we extract attach ID from URL, and resolution:
				//custom-image/(attachID)/(320x240)
				$splittedValues=explode('/',$url);
				if(count($splittedValues)<3)
				{
					//assume 3 items at least:custom-image,attachID,320x240
					return;
				}

				//take last portion - this is resolution:
				$resolution=end($splittedValues);
				$attachID=prev($splittedValues);

				//check if we have such attachID in our base:
				$testPermLink=post_permalink($attachID);
				if($testPermLink===FALSE)
				{
					return;
				}

				$shortDescr;
				$imgNode=DynamicKawaiiImages::CreateImageElement($attachID, $resolution, $shortDescr);

				$wp_query->is_404 = false;
				status_header('200');

				get_header();
                
                $itPost=get_post($attachID);
				$parentPost=$itPost;
					
				echo '<a href="' . get_permalink( $parentPost ) . '">'. get_the_title($parentPost) .'</a>';
    			
				echo '<h1>Image size: ' . $resolution . '</h1>';     

				$resDetector=new KawaiiResolutionDetector();
				$mobilePhones=$resDetector->GetResolutionMobilePhones($resolution);
				if($mobilePhones!=NULL)
				{
					echo '<p>Wallpaper for mobile phones: ' . $mobilePhones. '</p>';
				}
	
				echo '<div id="container" class="single-attachment">';
				echo '	<div id="content" role="main">';
				echo $imgNode;
				echo '	</div><!-- #content -->';
				echo '</div><!-- #container -->';
				
				get_sidebar();
				get_footer();

				return;			
			}//if custom-image - special fake page
			

			//another check method for our special URL
			if (strpos($url,'/custom/') == false) 
			{
				return;
			}

			if(array_key_exists('newsize', $_GET)===FALSE ||
				array_key_exists('code', $_GET)===FALSE ||
				array_key_exists('id', $_GET)===FALSE
					)
			{
				return;
			}

			$code=$_GET['code'];
			$newsize=$_GET['newsize'];
			$imageID=$_GET['id'];			

			//--- Ѕлок защиты от пр€мой ссылки на генерируемое изображение			
			$nowTime = time();
			$encryptor=new EncryptorKawaii();
			$timeCode=(int)$encryptor->Decode($code);
			$timeDiff=$nowTime-$timeCode;
			//разница в секундах от сгенерированного кода 
			//при загрузке страницы и текущего времени. ћы
			//считаем что человек вр€дли будет "думать" более 2 часов,
			//поэтому тут будет так - если прошло более 2 часов теб€
			//редирект€т снова на страницу аттача.
			if($timeDiff>120*60)
			{
				//пробуем детектить пост-пермалинк, если дали
				//правильный ID изображени€
				$testPermLink=post_permalink($imageID);
				if($testPermLink===FALSE)
				{
					return;
				}

				header("location:".$testPermLink);
				return;
			}
			//--- (end)Ѕлок защиты от пр€мой ссылки на генерируемое изображение
			

			$imageCacheDirBase=WP_CONTENT_DIR . '/imagescache';
			//check this directory, if need - create it			
			if(! (is_dir($imageCacheDirBase) || mkdir($imageCacheDirBase)) )
			{
				return;
			}

			//post perma link looks like:
			//http://kawaii-mobile.org/2012/11/hagure-yuusha-no-estetica/aesthetica-of-a-rogue-hero-hagure-yuusha-no-estetica-miu-myuu-ousawa-haruka-nanase-320x480/
			$postPermLink=post_permalink($imageID);
			if($postPermLink===FALSE)
			{
				return;
			}

			//разделим их по слешам и выделим им€ поста, это будет им€ подпапки
			$urlParts=explode('/', $postPermLink);
			end($urlParts);//прыгнули в конец массива
			prev($urlParts);
			$realPostName=prev($urlParts);//и вз€ли пред-последнюю часть (hagure-yuusha-no-estetica)
            
			//это будет под-папка в кеше:
			$imageCacheDir=$imageCacheDirBase . '/' . $realPostName;
			//check this directory, if need - create it			
			if(! (is_dir($imageCacheDir) || mkdir($imageCacheDir)) )
			{
				return;
			}			

			//все файлы именуютс€ в стиле id_320x480 , чтобы их легче найти
			$filePrefix=$imageID.'_'.$newsize;

			$filesInCache = glob($imageCacheDir. '/'.$filePrefix.'*.*');
			if($filesInCache!=FALSE && is_array($filesInCache) && count($filesInCache)>0)
			{
				$resultFileName=$filesInCache[0];

				DynamicKawaiiImages::SendBaseImageHeaders($resultFileName);

				if(readfile($resultFileName)>0)
				{
        			die();
					return;
				}
			}
           
			$attMeta=wp_get_attachment_metadata($imageID);
            if($attMeta===FALSE)
			{
				return;
			}
					
			$attWidth=(int)$attMeta['width'];
			$attHeight=(int)$attMeta['height'];
			$attFileName=$attMeta['file'];
			$fileExt=pathinfo($attFileName, PATHINFO_EXTENSION);

			$contentType=DynamicKawaiiImages::GetContentType($attFileName);

			//parse new size:
			$destWidth=0;
			$destHeight=0;
			
			//$newsize
			$sizeParts=explode('x', $newsize);		
			$destWidth=(int)$sizeParts[0];
			$destHeight=(int)$sizeParts[1];
                        
			//check parameters (for bad users)
		
			$resDetector=new KawaiiResolutionDetector();
			if($resDetector->IsResolutionAvailable($attWidth, $attHeight, $destWidth, $destHeight)==FALSE)
			{
				//if we have un-supported resolution, redirect to attach post.
				//this possible if 'smart' users want from us something...
				header("location:".$postPermLink);
				return;
			}

			$upload_dir = wp_upload_dir();
			$fullFileName=$upload_dir['basedir'] . '/' . $attFileName;

			$image = new SimpleImage();
			$image->load($fullFileName);

			DynamicKawaiiImages::SendBaseImageHeaders($attFileName);

			//header("HTTP/1.0 200 OK");
			//header($contentType);

			//тут им€ файла дл€ сохранени€ в кеш
			$fileNameForSave=$imageCacheDir.'/'.$filePrefix.'.'.$fileExt;

			//проверим, позвол€ет ли изображение быть просто ресайзенным или
			//нужна интеллектуальна€ обрезка + ресайз
			if($resDetector->IsSimpleResize($attWidth, $attHeight, $destWidth, $destHeight))
			{
				$image->resize($destWidth, $destHeight);

				DynamicKawaiiImages::OutputFileToBrowser($image, $fileNameForSave);
				return;
			}

			//если дошли сюда нужно вырезать и возможно ресайзить
			$cutHeight=$resDetector->GetCutHeight($attWidth, $attHeight, $destWidth, $destHeight);
			if($cutHeight>0)
			{
				$image->CutByHeightAndResize($cutHeight, $destWidth, $destHeight);
				DynamicKawaiiImages::OutputFileToBrowser($image, $fileNameForSave);
				return;

			}

			//≈сли мы дошли сюда, то по ширине нужно уменьшить, чтобы
			//выдержать пропорции
			$image->CutByWidthAndResize($destWidth, $destHeight);
			DynamicKawaiiImages::OutputFileToBrowser($image, $fileNameForSave);
					    
		}//do_template_redirect

		function do_content($content)
		{
			global $post;

			if($post->post_type != 'attachment' || is_feed() || is_home() || !is_single())
			{
				return $content;//do nothing
			}
			
			$imageID=$post->ID;
			$imgURL=wp_get_attachment_url($imageID);
		
			//full URL to attach page
			$postPermLink=post_permalink($imageID);			
			$fileName = basename($imgURL);

			//parts of file name..we need it later
			//Madlax.Margaret-Burton.Elenore-Baker.Madlax-HTC-Cha-Cha-wallpaper.Vanessa-Rene.320x480.jpg
			$nameParts=explode('.', $fileName);			
			$shortFileName=$nameParts[0];

			$namePartsCount=count($nameParts);

			if($namePartsCount>2)
			{
				$shortFileName='';
				//если можно, отрежем часть с разрешением
				for($q=0; $q<$namePartsCount-2; $q++)
				{
					if(strlen($shortFileName)>0)
					{
						$shortFileName.= '.';
					}

					$shortFileName.=$nameParts[$q];					
				}				
			}//if
			
			$fileExt=end($nameParts);//extension (jpg)

			$attMeta=wp_get_attachment_metadata($imageID);
			if($attMeta==FALSE)
			{
				return $content;//something wrong...default
			}

			$attWidth=$attMeta['width'];
			$attHeight=$attMeta['height'];            		

			//get available resolutions for this size:
			$resDetector=new KawaiiResolutionDetector();
			$resArr=$resDetector->GetAvailableResolutions($attWidth, $attHeight);

			//secret code generation	
			$nowTime = (string)time();
			$encryptor=new EncryptorKawaii();
			$secretCode=$encryptor->Encode($nowTime);
				
			$linkNameCurrent=$resDetector->GetResolutionDescription($attWidth, $attHeight);

			$mainLink='<a href="'. $imgURL.'" target="_blank">'.$linkNameCurrent.'</a>';

			$content .= '<br/>';
			$content .= $mainLink;            		
			$content .= '<br/>';

			foreach ($resArr as $resName => $resParams)
			{
				$linkName=$resName;
				if (array_key_exists('description', $resParams))
				{
					$linkName=$linkName . ' (' . $resParams['description']. ')';
				}

				//good file name. 
				$addLink='<a href="'.$postPermLink .'custom-image/'. $imageID .'/'.$resName. '" target="_blank">'.$linkName.'</a><br/>';
				$content .= $addLink;
			}
						
			
			return $content;
		}//do_content


	}//class

	if (class_exists("DynamicKawaiiImages")) 
	{
		$pluginDynamicKawaiiImages = new DynamicKawaiiImages();
	}

} //End Class DynamicKawaiiImages

//Actions and Filters	
if (isset($pluginDynamicKawaiiImages)) 
{
	//Actions   template_redirect  wp
	add_action('wp', array('DynamicKawaiiImages', 'do_template_redirect'));

	add_filter('the_content', array('DynamicKawaiiImages', 'do_content'));

}



