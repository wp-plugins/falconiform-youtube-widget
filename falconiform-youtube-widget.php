<?php
/**
 * Plugin Name: Falconiform YouTube Widget
 * Plugin URI: http://fabulierer.de/
 * Description: Adds a widget that shows a YouTube video.
 * Author: Ronny Harbich
 * Version: 1.0.0
 * Author URI: http://fabulierer.de/
 * License: GPLv2 or later
 */


/*
 * Define plugin constants.
 */
define( 'FF_YOUTUBE_WIDGET_ID', 'ff_youtube_widget' );
define( 'FF_YOUTUBE_WIDGET_SLUG', 'ff-youtube-widget' );
define( 'FF_YOUTUBE_WIDGET_VERSION', '1.0.0' );
define( 'FF_YOUTUBE_TEXT_DOMAIN', 'ff_youtube_widget' );


/**
 * The Falconiform YouTube Widget class.
 */
class FF_YouTubeWidget extends WP_Widget {

	/**
	 * Slug for the dedicated settings page in the administration interface.
	 */
	const SETTINGS_YOUTUBE_WIDGET = 'ff-youtube-widget-settings';

	/**
	 * Slug for the YouTube video IDs in the dedicated settings page.
	 */
	const SETTING_YOUTUBE_VIDEO_IDS = 'youtube-video-ids';

	/**
	 * Number of maximal videos that can be set in the dedicated settings page.
	 */
	const NUMBER_OF_YOUTUBE_VIDEO_IDS = 5;

	/**
	 * Prefix for YouTube player parameters in the widget settings.
	 */
	const PLAYER_PARAM_PREFIX = 'yt-param-';


	/**
	 * Do not use directly. Use {@link FF_YouTubeWidget::get_all_settings} instead.
	 *
	 * @var array
	 */
	private $defaultSettings;

	/**
	 * @var int The length of {@link FF_YouTubeWidget::PLAYER_PARAM_PREFIX}.
	 */
	private $playerParamPrefixLength;

	/**
	 * @var array The default possible values for a checkbox in the settings.
	 */
	private static $defaultCheckboxValues = array( 0, 1 );

	/**
	 * @var array Array of all setting fields of the widget, i. e., an array of $fieldName => array($description, $fieldType, $defaultValue, $converterFunction, $validateFunction, $validValues).
	 */
	private $widgetFields;


	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct(
			FF_YOUTUBE_WIDGET_ID, // widget base ID
			__( 'YouTube Video', FF_YOUTUBE_TEXT_DOMAIN ), // widget name at the widget page in the administration interface
			array( 'description' => __( 'Show a YouTube video.', FF_YOUTUBE_TEXT_DOMAIN ), ) // widget description
		);


		// get the length of self::PLAYER_PARAM_PREFIX
		$this->playerParamPrefixLength = strlen( self::PLAYER_PARAM_PREFIX );


		// set default values for dedicated settings page
		$this->defaultSettings = array(
			self::SETTING_YOUTUBE_VIDEO_IDS => array_fill( 0, self::NUMBER_OF_YOUTUBE_VIDEO_IDS, 'kMJXap37bzw' ),
		);


