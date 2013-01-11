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

		function do_template_redirect()
		{	
			/*					
		    if(is_404()===FALSE)
			{
				return;	
			}*/

			//another check method for our special URL
			$url = $_SERVER['REQUEST_URI'];
			if (strpos($url,'/custom/') == false) 
			{
				return;
			}

			//error_log("do_template_redirect\r\n", 3, "d:/dynamic-images.log");
			//$time[] =  microtime(true);
			//sleep(3);

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

			//--- Блок защиты от прямой ссылки на генерируемое изображение
			include ('encryptor-kawaii.php');
			$nowTime = time();
			$encryptor=new EncryptorKawaii();
			$timeCode=(int)$encryptor->Decode($code);
			$timeDiff=$nowTime-$timeCode;
			//разница в секундах от сгенерированного кода 
			//при загрузке страницы и текущего времени. Мы
			//считаем что человек врядли будет "думать" более 2 часов,
			//поэтому тут будет так - если прошло более 2 часов тебя
			//редиректят снова на страницу аттача.
			if($timeDiff>120*60)
			{
				//пробуем детектить пост-пермалинк, если дали
				//правильный ID изображения
				$testPermLink=post_permalink($imageID);
				if($testPermLink===FALSE)
				{
					return;
				}

				header("location:".$testPermLink);
				return;
			}
			//--- (end)Блок защиты от прямой ссылки на генерируемое изображение
			

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

			//разделим их по слешам и выделим имя поста, это будет имя подпапки
			$urlParts=explode('/', $postPermLink);
			end($urlParts);//прыгнули в конец массива
			prev($urlParts);
			$realPostName=prev($urlParts);//и взяли пред-последнюю часть (hagure-yuusha-no-estetica)
            
			//это будет под-папка в кеше:
			$imageCacheDir=$imageCacheDirBase . '/' . $realPostName;
			//check this directory, if need - create it			
			if(! (is_dir($imageCacheDir) || mkdir($imageCacheDir)) )
			{
				return;
			}			

			//все файлы именуются в стиле id_320x480 , чтобы их легче найти
			$filePrefix=$imageID.'_'.$newsize;

			$filesInCache = glob($imageCacheDir. '/'.$filePrefix.'*.*');
			if($filesInCache!=FALSE && is_array($filesInCache) && count($filesInCache)>0)
			{
				$resultFileName=$filesInCache[0];

				DynamicKawaiiImages::SendBaseImageHeaders($resultFileName);

				if(readfile($resultFileName)>0)
				{
					//$time[] =  microtime(true);					
					//$diffTime=$time[1] - $time[0];
					//error_log("do_template_redirect cached:". $diffTime ."\r\n", 3, "d:/dynamic-images.log");

        			die();
					return;
				}
			}

			include ('simpleimage.php');
			include ('kawaii-resolution.php');
            
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

			//тут имя файла для сохранения в кеш
			$fileNameForSave=$imageCacheDir.'/'.$filePrefix.'.'.$fileExt;

			//проверим, позволяет ли изображение быть просто ресайзенным или
			//нужна интеллектуальная обрезка + ресайз
			if($resDetector->IsSimpleResize($attWidth, $attHeight, $destWidth, $destHeight))
			{
				$image->resize($destWidth, $destHeight);

				//$time[] =  microtime(true);					
				//$diffTime=$time[1] - $time[0];
				//error_log("do_template_redirect IsSimpleResize:". $diffTime ."\r\n", 3, "d:/dynamic-images.log");

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

			//Если мы дошли сюда, то по ширине нужно уменьшить, чтобы
			//выдержать пропорции
			$image->CutByWidthAndResize($destWidth, $destHeight);
			DynamicKawaiiImages::OutputFileToBrowser($image, $fileNameForSave);
					    
		}//do_template_redirect

	}

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
	
}



