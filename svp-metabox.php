<?php

// Adds some includes
require_once( 'includes/class-video-base.php' );
require_once( 'includes/class-videos.php' );
require_once( 'includes/class-post.php' );
require_once( 'includes/class-source-base.php' );
require_once( 'includes/class-source.php' );
$svp_video_base = new SVP_Video_Base();
$svp_videos = new SVP_Videos();
$svp_post = new SVP_Post();
$svp_source_base = new SVP_Source_Base();
$svp_source = new SVP_Source();

global $post;

// Retrieves data of current post
$svp_post->read( $post->ID );

// Retrieves the list of available videos
$videos = array();
$videos = $svp_videos->get_all_available_videos();

?>

<div id="svp-metabox-container" class="svp-wrapper">
	<?php if ( count( $videos ) > 0 ): ?>
		<ul class="svp-list svp-container" id="svp-list-videos">
			<?php 
			$i = 0;
			$selected = 0;
			foreach ( $videos as $video ):
				if ( ! is_null( $svp_post->get_video() ) && $svp_post->get_video()->get_ID() == $video->ID ) 
					$selected = (int) $video->ID;
				($i % 2 == 0) ? $alternate = ' alternate' : $alternate = '';
				$svp_video_base->read( $video->ID );
				$source_id = $svp_video_base->get_source_ID();
				$svp_source_base->read( $source_id );
				$source = $svp_source->factory( $svp_source_base->get_source_type_code() );
				$source->read( $source_id );
				$delivery_method = '';
				if ( $source->get_videos_type() == SVP_VIDEO_TYPE_ADAPTIVE || $source->get_videos_type() == SVP_VIDEO_TYPE_LIVE )
					$delivery_method = 'AdaptiveStreaming';
			?>
			<li class="svp-row-content<?php print $alternate; ?> svp-container">
				<span class="svp-label svp-highlight svp-video-name">
					<?php print $video->filename; ?>
				</span>
				<span class="svp-label svp-downlight svp-source-name">
					<?php print $svp_source_base->get_name(); ?>
				</span>
				<span class="svp-label svp-action svp-container">
					<a href="javascript:void(0);" class="svp-button svp-play" title="<?php _e( 'Play this video', 'svp-translate' ); ?>"><span><?php _e( 'Play', 'svp-translate' ); ?></span></a>
					<?php
						$activated = '';
						if ( $selected == (int) $video->ID )
							$activated = ' svp-activated';
					?>
					<a href="javascript:void(0);" class="svp-button svp-select<?php print $activated; ?>" title="<?php _e( 'Select this video', 'svp-translate' ); ?>"><span><?php _e( 'Select', 'svp-translate' ); ?></span></a>
					<span class="svp-hidden svp-video-url" id="svp-video-url-<?php print $video->ID; ?>"><?php print $source->make_video_url( $video->filename ); ?></span>
					<span class="svp-hidden svp-delivery-method" id="svp-delivery-method-<?php print $video->ID; ?>"><?php print $delivery_method; ?></span>
				</span>
			</li>
			<?php 
			$i++;
			endforeach; 
			?>
		</ul>
		
		<div id="svp-player-container"></div>
		
		<script type="text/javascript">
			var SVP_Metabox = new Object();
			SVP_Metabox._params = { 
				target: null, 
				video_url: null, 
				delivery_method: 'ProgressiveDownload',
				player_url: '<?php print plugins_url( '/player/Player.xap', __FILE__ ); ?>',
				close_label: '<?php _e( 'Close', 'svp-translate' ); ?>',
				next_label: '<?php _e( 'Go to next video', 'svp-translate' ); ?>',
				previous_label: '<?php _e( 'Go to previous video', 'svp-translate' ); ?>',
				select_label: '<?php _e( 'Select this video', 'svp-translate' ); ?>'
			};
		</script>
		
		<input type="hidden" name="svp_video" id="svp-video" value="<?php print $selected; ?>" />
		
	<?php
	else: 
		printf( __( 'No video is currently available. Please <a href="%s">add at least one source of videos</a>. Nor forget to scan your sources of videos.', 'svp-translate' ), get_admin_url() . 'admin.php?page=svp-source' ); 
	endif;
	?>
</div>
