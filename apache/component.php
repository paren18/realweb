<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!function_exists('sortprop')) {
function sortprop($a, $b)
{
	$key = 'SORT';
	if ($a[$key] == $b[$key]) {
		return 0;
	}
	return ($a[$key] < $b[$key]) ? -1 : 1;
}
}


CModule::IncludeModule("iblock");

$calc = htmlspecialcharsEx($_REQUEST["calculate"]);
$ord = htmlspecialcharsEx($_REQUEST["order"]);
$payment = htmlspecialcharsEx($_REQUEST["payment"]);


$action = $calc?"calculate":"";
$action = $ord?"order":$action;
$action = $payment?"payment":$action;

if(CModule::IncludeModule("yenisite.market"))
{
	$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("CODE" => "YENISITE_MARKET_ORDER"));
	$arr = $rsIBlock->Fetch();
	if($action == "payment")
	{
		$order_id = htmlspecialcharsEx($_REQUEST["id"]);
		if(is_numeric($order_id))
		{
			
			$arResult['ORDER'] = CMarketOrder::GetByID($order_id);
			
			include($_SERVER['DOCUMENT_ROOT'].$arResult['ORDER']['PAY_SYSTEM']['PATH_TO_ACTION']);
		}
		else
			echo GetMessage("NO_ORDER_ID");
		return;
	}
	$arProperty[] = array();
	$rsProp = CIBlockProperty::GetList(Array("SORT"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arr["ID"]));
	while($arr=$rsProp->Fetch())
	{
		$arProperty[$arr["CODE"]] = $arr;		
	}
	if(is_array($arParams['PROPERTY_CODE']))
	{
		foreach($arParams["PROPERTY_CODE"] as $code)
		{

			$type = $arProperty[$code]["PROPERTY_TYPE"];
			$arResult["DISPLAY_PROPERTIES"][$code]["SORT"] = $arProperty[$code]["SORT"];
			$arResult["DISPLAY_PROPERTIES"][$code]["TYPE"] = $type;
			$arResult["DISPLAY_PROPERTIES"][$code]["USER_TYPE"] = $arProperty[$code]["USER_TYPE"];
			switch($type)
			{

				
				case "N":
				case "S":
					$arResult["DISPLAY_PROPERTIES"][$code]["IS_REQUIRED"] = $arProperty[$code]["IS_REQUIRED"];              
					$arResult["DISPLAY_PROPERTIES"][$code]["NAME"] = $arProperty[$code]["NAME"];
					$arResult["DISPLAY_PROPERTIES"][$code]["VALUE"] = htmlspecialcharsEx($_REQUEST["PROPERTY"][$code]);
					if($arProperty[$code]["USER_TYPE"] == "HTML")
					{
						$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] = "<textarea name=\"PROPERTY[".$code."]\">".$arResult["DISPLAY_PROPERTIES"][$code]["VALUE"]."</textarea>";
						$arResult["DISPLAY_PROPERTIES"][$code]["UT"] = "HTML";
					}
					else
					{
						$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] = "<input type=\"text\" name=\"PROPERTY[".$code."]\" value=\"".$arResult["DISPLAY_PROPERTIES"][$code]["VALUE"]."\" />";
						$arResult["DISPLAY_PROPERTIES"][$code]["UT"] = "N";
					}
					break;
				case "L":
					$arResult["DISPLAY_PROPERTIES"][$code]["IS_REQUIRED"] = $arProperty[$code]["IS_REQUIRED"];   				                
						$arResult["DISPLAY_PROPERTIES"][$code]["NAME"] = $arProperty[$code]["NAME"];
						$res = CIBlockPropertyEnum::GetList(array("sort" => "asc"), array("PROPERTY_ID" => $arProperty[$code]['ID']));
						$arResult["DISPLAY_PROPERTIES"][$code]["VALUE"] = htmlspecialcharsEx($_REQUEST["PROPERTY"][$code]);
						while($arres =  $res->GetNext()){
							if(!$arResult["DISPLAY_PROPERTIES"][$code]["VALUE"]) 
							{
								if($arres['DEF'] == 'Y')
									$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] .= "<input type=\"radio\" checked=\"checked\" name=\"PROPERTY[".$code."]\" value=\"".$arres['ID']."\" />".$arres['VALUE']."<br/>";						
								else
									$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] .= "<input type=\"radio\" name=\"PROPERTY[".$code."]\" value=\"".$arres['ID']."\" />".$arres['VALUE']."<br/>";						
							}
							else
							{
								if($arResult["DISPLAY_PROPERTIES"][$code]["VALUE"] == $arres['ID'])
									$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] .= "<input type=\"radio\" checked=\"checked\" name=\"PROPERTY[".$code."]\" value=\"".$arres['ID']."\" />".$arres['VALUE']."<br/>";						
								else
									$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] .= "<input type=\"radio\" name=\"PROPERTY[".$code."]\" value=\"".$arres['ID']."\" />".$arres['VALUE']."<br/>";						
							}
						}
						
						$arResult["DISPLAY_PROPERTIES"][$code]["UT"] = "L";
						

				break;
				case "E":
					$arResult["DISPLAY_PROPERTIES"][$code]["IS_REQUIRED"] = $arProperty[$code]["IS_REQUIRED"];   				                
					$arResult["DISPLAY_PROPERTIES"][$code]["NAME"] = $arProperty[$code]["NAME"];
					$res = CIBlockElement::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID"=>$arProperty[$code]['LINK_IBLOCK_ID'], "ACTIVE"=>"Y"));
					$arResult["DISPLAY_PROPERTIES"][$code]["VALUE"] = htmlspecialcharsEx($_REQUEST["PROPERTY"][$code]);
					$checked = false;
					while($ob =  $res->GetNextElement())
					{
						$arres = $ob->GetFields();
						
						$checkRobo = CMarketOrder::CheckPaymentOnRobo($arres['IBLOCK_ID'], $arres['ID'], $arres['CODE']);
						if ($checkRobo == false){
						
						$resProp = CIBlockElement::GetList(array(), array('ID'=>$arres['ID']), false, array(), array("ID", "NAME", "PROPERTY_PRICE", "PROPERTY_PATH_TO_ACTION"));
						$arProps =  $resProp->GetNext();
						if(is_numeric($arProps['PROPERTY_PRICE_VALUE_ID']))
						{
							if($arProps['PROPERTY_PRICE_VALUE']==0)
								$arProps['DISPLAY_PRICE']=' ('.GetMessage("DELIVERY_FREE").')';
							else
								$arProps['DISPLAY_PRICE']=' ('.$arProps['PROPERTY_PRICE_VALUE'].' <span class="rubl">'.GetMessage("RUB").'</span>)';
						}
						if(is_numeric($arProps['PROPERTY_PATH_TO_ACTION_VALUE_ID']))
						{
							$arResult["PAY_SYSTEM"][$arres['ID']]['NEED_PAY'] = true;
						}
						else
						{
							$arResult["PAY_SYSTEM"][$arres['ID']]['NEED_PAY'] = false;
						}
						if(!$arResult["DISPLAY_PROPERTIES"][$code]["VALUE"]) 
						{
							if(!$checked)
							{
								$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] .= "<input type=\"radio\" checked=\"checked\" name=\"PROPERTY[".$code."]\" value=\"".$arres['ID']."\" placeholder=\"".$arProps['PROPERTY_PRICE_VALUE']."\" />".$arres['NAME'].$arProps['DISPLAY_PRICE']."<br/>";	
								$checked = true;
							}
							else
							{
								$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] .= "<input type=\"radio\"  name=\"PROPERTY[".$code."]\" value=\"".$arres['ID']."\" placeholder=\"".$arProps['PROPERTY_PRICE_VALUE']."\" />".$arres['NAME'].$arProps['DISPLAY_PRICE']."<br/>";
							}
						}
						else
						{
							if($arResult["DISPLAY_PROPERTIES"][$code]["VALUE"] == $arres['ID'])
							{
								$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] .= "<input type=\"radio\" checked=\"checked\" name=\"PROPERTY[".$code."]\" value=\"".$arres['ID']."\" placeholder=\"".$arProps['PROPERTY_PRICE_VALUE']."\" />".$arres['NAME'].$arProps['DISPLAY_PRICE']."<br/>";		
							}
							else
							{
								$arResult["DISPLAY_PROPERTIES"][$code]["INPUT"] .= "<input type=\"radio\" name=\"PROPERTY[".$code."]\" value=\"".$arres['ID']."\" placeholder=\"".$arProps['PROPERTY_PRICE_VALUE']."\" />".$arres['NAME'].$arProps['DISPLAY_PRICE']."<br/>";
							}
						}
						
						}
					}
					
					$arResult["DISPLAY_PROPERTIES"][$code]["UT"] = "E";
				break;
			}
		}
	}
