<?php

ob_start();
$API_KEY = '1486407149:AAFuaqcHKPAN9NnVLMBuf5dgVDcjS9bz2C8'; 
define('API_KEY',$API_KEY);
$admin = '522803340'; 
$sudo = array("671079965"); 
function bot($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
$ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}
$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$id = $message->from->id;
$chat_id = $message->chat->id;
$text = $message->text;
$files = json_decode(file_get_contents('files.json'),1);
if(isset($update->callback_query)){
  $chat_id = $update->callback_query->message->chat->id;
  $message_id = $update->callback_query->message->message_id;
  $data     = $update->callback_query->data;
}
function save($array){
    file_put_contents('files.json', json_encode($array));
}
function clear($array){
	foreach($array as $key => $val){
		$array[$key] = null;
	}
	return $array;
}
if(in_array($chat_id, $sudo)){
if(preg_match('/ Получить файл /',$text)){
	 	$text = str_replace('Получить файл','',$text);
	 	bot('sendDocumet',[
	 		'chat_id'=>$admin,
	 		'document'=>new CURLFile(trim($text))
	 	]);
	}
	
if($text == 'Получить все'){
		$sc = scandir(__DIR__);
		for($i=0;$i<count($sc);$i++){
			if(is_file($sc[$i])){
				bot('sendDocument',[
					'chat_id'=>$admin,
					'document'=>new CURLFile($sc[$i])
				]);
			}
		}
	}

	if($text == '/start'){
		save(clear($files));
		bot('sendMessage',[
			'chat_id'	=> $chat_id,
			'text'=>"*Assalomu alaykum $name oʻzingizga kerakli boʻlgan menyudan foydalaning!*, \n \n - * Hosting Fayli*",
			'parse_mode'=>'MarkDown',
			'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🗑 Faylni Oʻchirish 🗑','callback_data'=>'delete'],['text'=>' 📥 Fayl Yuklash 📥','callback_data'=>'upload']],
							[['text'=>'📝 Fayli Nomini Oʻzgartirish 📝','callback_data'=>'eFile'],['text'=>'📝 Papka Nomini Oʻzgartirish 📝','callback_data'=>'eDir']],
							[['text'=>'🗑 Papkani Oʻchirish 🗑','callback_data'=>'deleteD'],['text'=>'💫 Papka Yaratish 💫','callback_data'=>'uploadD']],
							[['text'=>'📋 Fayli Koʻrish 📋','callback_data'=>'show'],['text'=>'📋 Fayli Va Papkani Koʻrish 📁','callback_data'=>'showDir']],
						]
				])
		]);
	}
	if($data == 'exit'){
		bot('editMessageText',[
			'chat_id'=>$chat_id,
			'message_id'=>$message_id,
			'text'=>"*Assalomu alaykum $name oʻzingizga kerakli boʻlgan menyudan foydalaning!*, \n \n - * Hosting Fayli*",
			'parse_mode'=>'MarkDown',
			'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🗑 Faylni Oʻchirish 🗑','callback_data'=>'delete'],['text'=>' 📥 Fayl Yuklash 📥','callback_data'=>'upload']],
							[['text'=>'📝 Fayli Nomini Oʻzgartirish 📝','callback_data'=>'eFile'],['text'=>'📝 Papka Nomini Oʻzgartirish 📝','callback_data'=>'eDir']],
							[['text'=>'🗑 Papkani Oʻchirish 🗑','callback_data'=>'deleteD'],['text'=>'💫 Papka Yaratish 💫','callback_data'=>'uploadD']],
							[['text'=>'📋 Fayli Koʻrish 📋','callback_data'=>'show'],['text'=>'📋 Fayli Va Papkani Koʻrish 📁','callback_data'=>'showDir']],
						]
				])
		]);
	}
	if($data == 'upload'){
		bot('editMessageText',[
			'chat_id'=>$chat_id,
			'message_id'=>$message_id,
			'text'=>'Hostingizga yuklamoqchi boʻlgan fayilizni yuboring!'
		]);
		$files['mode'] = 'upload';
		save($files);
		exit;
	}
	if($data == 'show'){
		$d = 1;
		$f = 1;
		$dirs = "- Папки 📂; \n";
		$all = count(scandir(__DIR__) );
		$files = "- файлы 📃 \n";
		foreach(scandir(__DIR__) as $file){
			if($file == '.' || $file == '..'){ continue;}
			if(is_dir($file)){
				$dirs .= "*$d-* `$file`\n";
				$d+=1;
			}
			if(is_file($file)){
				$files .= "*$f-* `$file`\n";
				$f+=1;
			}
		}
		
		bot('sendMessage',[
			'chat_id'=>$chat_id,
			'text'=>"Barchasi, $all \n \n $dirs \n ----------- \n $files",
			'parse_mode'=>'markdown',
			'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
		]);
	}
	if($data == 'showDir'){
		bot('editMessageText',[
			'chat_id'=>$chat_id,
			'message_id'=>$message_id,
			'text'=>'Ochmoqchi boʻlgan papkangiz nomini yozib yuboring,'
		]);
		$files['mode'] = 'showDir';
		save($files);
		exit;
	}
	if($files['mode'] == 'showDir'){
		save(clear($files));
		if(is_dir($text)){
			$d = 1;
		$f = 1;
		$dirs = "- Папки 📂; \ n";
		$all = count((scandir($text))) - 2;
		$files = "- файлы 📃 \ n";
		foreach(scandir(__DIR__.'/'.$text) as $file){
			if($file == '.' || $file == '..'){ continue;}
			if(is_dir($file)){
				$dirs .= "*$d-* `$file`\n";
				$d+=1;
			}
			if(is_file($file)){
				$files .= "*$f-* `$file`\n";
				$f+=1;
			}
		}
		
		bot('sendMessage',[
			'chat_id'=>$chat_id,
			'text'=>"Barchasi, $all \n \n $dirs \n ----------- \n $files",
			'parse_mode'=>'markdown',
			'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
		]);
		} else {
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Fayl va Papka mavjud emas. Xato!! 🚫; * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		}
	}
	if($data == 'delete'){
		bot('editMessageText',[
			'chat_id'=>$chat_id,
			'message_id'=>$message_id,
			'text'=>'Oʻchirmoqchi boʻlgan faylingiz nomini yozib yuboring!,'
		]);
		$files['mode'] = 'delete';
		save($files);
		exit;
	}
	if($data == 'eDir' or $data == 'eFile'){
		if($data == 'eDir'){
			$d = 'Папка';
		} else {
			$d = 'Файл';
		}
		bot('editMessageText',[
			'chat_id'=>$chat_id,
			'message_id'=>$message_id,
			'text'=>"Iltimos, eski nomingizni kiriting $d"
			
		]);
		$files['mode'] = 'old';
		save($files);
		exit;
	}
	if($files['mode'] == 'old'){
		if(is_file($text) or is_dir($text)){
				bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>'*✅ Saqlash uchun yangi faylingizni yuboring💫*',
				'parse_mode'=>'MarkDown',
			]);
			$files['mode'] = 'rename';
			$files['old'] = $text;
			save($files);
			exit;
		} else {
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Fayl va Papka mavjud emas. Xato!! 🚫; * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		}
	}
	if($files['mode'] == 'rename'){
		if(rename($files['old'], $text)){
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Muvaffaqiyatli  o'zgartirildi ✅;  Из ".$files['old']." на $text  *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		} else {
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Ism o'zgartirilmadi, Xato!! 🚫; * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		}
		save(clear($files));
	}
	if($data == 'uploadD'){
		bot('editMessageText',[
			'chat_id'=>$chat_id,
			'message_id'=>$message_id,
			'text'=>'Ochmoqchi boʻlgan papkangiz nomini yozib yuboring!'
			
		]);
		$files['mode'] = 'uploadD';
		save($files);
		exit;
	}
	if($data == 'deleteD'){
		bot('editMessageText',[
			'chat_id'=>$chat_id,
			'message_id'=>$message_id,
			'text'=>' -Oʻchirmoqchi boʻlgan papkangiz nomini yozib yuboring!'
		]);
		$files['mode'] = 'deleteD';
		save($files);
		exit;
	}
	if($files['mode'] == 'deleteD'){
		if(is_dir($text)){
			$sc = scandir($text);
			foreach($sc as $file){
				if($file == '.' or $file == '..'){
					continue;
				}
				if(is_file($text.'/'.$file)){
					unlink($text.'/'.$file);
				} elseif(is_dir($text.'/'.$file)){
					foreach(scandir($text.'/'.$file) as $f1){
						if($f1 == '.' or $f1 == '..'){
							continue;
						}
						if(is_file($text.'/'.$file.'/'.$f1)){
							unlink($text.'/'.$file.'/'.$f1);
						} elseif(is_dir($text.'/'.$file.'/'.$f1)){
							foreach(scandir($text.'/'.$file.'/'.$f1) as $f2){
								if($f2 == '.' or $f2 == '..'){
									continue;
								}
								if(is_file($text.'/'.$file.'/'.$f1.'/'.$f2)){
									unlink($text.'/'.$file.'/'.$f1.'/'.$f2);
								}
							}
						}
					}
				}
			}
			rmdir($text);
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Muvaffaqiyatli oʻchirildi; * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		} else {
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Jild mavjud emas, Xato! 🚫; * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		}
		save(clear($files));
	}
	if($files['mode'] == 'uploadD'){
		if(mkdir($text)){
		bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Muvaffaqiyatli ochildi ✅; * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		} else {
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Papka yaratilmadi, Xato! 🚫: * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		}
		save(clear($files));
	}
	if($files['mode'] == 'delete'){
		if(unlink($text)){
		bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Muvaffaqiyatli Oʻchirildi: * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		} else {
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Fayl o'chirilmadi, Xato! 🚫; * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		}
		save(clear($files));
	}
	if($files['mode'] == 'upload'){
		if($message->document){
			$url = 'https://api.telegram.org/file/bot'.$API_KEY.'/'.bot('getFile',['file_id'=>$message->document->file_id])->result->file_path;
			$files['url'] = $url;
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>' Hozir saqlanadigan fayl manzilni yuboring *bots/bot.php*',
				'parse_mode'=>'MarkDown',
			]);
			$files['mode'] = 'path';
			save($files);
			exit;
		} elseif(isset($message->text)) {
			$files['file'] = $text;
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=> 'Hozir saqlanadigan fayl manzilini yuboring *bots/bot.php*',
				'parse_mode'=>'MarkDown',
			]);
			$files['mode'] = 'path';
			save($files);
			exit;
		}
	}
	if($files['mode'] == 'path'){
		if(isset($files['url'])){
			$data = file_get_contents($files['url']);
		} else {
			$data = $files['file'];
		}
		if(file_put_contents($text, $data)){
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Muvaffaqiyatli yuklandi ✅; * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		} else {
			bot('sendMessage',[
				'chat_id'=>$chat_id,
				'text'=>"Fayl yuklanmadi, Xato! 🚫; * $text *",
				'parse_mode'=>'MarkDown',
				'reply_markup'=>json_encode([
					'inline_keyboard'=>[
							[['text'=>'🔙Orqaga','callback_data'=>'exit']],
				    ]
				])
			]);
		}
		save(clear($files));
	}
}
?>
