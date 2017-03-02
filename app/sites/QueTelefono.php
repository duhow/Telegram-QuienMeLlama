<?php

namespace PhoneDict;

class QueTelefono extends CallerStruct {
	public $site = "Que Telefono";

	public function query($phone){
		$url = "https://quetelefono.com/$phone/amp" ;
		$web = file_get_contents($url);

		// CONTENT
		$pos = strpos($web, '<div class="container">');
		// $web = substr($web, $pos);

		// MOVE TO COMMENTS
		$pos = strpos($web, '<div class="card-container">', $pos);
		$pos = strpos($web, '<div class="card-full">', $pos);

		$lastpos = 0;
		$search = array();
		while($pos !== FALSE && $pos != $lastpos){
			$lastpos = $pos;
			$pos = strpos($web, 'card__content', $pos);
			if($pos != $lastpos){
				$search[] = $pos;
			}
		}

		if(!empty($search)){
			foreach($search as $compos){
				$pos = strpos($web, 'datetime=', $compos);
				$date = strtotime(substr($web, $pos + strlen('datetime="'), 25));

				if($date > 0){
					$this->date = date("Y-m-d H:i:s", $date);
				}

				$pos = strpos($web, '</h3>', $pos) + strlen('</h3>');
				$lim = strpos($web, '<h4', $pos) - $pos; // Marcado como

				$comment = substr($web, $pos, $lim);
				$comment = html_entity_decode($comment); // Accents
				$comment = strip_tags($comment); // Remove HTML
				$comment = trim($comment);

				if(!empty($comment)){
					$this->reviews[] = $comment;
				}

				if(strpos($web, "Dueño", $pos) !== FALSE){
					$pos = strpos($web, "Dueño", $pos);
					$pos = strpos($web, "<p>", $pos);
					$lim = strpos($web, "</p>", $pos) - $pos;

					$name = substr($web, $pos, $lim);
					$name = html_entity_decode($name);
					$name = strip_tags($name);
					$name = trim($name);

					if(!empty($name) && empty($this->name)){
						$this->name = $name;
					}
				}

				$pos = strpos($web, '<span class="progress-value"', $pos);
				$rating = substr($web, $pos, 100);
				$rating = strip_tags($rating);
				$rating = filter_var($rating, FILTER_SANITIZE_NUMBER_INT);

				if(!empty($rating)){
					$this->rating[] = $rating;
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