uasort($arResult["DISPLAY_PROPERTIES"], 'sortprop');


	/* delete and recalculate items */
	if($action == "calculate")
	{
		 if(isset($_REQUEST['Key']) && is_array($_REQUEST["Key"]))
		{
			foreach($_REQUEST["Key"] as $k=>$key)
			{
				if(isset($_REQUEST["count"][$k]) && $_REQUEST["count"][$k] > 0)
				{
					$_SESSION["YEN_MARKET_BASKET"][SITE_ID][$key]["YEN_COUNT"] = $_REQUEST["count"][$k];
				}
			}
		}elseif(isset($_REQUEST['count']) && is_array($_REQUEST["count"]))
		{
			foreach($_REQUEST["count"] as $k=>$v)
				if($v > 0)
					$_SESSION["YEN_MARKET_BASKET"][SITE_ID][$k]["YEN_COUNT"] = $v;
		}


		if(is_array($_REQUEST["del"]))
		{
			foreach($_REQUEST["del"] as $val)
			{
				unset($_SESSION["YEN_MARKET_BASKET"][SITE_ID][$val]);
			}
		}
	}

	$arResult['ERROR'] = array();

	/* print items */
	if(is_array($_SESSION["YEN_MARKET_BASKET"][SITE_ID]))
	{
		$arDeleteBasket = array();

		foreach($_SESSION["YEN_MARKET_BASKET"][SITE_ID] as $key=>$value)
		{

			$res = CMarketBasket::DecodeBasketItems($key);

			if(!is_array($arResult["PROPERTIES"][$res["ID"]]))
			{
				$ob_el = CIBlockElement::GetByID($res["ID"]);
				if($el = $ob_el->GetNextElement())
				{
					$arResult["PROPERTIES"][$res["ID"]] = $el->GetProperties();
					$arResult["FIELDS"][$res["ID"]] = $el->GetFields();
				}
			}
			$res["FIELDS"] = $arResult["FIELDS"][$res["ID"]];
			$res["COUNT"] = $value["YEN_COUNT"];
			$res["KEY"] = $key;

			/* ===== CHECK BASKET QUANTITY AND PRODUCT QUANTITY ===== */

			$arProduct = CMarketCatalogProduct::GetByID($res['ID'], $res['FIELDS']['IBLOCK_ID']);
			if ($arProduct['QUANTITY_TRACE'] == 'Y' && $arProduct['CAN_BUY_ZERO'] != 'Y') {
				if ($arProduct['QUANTITY'] <= 0) {
					$res["CAN_BUY"] = "N";
					$arResult['REFRESH_BASKET_SMALL'] = true;
					$arDeleteBasket[] = $key;
					if ($action == 'order') {
						$arResult['ERROR']['BASKET'] = GetMessage('BASKET_CHANGED');
					}
					continue;
				}

				$res['AVAILABLE_QUANTITY'] = $arProduct['QUANTITY'];
				if ($arProduct['QUANTITY'] < $res['COUNT']) {
					$res['COUNT'] = $arProduct['QUANTITY'];
					CMarketBasket::setQuantity($key, $res['COUNT']);

					if ($action == 'order') {
						$arResult['ERROR']['BASKET'] = GetMessage('BASKET_CHANGED');
					}
					$arResult['REFRESH_BASKET_SMALL'] = true;
				}
			}
			$res['PRODUCT'] = $arProduct;

			if ($action == 'order' && $res['COUNT'] != $value["YEN_COUNT"]) {
				$arResult['ERROR']['BASKET'] = GetMessage('BASKET_CHANGED');
			}

			/* ====================================================== */

			foreach($res["PROPERTIES"] as $key1=>$value1)
			{
				if($arResult["PROPERTIES"][$res["ID"]][$key1]["PROPERTY_TYPE"] == "L")
				{
					$db_enum = CIBlockProperty::GetPropertyEnum($arResult["PROPERTIES"][$res["ID"]][$key1]["ID"], array(), array());
					while($enum = $db_enum->Fetch()) {
						if($enum["ID"] == $res["PROPERTIES"][$key1]) {
							$res["PROPERTIES"][$key1] = $enum["VALUE"];
							break;
						}
					}
					$res["PROPERTIES"][$key1] = array("VALUE" => $res["PROPERTIES"][$key1], "NAME" => $arResult["PROPERTIES"][$res["ID"]][$key1]["NAME"]) ;
				}
				else
				if($arResult["PROPERTIES"][$res["ID"]][$key1]["PROPERTY_TYPE"] == "S"
				&& $arResult["PROPERTIES"][$res["ID"]][$key1]["USER_TYPE"] == "directory")
				{
					if (!array_key_exists('DISPLAY_VALUE', $arResult["PROPERTIES"][$res["ID"]][$key1])) {
						$arResult["PROPERTIES"][$res["ID"]][$key1] = CIBlockFormatProperties::GetDisplayValue($res['FIELDS'], $arResult["PROPERTIES"][$res["ID"]][$key1], '');
					}
					$arDisplayValue = $arResult["PROPERTIES"][$res["ID"]][$key1]['DISPLAY_VALUE'];
					if (is_array($arDisplayValue)) {
						foreach ($arResult["PROPERTIES"][$res["ID"]][$key1]['VALUE'] as $xmlKey => $xmlId) {
							if ($xmlId == $value1) {
								$res['PROPERTIES'][$key1] = $arDisplayValue[$xmlKey];
								break;
							}
						}
						$res["PROPERTIES"][$key1] = array("VALUE" => $res["PROPERTIES"][$key1], "NAME" => $arResult["PROPERTIES"][$res["ID"]][$key1]["NAME"]) ;
					} else {
						$res["PROPERTIES"][$key1] = array('VALUE' => $arDisplayValue, 'NAME' => $arResult["PROPERTIES"][$res["ID"]][$key1]["NAME"]);
					}
					unset($arDisplayValue);
				}
				else
				{
					$res["PROPERTIES"][$key1] = array("VALUE" => $res["PROPERTIES"][$key1], "NAME" => $arResult["PROPERTIES"][$res["ID"]][$key1]["NAME"]) ;
				}
			}
			
			$prices = CMarketPrice::GetItemPriceValues($res["ID"]);
			foreach($prices as $key=>$value)
			if(CMarketPrice::IsCanAdd($key))
			{
				$res["PRICE"][$key] = $value;
				$res["MIN_PRICE"] = $value;
			}

			foreach($res["PRICE"] as $price)
			if($price < $res["MIN_PRICE"])
				$res["MIN_PRICE"] = $price;

			$arResult["ITEMS"][] = $res;
		}

		foreach ($arDeleteBasket as $key) {
			CMarketBasket::Delete($key);
		}
	}

	$arResult["COMMON_PRICE"] = 0;
	$arResult["COMMON_COUNT"] = 0;
	if(is_array($arResult["ITEMS"]))
	{
		foreach($arResult["ITEMS"] as $key => $arElement)
		{
			$arResult["COMMON_PRICE"] += $arElement["MIN_PRICE"]*$arElement["COUNT"];
			$arResult["COMMON_COUNT"]++;
		}
	}


		/* add order */

	if($action == "order" && count($arResult['ERROR']) == 0)
	{
		$temp = GetMessage("TEXT");
		$temp2 = GetMessage("TEXT_ADMIN");
		$order = "";
		$detail_order = "";
		$preview_order = "";
	if(is_array($arResult["DISPLAY_PROPERTIES"]))
	{
		foreach($arResult["DISPLAY_PROPERTIES"] as $pk => $pv)
		{
			if($arResult["DISPLAY_PROPERTIES"][$pk]["VALUE"])
			{
				if($arResult["DISPLAY_PROPERTIES"][$pk]["UT"] == 'L') 
				{

					$ress = CIBlockPropertyEnum::GetList(array(), array("ID" => $arResult["DISPLAY_PROPERTIES"][$pk]["VALUE"]))->GetNext();
					$text .= "<b>".$pv["NAME"].":</b> ".$ress["VALUE"]."<br/>";
					$text2 .= "<b>".$pv["NAME"].":</b> ".$ress["VALUE"]."<br/>";
				}
				elseif($arResult["DISPLAY_PROPERTIES"][$pk]["UT"] == 'E')
				{
					$ress = CIBlockElement::GetByID($arResult["DISPLAY_PROPERTIES"][$pk]["VALUE"]);
					$ar_ress = $ress->GetNext();
					$text .= "<b>".$pv["NAME"].":</b> ".$ar_ress["NAME"]."<br/>";
					$text2 .= "<b>".$pv["NAME"].":</b> ".$ar_ress["NAME"]."<br/>";
				}
				else
				{
					$text .= "<b>".$pv["NAME"].":</b> ".$arResult["DISPLAY_PROPERTIES"][$pk]["VALUE"]."<br/>";
					$text2 .= "<b>".$pv["NAME"].":</b> ".$arResult["DISPLAY_PROPERTIES"][$pk]["VALUE"]."<br/>";
				}
				
				$temp = str_replace("#".$pk."#", $arResult["DISPLAY_PROPERTIES"][$pk]["VALUE"], $temp);
				$temp2 = str_replace("#".$pk."#", $arResult["DISPLAY_PROPERTIES"][$pk]["VALUE"], $temp2);
			}
			if($arResult["DISPLAY_PROPERTIES"][$pk]["IS_REQUIRED"] == 'Y' && !$pv["VALUE"] )
				$arResult["ERROR"][] = $pv["NAME"];
		}
	}
		$order .= '<table border="1" width="100%">';
		$order .= '<tr><td><b>'.GetMessage("TOVAR").'</b></td><td><b>'.GetMessage("KOLVO").'</b></td><td><b>'.GetMessage("PRICE_SHT").'</b></td></tr>';
		$ids = array();
	
	if(is_array($arResult["ITEMS"]))
	{
		foreach($arResult["ITEMS"] as $arItem){
		
			$ids[] = $arItem['ID'];
		
			if(!$arItem["MIN_PRICE"]) $arItem["MIN_PRICE"] = 0;
			$har = "";
			$preview_har = "";
			if(($propCount = count($arItem["PROPERTIES"])) > 0)
			{
				$i = 0;
				foreach($arItem["PROPERTIES"] as $arProp){
						$i++;
						if(!$arProp["NAME"]) continue;
						$har .= "<b>".$arProp["NAME"].":</b> <i>".$arProp["VALUE"].($i<$propCount?",&nbsp;":"")."</i>";
						$preview_har .= "".$arProp["NAME"].": ".$arProp["VALUE"].($i<$propCount?", ":"");
				}
			}
			
			//print_r($arItem);
			//die();
			
			$sec = CIBlockSection::GetByID($arItem["FIELDS"]['IBLOCK_SECTION_ID'])->GetNext();
			$order .="<tr><td>";
			if($sec)
				$order .= '<a href="http://'.$_SERVER['SERVER_NAME'].''.$sec["SECTION_PAGE_URL"].'" title="'.$sec["NAME"].'">'.$sec["NAME"].'</a> / ';
			$order .= '<a href="http://'.$_SERVER['SERVER_NAME'].''.$arItem["FIELDS"]["DETAIL_PAGE_URL"].'" title="'.$arItem["FIELDS"]["NAME"].'">'.$arItem["FIELDS"]["NAME"].'</a> ('.$har.') </td><td>'.$arItem["COUNT"].'</td><td>'.$arItem["MIN_PRICE"].' '.$arParams["UE"].'</td></tr>';
			
			if(!empty($preview_har) && $preview_har != ': ')
				$preview_order .= $arItem["FIELDS"]["NAME"].' ('.$preview_har.') '.$arItem["COUNT"].'x'.$arItem["MIN_PRICE"].' '.$arParams["UE"]."\n";
			else
				$preview_order .= $arItem["FIELDS"]["NAME"].' '.$arItem["COUNT"].'x'.$arItem["MIN_PRICE"].' '.$arParams["UE"]."\n";

			$detail_order .= '<p class="basket_item" data-id="item_' . $arItem['ID'] . '">';
			$detail_order .= '<span class="item_name">' . htmlspecialcharsBx($arItem["FIELDS"]["NAME"]);
			$detail_order .= ((!empty($preview_har) && $preview_har != ': ') ? ' (' . htmlspecialcharsBx($preview_har) . ')' : '') . '</span>';
			$detail_order .= ' <span class="item_count">' . $arItem["COUNT"] . '</span>';
			$detail_order .= 'x<span class="item_price">' . $arItem["MIN_PRICE"] . '</span>';
			$detail_order .= ' ' . $arParams["UE"] . "</p>\n";
		}
	}
		$order .= '</table>';
		
		/*  delivery   */
		if($arResult["DISPLAY_PROPERTIES"]["DELIVERY_E"]["UT"]=='E')
		{
			$res = CIBlockElement::GetList(array(), array('ID'=>$arResult["DISPLAY_PROPERTIES"]["DELIVERY_E"]["VALUE"]), false, array(), array("ID", "NAME", "PROPERTY_PRICE"));
			$arDelivery =  $res->GetNext();
			$preview_order .="\n".GetMessage("DELIVERY")."\n".$arDelivery['NAME']." - ";
			$detail_order  .="\n".GetMessage("DELIVERY")."\n".$arDelivery['NAME']." - <span class=\"delivery_price\">";
			if($arDelivery['PROPERTY_PRICE_VALUE']==0)
			{
				$preview_order .= GetMessage("DELIVERY_FREE");
				$detail_order  .= GetMessage("DELIVERY_FREE");
			}
			else
			{
				$preview_order .= $arDelivery['PROPERTY_PRICE_VALUE'] . " ".$arParams["UE"];
				$detail_order  .= $arDelivery['PROPERTY_PRICE_VALUE'] . " ".$arParams["UE"];
				$arResult["COMMON_PRICE"]+=$arDelivery['PROPERTY_PRICE_VALUE'];
			}
			$detail_order .= '</span>';
		}

		/*  payment  */
		if ($arResult['DISPLAY_PROPERTIES']['PAYMENT_E']['UT'] == 'E')
		{
			$res = CIBlockElement::GetList(array(), array('ID' => $arResult['DISPLAY_PROPERTIES']['PAYMENT_E']['VALUE']), false, array(), array('ID', 'NAME'));
			$arPayment = $res->GetNext();
			$preview_order .= "\n\n" . GetMessage("PAYMENT") . "\n" . $arPayment['NAME'];
		}


		$detail_order  .= "\n\n".GetMessage("TOTAL").$arResult["COMMON_PRICE"]." ".$arParams["UE"];
		$preview_order .= "\n\n".GetMessage("TOTAL").$arResult["COMMON_PRICE"]." ".$arParams["UE"];
		$preview_order = str_replace("()", "", $preview_order);
		$detail_order = str_replace("()", "", $detail_order);

		$temp = str_replace("#SUMM#", $arResult["COMMON_PRICE"]." ".$arParams["UE"], $temp);
		$temp = str_replace("#ORDER#", $order, $temp);
		
		$temp2 = str_replace("#SUMM#", $arResult["COMMON_PRICE"]." ".$arParams["UE"], $temp2);
		$temp2 = str_replace("#ORDER#", $order, $temp2);

		$text = str_replace("()", "", $temp.$text);		
		$order = str_replace("()", "", $order);

		$text2 = str_replace("()", "", $temp2.$text2);		



		$arEventFields = array("TEXT" => $text, "EMAIL" =>$arResult["DISPLAY_PROPERTIES"]["EMAIL"]["VALUE"]);
		
		$arEventFields2 = array("TEXT" => $text2, "EMAIL"  => $arParams['ADMIN_MAIL']);
		
		$el = new CIBlockElement;
		$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("CODE" => "YENISITE_MARKET_ORDER"));
		$arr = $rsIBlock->Fetch();

		$PROP = array();

		foreach($arResult["DISPLAY_PROPERTIES"] as $pk => $pv) {
			if($pv["UT"] == "HTML" && $pv["VALUE"])
				$PROP[$pk]["VALUE"]["TEXT"] = $pv["VALUE"];
			elseif($pv["UT"] == "L")		
			{
				 $PROP[$pk]["VALUE"] = $pv["VALUE"];
			}
			elseif($pv["VALUE"])
				$PROP[$pk] = $pv["VALUE"];
		}

		$PROP['AMOUNT'] = $arResult["COMMON_PRICE"];
		$PROP['SITE_ID'] = SITE_ID;
		$arLoadProductArray = Array(
		  "IBLOCK_SECTION_ID" => false,
		  "IBLOCK_ID"         => $arr["ID"],
		  "PREVIEW_TEXT"      => $preview_order,
		  "PREVIEW_TEXT_TYPE" => 'text',
		  "DETAIL_TEXT"       => $detail_order,
		  "DETAIL_TEXT_TYPE"  => 'html',
		  "PROPERTY_VALUES"   => $PROP,
		  "NAME"              => "Order ".date("d.m.Y h:i:s"), //$arResult["DISPLAY_PROPERTIES"]["FIO"]["VALUE"],
		  "ACTIVE"            => "Y",
		  );

	if(count($arResult["ERROR"]) == 0)	  
		if($PRODUCT_ID = $el->Add($arLoadProductArray))
		{
			foreach ($arResult['ITEMS'] as $key => $arItem) {
				if ($arItem['PRODUCT']['QUANTITY_TRACE'] == 'N') continue;
				CMarketCatalogProduct::TraceQuantity($arItem['COUNT'], $arItem['ID'], $arItem['FIELDS']['IBLOCK_ID']);
			}

			$property_enums = CIBlockPropertyEnum::GetList(Array(), Array("IBLOCK_ID"=>$arr["ID"], "CODE"=>"STATUS", "DEF" => "Y"))->GetNext();
			
			CIBlockElement::SetPropertyValueCode($PRODUCT_ID, 'STATUS', array("VALUE" => $property_enums['ID']));
			CIBlockElement::SetPropertyValueCode($PRODUCT_ID, 'ITEMS', $ids);
			
			require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/mainpage.php");
			if(!$sites = CMainPage::GetSiteByHost())
			{
				$sites = array();
				$rsSites = CSite::GetList($by="sort", $order="desc", Array());
				while ($arSite = $rsSites->Fetch())
				$sites[] = $arSite["ID"];
			}

			$link = $PRODUCT_ID;
			if (!empty($arParams['ORDER_URL'])) {
				$link = '<a href="' . $_SERVER['SERVER_NAME'] . str_replace('#ID#', $PRODUCT_ID, $arParams['ORDER_URL']) . '">' . $link . '</a>';
			}
			$arEventFields['TEXT'] = str_replace ('#ID#', $link, $arEventFields['TEXT']);
			CEvent::Send($arParams["EVENT"], $sites, $arEventFields);

			$text2 = str_replace("#ID#", $PRODUCT_ID, $text2);
			$text2 = str_replace("#IBLOCK_ID#", $arLoadProductArray['IBLOCK_ID'], $text2);
			$text2 = str_replace("#SERVER_NAME#", "http://{$_SERVER['SERVER_NAME']}", $text2);

			$arEventFields2 = array("TEXT" => $text2, "EMAIL"  => $arParams['ADMIN_MAIL']);
			CEvent::Send($arParams["EVENT_ADMIN"], $sites, $arEventFields2);

			unset($_SESSION["YEN_MARKET_BASKET"][SITE_ID]);
			//if (strlen($arResult["DISPLAY_PROPERTIES"])>0)

			if($arResult["PAY_SYSTEM"][$PROP['PAYMENT_E']]['NEED_PAY']===true)
			{
				LocalRedirect($APPLICATION->GetCurUri('payment=Y&id='.$PRODUCT_ID));
			}
			elseif($arParams["THANK_URL"]){
				LocalRedirect($arParams["THANK_URL"].'?id='.$PRODUCT_ID);
			} else {
				$arResult['ORDER_SUCCESS'] = 'Y';
			}
		} else {
			$arResult["ERROR"][] = $el->LAST_ERROR;
		}
	}
	if($action == "payment")
	{

	}

}

$this->IncludeComponentTemplate();
?>
