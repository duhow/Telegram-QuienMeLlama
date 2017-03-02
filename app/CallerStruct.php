<?php

class CallerStruct {
	public $result = FALSE;

	public $phone = NULL;
	public $name = NULL;
	public $date = NULL;
	public $is_spam = NULL;
	public $rating = NULL;
	public $reviews = array();

	public $site = NULL;

	protected function query($phone){ }

	public function __construct($phone = NULL){
		if(!empty($phone)){
			$this->phone = $phone;
			$this->query($phone);
		}
	}
}

?>
