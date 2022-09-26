<?php

class Main extends TelegramApp\Module {
	protected function hooks(){
		if($this->telegram->words() == 1){
			$tel = $this->telegram->text(TRUE);
			$tel = str_replace([" ", "-", "+"], "", $tel);

			if(is_numeric($tel) && strlen($tel) == 9){
				return $this->process_phone_number($tel);
			}
		}
	}

	private function process_phone_number($tel){
		if(!$this->check_user_available($this->telegram->user->id)){
			$this->telegram->send
				->text($this->telegram->emoji(":warning: Has superado el límite diario."))
			->send();

			$this->end();
		}

		$q = $this->telegram->send
			->text($this->telegram->emoji(":clock2: ") ."Buscando...")
		->send();

		require "app/sites/_CallerStruct.php";
		$res = 0;
		$files = scandir("app/sites/");
		shuffle($files);
		foreach($files as $f){
			if($res >= 3){ break; } // HACK LIMIT
			if(strpos($f, "_CallerStruct") !== FALSE){ continue; }
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
				->text($this->telegram->emoji(":times:") ." No se han encontrado coincidencias para el teléfono $tel.")
			->edit("text");
		}
		$this->end();
	}

	private function check_user_available($user, $limit = 3){
		$users = array();
		if(file_exists("users.txt") && is_readable("users.txt")){
			$date = filemtime("users.txt");
			if(date("d") != date("d", $date)){
				unlink("users.txt");
			}else{
				$fp = fopen("users.txt", "r");
				while(!feof($fp)){
					$u = explode(",", fgets($fp, 32), 2);
					if(empty($u) or count($u) != 2){ continue; }
					$users[$u[0]] = filter_var($u[1], FILTER_SANITIZE_NUMBER_INT);
				}
				fclose($fp);
			}
		}

		$ukey = array_keys($users);
		if(isset($user, $ukey)){
			if($users[$user] >= $limit){ return FALSE; }
		}

		if(!isset($users[$user])){
			$users[$user] = 1;
		}else{
			$users[$user]++;
		}

		$data = "";
		foreach($users as $u => $p){
			$data .= "$u,$p\n";
		}

		file_put_contents("users.txt", $data);
		return TRUE;
	}

	private function show_phone_info($obj, $maxrews = 0, $offset = 0){
		$str = "<b>$obj->site</b> - ";
		if(!empty($obj->date) and strtotime($obj->date) != FALSE){ $str .= date("d/m/Y H:i:s", strtotime($obj->date)); }
		$str .= " Val: " .round($obj->rating, 2) ." ";

		if($obj->rating == 0 or $obj->rating == NULL){ $str .= $this->telegram->emoji(":question:"); }
		elseif($obj->rating <= 4){ $str .= $this->telegram->emoji(":white_check_mark:"); }
		elseif($obj->rating > 4 && $obj->rating < 6){ $str .= $this->telegram->emoji(":warning:"); }
		elseif($obj->rating > 6){ $str .= $this->telegram->emoji(":x:"); }

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
				$n = count($reviews) - 1;
				if($reviews[0] == $reviews[$n]){ unset($reviews[$n]); }
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
