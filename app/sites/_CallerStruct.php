<?php

namespace PhoneDict;

define("PHONEDICT_DEBUG", TRUE);

class CallerStruct {
	public $result = FALSE;
	public $enabled = TRUE;

	public $phone = NULL;
	public $name = NULL;
	public $date = NULL;
	public $location = ['city' => NULL, 'country' => 'Spain'];
	public $is_spam = NULL;
	public $rating = NULL; // More is dangerous
	public $reviews = array();

	public $site = NULL;

	protected function query($phone){ }

	public function __construct($phone = NULL){
		if(empty($phone)){ return; }
		if(!$this->enabled){ return; }

		if(PHONEDICT_DEBUG){
			error_log("Starting review in " .$this->site);
			$time = microtime(true);
		}

		$this->phone = $phone;
		$this->query($phone);

		if(PHONEDICT_DEBUG){
			error_log("Review " .$this->site ." took " . round((microtime(true) - $time)*1000) ."ms");
		}
	}
}

?>
