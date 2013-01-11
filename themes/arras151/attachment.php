<?php get_header(); ?>

<div id="content" class="section">
<?php arras_above_content() ?>

<?php if (have_posts()) : the_post(); ?>
	<?php arras_above_post() ?>
	<div id="post-<?php the_ID() ?>" <?php arras_single_post_class() ?>>

        <?php arras_postheader() ?>

		<div class="entry-content single-post-attachment clearfix">
		<?php 
			include (WP_PLUGIN_DIR.'/dynamic-kawaii-images/kawaii-resolution.php');
			echo '<table border="0">';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
			the_attachment_link($post->ID, false);
			echo '</td>';

			echo '<td>';

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
			}
			
			$fileExt=end($nameParts);//extension (jpg)

			//ini_set('display_errors',1);//DEBUG!!!!
			//error_reporting(E_ALL);		//DEBUG!!!!

			$attMeta=wp_get_attachment_metadata($imageID);
			if($attMeta!=FALSE)
			{
				$attWidth=$attMeta['width'];
				$attHeight=$attMeta['height'];

				//get available resolutions for this size:
				$resDetector=new KawaiiResolutionDetector();
				$resArr=$resDetector->GetAvailableResolutions($attWidth, $attHeight);

				//secret code generation
				include (WP_PLUGIN_DIR.'/dynamic-kawaii-images/encryptor-kawaii.php');
				$nowTime = (string)time();
				$encryptor=new EncryptorKawaii();
				$secretCode=$encryptor->Encode($nowTime);
				
				$linkNameCurrent=$resDetector->GetResolutionDescription($attWidth, $attHeight);
				echo '<a href="'. $imgURL.'" target="_blank">'.$linkNameCurrent.'</a><br/>';

				foreach ($resArr as $resName => $resParams)
				{
					$linkName=$resName;
					if (array_key_exists('description', $resParams))
					{
						$linkName=$linkName . ' (' . $resParams['description']. ')';
					}

					//good file name. 
					$fileNameGood='kawaii-mobile.com.'.$shortFileName.'.'.$resName.'.'.$fileExt;
					
					echo '<a href="'. $postPermLink.'custom/'.$fileNameGood.'?newsize='.$resName.'&code='.$secretCode.'&id='.$imageID.'" target="_blank">'.$linkName.'</a><br/>';
				}
			}
				
			echo '</td>';

			echo '</tr>';		

			echo '</tbody>';
			echo '</table>';			


		?>
		<?php the_content( __('<p>Read the rest of this entry &raquo;</p>', 'arras') ); ?>	
        
        <?php wp_link_pages(array('before' => __('<p><strong>Pages:</strong> ', 'arras'), 
			'after' => '</p>', 'next_or_number' => 'number')); ?>
        </div>
		
		<?php arras_postfooter() ?>

        <?php 
		if ( arras_get_option('display_author') ) {
			arras_post_aboutauthor();
		}
        ?>
    </div>
    
	<?php arras_below_post() ?>
	<a name="comments"></a>
    <?php comments_template('', true); ?>
	<?php arras_below_comments() ?>
    
<?php else: ?>

<?php arras_post_notfound() ?>

<?php endif; ?>

<?php arras_below_content() ?>
</div><!-- #content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>