<?php
require_once "Offer.php";

class Functions{
	private $select_and_from_query_part = "select offers.Internal_id, types.Type_name, flat_types.Typename, localities.Locality_name, districts.District_name, offers.Address, offers.Description, offers.Room_counts, offers.Floor, offers.Floors_total, offers.Area, offers.Lot_area, offers.Living_space, offers.Kitchen_space, offers.Price, offers.Image_url, offers.IsNew, offers.IsEdit, offers.Orient, offers.Entity_id, offers.BuildStatus, offers.IsNewBuild, offers.Old_price, offers.House_number, offers.User_entity_id FROM offers inner join bind_whitelist_distr_flats on offers.Id_type=bind_whitelist_distr_flats.Id_type AND offers.Id_locality=bind_whitelist_distr_flats.Id_locality AND (offers.Id_flat_type=bind_whitelist_distr_flats.Id_flat_type OR bind_whitelist_distr_flats.Id_flat_type=1) AND (offers.Id_district=bind_whitelist_distr_flats.Id_district OR bind_whitelist_distr_flats.Id_district=1) AND (offers.Room_counts=bind_whitelist_distr_flats.Room_counts OR bind_whitelist_distr_flats.Room_counts=0) AND (offers.Orient=(SELECT orients.Orient_name FROM orients WHERE orients.Id_orient=bind_whitelist_distr_flats.Id_orient) OR bind_whitelist_distr_flats.Id_orient=1) AND (offers.IsNewBuild=bind_whitelist_distr_flats.Id_build_status OR bind_whitelist_distr_flats.Id_build_status=2) AND ((offers.Price<=bind_whitelist_distr_flats.Price_lower_than OR bind_whitelist_distr_flats.Price_lower_than=0) AND (offers.Price>=bind_whitelist_distr_flats.Price_upper_than OR bind_whitelist_distr_flats.Price_upper_than=0)) inner join types on offers.Id_type=types.Id_type inner join flat_types on offers.Id_flat_type=flat_types.Id_flat_type INNER JOIN localities ON offers.Id_locality=localities.Id_locality inner join districts on offers.Id_district=districts.Id_district ";
		/*
		0	offers.Internal_id			string
		1	types.Type_name				string
		2	flat_types.Typename			string
		3	localities.Locality_name	string
		4	districts.District_name		string
		5	offers.Address				string
		6	offers.Description			string
		7	offers.Room_counts			int
		8	offers.Floor				int
		9	offers.Floors_total			int
		10	offers.Area					double
		11	offers.Lot_area				double
		12	offers.Living_space			double
		13	offers.Kitchen_space		double
		14	offers.Price				int
		15	offers.Image_url			string
		16	offers.IsNew				boolean
		17	offers.IsEdit				boolean
		18	offers.Orient				string
		19	offers.Entity_id			int
		20	offers.BuildStatus			string
		21	offers.IsNewBuild			boolean
		22	offers.Old_price			int
		23 offers.House_number			string
		24 offers.User_entity_id		int
		25 offers.Entity_id	          	int
		*/
	public function getOffersFromDBResult($db_result){
		$return_array = array();
		if($db_result){
			$row_check = mysqli_num_rows($db_result);
			if($row_check > 0){
				for($i = 0; $i < $row_check; $i++){
					$row = mysqli_fetch_row($db_result);
					
					$id_offer = $row[0];
					$id_database = $row[19];
					$id_user = $row[24];
					
					$offer_type = $row[1];
					$flat_type = $row[2];
					$building_status = $row[20];
					$is_novostroy = $row[21];
					
					$city = $row[3];
					$district = $row[4];
					$street = preg_replace("/\([^)]+\)/", "", $row[5]);
					$street = preg_replace("/[ ]{2,}/"," ", $street);
					$house_num = $row[23];
					$orient = $row[18];
					
					$description = $row[6];
					$room_count = $row[7];
					$floor = $row[8];
					$total_floors = $row[9];
					
					$sq_all = $row[10];
					$sq_live = $row[12];
					$sq_kitchen = $row[13];
					$sq_area = $row[11];
					
					$price = $row[14];
					$old_price = $row[22];
					
					$image_url = $row[15];
					
					$is_new_offer = $row[16];
					$is_edit_offer = $row[17];
					
					$offer_description = "";
					
					$currency = "";
					if($offer_type == "аренда") $currency = "грн.";
					else $currency = "$";
					
					//код базы
					$site_url = $this->getSiteUrl($offer_type, $flat_type);
					$offer_description = "🔍 <a href=\"${site_url}${id_offer}\">${id_offer}</a>";
					
					//статус обновления
					if($is_new_offer == 1) $offer_description = $offer_description . "\r\n🔥 Новый объект 🔥";
					if($old_price != $price && $old_price != 0){
						$smile_status = "";
						$text_status = "";
						$diff = $old_price - $price;
						
						if($diff > 0)
						{
							$smile_status = "📉";
							$text_status = "Цена упала на";
						}
						else
						{
							$smile_status = "📈";
							$text_status = "Цена поднялась на";
							$diff = $diff * -1;
						}
						$offer_description = $offer_description . "\r\n${smile_status} ${text_status} ${diff} ${currency} ${smile_status}";
					}
					//---адрес---//
						//город
						$offer_description = $offer_description . "\r\n📍 ${city}";
						
						//район
						if($district != "Все") $offer_description = $offer_description . ", ${district}";
						
						//улица
						if(!is_null($street)) 
						{
							$offer_description = $offer_description . "\r\n🚏 ${street}";
							//номер дома
							if(!is_null($house_num)) $offer_description = $offer_description . " ${house_num}";
						}
						//ориентир
						if(!is_null($orient) && $orient != "") 
						{
							//if(!is_null($street) || $street == "") $offer_description = $offer_description . ", ";
							$offer_description = $offer_description . "\r\n🚏 Ориентир: ${orient}";
						}
					//---конец адрес---//
					
					//тип объекта
					$offer_description = $offer_description . "\r\n🔑 ${flat_type}";
					
					//вторичка-новострой
					if($is_novostroy == 0) $offer_description = $offer_description . ", вторичка";
					else $offer_description = $offer_description . ", новострой";
					
					//тип сделки
					$offer_description = $offer_description . ", ${offer_type}";
					
					//кол-во комнат
					$offer_description = $offer_description . "\r\n🏘 " . $this->declOfNum($room_count, array('комната','комнаты','комнат'));
					
					//состояние объекта
					$offer_description = $offer_description . "\r\n🛠 Cостояние: ${building_status}";
					
					//этаж этажность площадь
					$offer_description = $offer_description . " \r\n🏢 ${floor} / ${total_floors} \n📐 ${sq_all} / ${sq_live} / ${sq_kitchen}";
					if(!is_null($sq_area) && $sq_area > 0) $offer_description = $offer_description . ", участок " . $this->declOfNum($sq_area, array('сотка','сотки','соток'));
					
					//цена
					$offer_description = $offer_description . "\r\n \n💰 Цена: " . preg_replace('/(?<=\d)(?=(\d{3})+$)/', ' ', $price) . " ${currency}";
					
					//описание
					$offer_description = $offer_description . "\n\n" . $description;
					
					//save
					$return_array[] = new Offer($offer_description, $id_offer, $id_database, $image_url, $site_url, $city, $street, $house_num, $id_user, $id_database);
				}
				
			}
		}
		return $return_array;
	}
	
