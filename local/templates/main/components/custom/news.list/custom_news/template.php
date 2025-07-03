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

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
$this->addExternalCss("/bitrix/css/main/bootstrap.css");
$this->addExternalCss("/bitrix/css/main/font-awesome.css");
$this->addExternalCss($this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css');
?>
<div class="container">
<div class="news-grid">
    <?php foreach ($arResult["ITEMS"] as $item): ?>
        <div class="news-card">
            <?php if ($item["PREVIEW_PICTURE"]): ?>
                <div class="news-image">
                    <img src="<?= $item["PREVIEW_PICTURE"]["SRC"] ?>" alt="<?= htmlspecialcharsbx($item["NAME"]) ?>">
                </div>
            <?php endif; ?>
            <div class="news-content">
                <div class="news-date"><?= $item["ACTIVE_FROM"] ?></div>
                <h3 class="news-title">
                    <a href="#">
                        <?= htmlspecialcharsbx($item["NAME"]) ?>
                    </a>
                </h3>
                <div class="news-preview"><?= $item["PREVIEW_TEXT"] ?></div>
                <div class="like-wrapper" data-news-id="<?= $item['ID'] ?>">
                    <button class="like-btn <?= $item['USER_LIKED'] ? 'liked' : '' ?>">❤️</button>
                    <span class="like-count"><?= htmlspecialchars($item['LIKE_COUNT']) ?></span>
                </div>


            </div>
        </div>
    <?php endforeach; ?>
</div>

    <?php if ($arResult["NAV_RESULT"]->NavPageNomer < $arResult["NAV_RESULT"]->NavPageCount): ?>
        <div class="load-more-wrap">
            <button id="load-more"
                    data-next-page="<?= $arResult["NAV_RESULT"]->NavPageNomer + 1 ?>"
                    data-url="<?= $arResult["NAV_RESULT"]->sUrlPath ?>">Показать ещё</button>
        </div>
    <?php endif; ?>

</div>


