<?php

namespace PhoneDict;

class Tellows extends CallerStruct {

	public function query($phone){
		$url = "https://www.tellows.es/num/$phone";
	}

}

?>
