<?php
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

class CustomNewsListComponent extends CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [
            'loadMore' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf()
                ],
            ],
        ];
    }

    public function loadMoreAction()
    {
        $page = (int)\Bitrix\Main\Context::getCurrent()->getRequest()->getPost('page');
        if ($page <= 1) $page = 2;

        global $APPLICATION;

        $_GET['PAGEN_1'] = $page;
        $_REQUEST['PAGEN_1'] = $page;
        $this->arParams['PAGEN_1'] = $page;

        $this->arParams["DISPLAY_TOP_PAGER"] = "N";
        $this->arParams["DISPLAY_BOTTOM_PAGER"] = "N";
        $this->arParams["CACHE_TYPE"] = "N";

        ob_start();
        $this->executeComponent(); // <--- ОБЯЗАТЕЛЬНО!
        $html = ob_get_clean();

        $itemsCount = isset($this->arResult['ITEMS']) ? count($this->arResult['ITEMS']) : 0;

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/log.txt',
            date('Y-m-d H:i:s') . " | page: {$page} | ITEMS COUNT: {$itemsCount}" . PHP_EOL,
            FILE_APPEND
        );

        $hasMore = isset($this->arResult["NAV_RESULT"]) &&
            $this->arResult["NAV_RESULT"]->NavPageNomer < $this->arResult["NAV_RESULT"]->NavPageCount;

        return [
            'html' => $html,
            'hasMore' => $hasMore,
            'nextPage' => $page + 1,
        ];
    }



}
