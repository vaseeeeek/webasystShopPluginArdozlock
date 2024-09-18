<?php

class shopArdozlockBuyerService
{
    protected $buyersModel;
    protected $unlockedPagesModel;

    public function __construct()
    {
        $this->buyersModel = new shopArdozlockBuyersModel();
        $this->unlockedPagesModel = new shopArdozlockUnlockedbuyerpagesModel();
    }

    /**
     * Установить срок доступа для покупателя.
     *
     * @param int $buyer_id ID покупателя
     * @param int $access_duration_days Срок доступа в днях
     */
    public function setBuyerAccessDuration($buyer_id, $access_duration_days)
    {
        $this->buyersModel->setAccessDuration($buyer_id, $access_duration_days);
    }

    /**
     * Проверка доступности страницы в зависимости от срока доступа.
     *
     * @param int $buyer_id ID покупателя
     * @return bool Доступ разрешен или нет
     */
    public function isAccessAllowed($buyer_id)
    {
        $access_end_date = $this->buyersModel->getAccessEndDate($buyer_id);

        if ($access_end_date) {
            $current_date = date('Y-m-d');
            return $current_date <= $access_end_date;
        }

        // Если срок не установлен или истек, доступ запрещен
        return false;
    }

    /**
     * Установить дату начала доступа, если она еще не установлена.
     *
     * @param int $buyer_id ID покупателя
     */
    public function setAccessStartDateIfNotSet($buyer_id)
    {
        $buyer = $this->buyersModel->getById($buyer_id);

        if (empty($buyer['access_start_date'])) {
            $this->buyersModel->setAccessStartDate($buyer_id, date('Y-m-d'));
        }
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
            $blockedPages = $this->unlockedPagesModel->getUnlockedPagesByBuyer($buyer['id']);
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
