<?php

namespace PhoneDict;

class ListaSpam extends CallerStruct {

	public function query($phone){
		$url = "http://www.listaspam.com/busca.php?Telefono=$phone";
	}

}

?>
