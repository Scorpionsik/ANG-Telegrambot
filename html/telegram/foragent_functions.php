<?php

class Offer{
	private $message;
	private $internal_id;
	private $entity_id;
	
	public function __construct($message, $internal_id, $entity_id)
	{
		$this->message = $message;
		$this->internal_id = $internal_id;
		$this->entity_id = $entity_id;
	}
	
	public function getMessage()
	{
		return $this->message;
	}
	
	public function getInternalId()
	{
		return $this->internal_id;
	}
	
	public function getEntityId()
	{
		return $this->entity_id;
	}
}

function declOfNum($num, $titles) {
    $cases = array(2, 0, 1, 1, 1, 2);
    return $num . " " . $titles[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];
}

function makeOfferMessages($dblink, $whitelist_id_user, $clause = null, $limit = -1){
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
	15	offers.Image_url			string (empty)
	16	offers.IsNew				boolean
	17	offers.IsEdit				boolean
	18	offers.Orient				string
	19	offers.Entity_id			int
	20	offers.BuildStatus			string
	21	offers.IsNewBuild			boolean
	22	offers.Update_timestamp		double
	*/
	$result_array = array();

	$query = "select offers.Internal_id, types.Type_name, flat_types.Typename, localities.Locality_name, districts.District_name, offers.Address, offers.Description, offers.Room_counts, offers.Floor, offers.Floors_total, offers.Area, offers.Lot_area, offers.Living_space, offers.Kitchen_space, offers.Price, offers.Image_url, offers.IsNew, offers.IsEdit, offers.Orient, offers.Entity_id, offers.BuildStatus, offers.IsNewBuild, offers.Update_timestamp FROM offers inner join bind_whitelist_distr_flats on offers.Id_type=bind_whitelist_distr_flats.Id_type AND offers.Id_locality=bind_whitelist_distr_flats.Id_locality AND (offers.Id_flat_type=bind_whitelist_distr_flats.Id_flat_type OR bind_whitelist_distr_flats.Id_flat_type=1) AND (offers.Id_district=bind_whitelist_distr_flats.Id_district OR bind_whitelist_distr_flats.Id_district=1) AND (offers.Room_counts=bind_whitelist_distr_flats.Room_counts OR bind_whitelist_distr_flats.Room_counts=0) AND (offers.Orient=(SELECT orients.Orient_name FROM orients WHERE orients.Id_orient=bind_whitelist_distr_flats.Id_orient) OR bind_whitelist_distr_flats.Id_orient=1) AND (offers.IsNewBuild=bind_whitelist_distr_flats.Id_build_status OR bind_whitelist_distr_flats.Id_build_status=2) inner join types on offers.Id_type=types.Id_type inner join flat_types on offers.Id_flat_type=flat_types.Id_flat_type INNER JOIN localities ON offers.Id_locality=localities.Id_locality inner join districts on offers.Id_district=districts.Id_district where bind_whitelist_distr_flats.Id_whitelist_user=" . $whitelist_id_user;
	if(!is_null($clause) && $clause!=""){
		$query = $query . " AND (" . $clause . ")";
	}
	$query = $query . " ORDER BY offers.Update_timestamp desc"
	if($limit > 0) $query = $query . " limit ${limit}";
	$query = $query . ";";
	$result_bind = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));
	
	if($result_bind)
	{
		$row_bind_count = mysqli_num_rows($result_bind);
		
		if($row_bind_count > 0)
		{
			for($i = 0; $i < $row_bind_count; $i++)
			{				
				$row_bind = mysqli_fetch_row($result_bind);
				//код базы			
				$offer_message = "🔍 " . $row_bind[0];
				
				//новая/обновленная
				if($row_bind[16]==1) $offer_message = $offer_message . "\r\n🔥🔥Новая🔥🔥";
				else if($row_bind[17]==1)$offer_message = $offer_message . "\r\n➡️➡️Обновлена⬅️⬅️";
				
				//---адрес---//
				//город
				$offer_message = $offer_message . "\r\n📍 " . $row_bind[3];
				
				//район
				if($row_bind[4] != 1)
				{
					$offer_message = $offer_message . ", " . $row_bind[4];
				}
				
				//улица
				if($row_bind[5] != null)
				{
					$offer_message = $offer_message . ", " . $row_bind[5];
				}
				
				//ориентир
				if($row_bind[18] != null and $row_bind[18] != "")
				{
					$offer_message = $offer_message . ", ориентир: " . $row_bind[18];
				}
				//---конец адрес---//
				
				//тип объекта
				$offer_message = $offer_message . "\r\n🔑 " . $row_bind[2];
				
				//вторичка-новострой
				if($row_bind[21] == 0) $offer_message = $offer_message . ", вторичка";
				else $offer_message = $offer_message . ", новострой";
				
				//тип сделки
				$offer_message = $offer_message . ", " . $row_bind[1];

				//кол-во комнат
				$offer_message = $offer_message . "\r\n🏘 " . declOfNum($row_bind[7],array('комната','комнаты','комнат'));
				
				
				//состояние объекта
				$offer_message = $offer_message . "\r\n🛠 Cостояние: " . $row_bind[20];
								
				//этаж-этажность, площадь
				$offer_message = $offer_message . " \r\n🏢 " . $row_bind[8] . " / " . $row_bind[9] . " \n📐 " . $row_bind[10] . " / " . $row_bind[12] . " / " . $row_bind[13];
				if($row_bind[11] != null && $row_bind[11] > 0) $offer_message = $offer_message . ", участок " .  declOfNum($row_bind[11],array('сотка','сотки','соток'));
				
				//цена
				$offer_message = $offer_message . "\r\n \n💰 Цена: " . $row_bind[14];
				if($row_bind[1] == "аренда") $offer_message = $offer_message . " грн.";
				else $offer_message = $offer_message . " $";
				
				//описание
				$offer_message = $offer_message . "\n\n" . $row_bind[6];
				
				//сохраняем готовый объект
				$result_array[] = new Offer($offer_message, $row_bind[0], $row_bind[19]);
			}
			
		}

	}

	mysqli_free_result($result_bind);
	
	return $result_array;
}




?>