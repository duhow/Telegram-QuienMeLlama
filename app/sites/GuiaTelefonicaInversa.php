<?php

namespace PhoneDict;

class GuiaTelefonicaInversa extends CallerStruct {
	public $site = "Guía telefónica inversa";

	public function query($phone){
		$url = "https://www.guiatelefonicainversa.es/numero/$phone";
		$web = file_get_contents($url);

		if(
			strpos($web, "Nadie ha comentado este") !== FALSE or
			strpos($web, '<div id="recaptcha"') !== FALSE
		){
			$this->result = FALSE;
			return;
		}

		// CONTENT
		$pos = strpos($web, '<table class="commentList">');

		$lastpos = 0;
		$search = array();
		while($pos !== FALSE && $pos != $lastpos){
			$lastpos = $pos;
			$pos = strpos($web, '<tr class="rank', $pos + 1);
			if($pos != $lastpos){
				$search[] = $pos;
			}
		}

		if(!empty($search)){
			foreach($search as $pos){
				$pos = strpos($web, 'ratingValue', $pos) + strlen("ratingValue' ");
				$lim = strpos($web, '>', $pos) - $pos;
				$rating = substr($web, $pos, $lim);
				$rating = filter_var($rating, FILTER_SANITIZE_NUMBER_INT);

				if(!empty($rating)){
					$this->rating[] = 8 - ($rating * 2);
				}

				$pos = strpos($web, 'datePublished', $pos) + strlen('datePublished" content="');

				$date = substr($web, $pos, 10);
				$date = trim($date);
				$date = strtotime($date);

				if($date != FALSE && $date > 0){
					$this->date = date("Y-m-d H:i:s", strtotime($date));
				}

				$pos = strpos($web, '<td id=', $pos);
				$lim = strpos($web, '</td>', $pos) - $pos;

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
