<?php

namespace PhoneDict;

class TelefonoSpam extends CallerStruct {
	public $site = "TelefonoSpam";

	public function query($phone){
		$url = "http://www.telefonospam.es/$phone";
		$web = file_get_contents($url);

		if(strpos($web, "no ha sido comentado") !== FALSE){
			$this->result = FALSE;
			return;
		}

		$pos = strpos($web, '<div class="cat">');

		$lastpos = 0;
		$search = array();
		while($pos !== FALSE && $pos != $lastpos){
			$lastpos = $pos;
			$pos = strpos($web, '<div id="comentario', $pos + 1);
			if($pos != $lastpos){
				$search[] = $pos;
			}
		}

		if(!empty($search)){
			foreach($search as $pos){
				$pos = strpos($web, " hace ", $pos);
				$pos = strpos($web, "<em", $pos);
				$lim = strpos($web, "</em>", $pos);

				$comment = substr($web, $pos, $lim);
				$comment = html_entity_decode($comment); // Accents
				$comment = strip_tags($comment); // Remove HTML
				$comment = trim($comment);

				if(!empty($comment)){
					$this->reviews[] = $comment;
				}
			}
			if(!empty($this->reviews)){ $this->result = TRUE; }
		}
	}
}

?>
