<?php
$root_dir = explode('html',__DIR__)[0] . 'html';
require_once $root_dir . "/vendor/autoload.php";
require_once "Functions.php";
require_once "WhitelistInfo.php";
require_once "WhitelistUser.php";
require_once __DIR__ . "/Keyboards/InlineOfferBotKeyboard.php";

class NotificationBot{
	private $bot;
	private $db;
	private $functions;
	
	public function __construct($bot_token){
		include "connection_agent.php";
		$this->db = new mysqli($host, $dblogin, $dbpassw, $database);
		$this->bot = new \TelegramBot\Api\Client($bot_token);
		$this->functions = new Functions();
	}
	
		//очистка данных
	function __destruct(){
		$this->dispose();
	}
	
	public function getWhitelistUsers($single_user = null){
		$return = array();
		$query_part = "";
		if(!is_null($single_user)) $query_part = " and Id_whitelist_user = ${single_user}";
		$query = "select telegram_users.Id_whitelist_user as 'Id', telegram_users.Id_telegram_user as 'Telegram', white_list.Is_accept_base_button, white_list.Is_get_new_offers, white_list.Is_get_edit_offers, telegram_users.IsExist from telegram_users join white_list using (Id_whitelist_user) WHERE white_list.Is_banned != 1 and white_list.Is_locked != 1${query_part} and (white_list.Is_get_new_offers=1 or white_list.Is_get_edit_offers=1);";
		$result = $this->getRequestResult($query);
		if($result){
			$row_check = mysqli_num_rows($result);
			if($row_check > 0){ 
				for($i = 0; $i < $row_check; $i++){
					$row = mysqli_fetch_row($result);
					$whitelist_info = new WhitelistInfo($row[0], null, null, null, null, $row[2], $row[3], $row[4], null);
					$whitelist_user = new WhitelistUser($row[1], $whitelist_info, $row[5]);
					$return[] = $whitelist_user;
				}
				
			}
			mysqli_free_result($result);
		}
		return $return;
	}
	
	public function getOffersForWhitelistUser($whitelist_user){
		$query_part = "";
		if($whitelist_user->getWhitelistInfo()->getIsGetNewOffers() == 1 || $whitelist_user->getWhitelistInfo()->getIsGetEditOffers() == 1){
			$query_part = " and (";
			if($whitelist_user->getWhitelistInfo()->getIsGetNewOffers() == 1){
				$query_part = $query_part . "offers.IsNew=1";
				if($whitelist_user->getWhitelistInfo()->getIsGetEditOffers() == 1){
					$query_part = $query_part . " or ";
				}
			}
			if($whitelist_user->getWhitelistInfo()->getIsGetEditOffers() == 1){
				$query_part = $query_part . "offers.IsEdit=1";
			}
			$query_part = $query_part . ")";
		}
		
		$query = "where bind_whitelist_distr_flats.Id_whitelist_user=" . $whitelist_user->getWhitelistInfo()->getIdWhitelist() . "${query_part};";
		return $this->getOffers($query);
	}
	
	public function showOffer($offer, $id_telegram, $whitelist_info){
		$inline_offer_keyboard = new InlineOfferBotKeyboard($offer, $whitelist_info);
		//основное сообщение
		$this->sendMessage($id_telegram, $offer->getOfferDescription());
		//фотографии
		if(!is_null($offer->getImageUrl()) && $offer->getImageUrl() != ""){
			try{
				$this->sendPhoto($id_telegram, "https://an-gorod-image.com.ua/storage/uploads/preview/" . $offer->getImageUrl(), "<a href='https://angbots.ddns.net/image_ang/some_pic_get.php?entity=" . $offer->getIdOffer() . "'><b>Посмотреть все фотографии</b></a>");
			}
			catch(Exception $e){
			}
		}
		//место под телефоны и инлайн клаву
		$this->sendMessage($id_telegram, "Чтобы посмотреть контакты владельца объекта <b>". $offer->getIdOffer() ."</b>, нажмите на кнопку 'Телефоны' ниже.", $inline_offer_keyboard, true);
	}
	
	public function sendStartMessage($whitelist_user){
		$separator = "➖➖➖➖";
		$this->sendMessage($whitelist_user->getIdTelegram(), "${separator}<b>Есть новые обновления!</b>${separator}");
	}
	
	public function sendEndMessage($offers_count, $whitelist_user){
		$this->sendMessage($whitelist_user->getIdTelegram(), $this->functions->declOfNum($offers_count, array('объект пришел','объекта пришло','объектов пришло')) . " за последние несколько минут.");
	}
	
	public function setIsExist($whitelist_user, $value){
		$query = "update telegram_users set IsExist = ${value} where Id_telegram_user=" . $whitelist_user->getIdTelegram() . ";";
		$this->getRequestResult($query);
	}
	
	private function getOffers($where_query_part){
		$result = $this->getRequestResult($this->functions->getSelectAndFromQueryPart() . $where_query_part);
		$offers_array = $this->functions->getOffersFromDBResult($result);
		mysqli_free_result($result);
		return $offers_array;
	}
	
	//получить результат запроса из базы данных
	private function getRequestResult($query){
		$result = mysqli_query($this->db, $query) or die("Ошибка " . mysqli_error($this->db));
		return $result;
	}
	
		//отправка сообщений в телеграм-чат
	private function sendMessage($id_telegram, $message_text, $bot_keyboard = null){
		$keyboard = null;
		if(!is_null($bot_keyboard)){
				$keyboard = $this->getInlineKeyboard($bot_keyboard);
		}
		$this->bot->sendMessage($id_telegram, $message_text, 'HTML', true, null, $keyboard);
	}
	
	private function sendPhoto($id_telegram, $photo_link, $description){
		$this->bot->sendPhoto($id_telegram, $photo_link, $description, null, null, false, "HTML");
	}
	
	private function getInlineKeyboard($bot_keyboard){
		$return = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($bot_keyboard->getKeyboardArray());
		return $return;
	}
	
	//закрывает подключение к базе данных
	private function dispose(){
		mysqli_close($this->db);
	}
}

?>