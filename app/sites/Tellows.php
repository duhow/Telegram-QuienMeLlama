<?php

namespace PhoneDict;

class Tellows extends CallerStruct {

	public function query($phone){
		$url = "https://www.tellows.es/num/$phone?mobile=1";
		$web = file_get_contents($url);

		if(strpos($web, "Todavía no hay comentarios para") !== FALSE){
			$this->result = FALSE;
			return;
		}

		$pos = strpos($web, "Nombre / empresa") + strlen("Nombre / empresa:");
		$lim = strpos($web, '<span id="klappenFirma"', $pos) - $pos;

		$name = substr($web, $pos, $lim);
		$name = html_entity_decode($name);
		$name = strip_tags($name);
		$name = trim($name);

		if(!empty($name) && empty($this->name)){
			$this->name = $name;
		}

		$pos = strpos($web, '<ol id="singlecomments"');
		$lastpos = 0;
		$search = array();
		while($pos !== FALSE && $pos != $lastpos){
			$lastpos = $pos;
			$pos = strpos($web, '<li id=', $pos);
			if($pos != $lastpos){
				$search[] = $pos;
			}
		}

		if(!empty($search)){
			foreach($search as $compos){
				$pos = strpos($web, '<div id="score" class="realscore', $pos) + 32 // strlen;
				$rating = substr($web, $pos, 18);
				$rating = strip_tags($rating);
				$rating = filter_var($rating, FILTER_SANITIZE_NUMBER_INT);

				if(!empty($rating)){
					$this->rating[] = $rating;
				}

				$pos = strpos($web, '<div class="comment-meta', $pos);
				$lim = strpos($web, '</div>', $pos) - $pos;

				$date = substr($web, $pos, $lim);
				$date = strip_tags($date);
				$date = trim($date);

				if(!empty($date)){
					$this->date = date("Y-m-d H:i:s", strtotime($date));
				}

				$pos = strpos($web, '<p>', $pos);
				$lim = strpos($web, '</p>', $pos) - $pos;

				$comment = substr($web, $pos, $lim);
				$comment = html_entity_decode($comment); // Accents
				$comment = strip_tags($comment); // Remove HTML
				$comment = trim($comment);

				if(!empty($comment)){
					$this->reviews[] = $comment;
				}
			}

			$this->rating = array_sum($this->rating) / count($this->rating);
			if(!empty($this->reviews)){ $this->result = TRUE; }
		}
	}

}

?>
