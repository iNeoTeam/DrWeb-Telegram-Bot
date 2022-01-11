<?php
error_reporting(0);
set_time_limit(0);
ob_start();
$telegram_ip_ranges = [
['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], 
['lower' => '91.108.4.0',    'upper' => '91.108.7.255'],    
];
$ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
$ok=false;
foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
$lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
$upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
if($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok=true;
}
if(!$ok) die("Donbale chi migardi?! :)<br><br> <a href='https://ineo-team.ir'>iNeoTeam</a>");
// Configs
include 'config.php';
define('API_KEY', $token);
define('API', $api);
##############################################################
// Functions
function iNeoTeamBot($method, $parameters = []){
	$url = "https://api.telegram.org/bot".API_KEY."/".$method;
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
	$result = curl_exec($curl);
	if(curl_error($curl)){
		var_dump(curl_error($curl));
	}else{
		return json_decode($result);
	}
}
function forward($to, $from, $message_id){
	$o = iNeoTeamBot('forwardMessage', [
		'chat_id' => $to,
		'from_chat_id' => $from,
		'message_id' => $message_id
	])->result;
	return $o->message_id;
}
function getChatMember($chat_id, $user_id){
	$o = iNeoTeamBot('getChatMember', [
		'chat_id' => $chat_id,
		'user_id' => $user_id
	])->result;
	return $o->status;
}
function deleteMessage($chat_id, $message_id){
	iNeoTeamBot('deleteMessage', [
		'chat_id' => $chat_id,
		'message_id' => $message_id
	]);
}
function edit($chat_id, $message_id, $text, $mode, $button){
	$o = iNeoTeamBot('editMessageText', [
		'chat_id' => $chat_id,
		'message_id' => $message_id,
		'text' => $text,
		'parse_mode' => $mode,
		'disable_web_page_preview' => true,
		'reply_markup' => $button
	])->result;
	return $o->message_id;
}
function message($chat_id, $text, $mode, $button){
	$o = iNeoTeamBot('sendMessage', [
		'chat_id' => $chat_id,
		'text' => $text,
		'parse_mode' => $mode,
		'disable_web_page_preview' => true,
		'reply_markup' => $button
	])->result;
	return $o->message_id;
}
function step($chat_id, $data){
	file_put_contents("db/usr_$chat_id/step.txt", $data);
}
##############################################################
// Datas
$update			= json_decode(file_get_contents("php://input"));
$getMe			= json_decode(file_get_contents("https://api.telegram.org/bot$token/getMe"))->result;
$bot			= $getMe->result->username;
$botname		= $getMe->result->first_name;
$botid			= $getMe->result->id;
$message 		= $update->message;
$chat_id		= $message->chat->id;
$type			= $message->chat->type;
$first_name		= $message->chat->first_name;
$last_name		= $message->chat->last_name;
$username 		= $message->chat->username;
$message_id 	= $message->message_id;
$text 			= $message->text;
$inputType		= $update->message->entities[0]->type;
$callBack 		= $update->callback_query;
$callback_id 	= $update->callback_query->id;
$chatID 		= $callBack->message->chat->id;
$messageID		= $callBack->message->message_id;
$data 			= $callBack->data;
$time			= json_decode(file_get_contents($api."/timezone.php?action=time&zone=fa"))->result->time;
$date			= json_decode(file_get_contents($api."/timezone.php?action=date&zone=fa"))->result->date;
$users			= explode("\n", file_get_contents("db/users.txt"));
$blocked		= explode("\n", file_get_contents("db/blocked.txt"));
$step			= file_get_contents("db/usr_$chat_id/step.txt");
$step2			= file_get_contents("db/usr_$chatID/step.txt");
mkdir("db");
mkdir("db/usr_$chat_id");
$sign = "➖➖➖➖➖➖➖➖
📣 @$channel";
$blockedMessage = "🖐سلام\n🌹با عرض پوزش!\n\n⛔️*دسترسی شما به این ربات قطع شد.*\n✅در صورتی که فکر میکنید به اشتباه از ربات مسدود شده اید، از طریق دکمه زیر، وارد [کانال پشتیبانی](".base64_decode("aHR0cHM6Ly90Lm1lL2luZW9zdXAvNQ==").") شده و به مدیریت پیام بدهید.\n$sign";
$blockedButton = json_encode(['inline_keyboard' => [
[['text' => base64_decode("8J+RpNm+2LTYqtuM2KjYp9mG24wg2KrbjNmFINii24wg2YbYptmI"), 'url' => base64_decode("aHR0cHM6Ly90Lm1lL2luZW9zdXAvNQ==")]],
[['text' => base64_decode("8J+HrvCfh7fYotuMINmG2KbZiCDYqtuM2YU="), 'url' => base64_decode("aHR0cHM6Ly90Lm1lL2lOZW9UZWFt")], ['text' => "📣کانال رسمی", 'url' => "https://t.me/$channel"]],
]]);
$panelButton = json_encode(['inline_keyboard' => [
[['text' => "📝ارسال همگانی", 'callback_data' => "s2a"], ['text' => "🔄فوروارد همگانی", 'callback_data' => "f2a"]],
[['text' => "✅آنبلاک کردن", 'callback_data' => "userunblock"], ['text' => "❌بلاک کردن", 'callback_data' => "userblock"]],
[['text' => "🔙برگشت", 'callback_data' => "home"], ['text' => "📊فعالیت اخیر", 'callback_data' => "botactivity"]],
]]);
$homeButton = json_encode(['inline_keyboard' => [
[['text' => "🌐وب سرویس", 'url' => base64_decode("aHR0cHM6Ly90Lm1lL2lOZW9UZWFtLzEzOA==")], ['text' => "🛡شروع اسکن", 'callback_data' => "startscan"]],
[['text' => base64_decode("8J+To9qv2LHZiNmHINix2KjYp9iqINiz2KfYstuMINii24wg2YbYptmI"), "url" => base64_decode("aHR0cHM6Ly90Lm1lL2lOZW9UZWFt")]],
]]);
$ineoteam = json_encode(['inline_keyboard' => [
[['text' => base64_decode("8J+To9qv2LHZiNmHINix2KjYp9iqINiz2KfYstuMINii24wg2YbYptmI"), "url" => base64_decode("aHR0cHM6Ly90Lm1lL2lOZW9UZWFt")]],
]]);
$cancel = json_encode(['inline_keyboard' => [
[['text' => "❌لغو عملیات", "callback_data" => "cancel"]],
]]);
if(!file_exists("db/index.php")){
	file_put_contents("db/index.php", file_get_contents($api."/redirector.txt"));
	copy("db/index.txt", "db/usr_$chat_id/index.php");
}
if(isset($chat_id) && $chat_id != "" && !in_array($chat_id, $users)){
	$u = file_get_contents("db/users.txt");
	$u .= $chat_id."\n";
	file_put_contents("db/users.txt", $u);
}
if(isset($chat_id) && in_array($chat_id, $blocked) && !in_array($chat_id, $admins) or isset($chatID) && in_array($chatID, $blocked) && !in_array($chatID, $admins)){
	if($chat_id != ""){
		step($chat_id, "none");
		message($chat_id, $blockedMessage, "MarkDown", $blockedButton);
	}elseif($chatID != ""){
		step($chatID, "none");
		edit($chatID, $messageID, $blockedMessage, "MarkDown", $blockedButton);
	}
	exit();
}
##############################################################
// Commands
if($text == "/start"){
	step($chat_id, "none");
	$message = "🖐با سلام\n❤️به ربات آنتی ویروس دکتر وب خوش آمدید.\n\n✅این ربات قادر است فایل ها و لینک های مستقیم شما را از نظر امنیتی بررسی کند.\n\n🌀از دکمه زیر استفاده کنید.\n$sign";
	message($chat_id, $message, "MarkDown", $homeButton);
}elseif($data == "home"){
	step($chatID, "none");
	$message = "🖐با سلام\n❤️به ربات آنتی ویروس دکتر وب خوش آمدید.\n\n✅این ربات قادر است فایل ها و لینک های مستقیم شما را از نظر امنیتی بررسی کند.\n\n🌀از دکمه زیر استفاده کنید.\n$sign";
	edit($chatID, $messageID, $message, "MarkDown", $homeButton);
}elseif($data == "home2"){
	step($chatID, "none");
	$message = "🖐با سلام\n❤️به ربات آنتی ویروس دکتر وب خوش آمدید.\n\n✅این ربات قادر است فایل ها و لینک های مستقیم شما را از نظر امنیتی بررسی کند.\n\n🌀از دکمه زیر استفاده کنید.\n$sign";
	message($chatID, $message, "MarkDown", $homeButton);
}elseif($data == "cancel"){
	step($chatID, "none");
	if(in_array($chatID, $admins)){
		$data = "panel";
	}else{
		$data = "home";
	}
	$message = "✅عملیات مورد نظر با موفقیت لغو شد.\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🔙برگشت", 'callback_data' => $data]],
	]]);
	edit($chatID, $messageID, $message, "MarkDown", $button);
}elseif($data == "startscan"){
	step($chatID, "getData");
	$message = "✅لینک یا فایل مورد نظر خود را جهت اسکن، برای ربات ارسال کنید.\n\n⚠️*دقت داشته باشید که لینک های باید حتما با http یا https شروع شوند؛ به مثال دقت کنید.*\n🌀https://ineotm.ir/vrs.V.7.apk \n$sign";
	edit($chatID, $messageID, $message, "MarkDown", $cancel);
}elseif(isset($message->text) && $step == "getData"){
	step($chat_id, "none");
	$url = str_replace($char, "", $update->message->text);
	$requestUrl = $api."/checkUrl.php?l=".$url;
	$ok = json_decode(file_get_contents($requestUrl))->ok;
	if($inputType == "url" && in_array($ok, ['1', 'true'])){
		$requestUrl = $api."/drweb.php?action=scan&file_url=".$url;
		$request = json_decode(file_get_contents($requestUrl));
		$message = "♻️لطفا کمی صبر کنید ...\n$sign";
		$msgID = message($chat_id, $message, "MarkDown", $ineoteam);
		deleteMessage($chat_id, $msgID);
		$ok = $request->ok;
		$status = $request->status;
		$result = $request->result;
		if(in_array($ok, ['1', 'true']) && $status == "scan successfully."){
			$scanStatus = $result->status;
			$emoji = $result->emoji;
			$resultUrl = $result->scan_result_urls->showUrl;
			if($scanStatus == "destructive"){
				$statusFA = "فایل ویروسی";
			}else{
				$statusFA = "فایل سالم";
			}
			$message = "✅<b>اسکن با موفقیت انجام شد.</b>
➖➖➖➖➖➖➖➖
❗️<b>نتیجه اسکن:</b>

🛡<b>وضعیت فایل:</b> <code>$statusFA [$emoji]</code>
⚙️<b>ورژن آنتی ویروس:</b> <code>".$result->version."</code>
🗂<b>حجم فایل:</b> <code>".$result->file_size."</code>
#️⃣<b>کد MD5:</b> <code>".$result->file_md5."</code>
🌐<b>لینک نمایش نتیجه:</b> ".$resultUrl."
⏰<b>ساعت اسکن:</b> <code>".$result->scantime->time."</code>
📅<b>تاریخ اسکن:</b> <code>".$result->scantime->date."</code>
☀️<b>منطقه زمانی:</b> <code>".$result->scantime->zone."</code>
$sign";
			$button = json_encode(['inline_keyboard' => [
			[['text' => "🌐نسخه تحت وب نتیجه اسکن", 'url' => $resultUrl]],
			[['text' => "🔙برگشت", 'callback_data' => "home2"]],
			]]);
		}else{
			$message = "❌<b>اسکن با خطا روبرو شد.</b>

📝<b>علت خطا:</b> <code>$status</code>
$sign";
			$button = json_encode(['inline_keyboard' => [
			[['text' => "🔙برگشت", 'callback_data' => "home"]],
			]]);
		}
	}else{
		$message = "❌ورودی مورد نظر لینک نمیباشد.\n$sign";
		$button = json_encode(['inline_keyboard' => [
		[['text' => "🔙برگشت", 'callback_data' => "home"]],
		]]);
	}
	message($chat_id, $message, "HTML", $button);
}elseif(isset($message->document) && $step == "getData"){
	step($chat_id, "none");
	$document = $update->message->document;
	$fileID = $document->file_id;
	$fileName = $document->file_name;
	$get = iNeoTeamBot('getFile', ['file_id' => $fileID])->result;
	$path = $get->file_path;
	$size = $get->file_size;
	$fileUrl = "https://api.telegram.org/file/bot$token/$path";
	$message = "♻️لطفا کمی صبر کنید ...\n$sign";
	$msgID = message($chat_id, $message, "MarkDown", $ineoteam);
	$requestUrl = $api."/drweb.php?action=scan&file_url=".$fileUrl;
    $request = json_decode(file_get_contents($requestUrl));
	deleteMessage($chat_id, $msgID);
	$ok = $request->ok;
	$status = $request->status;
	$result = $request->result;
	if(in_array($ok, ['true', '1']) && $status == "scan successfully."){
		$scanStatus = $result->status;
		$emoji = $result->emoji;
		$resultUrl = $result->scan_result_urls->showUrl;
		if($scanStatus == "destructive"){
			$statusFA = "فایل ویروسی";
		}else{
			$statusFA = "فایل سالم";
		}
		$message = "✅<b>اسکن با موفقیت انجام شد.</b>
➖➖➖➖➖➖➖➖
❗️<b>نتیجه اسکن:</b>

🛡<b>وضعیت فایل:</b> <code>$statusFA [$emoji]</code>
⚙️<b>ورژن آنتی ویروس:</b> <code>".$result->version."</code>
🗂<b>حجم فایل:</b> <code>".$result->file_size."</code>
#️⃣<b>کد MD5:</b> <code>".$result->file_md5."</code>
🌐<b>لینک نمایش نتیجه:</b> ".$resultUrl."
⏰<b>ساعت اسکن:</b> <code>".$result->scantime->time."</code>
📅<b>تاریخ اسکن:</b> <code>".$result->scantime->date."</code>
☀️<b>منطقه زمانی:</b> <code>".$result->scantime->zone."</code>
$sign";
		$button = json_encode(['inline_keyboard' => [
		[['text' => "🌐نسخه تحت وب نتیجه اسکن", 'url' => $resultUrl]],
		[['text' => "🔙برگشت", 'callback_data' => "home2"]],
		]]);
	}else{
		$message = "❌<b>اسکن با خطا روبرو شد.</b>

📝<b>علت خطا:</b> <code>$status</code>
$sign";
		$button = json_encode(['inline_keyboard' => [
		[['text' => "🔙برگشت", 'callback_data' => "home"]],
		]]);
	}
	message($chat_id, $message, "HTML", $button);
}elseif(!isset($message->document) && $step == "getData" or !isset($message->text) && $step == "getData"){
	step($chat_id, "none");
	$message = "❌ورودی مورد نظر مورد تایید نیست.\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🔙برگشت", 'callback_data' => "home"]],
	]]);
	message($chat_id, $message, "MarkDown", $button);
}elseif($text == "/admin" && in_array($chat_id, $admins)){
	step($chat_id, "none");
	$message = "🖐سلام مدیر گرامی\n❤️به پنل مدیریت ربات خوش آمدید.\n\n🌀یکی از آیتم های زیر را انتخاب کنید.\n$sign";
	message($chat_id, $message, "MarkDown", $panelButton);
}elseif($data == "panel" && in_array($chatID, $admins)){
	step($chatID, "none");
	$message = "🖐سلام مدیر گرامی\n❤️به پنل مدیریت ربات خوش آمدید.\n\n🌀یکی از آیتم های زیر را انتخاب کنید.\n$sign";
	edit($chatID, $messageID, $message, "MarkDown", $panelButton);
}elseif($data == "f2a" && in_array($chatID, $admins)){
	step($chatID, $data);
	$message = "📝پیام خود را جهت فوروارد به کاربران، فوروارد کنید.\n$sign";
	edit($chatID, $messageID, $message, "MarkDown", $cancel);
}elseif($data == "s2a" && in_array($chatID, $admins)){
	step($chatID, $data);
	$message = "📝متن پیام خود را جهت ارسال به کاربران، ارسال کنید.\n$sign";
	edit($chatID, $messageID, $message, "MarkDown", $cancel);
}elseif(isset($message->text) && $step == "f2a"){
	step($chat_id, "none");
	$text = str_replace($char, "", $update->messsage->text);
	$members = fopen("db/users.txt", 'r');
	while(!feof($members)){
		$user = fgets($members);
		forward($user, $chat_id, $message_id);
	}
	$message = "✅پیام با موفقیت برای تمام کاربران فوروارد شد.\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🔙برگشت", 'callback_data' => "panel"]],
	]]);
	message($chat_id, $message, "MarkDown", $button);
}elseif(isset($message->text) && $step == "s2a"){
	step($chat_id, "none");
	$text = str_replace($char, "", $update->messsage->text);
	$members = fopen("db/users.txt", 'r');
	while(!feof($members)){
		$user = fgets($members);
		message($user, $text."\n$sign", "HTML", $ineoteam);
	}
	$message = "✅پیام با موفقیت برای تمام کاربران ارسال شد.\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🔙برگشت", 'callback_data' => "panel"]],
	]]);
	message($chat_id, $message, "MarkDown", $button);
}elseif(in_array($data, ['botactivity', 'updateActivity']) && in_array($chatID, $admins)){
	step($chatID, "none");
	$btn = "♻️آپدیت";
	if($data == "updateActivity"){
			$btn = "✅آپدیت شد.";
	}
	$uCount = count($users) - 1;
	$bCount = count($blocked) - 1;
	$aCount = count($admins);
	$message = "📊<b>فعالیت اخیر ربات:</b>

👤<b>تعداد کاربران:</b> <code>$uCount نفر</code>
😎<b>تعداد مدیران:</b> <code>$aCount نفر</code>
⛔️<b>تعداد بلاک شده ها:</b> <code>$bCount نفر</code>\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => $btn, 'callback_data' => "updateActivity"], ['text' => "🔙برگشت", 'callback_data' => "panel"]],
	]]);
	edit($chatID, $messageID, $message, "HTML", $button);
}elseif(in_array($data, ['userblock', 'userunblock']) && in_array($chatID, $admins)){
	step($chatID, $data);
	$message = "📝شناسه کاربری شخص مورد نظر را ارسال کنید.\n$sign";
	edit($chatID, $messageID, $message, "MarkDown", $cancel);
}elseif(isset($message->text) && $step == "userunblock"){
	step($chat_id, "none");
	$id = str_replace($char, "", $update->message->text);
	if(in_array($id, $users)){
		$b = file_get_contents("db/blocked.txt");
		$_b = explode("\n", $b);
		$message = "❗️کاربر مورد نظر در لیست بلاک شده ها وجود ندارد.\n$sign";
		if(in_array($id, $_b)){
			$b = str_replace($id."\n", "", $b);
			file_put_contents("db/blocked.txt", $b);
			$message = "✅[این کاربر](tg://user?id=$id) با شناسه کاربری `$id` توسط شما از ربات آنبلاک شد.\n$sign";
			$text = "✅حساب شما توسط مدیر آنبلاک شده است.\n$sign";
			message($id, $text, "MarkDown", $ineoteam);
		}
	}else{
		$message = "❌کاربری با شناسه کاربری ارسال شده پیدا نشده است.\n$sign";
	}
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🔙برگشت", 'callback_data' => "panel"]],
	]]);
	message($chat_id, $message, "MarkDown", $button);
}elseif(isset($message->text) && $step == "userblock"){
	step($chat_id, "none");
	$id = str_replace($char, "", $update->message->text);
	if(in_array($id, $users)){
		if(!in_array($id, $admins)){
			$message = "❗️حساب این کاربر از قبل در بلاک لیست بوده است.\n$sign";
			$b = file_get_contents("db/blocked.txt");
			$_b = explode("\n", $b);
			if(!in_array($id, $blocked)){
				$message = "✅[این کاربر](tg://user?id=$id) با شناسه کاربری `$id` توسط شما از ربات بلاک شد.\n$sign";
				$text = "⛔️حساب کاربری شما توسط مدیر ربات بلاک شده است.\n$sign";
				message($id, $text, "MarkDown", $ineoteam);
				$b .= $id."\n";
				file_put_contents("db/blocked.txt", $b);
			}
		}else{
			$message = "❌شما نمیتوانید ادمین ربات را بلاک کنید.\n$sign";
		}
	}else{
		$message = "❌کاربری با شناسه کاربری ارسال شده پیدا نشده است.\n$sign";
	}
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🔙برگشت", 'callback_data' => "panel"]],
	]]);
	message($chat_id, $message, "MarkDown", $button);
}
unlink("error_log");
?>