	public function getSelectAndFromQueryPart(){
		return $this->select_and_from_query_part;
	}

	public function declOfNum($num, $titles) {
		$cases = array(2, 0, 1, 1, 1, 2);
		return $num . " " . $titles[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];
	}

	public function getSiteUrl($offer_type, $flat_type)
	{
		$result = "https://an-gorod.com.ua/";
		
		switch($flat_type)
		{
			case "Квартира":
				if($offer_type == "продажа")
				{
					//продажа
					$result = $result . "real/flat/sale";
				}
				else 
				{
					//аренда
					$result = $result . "real/flat/rent";
				}
			break;
			case "Гостинка":
				if($offer_type == "продажа")
				{
					//продажа
					$result = $result . "kupit-komnatu-v-kharkove";
				}
				else 
				{
					//аренда
					$result = $result . "snyat-gostinku-kharkov";
				}
			break;
			case "Дом":
				if($offer_type == "продажа")
				{
					//продажа
					$result = $result . "real/house/sale";
				}
				else 
				{
					//аренда
					$result = $result . "snyat-dom-v-kharkove";
				}
			break;
			case "Участок":
				if($offer_type == "продажа")
				{
					//продажа
					$result = $result . "kupit-uchastok";
				}
				else 
				{
					//аренда
					$result = $result . "arenda-komercheskoi-nedvigimosti"; //возможно надо будет исправить
				}
			break;
			default: //коммерческая
			if($offer_type == "продажа")
				{
					//продажа
					$result = $result . "real/estate/sale";
				}
				else 
				{
					//аренда
					$result = $result . "arenda-komercheskoi-nedvigimosti";
				}
			break;
		}
		return ($result . "?q=");
	}


}

?>