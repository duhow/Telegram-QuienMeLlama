<?php

class Main extends TelegramApp\Module {
	protected function hooks(){
		if($this->telegram->words() == 1){
			$tel = $this->telegram->text(TRUE);
			$tel = str_replace([" ", "-", "+"], "", $tel);

			if(is_numeric($tel) && strlen($tel) == 9){
				$this->telegram->send
					->text($this->telegram->emoji(":clock: ") ."Buscando...")
				->send();

				foreach(scandir("app/sites/") as $f){
					if(is_readable("app/sites/$f") && substr($f, -4) == ".php"){
						require "app/sites/$f";
						$name = substr($f, 0, -4);

						$find = new PhoneDict\{$name}($phone);
						if($find->result){
							$this->telegram->send
								->text("Se ha encontrado en $name")
							->send();
						}
					}
				}
			}
		}
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
