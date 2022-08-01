<?

/*Подлкючить класс*/
require('Import.php');

function ImportAgent()
{
	$path = $_SERVER["DOCUMENT_ROOT"].'/csv/data.csv'
	$iblockId = 69;

	$import = new Test\Import($path, $iblockId);
	$import->process();
	
	return "ImportAgent();";
}