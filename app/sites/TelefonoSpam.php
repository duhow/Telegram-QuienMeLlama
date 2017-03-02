<?php

namespace PhoneDict;

class TelefonoSpam extends CallerStruct {

	public function query($phone){
		$url = "http://www.telefonospam.es/$phone";
	}

}

?>
