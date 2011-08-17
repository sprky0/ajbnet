<?php

/**
 * This is the same functionality availble in AJBnet core
 * AJBnet class.  So I will do that now.
 * 
 * @package AJBnet
 * @subpackage Library
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 8/2009
 */
class Timer {

	/**
	 * Storage for Timer markers
	 * 
	 * @var array
	 */
	private $Markers = array();

	public function __construct($MarkStart=TRUE) {
		if ((boolean)$MarkStart)
			$this->Mark("Construct");
	}

	/**
	 * @param string $Label Label for marker.
	 * @return float Time of mark.
	 */
	public function Mark($Label = "") {
		$Marker = array(
			0 => microtime(),
			1 => $Label
		);
		$this->Markers[] = $Marker;
		return $Marker[0];
	}

	/**
	 * @return float
	 */
	public function TotalDuration() {
		if (count($this->Markers) == 0)
			throw new ApplicationException("Can't determine start time!");
		else if (count($this->Markers) == 1)
			$this->Mark("Get Duration");
		return ($this->Markers[count($this->Markers) - 1][0] - $this->Markers[0][0]) * 1000;
	}

}

?>