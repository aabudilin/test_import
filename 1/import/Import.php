<?

namespace Test;

class Import
{
	protected $path;
	protected $iblockId;
	protected $log;

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
		$PROP['VENDOR'] = $data[2];
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

		if($el->Add($arLoad)) {
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
		$PROP['QUANTITY'] = $data[4];
		$PROP['PRICE'] = $data[5];
		CIBlockElement::SetPropertyValuesEx($id, $this->iblockId, $PROP);
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

	public function showLog()
	{
		echo '<p><b>Результат обработки</b></p>';
		echo 'Создано - '.$this->log['new'].'<br>';
		echo 'Обновлено - '.$this->log['update'].'<br>';
	}

}