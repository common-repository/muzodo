<?php
 include_once("MuzodoWebWidget.php");
/*
Plugin Name: Muzodo Upcoming Events Widget
Plugin URI: http://wordpress.org/plugins/muzodo/ 
Version: 1.1.2
Author: Chris Ahern
Author URI: http://muzodo.com 
Description: Displays the upcoming events of a muzodo group.
Text Domain: muzodo
Domain Path: /languages/
*/

class Muzodo_Upcoming_Events extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'muzodo-widget', // Base ID
			__('Muzodo Upcoming Events', 'muzodo'), // Name
			array( 'description' => __('Displays a list of upcoming events for your group directly from Muzodo.', 'muzodo'), ) // Args
		);
	}

	function form($instance) {

	  //$defaults = array('muzodoapikey' => '', 'title' => ''); //, numberposts' => '5','catid'=>'1','title'=>'','rss'=>'');
	  $defaults = array(
		'muzodoapikey' => '',
		'muzodo-filterPrivateEvents' => '1',
		'muzodo-filterCancelledEvents' => '1',
		'muzodo-filterUnconfirmedEvents' => '1',
		'muzodo-dateAndTimeFormat' => 'ONECOLUMN',
		'muzodo-showAddress' => '0'
	  );
	  
	  $dateAndTimeOptions = array(
		'ONECOLUMN' => __('Date and Time in one column', 'muzodo'),
		'DATEONLY' => __('Date only', 'muzodo'),
		'SEPARATE' => __('Date and Time separate', 'muzodo'));

	  $instance = wp_parse_args((array) $instance, $defaults ); 
?>

	  <p>
	     <label for="<?php echo $this->get_field_id('muzodoapikey'); ?>"><?= __('Muzodo API Key:', 'muzodo') ?></label>
	     <input type="text" name="<?php echo $this->get_field_name('muzodoapikey') ?>" 
			id="<?php echo $this->get_field_id('muzodoapikey') ?>" 
			value="<?php echo $instance['muzodoapikey'] ?>" 
			size="36"
			style="font-size: smaller;">
	  </p>

	  <p>
         <input type="checkbox" name="<?php echo $this->get_field_name('muzodo-filterPrivateEvents') ?>" 
			id="<?php echo $this->get_field_name('muzodo-filterPrivateEvents') ?>" <?php checked($instance['muzodo-filterPrivateEvents'], 1); ?> />
	     <label for="<?php echo $this->get_field_id('muzodo-filterPrivateEvents'); ?>"><?= __('Filter Private Events', 'muzodo') ?></label>
	  </p>

	  <p>
         <input type="checkbox" name="<?php echo $this->get_field_name('muzodo-filterCancelledEvents') ?>" 
			id="<?php echo $this->get_field_name('muzodo-filterCancelledEvents') ?>" <?php checked($instance['muzodo-filterCancelledEvents'], 1); ?> />
	     <label for="<?php echo $this->get_field_id('muzodo-filterCancelledEvents'); ?>"><?= __('Filter Cancelled Events', 'muzodo') ?></label>
	  </p>

	  <p>
         <input type="checkbox" name="<?php echo $this->get_field_name('muzodo-filterUnconfirmedEvents') ?>" 
			id="<?php echo $this->get_field_name('muzodo-filterUnconfirmedEvents') ?>" <?php checked($instance['muzodo-filterUnconfirmedEvents'], 1); ?> />
	     <label for="<?php echo $this->get_field_id('muzodo-filterUnconfirmedEvents'); ?>"><?= __('Filter Unconfirmed Events', 'muzodo') ?></label>
	  </p>

	  <p>
	     <label for="<?php echo $this->get_field_id('muzodo-dateAndTimeFormat'); ?>"><?= __('Date and Time options:', 'muzodo') ?></label>
		 <select id="<?php echo $this->get_field_id('muzodo-dateAndTimeFormat'); ?>" 
			name="<?php echo $this->get_field_name('muzodo-dateAndTimeFormat'); ?>">
   <?php foreach ($dateAndTimeOptions as $optionKey => $optionValue) {
        echo '<option value="' . $optionKey . '"';
        if ($optionKey == $instance['muzodo-dateAndTimeFormat']) echo ' selected="selected"';
        echo '>' . $optionValue . '</option>';
     } ?>
    </select>
	  </p>
	  
	  <p>
         <input type="checkbox" name="<?php echo $this->get_field_name('muzodo-showAddress') ?>" 
			id="<?php echo $this->get_field_name('muzodo-showAddress') ?>" <?php checked($instance['muzodo-showAddress'], 1); ?> />
	     <label for="<?php echo $this->get_field_id('muzodo-showAddress'); ?>"><?=__('Show Venue (public events only)', 'muzodo') ?></label>
	  </p>
	  
	  <?php
	  // See: http://www.packtpub.com/article/how-write-widget-wordpress3 for other types of form fields
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
	function update($new_instance, $old_instance) {
	  $instance = $old_instance;

	  $instance['muzodoapikey'] = (isset($new_instance['muzodoapikey']) && !empty($new_instance['muzodoapikey'])) ? sanitize_text_field($new_instance['muzodoapikey']) : '';
	  $instance['muzodo-filterPrivateEvents'] = (isset($new_instance['muzodo-filterPrivateEvents']) && !empty($new_instance['muzodo-filterPrivateEvents'])) ? 1 : 0;
	  $instance['muzodo-filterCancelledEvents'] = (isset($new_instance['muzodo-filterCancelledEvents']) && !empty($new_instance['muzodo-filterCancelledEvents'])) ? 1 : 0;
	  $instance['muzodo-filterUnconfirmedEvents'] = (isset($new_instance['muzodo-filterUnconfirmedEvents']) && !empty($new_instance['muzodo-filterUnconfirmedEvents'])) ? 1 : 0;
	  $instance['muzodo-dateAndTimeFormat'] = (isset($new_instance['muzodo-dateAndTimeFormat']) && !empty($new_instance['muzodo-dateAndTimeFormat'])) ? sanitize_text_field($new_instance['muzodo-dateAndTimeFormat']) : '';
	  
	  $instance['muzodo-showAddress'] = (isset($new_instance['muzodo-showAddress']) && !empty($new_instance['muzodo-showAddress'])) ? 1 : 0;

	  //print_r($instance);
	  return $instance;
	}

 
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget($args, $instance) {
		//print_r($args);
		//print_r($instance);
		
		$apikey = $instance['muzodoapikey'];
		
		$muzodoWebWidget = new MuzodoWebWidget($apikey);
		$muzodoWebWidget->filterPrivateEvents($instance['muzodo-filterPrivateEvents']);
		$muzodoWebWidget->filterCancelledEvents($instance['muzodo-filterCancelledEvents']);
		$muzodoWebWidget->filterUnconfirmedEvents($instance['muzodo-filterUnconfirmedEvents']);

		$cached = "YES";
		$added = "NO";
		//delete_option("muzodocachetimestamp");
		$timestamp = get_option("muzodocachetimestamp", time());
		if ($timestamp == time()) {
			add_option("muzodocachetimestamp", time());
			$events = $muzodoWebWidget->getUpcomingEvents();
			add_option("muzodocachedata", $events);
			$added = "YES";
			$cached = "NO";
		}

		if (time() - $timestamp > 2 * 60) {
			$events = $muzodoWebWidget->getUpcomingEvents();
			update_option("muzodocachetimestamp", time());
			update_option("muzodocachedata", $events);
			$cached = "NO";
		} else{
			$events = get_option("muzodocachedata");
		}
		
		//$out = $timestamp . " curr=" . time() . " added:" . $added . " cache:" . $cached . "
		$out = "
<table class='performance muzodo-table'>
  <thead class='muzodo-table-head'>
    <tr class='muzodo-header-row'>";
	
		if ($instance['muzodo-dateAndTimeFormat'] == 'SEPARATE') {
			$out .= "
		<td class='widget-title muzodo-date'>" . __('Date', 'muzodo') . "</td>
        <td style='width: 8px'></td>
		<td class='widget-title muzodo-time'>" . __('Time', 'muzodo') . "</td>
        <td style='width: 8px'></td>";
		
		} else {
			$out .= "
		<td class='widget-title muzodo-datetime'>" . __('Date', 'muzodo') . "</td>
        <td style='width: 8px'></td>";
		}
		
		if ($instance['muzodo-showAddress'] == 1) {
			$out .= "
		<td class='widget-title muzodo-performance-short'>" . __('Performance', 'muzodo') . "</td>
		<td></td>
		<td class='widget-title muzodo-venue'>" . __('Venue', 'muzodo') . "</td>";
		
		} else {
			$out .= "
        <td class='widget-title muzodo-performance-long'>" . __('Performance', 'muzodo') . "</td>";
		}
		
		$out .= "
    </tr>
  </thead>
  <tbody class='muzodo-table-body'>";

		foreach ($events as $key => $event) {
			$times = ", " . $event->FormattedStartTime . "-" . $event->FormattedEndTime;
			if ($event->FormattedStartTime == "TBD")
			{
				$times = "";
			}
			
			$dateValue = $event->FormattedDate . $times;
			$timeValue = "";
			if ($instance['muzodo-dateAndTimeFormat'] == 'SEPARATE') {
				$dateValue = $event->FormattedDate;
				$timeValue = $event->FormattedStartTime . "-" . $event->FormattedEndTime;
			} elseif ($instance['muzodo-dateAndTimeFormat'] == 'DATEONLY') {
				$dateValue = $event->FormattedDate;
			}
				
			$out .= "
    <tr class='muzodo-body-row'>
        <td class='performance-date'>
                " . $dateValue . "
        </td>";
		
			if ($instance['muzodo-dateAndTimeFormat'] == 'SEPARATE') {
			$out .= "
		<td></td>
        <td class='performance-date'>
                " . $timeValue . "
        </td>";
			}
			
			$out .= "
	<td></td>
        <td class='performance-name'>
                " . $event->Name . "
        </td>";
		
			if ($instance['muzodo-showAddress'] == 1) {
			$out .= "
		<td></td>
        <td class='performance-location'>
                " . $event->Address . "
        </td>";
			}
			
			$out .= "
    </tr>";

    	}

    	$out .= "</tbody> </table>";

		echo $args['before_widget'];
		echo $out;
		echo $muzodoWebWidget->getSchemas();
		echo $args['after_widget'];

	/*
	  $rss = $instance['rss'];

	  global $wpdb;
	  $posts = get_posts('numberposts='.$numberposts.'&category='.$catid);
	  $out = '<ul>';
	  foreach($posts as $post) {
	     $out .= '<li><a href="'.get_permalink($post->ID).'">'.$post->post_title.'</a></li>';
	  }
	  if ($rss) $out .= '<li><a href="'.get_category_link($catid).'feed/" class="rss">Category RSS</a></li>';
	  $out .= '</ul>';
	*/

	}
}

function muzodo_scripts() {
	//wp_enqueue_script('jquery');
	wp_register_style('muzodo-widget', plugins_url('css/muzodo-widget.css', __FILE__));
	wp_enqueue_style('muzodo-widget');
}

function muzodo_load_widgets() {
   register_widget('Muzodo_Upcoming_Events');
}

function load_muzodo_textdomain() {
	load_plugin_textdomain('muzodo', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'load_muzodo_textdomain');
add_action('wp_enqueue_scripts','muzodo_scripts');
add_action('widgets_init', 'muzodo_load_widgets');

?>
