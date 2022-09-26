<?php

namespace PhoneDict;

class GuiaTelefonos extends CallerStruct {
	public $site = "Guía Telefonos";

	public function query($phone){
		$url = "https://guia-telefonos.com/$phone";
		$web = file_get_contents($url);

		if(strpos($web, "No hay comentarios para este") !== FALSE){
			$this->result = FALSE;
			return;
		}

		$pos = strpos($web, '<div id="comments">');

		$lastpos = 0;
		$search = array();
		while($pos !== FALSE && $pos != $lastpos){
			$lastpos = $pos;
			$pos = strpos($web, '<div class="comment', $pos + 1);
			if($pos != $lastpos){
				$search[] = $pos;
			}
		}

		if(!empty($search)){
			foreach($search as $pos){
				$pos = strpos($web, '<div class="comment_comment">', $pos);
				$lim = strpos($web, '</div>', $pos) - $pos;

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
