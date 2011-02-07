<?php
/**
 * Microsoft Azure progressive download videos source management.
 * 
 * @author Adenova <agence@adenova.fr>
 * @since 1.5.0
 */
if ( ! class_exists ( 'SVP_Source_Azure_Progressive' ) )
{
	require_once( 'class-source-base.php' );
	
	// It's a source for progressive videos : include the progressive video type class
	require_once( 'class-video-progressive.php' );
	
	class SVP_Source_Azure_Progressive extends SVP_Source_Base
	{
		// Constructor
		function SVP_Source_Azure_Progressive()
		{
			$this->__construct();
		}
		
		function __construct()
		{
			$svp_video_progressive = new SVP_Video_Progressive();
			$this->set_videos_type( $svp_video_progressive->get_type() );
		}
		
		function configure()
		{
			$name = '';
			$options = array();
			$options['svp_source_url'] = '';
			$options['svp_source_dirname'] = '';
			$options['svp_source_user'] = '';
			$options['svp_source_key'] = '';
			$options['svp_source_cdn_activation'] = 0;
			$options['svp_source_cdn_url'] = '';
			$id = $this->get_ID();
			if ( ! empty( $id ) )
			{
				$name = $this->get_name();
				$options = $this->get_options();
			}
			($options['svp_source_cdn_activation'] == 1) ? $cdn_activation_checked = ' checked' : $cdn_activation_checked = '';
			return '
				<div class="stuffbox">
					<h3>' . __( 'Main configuration' , 'svp-translate' ) . '</h3>
					<div class="inside">
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_name"><abbr class="required">' . __( 'Source name', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_name" type="text" id="svp_source_name" value="' . $name . '" class="regular-text code" />
									<br />' . __( 'Here, you have to place your source type name.', 'svp-translate' ) . '
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_url"><abbr class="required">' . __( 'Blob storage URL', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_url" type="text" id="svp_source_url" value="' . $options['svp_source_url'] . '" class="regular-text code" />
									<br />' . __( 'Example&nbsp;: <code>http://example.blob.core.windows.net/</code> &#8212; don&#8217;t forget the <code>http://</code> prefix.', 'svp-translate' ) . '
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_dirname"><abbr class="required">' . __( 'Videos container name', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_dirname" type="text" id="svp_source_dirname" value="' . $options['svp_source_dirname'] . '" class="regular-text code" />
									<br />' . __( 'This is the container name that contains your videos on the Microsoft Azure blob storage.', 'svp-translate' ) . '
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="stuffbox">
					<h3>' . __( 'Videos access configuration' , 'svp-translate' ) . '</h3>
					<div class="inside">
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_user"><abbr class="required">' . __( 'Blob storage login', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_user" type="text" id="svp_source_user" value="' . $options['svp_source_user'] . '" class="regular-text code" />
									<br />' . __( 'This is the login defined when you configured your blob storage.', 'svp-translate' ). '
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_key"><abbr class="required">' . __( 'Blob storage key', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_key" type="text" id="svp_source_key" value="' . $options['svp_source_key'] . '" class="large-text code" />
									<br />' . __( 'This is the key you obtained when you configured your blob storage.', 'svp-translate' ) . '
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_cdn_activation">' . __( 'CDN activation', 'svp-translate' ) . '</label>
								</th>
								<td>
									<label for="svp_source_cdn_activation">
										<input name="svp_source_cdn_activation" type="checkbox" id="svp_source_cdn_activation" value="1"' . $cdn_activation_checked . ' />' . __( 'Activate', 'svp-translate' ) . '
										<br />' . __( 'This option must have been activated in your blob storage.', 'svp-translate' ) . '
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_cdn_url">' . __( 'CDN URL', 'svp-translate' ) . '</label>
								</th>
								<td>
									<input name="svp_source_cdn_url" type="text" id="svp_source_cdn_url" value="' . $options['svp_source_cdn_url'] . '" class="regular-text code" />
									<br />' . __( 'This is the CDN URL you obtained when you activated CDN option in your blob storage. Example&nbsp;: <code>http://example.blob.core.windows.net/</code> &#8212; don&#8217;t forget the <code>http://</code> prefix.', 'svp-translate' ) . '
								</td>
							</tr>
						</table>
					</div>
				</div>';
		}
		
		function scan( $id = null )
		{
			if ( empty( $id ) )
			{
				wp_die( __( 'Parameter ID is undefined.', 'svp-translate' ) );
				exit();
			}
			
			global $wpdb;
			
			$sql = 'SELECT options FROM ' . $wpdb->prefix . 'svp_sources WHERE ID = %d';
			$source = $wpdb->get_row( $wpdb->prepare( $sql, (int) $id ) );
			
			$options = null;
			$result = array();
			if ( ! empty( $source ) )
			{
				if ( ! empty( $source->options ) )
					$options = unserialize( $source->options );
				if ( ! empty( $options ) )
				{
					// Call PHP Windows Azure SDK library
					$path = realpath( dirname( __FILE__ ) . '/../library' );
					set_include_path( get_include_path() . PATH_SEPARATOR . $path );
					require_once( 'Microsoft/WindowsAzure/Storage/Blob.php' );
					try
					{
						$storage = new Microsoft_WindowsAzure_Storage_Blob(
							'blob.core.windows.net',
							$options['svp_source_user'],
							$options['svp_source_key'] );
						$blobs = $storage->listBlobs( $options['svp_source_dirname'] );
					}
					catch (Exception $e)
					{
						wp_die( sprintf( __( 'An error occured getting the videos on your Windows Azure Blob Storage with the message : <em>%s</em>', 'svp-translate' ), $e->getMessage() ) );
						exit();
					}
					
					foreach ( $blobs as $blob )
						$result[] = $blob->name;
				}
			}
			
			// Create instance of progressive video class
			$svp_video_progressive = new SVP_Video_Progressive();
			
			// Filter by extension
			$videos = new SVP_Videos();
			$result = $videos->filter_by_extensions( $result, $svp_video_progressive->get_extensions() );
			
			// Do the synchronization
			$synchro = $this->synchro( $id, $result );
			
			// Returns an array of videos
			return $synchro;
		}
		
		function check( $data = array() )
		{
			// Instanciation de la classe des utilitaires
			$utils = new SVP_Utils();
			
			// Réalise les tests
			if ( empty( $data['svp_source_name'] ) )
				return false;
			foreach ( $data as $key => $value )
			{
				if ( in_array( $key, $this->get_input_list() ) )
				{
					switch ( $key )
					{
						case 'svp_source_url':
							if ( ! $utils->is_url( $data[$key] ) )
								return false;
							break;
						case 'svp_source_dirname':
							if ( empty( $data[$key] ) )
								return false;
							break;
						case 'svp_source_user':
							if ( empty( $data[$key] ) )
								return false;
							break;
						case 'svp_source_key':
							if ( empty( $data[$key] ) )
								return false;
							break;
						case 'svp_source_cdn_activation':
							break;
						case 'svp_source_cdn_url':
							if ( ! empty( $data[$key] ) && ! $utils->is_url( $data[$key] ) )
								return false;
							break;
					}
				}
			}
			return true;
		}
		
		function get_input_list()
		{
			return array( 
				'svp_source_url', 
				'svp_source_dirname',
				'svp_source_user',
				'svp_source_key',
				'svp_source_cdn_activation',
				'svp_source_cdn_url' );
		}
		
		function make_video_url( $filename = '' )
		{
			// Call parent class method
			parent::make_video_url( $filename );
			
			// Get utils class instance
			$svp_utils = new SVP_Utils();
			
			// Get current User Agent
			$user_agent = $svp_utils->get_user_agent();
			
			// Get source options
			$options = $this->get_options();
			
			// Initialize URL
			$url = '';
			
			// Constructs URL
			if ( array_key_exists( 'svp_source_cdn_activation', $options ) && $options['svp_source_cdn_activation'] == 1 )
			{
				if ( array_key_exists( 'svp_source_cdn_url', $options ) && ! empty( $options['svp_source_cdn_url'] ) )
					$url .= $svp_utils->add_endurl_slash( $options['svp_source_cdn_url'] );
				else
					$url .= $svp_utils->add_endurl_slash( $options['svp_source_url'] );
			}
			else
				$url .= $svp_utils->add_endurl_slash( $options['svp_source_url'] );
			if ( ! empty( $options['svp_source_dirname'] ) )
				$url .= $svp_utils->add_endurl_slash( $options['svp_source_dirname'] );
			switch ( $user_agent )
			{
				case SVP_USER_AGENT_IPHONE:
				case SVP_USER_AGENT_IPAD:
					$svp_video_progressive = new SVP_Video_Progressive();
					$extensions = $svp_video_progressive->get_extensions();
					$extension = substr( $filename, strrpos( $filename, '.' ) + 1 );
					if ( $extension !== false && $extension == $extensions[0] ) // MP4
						$url .= urlencode( $filename );
					else
						return '';
					break;
				default:
					$url .= urlencode( $filename );
					break;
			}
			return $url;
		}
		
		function make_thumbnail_url( $filename )
		{
			if ( empty( $filename ) )
			{
				wp_die( 'The video filename can not be empty.' );
				exit();
			}
			else
			{
				// Add some includes
				require_once( 'class-utils.php' );
				$svp_utils = new SVP_Utils();
				
				// Get source options
				$options = $this->get_options();
				if ( empty( $options ) )
				{
					wp_die( sprintf( __( 'Source options are undefined. Please read source data before to call method <em>%s</em>.', 'svp-translate' ), __FUNCTION__ ) );
					exit();
				}
				if ( ! array_key_exists( 'svp_source_url', $options ) || ! array_key_exists( 'svp_source_dirname', $options ) )
				{
					wp_die( __( 'The URL or the name directory of the source is missing.', 'svp-translate' ) );
					exit();
				}
				
				// Initialize URL
				$url = '';
				
				// Constructs URL
				if ( array_key_exists( 'svp_source_cdn_activation', $options ) && $options['svp_source_cdn_activation'] == 1 )
				{
					if ( array_key_exists( 'svp_source_cdn_url', $options ) && ! empty( $options['svp_source_cdn_url'] ) )
						$url .= $svp_utils->add_endurl_slash( $options['svp_source_cdn_url'] );
					else
						$url .= $svp_utils->add_endurl_slash( $options['svp_source_url'] );
				}
				else
					$url .= $svp_utils->add_endurl_slash( $options['svp_source_url'] );
				if ( ! empty( $options['svp_source_dirname'] ) )
					$url .= $svp_utils->add_endurl_slash( $options['svp_source_dirname'] );
				$pos = strrpos( $filename, '.' );
				if ( $pos !== false )
				{
					$url .= urlencode( substr( $filename, 0, $pos ) . '_' . 
						SVP_VIDEO_SUFFIX_THUMB . '.' . 
						SVP_VIDEO_EXT_THUMB );
				}
				else
					return '';
				
				$thumbnail_handler = @fopen( $url, 'r' );
				if ( $thumbnail_handler === false )
					return '';
				
				return $url;
			}
		}
	}
}