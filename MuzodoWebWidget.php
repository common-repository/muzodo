<?php
/**
 * @package muzodo
 * @link    https://www.muzodo.com/
 * @author  Chris Ahern <chris@muzodo.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version 1.1
 */ 

/**
 * The Muzodo Web Widget class
 *
 * This class is responsible for fetching events from Muzodo for the given Group API ID 
 * and filtering events based on user preferences.
 *
 * @see Style
 * @package muzodo
 */ 
class MuzodoWebWidget {
	private $_apiId = "";
	private $_filterPrivateEvents = true;
	private $_filterCancelledEvents = true;
	private $_filterUnconfirmedEvents = true;
	private $_schemas = "";

	function __construct($apiId) {
		$this->setApiID($apiId);
	} 
  
	function get_url_contents($url){
		$crl = curl_init();
		$timeout = 5;
		curl_setopt ($crl, CURLOPT_URL,$url);
		curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
		$ret = curl_exec($crl);
		curl_close($crl);
		return $ret;
	}

	// Set API ID	
	public function setApiID($apiId) {
		$this->_apiId = $apiId;
	}
	
	public function filterPrivateEvents($truefalse) {
		$this->_filterPrivateEvents = $truefalse;
	}

	public function filterCancelledEvents($truefalse) {
		$this->_filterCancelledEvents = $truefalse;
	}

	public function filterUnconfirmedEvents($truefalse) {
		$this->_filterUnconfirmedEvents = $truefalse;
	}
	
	public function getUpcomingEvents() {
		$url = "https://muzodo.com/api/v1/group/" . $this->_apiId . "/events";
		$events = json_decode(stripslashes($this->get_url_contents($url)));

		$filteredEvents = array();
		foreach ($events as $key => $event) {
			if ($this->_filterUnconfirmedEvents && $event->Confirmed == 0)
				continue;

			if ($this->_filterCancelledEvents && $event->Cancelled == 1)
				continue;

			if ($this->_filterPrivateEvents && $event->EventType == "PRIVATE")
				continue;

			if ($event->EventType != "PUBLIC" && $event->EventType != "PRIVATE")
				continue;

			array_push($filteredEvents, $event);
			
			$this->_schemas .= $event->schema;
		}
		
		return $filteredEvents;
	}

	public function getSchemas() {
		return $this->_schemas;
	}
}

?>
