<?php

namespace PhoneDict;

class GuiaTelefonos extends CallerStruct {

	public function query($phone){
		$url = "http://guia-telefonos.com/$phone";
	}

}

?>
