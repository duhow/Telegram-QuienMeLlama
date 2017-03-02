<?php

class Main extends TelegramApp\Module {
	protected function hooks(){
		if($this->telegram->words() == 1){
			$tel = $this->telegram->text(TRUE);
			$tel = str_replace([" ", "-", "+"], "", $tel);

			if(is_numeric($tel) && strlen($tel) == 9){
				$q = $this->telegram->send
					->text($this->telegram->emoji(":clock: ") ."Buscando...")
				->send();

				require "app/CallerStruct.php";
				$res = 0;
				foreach(scandir("app/sites/") as $f){
					if($res >= 3){ break; } // HACK LIMIT
					if(is_readable("app/sites/$f") && substr($f, -4) == ".php"){
						require "app/sites/$f";
						$name = substr($f, 0, -4);

						$class = "PhoneDict\\$name";
						$find = new $class($tel);
						if($find->result){
							$this->show_phone_info($find, 4);
							$res++;
						}
					}
				}
				if($res >= 3){
					$this->telegram->send
						->text("Se devuelven los $res primeros resultados.")
					->send();
				}elseif($res == 0){
					$this->telegram->send
						->message($q['message_id'])
						->text("No se han encontrado coincidencias para el teléfono $tel.")
					->edit("text");
				}
				$this->end();
			}
		}
	}

	private function show_phone_info($obj, $maxrews = 0, $offset = 0){
		$str = "<b>$obj->site</b> - ";
		if(!empty($obj->date)){ $str .= date("d/m/Y H:i:s", strtotime($obj->date)); }
		$str .= " Val: " .round($obj->rating, 2) ." ";

		if($obj->rating == 0 or $obj->rating == NULL){ $str .= $this->telegram->emoji(":question-red:"); }
		elseif($obj->rating <= 4){ $str .= $this->telegram->emoji(":ok:"); }
		elseif($obj->rating > 4 && $obj->rating < 6){ $str .= $this->telegram->emoji(":warning:"); }
		elseif($obj->rating > 6){ $str .= $this->telegram->emoji(":times:"); }

		$str .= "\n" .@$obj->name ."\n";
		// TODO offset
		$reviews = array();
		if($maxrews == 0){ $reviews = $obj->reviews; }

		elseif($maxrews > 0){
			$c = 0;
			foreach($obj->reviews as $rev){
				if($c >= $maxrews){ break; }
				$reviews[] = $rev;
				$c++;
			}

			if(count($reviews) > 1){
				unset($reviews[count($reviews) - 1]); // Last duplicates
			}
		}

		$str .= "\n";
		foreach($reviews as $rev){
			$str .= $rev ."\n\n";
		}

		return $this->telegram->send
			->text($str, 'HTML')
		->send();
	}

	public function start(){
		if(!$this->telegram->is_chat_group()){
			$this->help();
		}
	}

	public function help(){
		$str = "<b>¡Bienvenido a Quien Me Llama!</b>\n\n"
				."Este bot te permitirá saber qué empresa o persona te está llamando. Tan sólo tienes que poner su número de teléfono.\n\n"
				."Para evitar abusos, puedes realizar tres peticiones al día.";

		$this->telegram->send
			->text($str, "HTML")
		->send();

		$this->end();
	}
}

?>
