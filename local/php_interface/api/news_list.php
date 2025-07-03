<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context;
use Bitrix\Main\Loader;

header('Content-Type: application/json; charset=utf-8');

if (!Loader::includeModule('iblock')) {
    http_response_code(500);
    echo json_encode(['error' => 'IBlock module not loaded']);
    exit;
}

$request = Context::getCurrent()->getRequest();

$page = max((int)$request->get('page'), 1);
$limit = (int)$request->get('limit');
if ($limit <= 0) {
    $limit = 10;
}
$offset = ($page - 1) * $limit;

// Фильтр
$filter = [
    'IBLOCK_ID' => 5, // Укажи правильный ID инфоблока новостей
    'ACTIVE' => 'Y'
];

$dateFrom = $request->get('date_from');
$dateTo = $request->get('date_to');

if ($dateFrom && strtotime($dateFrom)) {
    $filter['>=DATE_ACTIVE_FROM'] = date('d.m.Y 00:00:00', strtotime($dateFrom));
}
if ($dateTo && strtotime($dateTo)) {
    $filter['<=DATE_ACTIVE_FROM'] = date('d.m.Y 23:59:59', strtotime($dateTo));
}


// Получаем элементы
$items = [];
$res = CIBlockElement::GetList(
    ['DATE_ACTIVE_FROM' => 'DESC'],
    $filter,
    false,
    ['nPageSize' => $limit, 'iNumPage' => $page],
    ['ID', 'NAME', 'PREVIEW_TEXT', 'DATE_ACTIVE_FROM', 'DETAIL_PAGE_URL']
);

while ($item = $res->GetNext()) {
    $items[] = [
        'id' => (int)$item['ID'],
        'name' => $item['NAME'],
        'preview_text' => $item['PREVIEW_TEXT'],
        'date' => $item['DATE_ACTIVE_FROM'],
        'url' => $item['DETAIL_PAGE_URL'],
    ];
}

// Навигация
$nav = $res->GetPageNavString('', '', true);
$totalPages = $res->NavPageCount;
$totalItems = $res->NavRecordCount;

echo json_encode([
    'page' => $page,
    'limit' => $limit,
    'total_items' => $totalItems,
    'total_pages' => $totalPages,
    'items' => $items
]);
