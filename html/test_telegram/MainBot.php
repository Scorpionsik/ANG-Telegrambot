<?php 
$root_dir = explode('html',__DIR__)[0] . 'html';
require_once $root_dir . "/vendor/autoload.php";
include "RequestInfo.php";
include __DIR__ . "/Modules/MainBotModule.php";
include __DIR__ . "/Modules/RegisterBotModule.php";
include __DIR__ . "/Modules/TestBotModule.php";


class MainBot{
	private $bot;
	private $db;
	private $id_admin = 780925203;

	//������������� ����
	public function __construct($bot_token){
		include "connection_agent.php";
		$this->db = new mysqli($host, $dblogin, $dbpassw, $database);

		$this->bot = new \TelegramBot\Api\Client($bot_token);
		/*
		$this->bot->command('test', function ($message) {
			$request_info = $this->getFullInfo(new RequestInfo($message));
			$this->sendMessage($request_info->getIdTelegram(), $request_info->getIdTelegram() . ": " . $request_info->getIdWhitelist() . ", " . $request_info->getModeValue());
		});*/

		$this->bot->command('help', function ($message) {
			$this->commandHelp($message->getChat()->getId());
		});

		$this->bot->on(function ($Update) {
			$this->distribute($this->getFullInfo(new RequestInfo($Update)));
		}, function ($Update){
			return true;
		});

		$this->bot->run();
	}

	//������� ������
	function __destruct(){
		$this->dispose();
	}

	private function distribute($request_info){
		$module = null;
		if(is_null($request_info->getIdWhitelist())){
			$module = new RegisterBotModule($this);
		}
		else{
			switch($request_info->getModeValue()){
				//��������� ������������ ���� ��� �������
				case 1:
					$module = new TestBotModule($this);
				break;
				//����������� ����� ������ ����
				default:
					$module = new MainBotModule($this);
				break;
			}
		}
		if(!is_null($module)) $module->Start($request_info);
	}

	//�������� ��������� � ��������-���
	public function sendMessage($id_telegram, $message_text){
		$this->bot->sendMessage($id_telegram, $message_text, 'HTML');
	}

	public function callAdmin($message_text){
		$this->bot->sendMessage($this->id_admin, $message_text, 'HTML');
	}

	//�������� ��������� ������� �� ���� ������
	public function getRequestResult($query){
		$result = mysqli_query($this->db, $query) or die("������ " . mysqli_error($this->db));
		return $result;
	}

	//�������� RequestInfo � ����������� � id_whitelist ������������; ���� ������������ �� ���� � ���� ������, ��������� ���
	private function getFullInfo($request_info){
		$return = $request_info;
		$query = "SELECT * FROM telegram_users  where Id_telegram_user=". $request_info->getIdTelegram() .";";
		$result = $this->getRequestResult($query);
		if($result)
		{
			$row_check = mysqli_num_rows($result);
			if($row_check == 0){
				$query = "INSERT INTO telegram_users (Id_telegram_user) values (". $request_info->getIdTelegram() .");";
				$this->getRequestResult($query);
			}
			else{
				$row = mysqli_fetch_row($result);
				if($row){
					$return = new RequestInfo($request_info, $row[1], $row[4]);
				}
			}
			mysqli_free_result($result);
		}
		return $return;
	}

	//������� ��������� � ������
	private function commandHelp($id_telegram){
		$this->sendMessage($id_telegram, 'help <b>me</b>');
	}

	//��������� ����������� � ���� ������
	private function dispose(){
		mysqli_close($this->db);
	}
}

?>