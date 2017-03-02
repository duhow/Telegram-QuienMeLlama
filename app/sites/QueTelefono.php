<?php

namespace PhoneDict;

class QueTelefono extends CallerStruct {

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
				$this->date = date("Y-m-d H:i:s", $date);

				$pos = strpos($web, '</h3>', $pos) + strlen('</h3>');
				$lim = strpos($web, '<div', $pos) - $pos;

				$comment = substr($web, $pos, $lim);
				$comment = html_entity_decode($comment); // Accents
				$comment = strip_tags($comment); // Remove HTML
				$this->reviews[] = $comment;
				
				return $comment;
			}
		}
	}

}

?>
