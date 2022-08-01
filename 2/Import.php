<?

namespace Test;

class Import
{
	protected $path;
	protected $iblockId;
	protected $log;
	protected $vendorList;

	public function __construct (string $path, int $iblockId)
	{
		$this->path = $path;
		$this->iblockId = $iblockId;
		$this->log = [
			'new' => 0,
			'update' => 0,
			'error' => 0,
			'messages' => []
		];

		try {
	      if (!file_exists($this->path)) {
	        throw new \Exception('Не удалось открыть файл '.$this->path);
	      }
	    }
		catch (\Exception $ex) {
	        echo $ex->getMessage();
	    }

	    $this->vendorList = $this->getPropList('VENDOR');
	}

	public function process():void
	{
		$csv = new \CCSVData('R', true);
		$csv->LoadFile($this->path);
		$headerCsv = $csv->GetFirstHeader();
		$csv->SetDelimiter(';');
		while ($data = $csv->Fetch()) {
			if($product = $this->isset($data[1])) {
				$this->update($product['ID'], $data);
			} else {
				$this->save($data);
			}
		}
	}

	protected function save(array $data):bool
	{
		$el = new \CIBlockElement;
		$PROP = [];
		$PROP['VENDOR'] = [$this->getVendorId($data[2])];
		$PROP['MATERIAL'] = $data[3];
		$PROP['QUANTITY'] = $data[4];
		$PROP['PRICE'] = $data[5];
		$arLoad = Array(  
   			'MODIFIED_BY' => $GLOBALS['USER']->GetID(),
   			'IBLOCK_SECTION_ID' => false,
   			'IBLOCK_ID' => $this->iblockId,
   			'PROPERTY_VALUES' => $PROP,  
   			'NAME' => $data[1],
   			'CODE' => $data[0],
   			'ACTIVE' => 'Y',
		);

		if($id = $el->Add($arLoad)) {

			//Создаем товар
          	$productID = CCatalogProduct::add(array("ID" => $id, "QUANTITY" => $data[4]));

            //Добавляем цену
            $priceFields = Array(
              "CURRENCY"         => "RUB", 
              "PRICE"            => $data[5],
              "CATALOG_GROUP_ID" => 1,
              "PRODUCT_ID"       => $id,
            );
            CPrice::Add($priceFields);

			$this->log['new']++;
		   return true;
		} else {
		   $this->log['messages'][] = $el->LAST_ERROR;
		   $this->log['error']++;
		   return false;
		}
	}

	protected function update(int $id, array $data):void
	{
		$el = new \CIBlockElement;
		$PROP = [];
		$PROP['VENDOR'] = [$this->getVendorId($data[2])];;
		$PROP['MATERIAL'] = $data[3];
		CIBlockElement::SetPropertyValuesEx($id, $this->iblockId, $PROP);

		//Обновление цены
		$arPropPrice = Array(
          "CURRENCY"         => "RUB",
          "PRICE"            => $data[5],
          "CATALOG_GROUP_ID" => 1,
          "PRODUCT_ID"       => $id,
        );

        $res_price = CPrice::GetList(
            array(),
            array(
                "PRODUCT_ID" => $id,
                "CATALOG_GROUP_ID" => 1,
            )
        );

        if ($arr = $res_price->Fetch()) {
            CPrice::Update($arr["ID"],$arPropPrice);
        } else {
            CPrice::Add($arPropPrice);
        }


        //Обновление количества
        CCatalogProduct::Update($id, ['QUANTITY' => $data[4]]);

		$this->log['update']++;
	}

	protected function isset(string $code)
	{
		$item = \Bitrix\Iblock\ElementTable::getList([
			'select' => ['ID'], 
			'filter' => ['IBLOCK_ID' => $this->iblockId, "CODE" => $code],
			'limit' => 1,
		]);

		return $item->fetch();
	}

	public function showLog():void
	{
		echo '<p><b>Результат обработки</b></p>';
		echo 'Создано - '.$this->log['new'].'<br>';
		echo 'Обновлено - '.$this->log['update'].'<br>';
	}

	protected function getPropList(string $code):array
	{
	    $property_enums = \CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$this->iblockId, "CODE"=>$code));
	    $result = array();
	    while($enum_fields = $property_enums->GetNext()) {
	      $result[mb_strtoupper($enum_fields["VALUE"])] = $enum_fields["ID"];
	    }
	    return $result;
	}

	protected function addPropList(string $code, string $value):int
	{
		$property = \CIBlockProperty::GetByID($code, $this->iblockId)->GetNext();
	    $enum = new \CIBlockPropertyEnum();
		$enumId = $enum->Add([
		    'PROPERTY_ID' => $property['ID'],
		    'VALUE' => $value,
		]);
		return $enumId;
	}

	protected function getVendorId(string $value):int
	{
		if(!$id = $this->vendorList[mb_strtoupper($value)]) {
			$id = $this->addPropList('VENDOR', $value);
			$this->vendorList[mb_strtoupper($value)] = $id;
		}
		return $id;
	}

}