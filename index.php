<?php
include_once("MuzodoWebWidget.php"); 

/**
 * Plugin Name: Muzodo Events
 * Plugin URI: http://wordpress.org/plugins/muzodo/ 
 * Version: 2.2.4
 * Author: Chris Ahern
 * Author URI: http://muzodo.com 
 * Description: Displays the upcoming events of a muzodo group.
 * Text Domain: muzodo
 * Domain Path: /languages/
 *
 */
 
function muzodo_render_callback( $block_attributes, $content ) {
		$apikey = (isset($block_attributes['apiKey']) && !empty($block_attributes['apiKey'])) ? sanitize_text_field($block_attributes['apiKey']) : '';
		$filterPrivateEvents = (isset($block_attributes['filterPrivate']) && !empty($block_attributes['filterPrivate'])) ? 1 : 0;
		$filterCancelledEvents = (isset($block_attributes['filterCancelled']) && !empty($block_attributes['filterCancelled'])) ? 1 : 0;
		$filterUnconfirmedEvents = (isset($block_attributes['filterUnconfirmed']) && !empty($block_attributes['filterUnconfirmed'])) ? 1 : 0;
		$dateTimeSeparate = (isset($block_attributes['dateTimeSeparate']) && !empty($block_attributes['dateTimeSeparate'])) ? 1 : 0;
		$dateFormat = (isset($block_attributes['dateFormat']) && !empty($block_attributes['dateFormat'])) ? sanitize_text_field($block_attributes['dateFormat']) : '';
		$timeFormat = (isset($block_attributes['timeFormat']) && !empty($block_attributes['timeFormat'])) ? sanitize_text_field($block_attributes['timeFormat']) : '';
		$showVenue = (isset($block_attributes['showVenue']) && !empty($block_attributes['showVenue'])) ? 1 : 0;
		$noEventsText = (isset($block_attributes['noEventsText']) && !empty($block_attributes['noEventsText'])) ? sanitize_text_field($block_attributes['noEventsText']) : '';

		$muzodoWebWidget = new MuzodoWebWidget($apikey);
		$muzodoWebWidget->filterPrivateEvents($filterPrivateEvents);
		$muzodoWebWidget->filterCancelledEvents($filterCancelledEvents);
		$muzodoWebWidget->filterUnconfirmedEvents($filterUnconfirmedEvents);

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
		$events = $muzodoWebWidget->getUpcomingEvents();


		if (count($events) == 0) {
			$out = "<p>" . $noEventsText . "</p>";
			return $out;
		}
		
		$out = "
<table class='performance muzodo-table' style='border-width: 1px;'>
  <thead class='muzodo-table-head'>
    <tr class='muzodo-header-row'>";
	
		if ($dateTimeSeparate == 1 && $timeFormat != "NONE") {
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
		
		if ($showVenue == 1) {
			$out .= "
		<td class='widget-title muzodo-performance-short'>" . __('Event', 'muzodo') . "</td>
		<td></td>
		<td class='widget-title muzodo-venue'>" . __('Venue', 'muzodo') . "</td>";
		
		} else {
			$out .= "
        <td class='widget-title muzodo-performance-long'>" . __('Event', 'muzodo') . "</td>";
		}
		
		$out .= "
    </tr>
  </thead>
  <tbody class='muzodo-table-body'>";

	foreach ($events as $key => $event) {
		$times = $event->FormattedStartTime . (($timeFormat == "START-AND-END") ? "-" . $event->FormattedEndTime : "");
		//$out .= "times=$times";
		if ($event->FormattedStartTime == "TBD" || $timeFormat == "NONE") {
			$times = "";
		}
		
		$formattedDate = $event->FormattedDate;  // LONG-DAY
		
		if ($dateFormat == "LONG") {
			$formattedDate = substr($event->FormattedDate, 4);
		} elseif ($dateFormat == "SHORT-DD-MON") {
			$parts = explode(" ", substr($event->FormattedDate, 4));
			$formattedDate = $parts[0] . " " . $parts[1];
		} elseif ($dateFormat == "SHORT-MON-DD") {
			$parts = explode(" ", substr($event->FormattedDate, 4));
			$formattedDate = $parts[1] . " " . $parts[0];
		}
		
		//$out .= "formattedDate=$formattedDate";
		
		$dateValue = $formattedDate . (($times != "") ? ", " . $times : "");
		$timeValue = "";
		if ($dateTimeSeparate == 1) {
			$dateValue = $formattedDate;
			$timeValue = $times;
		}
			
		$out .= "
    <tr class='muzodo-body-row'>
        <td class='performance-date'>
                " . $dateValue . "
        </td>";
		
		if ($dateTimeSeparate == 1 && $timeFormat != "NONE") {
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
		
		if ($showVenue == 1) {
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

	//$out .= "<div>This is a result: " . print_r($block_attributes, true) . "<br><br>apikey=$apikey</div>";
	$out .= $muzodoWebWidget->getSchemas();

	return $out;
}

function muzodo_events_register_block() {
    register_block_type( __DIR__, array(
               'api_version' => 3,
		   //'editor_script' => 'gutenberg-examples-dynamic',
		   'render_callback' => 'muzodo_render_callback',
		   'example'  => array(
                        'attributes' => array(
                            'mode' => 'preview',
                        )
                    )
	   )
	);
}

add_action( 'init', 'muzodo_events_register_block' );