		if ( is_admin() ) {
			// set widget settings fields: $fieldName => array($description, $fieldType, $defaultValue, $converterFunction, $validateFunction, $validValues)
			$this->widgetFields = array(

				// title
				'title'                                      => array(
					__( 'Widget title:', FF_YOUTUBE_TEXT_DOMAIN ),
					'text-input',
					'',
					'convert_to_string',
					'validate_string'
				),

				// video id
				'video-id'                                   => array(
					__( 'YouTube video ID:', FF_YOUTUBE_TEXT_DOMAIN ),
					'text-input',
					'kMJXap37bzw',
					'convert_to_string',
					'validate_video_url'
				),

				// video index
				'video-index'                                => array(
					__( 'Index of the video ID from dedicated settings page:', FF_YOUTUBE_TEXT_DOMAIN ),
					'select',
					0,
					'convert_to_int',
					'validate_key_in_array',
					range( 1, self::NUMBER_OF_YOUTUBE_VIDEO_IDS ),
				),

				// take video index
				'take-video-index'                           => array(
					__( 'Take video from dedicated settings page via index.', FF_YOUTUBE_TEXT_DOMAIN ),
					'checkbox',
					0,
					'convert_to_int',
					'validate_in_array',
					self::$defaultCheckboxValues
				),

				// player width
				'player-width'                               => array(
					__( 'Player width (in pixel, 0 for automatic width):', FF_YOUTUBE_TEXT_DOMAIN ),
					'text-input',
					0,
					'convert_to_int',
					'validate_dimension'
				),

				// player height
				'player-height'                              => array(
					__( 'Player height (in pixel, 0 for automatic height):', FF_YOUTUBE_TEXT_DOMAIN ),
					'text-input',
					0,
					'convert_to_int',
					'validate_dimension'
				),

				// param theme
				self::PLAYER_PARAM_PREFIX . 'theme'          => array(
					__( 'Player theme:', FF_YOUTUBE_TEXT_DOMAIN ),
					'select',
					'dark',
					'convert_to_string',
					'validate_key_in_array',
					array(
						'dark'  => __( 'Dark', FF_YOUTUBE_TEXT_DOMAIN ),
						'light' => __( 'Light', FF_YOUTUBE_TEXT_DOMAIN ),
					),
				),

				// param color
				self::PLAYER_PARAM_PREFIX . 'color'          => array(
					__( 'Color of the player’s video progress bar:', FF_YOUTUBE_TEXT_DOMAIN ),
					'select',
					'red',
					'convert_to_string',
					'validate_key_in_array',
					array(
						'red'   => __( 'Red', FF_YOUTUBE_TEXT_DOMAIN ),
						'white' => __( 'White', FF_YOUTUBE_TEXT_DOMAIN ),
					),
				),

				// param controls
				self::PLAYER_PARAM_PREFIX . 'controls'       => array(
					__( 'Player controls visibility:', FF_YOUTUBE_TEXT_DOMAIN ),
					'select',
					2,
					'convert_to_int',
					'validate_key_in_array',
					array(
						2 => __( 'Display controls, Flash player loads after user initiates video playback', FF_YOUTUBE_TEXT_DOMAIN ),
						1 => __( 'Display controls, Flash player loads immediately', FF_YOUTUBE_TEXT_DOMAIN ),
						0 => __( 'Hide controls, Flash player loads immediately', FF_YOUTUBE_TEXT_DOMAIN ),
					),
				),

				// param autohide
				self::PLAYER_PARAM_PREFIX . 'autohide'       => array(
					__( 'Hide controls / progress bar at playback:', FF_YOUTUBE_TEXT_DOMAIN ),
					'select',
					2,
					'convert_to_int',
					'validate_key_in_array',
					array(
						2 => __( 'Show controls, decrease progress bar', FF_YOUTUBE_TEXT_DOMAIN ),
						1 => __( 'Hide controls, hide progress bar', FF_YOUTUBE_TEXT_DOMAIN ),
						0 => __( 'Show controls, show progress bar', FF_YOUTUBE_TEXT_DOMAIN ),
					),
				),

				// param autoplay
				self::PLAYER_PARAM_PREFIX . 'autoplay'       => array(
					__( 'Start playback automatically.', FF_YOUTUBE_TEXT_DOMAIN ),
					'checkbox',
					0,
					'convert_to_int',
					'validate_in_array',
					self::$defaultCheckboxValues
				),

				// param loop
				self::PLAYER_PARAM_PREFIX . 'loop'           => array(
					__( 'Restart the video automatically after playback ends.', FF_YOUTUBE_TEXT_DOMAIN ),
					'checkbox',
					0,
					'convert_to_int',
					'validate_in_array',
					self::$defaultCheckboxValues
				),

				// param rel
				self::PLAYER_PARAM_PREFIX . 'rel'            => array(
					__( 'Show related videos after playback ends.', FF_YOUTUBE_TEXT_DOMAIN ),
					'checkbox',
					1,
					'convert_to_int',
					'validate_in_array',
					self::$defaultCheckboxValues
				),

				// param showinfo
				self::PLAYER_PARAM_PREFIX . 'showinfo'       => array(
					__( 'Show video information like the video title.', FF_YOUTUBE_TEXT_DOMAIN ),
					'checkbox',
					1,
					'convert_to_int',
					'validate_in_array',
					self::$defaultCheckboxValues
				),

				// param iv_load_policy
				self::PLAYER_PARAM_PREFIX . 'iv_load_policy' => array(
					__( 'Show video annotations.', FF_YOUTUBE_TEXT_DOMAIN ),
					'checkbox',
					1,
					'convert_to_int',
					'validate_in_array',
					array( 3, 1 )
				),

				// param fs
				self::PLAYER_PARAM_PREFIX . 'fs'             => array(
					__( 'Show fullscreen button.', FF_YOUTUBE_TEXT_DOMAIN ),
					'checkbox',
					1,
					'convert_to_int',
					'validate_in_array',
					self::$defaultCheckboxValues
				),

				// param modestbranding
				self::PLAYER_PARAM_PREFIX . 'modestbranding' => array(
					__( 'Hide YouTube logo in the controls bar.', FF_YOUTUBE_TEXT_DOMAIN ),
					'checkbox',
					0,
					'convert_to_int',
					'validate_in_array',
					self::$defaultCheckboxValues
				),

				// param disablekb
				self::PLAYER_PARAM_PREFIX . 'disablekb'      => array(
					__( 'Disable keyboard control for the player.', FF_YOUTUBE_TEXT_DOMAIN ),
					'checkbox',
					0,
					'convert_to_int',
					'validate_in_array',
					self::$defaultCheckboxValues
				),

			);

			// triggered before any other hook when a user accesses the administration interface
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			// enqueue scripts and style for the administration interface
			//add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_css_and_scripts'));
			// add a menu to the administration interface
			add_action( 'admin_menu', array( $this, 'register_admin_menu_page' ) );
			// notices displayed near the top of administration pages
			add_action( 'admin_notices', array( $this, 'admin_notices_action' ) );
		}
		else {
			// enqueue scripts and style on the website (not in the administration interface)
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css_and_scripts' ) );
		}

	}


	/**
	 * Returns the settings for the dedicated settings page.
	 *
	 * @return array Array of all settings for the dedicated settings page. The key is the setting name and the value the setting value.
	 */
	private function get_all_settings() {
		// get settings from wordpress
		$settings = get_option( self::SETTINGS_YOUTUBE_WIDGET );

		// settings not available yet?
		if ( $settings === false || !is_array( $settings ) )
			// set default settings
			$settings = $this->defaultSettings;
		else
			// merge settings with default settings if keys (settings) are missing
			$settings = array_merge( $this->defaultSettings, $settings );

		return $settings;
	}

	/*function  enqueue_admin_css_and_scripts()
	{
		wp_register_style(FF_YOUTUBE_WIDGET_SLUG.'-admin', plugins_url('', __FILE__) . '/css/admin.css', array(), FF_YOUTUBE_WIDGET_VERSION);
		wp_enqueue_style(FF_YOUTUBE_WIDGET_SLUG.'-admin');
	}*/


	/**
	 * Administration interface initialization.
	 */
	function admin_init() {
		// let a user with edit_others_posts capability edit the settings
		add_filter( 'option_page_capability_' . self::SETTINGS_YOUTUBE_WIDGET, array( $this, 'get_settings_access_capability' ) );
		// register settings for the dedicated settings page
		register_setting( self::SETTINGS_YOUTUBE_WIDGET, self::SETTINGS_YOUTUBE_WIDGET, array( $this, 'validate_settings' ) );
		// add the general settings section
		add_settings_section( FF_YOUTUBE_WIDGET_SLUG . '-section-general', __( 'Dedicated Video Settings', FF_YOUTUBE_TEXT_DOMAIN ), array( $this, 'section_general_callback' ), FF_YOUTUBE_WIDGET_SLUG );
		// add a the settings field for the YouTube video IDs
		add_settings_field( self::SETTING_YOUTUBE_VIDEO_IDS, __( 'YouTube Video IDs', FF_YOUTUBE_TEXT_DOMAIN ), array( $this, 'render_field_youtube_video_ids' ), FF_YOUTUBE_WIDGET_SLUG, FF_YOUTUBE_WIDGET_SLUG . '-section-general' );
	}

	/**
	 * Callback for the option_page_capability_ filter.
	 *
	 * @return string A user capability.
	 */
	function get_settings_access_capability() {
		return 'edit_others_posts';
	}

	/**
	 * Callback for settings for the dedicated settings page. Validates the submitted settings.
	 *
	 * @param array $input Array of the new setting values.
	 *
	 * @return array Array of the corrected new settings values.
	 */
	function validate_settings( $input ) {
		$noError = true;

		// get the old settings
		$oldSettings = $this->get_all_settings();

		// make sure that all settings are set
		$newSettings = array_merge( $oldSettings, $input );

		// iterate all YouTube video IDs
		for ( $i = 0, $count = count( $newSettings[self::SETTING_YOUTUBE_VIDEO_IDS] ); $i < $count; $i++ ) {
			// empty YouTube video ID means that we simply do not show the video
			if ( $newSettings[self::SETTING_YOUTUBE_VIDEO_IDS][$i] != '' ) {
				// parse YouTube video ID from new setting
				$videoId = self::parse_video_url( $newSettings[self::SETTING_YOUTUBE_VIDEO_IDS][$i] );

				if ( $videoId !== false ) { // YouTube video ID found
					// set matched YouTube video ID in new setting
					$newSettings[self::SETTING_YOUTUBE_VIDEO_IDS][$i] = $videoId;
				}
				else { // YouTube video ID not found
					// restore old YouTube video ID
					$newSettings[self::SETTING_YOUTUBE_VIDEO_IDS][$i] = $oldSettings[self::SETTING_YOUTUBE_VIDEO_IDS][$i];

					// add an error for the user
					add_settings_error( self::SETTINGS_YOUTUBE_WIDGET, 'invalid-youtube-video-id-' . $i, sprintf( __( 'The video ID at index %s has an invalid format.', FF_YOUTUBE_TEXT_DOMAIN ), $i + 1 ) );

					$noError = false;
				}
			}
		}


		if ( $noError )
			// tell the user that the settings were updated successfully without errors
			add_settings_error( self::SETTINGS_YOUTUBE_WIDGET, 'settings-updated', __( 'Settings updated successfully.', FF_YOUTUBE_TEXT_DOMAIN ), 'updated' );
		else
			// tell the user that the settings were updated successfully but with some errors
			add_settings_error( self::SETTINGS_YOUTUBE_WIDGET, 'settings-updated', __( 'Settings updated, but some errors occurred.', FF_YOUTUBE_TEXT_DOMAIN ), 'updated' );


		return $newSettings;
	}

	/**
	 * Callback for the general settings section.
	 */
	function section_general_callback() {
		// not used at the moment
		//echo('<p>Some help text goes here.</p>');
	}

	/**
	 * Callback for the settings field for the YouTube video IDs.
	 */
	function render_field_youtube_video_ids() {
		// get settings
		$settings = $this->get_all_settings();

		// print a description for the YouTube video IDs setting
		echo( sprintf( '<p class="description">%s</p>',
			__( 'A YouTube video link (URL) or video ID or blank.', FF_YOUTUBE_TEXT_DOMAIN )
		) );

		// iterate all YouTube video IDs
		for ( $i = 0, $count = count( $settings[self::SETTING_YOUTUBE_VIDEO_IDS] ); $i < $count; $i++ ) {
			// create a HTML ID for each YouTube video ID
			$htmlId = self::SETTING_YOUTUBE_VIDEO_IDS . '-' . $i;

			// print a input field for each YouTube video ID
			echo( sprintf( '<p><label for="%s">%s</label> <input class="regular-text" type="text" id="%s" name="%s" value="%s" /></p>',
				$htmlId, // label ID
				sprintf( __( 'Video ID %s:', FF_YOUTUBE_TEXT_DOMAIN ), $i + 1 ), // label text
				$htmlId, // input ID
				self::SETTINGS_YOUTUBE_WIDGET . '[' . self::SETTING_YOUTUBE_VIDEO_IDS . '][' . $i . ']', // input name
				esc_attr( $settings[self::SETTING_YOUTUBE_VIDEO_IDS][$i] ) // input value
			) );
		}
	}

	/**
	 * Register menu for the dedicated settings page.
	 */
	function register_admin_menu_page() {
		add_menu_page(
			__( 'Falconiform YouTube Widget', FF_YOUTUBE_TEXT_DOMAIN ),
			__( 'YouTube Widget', FF_YOUTUBE_TEXT_DOMAIN ),
			'edit_others_posts',
			FF_YOUTUBE_WIDGET_SLUG,
			array( $this, 'render_admin_settings_page' ),
			'dashicons-format-video',
			'58.1337'
		);
	}

	/**
	 * Render the dedicated settings page.
	 */
	function render_admin_settings_page() {
		?>
		<div class="wrap">
			<h2><?php echo( __( 'YouTube Widget Dedicated Settings', FF_YOUTUBE_TEXT_DOMAIN ) ) ?></h2>
			<form action="options.php" method="POST">
				<?php settings_fields( self::SETTINGS_YOUTUBE_WIDGET ); ?>
				<?php do_settings_sections( 'ff-youtube-widget' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
	<?php
	}

	/**
	 * Displays all settings error messages.
	 */
	function admin_notices_action() {
		settings_errors( self::SETTINGS_YOUTUBE_WIDGET );
	}


	/**
	 * Register scripts and styles for the widget on the front-end (not in the administration interface).
	 */
	function enqueue_css_and_scripts() {
		wp_register_style( FF_YOUTUBE_WIDGET_SLUG . '-main', plugins_url( '', __FILE__ ) . '/css/youtube.css', array(), FF_YOUTUBE_WIDGET_VERSION );
		wp_enqueue_style( FF_YOUTUBE_WIDGET_SLUG . '-main' );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {
		// print stuff before widget
		echo( $args['before_widget'] );


		// filter widget title
		$title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );

		// add stuff before and after title
		if ( !empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];


		$youTubePlayerParameters = '';
		$ampersand = '';
		// iterate all widget settings
		foreach ( $instance as $fieldName => $fieldValue ) {
			// find all YouTube player parameters, i.e., fields with the PLAYER_PARAM_PREFIX
			if ( strpos( $fieldName, self::PLAYER_PARAM_PREFIX ) === 0 ) {
				// build YouTube player query variables (parameters)
				$youTubePlayerParameters .= $ampersand . substr( $fieldName, $this->playerParamPrefixLength ) . '=' . urlencode( $fieldValue );
				$ampersand = '&amp;';
			}
		}


		// take YouTube video ID from dedicated settings page or from the widget settings
		if ( $instance['take-video-index'] == 1 ) {
			// get settings (from dedicated settings page)
			$settings = $this->get_all_settings();
			// take YouTube video ID from dedicated settings page
			$videoId = $settings[self::SETTING_YOUTUBE_VIDEO_IDS][$instance['video-index']];
		}
		else {
			// take from the widget settings
			$videoId = $instance['video-id'];
		}


		if ( $videoId == '' ) {
			// do not show video
			?>
			<p><?php echo( __( 'No video selected.', FF_YOUTUBE_TEXT_DOMAIN ) ) ?></p>
		<?php
		}
		else {
			// URL encode the YouTube video ID to be on the safe side
			$videoIdUrlEncoded = urlencode( $videoId );
			// get iFrame height attribute from widget settings
			$heightAttribute = ( $instance['player-height'] == 0 ? '' : ' height="' . esc_attr( $instance['player-height'] ) . '"' );
			// get iFrame width attribute from widget settings
			$widthAttribute = ( $instance['player-width'] == 0 ? '' : ' width="' . esc_attr( $instance['player-width'] ) . '"' );
			// create the "playlist" parameter if the video shall loop (look YouTube player API for more information)
			$loopExtraParameter = ( $instance[self::PLAYER_PARAM_PREFIX . 'loop'] == 0 ? '' : '&amp;playlist=' . $videoIdUrlEncoded );

			// finally show the YouTube video player via iFrame
			echo( sprintf( '<iframe class="ff-youtube-widget-player-iframe" src="http://www.youtube.com/embed/%s"%s><p>%s</p></iframe>',
				$videoIdUrlEncoded . '?' . $youTubePlayerParameters . $loopExtraParameter, // player query variables (parameters)
				$heightAttribute . $widthAttribute, // height and width attribute
				__( 'The video cannot be shown. Your web browser does not support iFrames.', FF_YOUTUBE_TEXT_DOMAIN ) // text for no iFrame support
			) );
		}


		// print stuff after widget
		echo( $args['after_widget'] );
	}


	/**
	 * Converts a specified value into a string.
	 *
	 * @param mixed $value The value to be converted.
	 *
	 * @return string The converted value.
	 */
	private static function convert_to_string( $value ) {
		return strval( $value );
	}

	/**
	 * Converts a specified value into an int.
	 *
	 * @param mixed $value The value to be converted.
	 *
	 * @return int The converted value.
	 */
	private static function convert_to_int( $value ) {
		return intval( $value );
	}

	/**
	 * Validates if a specified value is a string.
	 *
	 * @param mixed $value The value to be tested.
	 *
	 * @return bool|string $value or <code>FALSE</code> if $value is not a string.
	 */
	private static function validate_string( $value ) {
		return is_string( $value ) ? $value : false;
	}

	/**
	 * Validates if a specified value is a dimension, i.e., a non-negative integer.
	 *
	 * @param mixed $value The value to be tested.
	 *
	 * @return bool|int $value or <code>FALSE</code> if $value is not a non-negative integer.
	 */
	private static function validate_dimension( $value ) {
		return is_numeric( $value ) ? absint( $value ) : false;
	}

	/**
	 * Validates if a specified value is contained in a specified array. The types have to match too.
	 *
	 * @param mixed $value The value to be tested.
	 * @param array $validValues Array of valid values.
	 *
	 * @return bool|mixed $value or <code>FALSE</code> if $value is not contained in $validValues.
	 */
	private static function validate_in_array( $value, array $validValues ) {
		return in_array( $value, $validValues, true ) ? $value : false;
	}

	/**
	 * Validates if a specified key is contained in a specified array. The types have to match too.
	 *
	 * @param mixed $key The key to be tested.
	 * @param array $array An Array.
	 *
	 * @return bool|mixed $key or <code>FALSE</code> if $key is not contained in $array.
	 */
	private static function validate_key_in_array( $key, array $array ) {
		return array_key_exists( $key, $array ) ? $key : false;
	}

	/**
	 * Validates if a specified value is a YouTube video URL or ID.
	 *
	 * @param string $value The value to be tested.
	 *
	 * @return bool|string A YouTube video ID, not the URL!, or <code>FALSE</code> if $value is no YouTube video URL or ID, or if $value is not a string.
	 */
	private static function validate_video_url( $value ) {
		return self::parse_video_url( $value );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	function update( $new_instance, $old_instance ) {
		// set setting values to return to the old setting values
		$instance = $old_instance;

		// iterate all widget setting fields
		foreach ( $this->widgetFields as $fieldName => $field ) {
			// set default value if field does not exists in the old values
			if ( !array_key_exists( $fieldName, $instance ) )
				$instance[$fieldName] = $field[2];

			// special handling for checkboxes; only checked checkboxes have a key
			if ( $field[1] == 'checkbox' && !array_key_exists( $fieldName, $new_instance ) )
				// get the first value of the checkbox default valid values (mostly 0)
				$new_instance[$fieldName] = $field[5][0];

			// does the field exists in the new settings?
			if ( array_key_exists( $fieldName, $new_instance ) ) {
				// convert the new setting value (always strings) to the correct type
				$convertedValue = call_user_func( array( 'FF_YouTubeWidget', $field[3] ), $new_instance[$fieldName] );
				// validate if the new setting value
				$validationResult = call_user_func( array( 'FF_YouTubeWidget', $field[4] ), $convertedValue, ( array_key_exists( 5, $field ) ? $field[5] : null ) );

				//  set setting value to the new setting value
				if ( $validationResult !== false )
					$instance[$fieldName] = $validationResult;
			}
		}


		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return void
	 */
	function form( $instance ) {
		// iterate widget setting fields
		foreach ( $this->widgetFields as $fieldName => $field ) {
			// get current setting value or if no exists the default value of the field
			$currentSettingValue = ( array_key_exists( $fieldName, $instance ) ? $instance[$fieldName] : $field[2] );

			// render setting fields switched by field type
			switch ( $field[1] ) {
				case 'text-input':
					$this->render_text_input( $fieldName, $field[0], $currentSettingValue );
					break;
				case 'select':
					$this->render_select( $fieldName, $field[0], $field[5], $currentSettingValue );
					break;
				case 'checkbox':
					$this->render_checkbox( $fieldName, $field[0], $field[5], $currentSettingValue );
					break;
			}
		}

		// render information
		?>
		<p><a href="https://developers.google.com/youtube/player_parameters" target="_blank"><?php echo( __( 'Get more information about the YouTube player parameters.', FF_YOUTUBE_TEXT_DOMAIN ) ) ?></a></p>
	<?php
	}

	/**
	 * Renders a HTML text input box.
	 *
	 * @param string $field_name The name/ID of the text input box.
	 * @param string $description A description.
	 * @param mixed $currentValue The current value of the input box.
	 */
	private function render_text_input( $field_name, $description, $currentValue ) {
		?>
		<p>
			<label for="<?php echo( $this->get_field_id( $field_name ) ) ?>"><?php echo( esc_html( $description ) ) ?></label>
			<input class="widefat" id="<?php echo( $this->get_field_id( $field_name ) ) ?>" name="<?php echo( $this->get_field_name( $field_name ) ) ?>" type="text" value="<?php echo( esc_attr( $currentValue ) ) ?>"/>
		</p>
	<?php
	}

	/**
	 * Renders a HTML checkbox.
	 *
	 * @param string $field_name The name/ID of the checkbox.
	 * @param string $description A description.
	 * @param array $values The possibles values of the checkbox. Must be an array if two elements.
	 * @param mixed $currentValue The current value of the checkbox. If $currentValue if the same value as the first value of $values the checkbox will be checked.
	 */
	private function render_checkbox( $field_name, $description, $values, $currentValue ) {
		?>
		<p>
			<input name="<?php echo( $this->get_field_name( $field_name ) ) ?>" id="<?php echo( $this->get_field_id( $field_name ) ) ?>" value="<?php echo( esc_attr( $values[1] ) ) ?>" type="checkbox"<?php echo( $currentValue == $values[1] ? 'checked = "checked"' : '' ) ?> class="checkbox"/>
			<label for="<?php echo( $this->get_field_id( $field_name ) ) ?>"><?php echo( esc_html( $description ) ) ?></label>
		</p>
	<?php
	}

	/**
	 * Renders a HTML option select box.
	 *
	 * @param string $field_name The name/ID of the option select box.
	 * @param string $description A description.
	 * @param array $options An array of key-values-pairs. The key is the option value and the value is the nice name of the option.
	 * @param mixed $currentValue The current value of the option select box, i.e., a key of $options.
	 */
	private function render_select( $field_name, $description, $options, $currentValue ) {
		?>
		<p>
			<label for="<?php echo( $this->get_field_id( $field_name ) ) ?>"><?php echo( esc_html( $description ) ) ?></label>
			<select name="<?php echo( $this->get_field_name( $field_name ) ) ?>" id="<?php echo( $this->get_field_id( $field_name ) ) ?>" class="widefat">
				<?php
				foreach ( $options as $option => $optionName ) {
					echo( sprintf( '<option value="%s"%s>%s</option>',
						esc_attr( $option ), // option value
						$currentValue === $option ? ' selected="selected"' : '', // option selected?
						esc_html( $optionName ) // the nice name of the option
					) );
				}
				?>
			</select>
		</p>
	<?php
	}


	/**
	 * Tries to parse a YouTube video URL for the video ID. For example it gets z9bQ341IpwE from http://www.youtube.com/watch?&v=z9bQ341IpwE
	 * If just a video ID like string is specified it tries to return a video ID too.
	 *
	 * @param string $videoUrlOrId A YouTube video URL or a video ID like string.
	 *
	 * @return bool|string Returns a YouTube video ID or <code>FALSE</code> if the parsing failed or $videoUrlOrId is not a string.
	 */
	private static function parse_video_url( $videoUrlOrId ) {
		// return if $videoUrlOrId is not a string
		if ( !is_string( $videoUrlOrId ) )
			return false;

		// match for YouTube video ID; get the id from the whole video url or check if we already have an id
		if ( preg_match( '/(?:.*v=(?P<ytid>[a-z0-9_-]+).*)|(?P<ytid_alt>[a-z0-9_-]+)/i', $videoUrlOrId, $matches ) === 1 )
			return ( $matches['ytid'] != '' ? $matches['ytid'] : $matches['ytid_alt'] );

		return false;
	}

}


// plugins loaded action hook
add_action( 'plugins_loaded', 'ff_youtube_widget_plugins_loaded' );

/**
 * Loads the current language for translating the widget.
 */
function ff_youtube_widget_plugins_loaded() {
	// get folder of the widget and add "/languages" directory
	$languagesDir = basename( dirname( __FILE__ ) ) . '/languages';
	// load language
	load_plugin_textdomain( FF_YOUTUBE_TEXT_DOMAIN, false, $languagesDir );
}


// register the widget via hook
add_action( 'widgets_init', create_function( '', 'return register_widget("FF_YouTubeWidget");' ) );
