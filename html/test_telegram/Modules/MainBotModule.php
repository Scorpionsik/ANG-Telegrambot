<?php
$telegram_dir = explode('Modules',__DIR__)[0];
require_once $telegram_dir . "Functions.php";
require_once $telegram_dir . "Offer.php";
require_once $telegram_dir . "Keyboards/DefaultBotKeyboard.php";
require_once $telegram_dir . "Keyboards/MainSearchBotKeyboard.php";
require_once $telegram_dir . "Keyboards/InlineOfferBotKeyboard.php";
require_once $telegram_dir . "Keyboards/InlineCountPagesBotKeyboard.php";
require_once "BotModule.php";

class MainBotModule extends BotModule{
	//максимальное количество объявлений на 1 странице
	private $find_code_query = "select offers.Internal_id, types.Type_name, flat_types.Typename, localities.Locality_name, districts.District_name, offers.Address, offers.Description, offers.Room_counts, offers.Floor, offers.Floors_total, offers.Area, offers.Lot_area, offers.Living_space, offers.Kitchen_space, offers.Price, offers.Image_url, offers.IsNew, offers.IsEdit, offers.Orient, offers.Entity_id, offers.BuildStatus, offers.IsNewBuild, offers.Old_price, offers.House_number, offers.User_entity_id FROM offers inner join types on offers.Id_type=types.Id_type inner join flat_types on offers.Id_flat_type=flat_types.Id_flat_type INNER JOIN localities ON offers.Id_locality=localities.Id_locality inner join districts on offers.Id_district=districts.Id_district ";
	private $quantity_per_page = 5;
	private $search_status_message = "Режим поиска: ";
	private $empty_offers_error_message = "Информации по вашему району на данный момент нет, попробуйте позже!";
	private $empty_search_offers_error_message = "Информации по заданным параметрам нет.";
	private $empty_search_db_error_message = "Ошибка состояния, попробуйте отменить поиск и начать его заново.";
	private $functions;
	public function __construct($main_bot){
		parent::__construct($main_bot);
		$this->functions = new Functions();
	}

