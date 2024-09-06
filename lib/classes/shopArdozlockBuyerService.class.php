<?php

class shopArdozlockBuyerService
{
    protected $buyersModel;
    protected $blockedPagesModel;

    public function __construct()
    {
        $this->buyersModel = new shopArdozlockBuyersModel();
        $this->blockedPagesModel = new shopArdozlockBlockedPagesModel();
    }

    /**
     * Получить всех покупателей с их заблокированными страницами.
     *
     * @return array Список покупателей с заблокированными страницами
     */
    public function getAllBuyersWithBlockedPages()
    {
        $buyers = $this->buyersModel->getAllBuyers();
        $allBuyersData = [];

        foreach ($buyers as $buyer) {
            $blockedPages = $this->blockedPagesModel->getBlockedPagesByBuyer($buyer['id']);
            $apps = $this->structureBlockedPagesByApps($blockedPages);
            $allBuyersData[] = [
                'id' => $buyer['id'],
                'name' => $buyer['name'],
                'showInfo' => false,
                'apps' => array_values($apps),
            ];
        }

        return $allBuyersData;
    }

    /**
     * Структурировать заблокированные страницы по приложениям.
     *
     * @param array $blockedPages Список заблокированных страниц
     * @return array Структурированные данные по приложениям
     */
    protected function structureBlockedPagesByApps($blockedPages)
    {   
        $apps = [
            'shop-cat' => ['name' => 'Магазин - категории', 'pages' => []],
            'shop-pages' => ['name' => 'Магазин - страницы', 'pages' => []],
            'site-pages' => ['name' => 'Сайт - страницы', 'pages' => []],
        ];

        foreach ($blockedPages as $blockedPage) {
            $pageData = $this->getPageData($blockedPage['page_id'], $blockedPage['page_type'], $blockedPage['application_id']);
            if ($pageData) {
                $apps[$blockedPage['application_id']]['pages'][] = [
                    'name' => $pageData['name'],
                    'access' => true,  // Если страница заблокирована, то доступ ограничен
                ];
            }
        }

        return $apps;
    }

    /**
     * Получить данные страницы по её ID и типу.
     *
     * @param int $page_id ID страницы
     * @param string $page_type Тип страницы
     * @param string $application_id ID приложения
     * @return array|null Данные страницы или null, если страница не найдена
     */
    protected function getPageData($page_id, $page_type, $application_id)
    {
        // Логика для получения данных страницы
        $pagesData = [
            1 => ['name' => 'Главная страница', 'application' => 'Блог'],
            2 => ['name' => 'Страница постов', 'application' => 'Блог'],
            3 => ['name' => 'Корзина', 'application' => 'Магазин'],
            4 => ['name' => 'Каталог товаров', 'application' => 'Магазин'],
            // Добавьте сюда другие страницы
        ];

        return isset($pagesData[$page_id]) ? $pagesData[$page_id] : null;
    }
}