<?php

namespace PhoneDict;

class ResponderONo extends CallerStruct {
	public $site = "Debería responder?";

	public function query($phone){
		$url = "https://www.responderono.es/numero-de-telefono/$phone" ;
		$web = file_get_contents($url);

		// CONTENT
		$pos = strpos($web, '<div class="mainInfoHeader">');
		$this->is_spam = (stripos(substr($web, 0, 220), 'negativ') !== FALSE);

		// GET ACTIVE RATINGS (most demanded)
		$pos = strpos($web, '<div class="ratings">', $pos);
		$pos = strpos($web, "<li class='active'>", $pos);
		$lim = strpos($web, '</li>', $pos);
		$ratings = filter_var(substr($web, $pos, $lim), FILTER_SANITIZE_NUMBER_INT);

		// GET COMMENTS
		$pos = strpos($web, '<div class="containerReviews">', $pos);
		$pos = strpos($web, '<div class="review reviewNew">', $pos);

		$lastpos = 0;
		$search = array();
		while($pos !== FALSE && $pos != $lastpos){
			$lastpos = $pos;
			$pos = strpos($web, '<div class="review" data-reviewid', $pos);
			if($pos != $lastpos){
				$search[] = $pos;
			}
		}

		if(!empty($search)){
			foreach($search as $compos){
				$pos = strpos($web, '<meta itemprop="ratingValue"', $pos);
				$rating = substr($web, $pos, 15);
				$rating = filter_var($rating, FILTER_SANITIZE_NUMBER_INT);

				if(!empty($rating)){
					$this->rating[] = $rating;
				}

				$pos = strpos($web, 'datetime=', $compos);
				$date = strtotime(substr($web, $pos + strlen('datetime="'), 25));

				if($date > 0){
					$this->date = date("Y-m-d H:i:s", $date);
				}

				$pos = strpos($web, 'itemprop="description">', $pos) + strlen('itemprop="description">');
				$lim = strpos($web, '</span>', $pos) - $pos; // span class="review_comment"

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

			if(!empty($this->reviews) or $this->is_spam){ $this->result = TRUE; }
		}
	}
}

?>
