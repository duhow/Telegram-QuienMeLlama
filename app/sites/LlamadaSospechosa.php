<?php

namespace PhoneDict;

class ListaSpam extends CallerStruct {
	public $site = "Llamada Sospechosa";

	public function query($phone){
		$url = "http://llamadasospechosa.com/telefono-celular-movil-$phone";
		$web = file_get_contents($url);

		if(
			strpos($web, "a la fecha no presenta denuncias") !== FALSE // encontradas para el
		){
			$this->result = FALSE;
			return;
		}

		$pos = strpos($web, '<ul class="media-list"');
		$lastpos = 0;
		$search = array();
		while($pos !== FALSE && $pos != $lastpos){
			$lastpos = $pos;
			$pos = strpos($web, '<li class="media comment', $pos + 1);
			if($pos != $lastpos){
				$search[] = $pos;
			}
		}

		if(!empty($search)){
			foreach($search as $pos){
				$pos = strpos($web, 'comment_text', $pos) + strlen("comment_text'>");
				$lim = strpos($web, '</p>', $pos) - $pos;

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
