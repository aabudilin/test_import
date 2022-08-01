<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<table class="test-list">
	<tr>
		<th>ID</th>
		<th>NAME</th>
		<th>CODE</th>
		<th>MATERIAL</th>
		<th>VENDOR</th>
		<th>QUANTITY</th>
		<th>PRICE</th>
	</tr>	
<?foreach($arResult["ITEMS"] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>

	<tr id="<?=$this->GetEditAreaId($arItem['ID']);?>">
		<td><?=$arItem['ID']?></td>
		<td><?=$arItem['NAME']?></td>
		<td><?=$arItem['CODE']?></td>
		<td><?=$arItem['DISPLAY_PROPERTIES']['MATERIAL']['VALUE']?></td>
		<td><?=$arItem['DISPLAY_PROPERTIES']['VENDOR']['VALUE']?></td>
		<td><?=$arItem['DISPLAY_PROPERTIES']['QUANTITY']['VALUE']?></td>
		<td><?=$arItem['DISPLAY_PROPERTIES']['PRICE']['VALUE']?></td>
	</tr>
<?endforeach;?>
</table>

<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?>
<?endif;?>
