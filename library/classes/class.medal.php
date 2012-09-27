<?php
/**
 * CLASS: Medal
 *
 * Contains all information about a medal.
 */

class Medal {
	
	public $army_id		= -1;
	public $id			= -1;
	public $img			= ""; //String containing relative URL to image used when displaying detailed info
	public $name		= "";
	public $ribbon		= ""; //String containing relative URL to image used in medal signatures
	public $time_stamp	= -1; //Time stamp of last update
	
}