	/* Обработка вводимых сообщений*/
	protected function forMessages($request_info, $whitelist_info){
		$message_text = $this->main_bot->getMessageText($request_info->getMessageData());
		$module_param = $request_info->getModeParam();
		$is_show_offers = true;
		//if($module_param == 2) $is_show_offers = false;
		$current_turn_page = $whitelist_info->getTurnPage();
		if(preg_match('/уведомл/',$message_text)){
			if(preg_match('/Присылать только/', $message_text)){
				$is_show_offers = false;
				$this->switchIsGetEditOffers($whitelist_info, 0);
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Теперь в уведомлениях будут приходить <b>только новые объекты</b>. Если вы снова хотите получать обновленные объекты, нажмите на \"Получать все объекты в уведомлениях\".", new DefaultBotKeyboard(false));
			}
			else if(preg_match('/Получать/', $message_text)){
				$is_show_offers = false;
				$this->switchIsGetEditOffers($whitelist_info, 1);
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Теперь в уведомлениях будут приходить <b>и новые, и обновленные объекты</b>. Если вы снова хотите получать только новые объекты, нажмите на \"Присылать только новые объекты в уведомлениях\".", new DefaultBotKeyboard(true));
			}
		}
		else if(preg_match('/\/key(board)?/',$message_text)){
			$is_show_offers = false;
			$keyboard=null;
			
			if($module_param == 2) $keyboard = new MainSearchBotKeyboard();
			else $keyboard = new DefaultBotKeyboard($whitelist_info->getIsGetEditOffers());
			
			$this->main_bot->sendMessage($request_info->getIdTelegram(), "Возвращаю клавиатуру", $keyboard);
		}
		else if(preg_match('/Отменить поиск/', $message_text)){
		    //$is_show_offers = true;
		    $this->main_bot->sendMessage($request_info->getIdTelegram(), "Поиск завершен.", new DefaultBotKeyboard($whitelist_info->getIsGetEditOffers()));
		    $this->cancelSearch($request_info, $whitelist_info);
		    $module_param = 0;
		    $is_show_offers = false;
		}
		else{
			//переключить на модуль выбора максимальной/минимальной цены
			if(preg_match('/Поиск по цене/', $message_text)){
				$is_show_offers = false;
				if($module_param == 2) $this->cancelSearch($request_info, $whitelist_info);
				$this->main_bot->changeMode($request_info, $whitelist_info, 1);
			}
			//перелистнуть страницу
			else if(preg_match('/^\d{1,3}$/', $message_text)){
			    $current_turn_page=$message_text;
			    /* Перелистнуть в режиме поиска */
			    if($module_param == 2){
			        $this->main_bot->getRequestResult("update agent_searches set Turn_page=" . $current_turn_page . " where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";");
			    }
			    /* Перелистнуть в обычном режиме */
			    else{
			 	   
				   $this->turnThePage($whitelist_info, $message_text);
			    }
			}
			//найти в базе данных по коду
			else if(preg_match('/^\d+\/\d+$/', $message_text)){
				$is_show_offers = false;
				$offer_array = $this->getOffersWithoutBind("WHERE offers.Internal_id='" . $message_text . "';");
				if(count($offer_array) > 0){
					$this->showOffer($offer_array[0], $request_info, $whitelist_info);
				}
				else $this->main_bot->sendMessage($request_info->getIdTelegram(), "Объект <u>" . $message_text . "</u> не найден в базе телеграм-бота.");
			}
			//поиск
			else{
			    if(!preg_match('/Получить всё/i', $message_text) && !preg_match('/Отмена/i', $message_text) && !preg_match('/Сбросить цену/i', $message_text)){
			    //$is_show_offers = false;
			        $search_params = $this->makeSearchArray($request_info->getMessageData()->getText());
    			    
    			    //
    			    if(count($search_params) > 0){
    			        //$this->main_bot->callAdmin(implode(" AND ", $search_params));
    			        $this->changeModeParam($request_info, $whitelist_info, 2);
    			        $module_param = 2;
    			        /* todo запись в таблицу agent_searches */
    			        $this->main_bot->getRequestResult("delete from agent_searches where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";");
    			        $query = "insert into agent_searches values (" . $whitelist_info->getIdWhitelist() . ", '". implode(" AND ", $search_params) ."', '". $request_info->getMessageData()->getText() ."', 1);";
    			        //$this->main_bot->callAdmin($query);
    			        $this->main_bot->getRequestResult($query);
    			        
    			        //$this->main_bot->sendMessage($request_info->getIdTelegram(), implode(" AND ", $search_params));
    			    }
    			    else 
    			    {
			             $is_show_offers = false;
			             $this->main_bot->sendMessage($request_info->getIdTelegram(), $this->empty_search_offers_error_message);
    			    }
			    }
			}
		}
		//показ объектов
		
		if($is_show_offers){
		    if($module_param == 2){
		        $this->showSearchResult($request_info, $whitelist_info);
		    }
		    else{
				if($request_info->getModeParam() == 0){
					$this->main_bot->sendMessage($request_info->getIdTelegram(), "Добро пожаловать, " . $whitelist_info->getUsername() . "!", new DefaultBotKeyboard($whitelist_info->getIsGetEditOffers()));
					$this->changeModeParam($request_info, $whitelist_info, 1);
				}
				if($this->showOffersOnPage($current_turn_page, $request_info, $whitelist_info, $this->makeOffersForMain($whitelist_info)) == 0)
				    $this->main_bot->sendMessage($request_info->getIdTelegram(), $this->empty_offers_error_message);
				$this->setOffersPress($request_info, $whitelist_info);
		      }
			}
	}
	/* конец Обработка вводимых сообщений*/
	
	// $this->main_bot->callAdmin(implode(" AND ", $matches));
	private function makeSearchArray($message_text){
	    
	    //$this->main_bot->callAdmin($message_text);
	    $search_params = array();
	    $matches = array();
	    $is_set_price = false;
	    
	    //по комнатам
	    $pattern = "/(\d)(?:-(\d))?к/";
	    if(preg_match($pattern, $message_text, $matches)){
	        $str_result = "";
	        if(count($matches) > 2) $str_result = $str_result . "offers.Room_counts BETWEEN " . $matches[1] . " and " . $matches[2];
	        else $str_result = "offers.Room_counts=" . $matches[1];
	        $search_params[] = $str_result;
	        
	    }
	    
	    //по району
	    $pattern = "/(\pL{3,}(?: \pL{3,})?)(?=\,)?/u";
	    if(preg_match($pattern, $message_text, $matches)){
	        $district_params = array();
	        $count = count($matches);
	        $this->main_bot->callAdmin(implode(" ; ", $matches));
	        $this->main_bot->callAdmin($count);
	        $step = 1;
	        while($step < $count) $district_params[] = "districts.District_name like (\"" . $matches[$step++] . "%\")"; //implode(" ; ", $matches);
	        $search_params[] = implode(" OR ", $district_params); //implode(" ; ", $matches);
	        //$this->main_bot->callAdmin($matches[0]);
	        //$this->main_bot->callAdmin(implode(" ; ", $matches));
	    }
	    
	    //по ценовой вилке
	    $pattern = "/(\d{4,})\-(\d{4,})(?:[ ]*\$)?/";
	    if(preg_match($pattern, $message_text, $matches)){
	        $search_params[] = "offers.Price BETWEEN " . $matches[1] . " and " . $matches[2];
	        $is_set_price = true;
	    }
	    
	    //по конкретной цене
	    $pattern = "/(?:([<>])?[ ]*)(\d{4,})(?:[ ]*\$)?/";
	    if(!$is_set_price && preg_match($pattern, $message_text, $matches)){
	        $search_params[] = "offers.Price". $matches[1] ."=" . $matches[2];
	        $is_set_price = true;
	    }
	    
	    return $search_params;
	}
	
	private function showSearchResult($request_info, $whitelist_info){
        /* todo показать объекты для поиска */
        $query = "SELECT * from agent_searches where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";";
        $result = $this->main_bot->getRequestResult($query);
        if($result){
            $row_check = mysqli_num_rows($result);
            if($row_check > 0){
                $row = mysqli_fetch_row($result);
                $search_query = $row[1];
                $search_input = $row[2];
                $search_turn_page = $row[3];
                $this->main_bot->sendMessage($request_info->getIdTelegram(), $this->search_status_message . $search_input, new MainSearchBotKeyboard());
                
                if($this->showOffersOnPage($search_turn_page, $request_info, $whitelist_info, $this->getOffersWithoutBind("WHERE " . $search_query . " ORDER BY offers.Update_timestamp desc;")) == 0)
                    $this->main_bot->sendMessage($request_info->getIdTelegram(), $this->empty_search_offers_error_message);
                    $this->main_bot->sendMessage($request_info->getIdTelegram(), $this->search_status_message . $search_input);
                    
            }
            else $this->main_bot->sendMessage($request_info->getIdTelegram(), $this->empty_search_db_error_message);
        }
        
    }
	
	private function makeOffersForMain($whitelist_info){
	    return $this->getOffers("WHERE bind_whitelist_distr_flats.Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . " ORDER BY offers.Update_timestamp desc;");
	}
		
	private function cancelSearch($request_info, $whitelist_info){
	    $this->main_bot->getRequestResult("delete from agent_searches where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";");
	    $this->changeModeParam($request_info, $whitelist_info, 0);
	}
	
	/* Обработка инлайн запросов*/
	protected function forCallbacks($request_info, $whitelist_info){
	    //перелистнуть страницу
	    if(preg_match('/^\d+$/', $request_info->getCallbackData())){
	        $module_param = $request_info->getModeParam();
	        if($module_param == 2){
	            $this->main_bot->getRequestResult("update agent_searches set Turn_page=" . $request_info->getCallbackData() . " where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";");
	            $this->showSearchResult($request_info, $whitelist_info);
	        }
	        else{
	            $this->turnThePage($whitelist_info, $request_info->getCallbackData());
	            if($this->showOffersOnPage($request_info->getCallbackData(), $request_info, $whitelist_info, $this->makeOffersForMain($whitelist_info)) == 0)
	                $this->main_bot->sendMessage($request_info->getIdTelegram(), $this->empty_offers_error_message);
	                $this->setOffersPress($request_info, $whitelist_info);
	        }
	    }
	    //отобразить телефоны
	    else if(preg_match('/^id\d+$/', $request_info->getCallbackData()) || preg_match('/^\d+\/\d+$/', $request_info->getCallbackData())){
	        $entity_id = "";
	        if(preg_match('/^\d+\/\d+/', $request_info->getCallbackData())){
	            $query = "SELECT Entity_id from offers where Internal_id='". $request_info->getCallbackData() ."';";
	            $result = $this->main_bot->getRequestResult($query);
	            if($result){
	                $row_check = mysqli_num_rows($result);
	                if($row_check > 0){
	                    $row = mysqli_fetch_row($result);
	                    $entity_id = $row[0];
	                }
	            }
	        }
	        else $entity_id = preg_replace('/^id/', "", $request_info->getCallbackData());
	        $text_title = "Контакты объекта";
	        //проверка, изменялся ли уже текст в сообщении
	        if(!preg_match("/Контакты объекта/", $request_info->getMessageData()->getText())){
	            $text_title = "➖➖➖<b>${text_title}</b>➖➖➖";
	            
	            /* Вычисляем, чьи контакты отобразить */
	            $whose_phone_show = "User_entity_id";
	            $query = "SELECT flat_owners.User_entity_id, offers.Agent_entity_id, offers.IsExclusive FROM flat_owners LEFT JOIN offers USING (User_entity_id) WHERE offers.Entity_id='". $entity_id . "'";
	            $result = $this->main_bot->getRequestResult($query);
	            if($result){
	                $row_check = mysqli_num_rows($result);
	                if($row_check > 0){
	                    $row = mysqli_fetch_row($result);
	                    if($row[1] > 0 && $row[2] == 1) $whose_phone_show = "Agent_entity_id";
	                }
	            }
	            /* end Вычисляем, чьи контакты отобразить */
	            
	            $query = "SELECT flat_owners.User_entity_id, flat_owners.Username, flat_owners.Agency , owner_phones.Phonenumber, offers.Entity_id, offers.Image_url, localities.Locality_name, offers.Address, offers.House_number, flat_types.Typename, types.Type_name, offers.IsExclusive, offers.Agent_entity_id FROM flat_owners LEFT JOIN offers ON flat_owners.User_entity_id = offers.${whose_phone_show} LEFT JOIN owner_phones ON offers.${whose_phone_show} = owner_phones.User_entity_id LEFT JOIN localities USING (Id_locality) LEFT JOIN flat_types USING (Id_flat_type) LEFT JOIN types USING (Id_type) WHERE offers.Entity_id='" . $entity_id . "';";           
	            
				$result = $this->main_bot->getRequestResult($query);
				if($result){
					$row_check = mysqli_num_rows($result);
					if($row_check > 0){ 
						$inline_offer_keyboard = null;
						$offer = null;
						$text_body = "\n";
						//собираем текст сообщения и необходимую информацию
						for($i = 0; $i < $row_check; $i++){
							$row = mysqli_fetch_row($result);
							
							if($i == 0){
								$is_exclusive = $row[11];
								$id_user = $row[0];
								$username = $row[1];
								$agency = $row[2];
								$id_database = $row[4];
								$image_url = $row[5];
								$city = $row[6];
								$street = $row[7];
								$house_num = $row[8];
								$flat_type = $row[9];
								$offer_type = $row[10];
								$agent_id = $row[12];
								
								
								//собираем клавиатуру
								$offer = new Offer("", $request_info->getCallbackData(), $id_database, $image_url, $this->functions->getSiteUrl($offer_type, $flat_type), $city, $street, $house_num, $id_user, $entity_id);
								$inline_offer_keyboard = new InlineOfferBotKeyboard($offer, $whitelist_info, false);
								
								//проверка на эксклюзивы
								if($is_exclusive == 1){
								    if($agent_id > 0) $text_body = $text_body . "🌟 <b>Эксклюзив</b> 🌟\n";
								    else{
								        $text_body = "\nКонтакты скрыты.";
								        break;
								    }
								}
								//пишем имя агента
								if(!is_null($username) && $username != ""){
									foreach(preg_split("/;/", $username) as $newname)
										{
											$text_body = $text_body . "💁‍♂️ ${newname}\r\n";
										}
								}
								else $text_body = $text_body . "🤷 Имя не указано\r\n";
								//пишем агенство
								if(!is_null($agency) && $agency != "") $text_body = $text_body . "📎 Агенство ${agency}\r\n";
							}
							//пишем телефоны
							$phonenumber = $row[3];
							$text_body = $text_body . preg_replace("/(0\d{2})(\d{3})(\d{2})(\d{2})/", "$1 $2 $3 $4", $phonenumber) . "\r\n";
						}
						//редактируем
						$this->main_bot->editMessage($request_info->getIdTelegram(), $request_info->getMessageData(), $text_title . $text_body, $inline_offer_keyboard);
						if(!is_null($offer)) $this->setPhonesPress($offer, $request_info, $whitelist_info);
					}
					//если информации о пользователе нет в базе
					else{
						$this->main_bot->editMessage($request_info->getIdTelegram(), $request_info->getMessageData(), "Данные о владельце не найдены.");
					}
					mysqli_free_result($result);
				}
			}
		}
		else{
			$this->main_bot->callAdmin("MainBotModule - Callback - " . $request_info->getCallbackData() . " - " . $whitelist_info->getIdWhitelist() . " " .$whitelist_info->getUsername());
		}
	}
	/* конец Обработка инлайн запросов*/
	
	private function showOffersOnPage($current_turn_page, $request_info, $whitelist_info, $offers_array){
		//$offers_array = $this->getOffers("WHERE bind_whitelist_distr_flats.Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . " ORDER BY offers.Update_timestamp desc;");
		$count_offers_array = count($offers_array);
		
		//есть информация для показа
		if($count_offers_array > 0){
			//вычисляем общее количество страниц
			$total_pages = 1;
			if($count_offers_array > $this->quantity_per_page){
				$total_pages = ceil($count_offers_array / $this->quantity_per_page);
				if($total_pages < 1) $total_pages=1;
			}
			//проверяем на валидность выбранную страницу
			if($current_turn_page > $total_pages) $current_turn_page = $total_pages;
			else if ($current_turn_page < 1) $current_turn_page = 1;
			//вычисляем диапазон, который покажем агенту
			$start_index = ($this->quantity_per_page * ($current_turn_page - 1));
			$end_index = min($start_index + $this->quantity_per_page, $count_offers_array);
			
			$separator = "➖➖➖➖";
			//начало страницы
			$this->main_bot->sendMessage($request_info->getIdTelegram(), "${separator}\n<b>Начало страницы ${current_turn_page} из ${total_pages}</b>\n${separator}\n" . $this->functions->declOfNum($end_index - $start_index, array('объект','объекта','объектов')));
			
			//показываем объявления
			for($i = $start_index; $i < $end_index; $i++){
				$this->showOffer($offers_array[$i], $request_info, $whitelist_info);
			}
			//конец страницы
			$inline_count_pages_keyboard = new InlineCountPagesBotKeyboard($current_turn_page, $total_pages);
			
			$end_page_text = "Всего " . $this->functions->declOfNum($count_offers_array, array('объект','объекта','объектов')) . " за последнюю неделю.";
			if($total_pages > 1) $end_page_text = "Конец страницы ${current_turn_page} из ${total_pages}, " . $this->functions->declOfNum($end_index - $start_index, array('объект','объекта','объектов')) . "\n\n" . $end_page_text;
			
			$this->main_bot->sendMessage($request_info->getIdTelegram(), $end_page_text, $inline_count_pages_keyboard, true);
		}
		//информации нет
		/*
			else{
				$this->main_bot->sendMessage($request_info->getIdTelegram(), "Информации по вашему району на данный момент нет, попробуйте позже!");
			}*/
		return $count_offers_array;
	}
	
	private function getOffers($where_query_part){
		$result = $this->main_bot->getRequestResult($this->functions->getSelectAndFromQueryPart() . $where_query_part);
		$offers_array = $this->functions->getOffersFromDBResult($result);
		mysqli_free_result($result);
		return $offers_array;
	}
	
	private function getOffersWithoutBind($where_query_part){
		$result = $this->main_bot->getRequestResult($this->find_code_query . $where_query_part);
		$offers_array = $this->functions->getOffersFromDBResult($result);
		mysqli_free_result($result);
		return $offers_array;
	}
	
	private function showOffer($offer, $request_info, $whitelist_info){
		$inline_offer_keyboard = new InlineOfferBotKeyboard($offer, $whitelist_info);
		//основное сообщение
		$this->main_bot->sendMessage($request_info->getIdTelegram(), $offer->getOfferDescription());
		//фотографии
		if(!is_null($offer->getImageUrl()) && $offer->getImageUrl() != ""){
			try{
				$this->main_bot->sendPhoto($request_info->getIdTelegram(), "https://an-gorod-image.com.ua/storage/uploads/preview/" . $offer->getImageUrl(), "<a href='https://angbots.ddns.net/image_ang/some_pic_get.php?entity=" . $offer->getIdOffer() . "'><b>Посмотреть все фотографии</b></a>");
			}
			catch(Exception $e){
			}
		}
		//место под телефоны и инлайн клаву
		$this->main_bot->sendMessage($request_info->getIdTelegram(), "Чтобы посмотреть контакты владельца объекта <b>". $offer->getIdOffer() ."</b>, нажмите на кнопку 'Телефоны' ниже.", $inline_offer_keyboard, true);
	}
	
	private function switchIsGetEditOffers($whitelist_info, $value){
		$query = "update white_list set Is_get_edit_offers=${value} where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";";
		$this->main_bot->getRequestResult($query);
	}
	
	private function turnThePage($whitelist_info, $page){
		$query = "update white_list set Turn_page=${page} where Id_whitelist_user=" . $whitelist_info->getIdWhitelist() . ";";
		$this->main_bot->getRequestResult($query);
	}
	
	private function setOffersPress($request_info, $whitelist_info){
		if(!$this->main_bot->checkIsIdAdmin($request_info)){
			$query = "insert into get_offers_press values(" . $whitelist_info->getIdWhitelist() . ", " . time() . ");";
			$this->main_bot->getRequestResult($query);
		}
	}
	
	private function setPhonesPress($offer, $request_info, $whitelist_info){
		if(!$this->main_bot->checkIsIdAdmin($request_info)){
			$query = "insert into agent_phone_press values (" . $whitelist_info->getIdWhitelist() . ", '" . $offer->getIdOffer() . "', " . $offer->getIdDatabase() .  "," . time() . ");";
			$this->main_bot->getRequestResult($query);
		}
	}
}

?>