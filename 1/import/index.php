<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require('Import.php');
$APPLICATION->SetTitle("Импорт из CSV");
use Test\Import;

$import = new Import('data.csv', 69);
$import->process();
$import->showLog();
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>