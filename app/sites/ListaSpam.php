<?php

namespace PhoneDict;

class ListaSpam extends CallerStruct {
	public $site = "Lista Spam";

	public function query($phone){
		$url = "https://www.listaspam.com/busca.php?Telefono=$phone";
		$web = file_get_contents($url);

		if(
			strpos($web, "Total denuncias</strong") !== FALSE // encontradas para el
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

			if($this->rating != NULL){
				$this->rating = array_sum($this->rating) / count($this->rating);
			}
			if(!empty($this->reviews)){ $this->result = TRUE; }
		}
	}
}

?>
