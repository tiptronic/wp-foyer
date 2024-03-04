<?php

/**
 * The channel object model.
 *
 * @since		1.0.0
 *
 * @package		Foyer
 * @subpackage	Foyer/includes
 * @author		Menno Luitjes <menno@mennoluitjes.nl>
 */
class Foyer_Channel {

	/**
	 * The Foyer Channel post type name.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $post_type_name    The Foyer Channel post type name.
	 */
	const post_type_name = 'foyer_channel';

	public $ID;
	private $post;

	/**
	 * The slides of this channel.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $slides    The slides of this channel.
	 */
	private $slides;

	/**
	 * The slides duration setting of this channel.
	 *
	 * @since    1.7.6
	 * @access   private
	 * @var      string    $slides    The slides duration setting of this channel.
	 */
	private $slides_duration;

    /**
	 * The slide durations JSON setting of this channel. +++andy
     * JSON is written to the database as a string.
     * It is read from the database as a string and then converted to an array.
     * {
     *      key: slide ID
     *      value: slide duration
     * }
     * 
     * Note: everything must be set in quotes
     * e.g. {"5":"6","6":"7"}
	 *
	 * @since    1.7.6
	 * @access   private
	 * @var      string    $slides    The slides duration setting of this channel.
	 */
	private $slide_durations;

	/**
	 * The slides transition setting of this channel.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $slides    The slides transition setting of this channel.
	 */
	private $slides_transition;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since	1.0.0
	 * @param	int or WP_Post	$ID		The id or the WP_Post object of the channel.
	 */
	public function __construct( $ID = false ) {

		if ( $ID instanceof WP_Post ) {
			// $ID is a WP_Post object
			$this->post = $ID;
			$ID = $ID->ID;
		}

		$this->ID = $ID;
	}

	/**
	 * Outputs the channel classes for use in the template.
	 *
	 * The output is escaped, so this method can be used in templates without further escaping.
	 *
	 * @since	1.0.1			Escaped the output.
	 *
	 * @param 	array 	$classes
	 * @return 	string
	 */
	public function classes( $classes = array() ) {

		$classes[] = 'foyer-channel';
		$classes[] = 'foyer-channel-' . intval( $this->ID );
		$classes[] = 'foyer-transition-' . $this->get_slides_transition();

		if ( empty( $classes ) ) {
			return;
		}

		?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" <?php
	}

	/**
	 * Get slides for this channel.
	 *
	 * @since	1.0.0
	 * @since	1.3.2	Only includes slides that are published.
	 *
	 * @access	public
	 * @return	array of Foyer_Slide	The slides for this channel.
	 */
	public function get_slides() {

		if ( ! isset( $this->slides ) ) {

			$slides = array();

			$posts = get_post_meta( $this->ID, Foyer_Slide::post_type_name, true );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {

					// Only include slides with post status 'publish'
					if ( 'publish' != get_post_status( $post ) ) {
						continue;
					}

					$slide = new Foyer_Slide( $post );
					$slides[] = $slide;
				}
			}

			$this->slides = $slides;
		}

		return $this->slides;
	}

	/**
	 * Get slides duration setting for this channel as saved in the database.
	 *
	 * @since	1.0.0
	 * @access	public
	 * @return	string		The slides duration setting for this channel as saved in the database.
	 */
	public function get_saved_slides_duration() {
		return get_post_meta( $this->ID, Foyer_Channel::post_type_name . '_slides_duration', true );
	}

    /**
	 * Get single slides duration setting as JSON for this channel as saved in the database.
	 *
	 * @since	1.7.6
	 * @access	public
	 * @return	string		The slide durations setting for this channel as saved in the database.
	 */

    public function get_saved_slide_durations() { //+++andy
        return get_post_meta( $this->ID, Foyer_Channel::post_type_name . '_slide_durations', true );
    }

	/**
	 * Get slides transition setting for this channel as saved in the database.
	 *
	 * @since	1.0.0
	 * @access	public
	 * @return	string		The slides transition setting for this channel as saved in the database.
	 */
	public function get_saved_slides_transition() {
			return get_post_meta( $this->ID, Foyer_Channel::post_type_name . '_slides_transition', true );
	}

	/**
	 * Get slides duration setting for this channel, or the default slides duration when not set.
	 *
	 * @since	1.0.0
	 * @access	public
	 * @return	string		The slides duration setting for this channel, or the default slides duration when not set.
	 */
	public function get_slides_duration() {

		if ( ! isset( $this->slides_duration ) ) {

			$slides_duration = self::get_saved_slides_duration();
			if ( empty( $slides_duration ) ) {
				$slides_duration = Foyer_Slides::get_default_slides_duration();
			}
			$this->slides_duration = $slides_duration;
		}

		return $this->slides_duration;
	}

    /**
	 * Get slide durations JSON setting for this channel, or the default slides duration when not set. +++andy
	 *
	 * @since	1.7.6
	 * @access	public
	 * @return	string		The slides duration setting for this channel, or the default slides duration when not set.
	 */
	public function get_slide_durations() {

		if ( ! isset( $this->slide_durations ) ) {
            $slide_durations = self::get_saved_slide_durations();
			if ( empty( $slide_durations ) ) {
                $slide_durations = array();
                // $slide_durations = array(
                //     5 => 6,
                //     8 => 22,
                //     11 => 11,
                //     12 => 14
                // );
                // update_post_meta( $this->ID, Foyer_Channel::post_type_name . '_slide_durations', $slide_durations );
			}
            $this->slide_durations = $slide_durations;
		}

		return $this->slide_durations;
	}

    public function get_slide_duration_for_slide($slide_id) {
        $slide_durations = $this->get_slide_durations();
        if(isset($slide_durations[$slide_id])) {
            return $slide_durations[$slide_id];
        }
        return $this->get_slides_duration();
    }

	/**
	 * Get slides transition setting for this channel, or the default slides transition when not set.
	 *
	 * @since	1.0.0
	 * @access	public
	 * @return	string		The slides transition setting for this channel, or the default slides transition when not set.
	 */
	public function get_slides_transition() {

		if ( ! isset( $this->slides_transition ) ) {

			$slides_transition = self::get_saved_slides_transition();
			if ( empty( $slides_transition ) ) {
				$slides_transition = Foyer_Slides::get_default_slides_transition();
			}
			$this->slides_transition = $slides_transition;
		}

		return $this->slides_transition;
	}
}
