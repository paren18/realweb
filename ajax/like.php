<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Application;

Loader::includeModule("highloadblock");

if (!check_bitrix_sessid()) {
    echo json_encode(['success' => false, 'error' => 'Invalid session']);
    die();
}

$response = ['success' => false];
$request = Application::getInstance()->getContext()->getRequest();
$userId = $GLOBALS['USER']->GetID();
$newsId = (int)$request->getPost("news_id");

if (!$userId || !$newsId) {
    echo json_encode($response);
    die();
}

$hlblock = HL\HighloadBlockTable::getList([
    'filter' => ['=NAME' => 'Likes']
])->fetch();

$entity = HL\HighloadBlockTable::compileEntity($hlblock);
$entityClass = $entity->getDataClass();

// Проверим — лайкал ли уже пользователь
$existing = $entityClass::getList([
    'filter' => ['UF_USER_ID' => $userId, 'UF_NEWS_ID' => $newsId],
])->fetch();

if ($existing) {
    // Удаляем лайк
    $entityClass::delete($existing['ID']);
    $response['liked'] = false;
} else {
    // Добавляем лайк
    $entityClass::add([
        'UF_USER_ID' => $userId,
        'UF_NEWS_ID' => $newsId
    ]);
    $response['liked'] = true;
}

// Получаем новое количество лайков
$count = $entityClass::getList([
    'filter' => ['UF_NEWS_ID' => $newsId],
    'select' => ['CNT'],
    'runtime' => [
        new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
    ]
])->fetch();

$response['count'] = (int)$count['CNT'];
$response['success'] = true;

echo json_encode($response);
