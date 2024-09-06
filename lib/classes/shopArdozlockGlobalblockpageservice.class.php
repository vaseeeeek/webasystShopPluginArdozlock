<?php

class shopArdozlockGlobalblockpageservice
{
    private $model;

    public function __construct()
    {
        $this->model = new shopArdozlockGlobalblockedpagesModel();
    }

    /**
     * Валидация и сохранение блокированных страниц
     *
     * @param array $blockedPages
     * @return array
     */
    public function processBlockedPages(array $blockedPages)
    {   
        waLog::dump($blockedPages);
        // Валидация данных
        $validPages = $this->validateBlockedPages($blockedPages);

        if (empty($validPages)) {
            return ['success' => false, 'error' => 'Invalid data provided.'];
        }

        // Очищаем старые записи
        $this->model->clearAllBlockedPages();

        // Сохраняем новые блокировки
        $this->model->saveBlockedPages($validPages);

        return ['success' => true, 'message' => 'Blocked pages successfully updated.'];
    }

    /**
     * Валидация данных
     *
     * @param array $blockedPages
     * @return array
     */
    private function validateBlockedPages(array $blockedPages)
    {
        $validPages = [];

        foreach ($blockedPages as $page) {
            // Убедимся, что все необходимые поля заполнены
            if (!empty($page['page_id']) && !empty($page['page_type']) && !empty($page['application_id'])) {
                // Здесь происходит правильная валидация application_id и page_type
                if ($this->isValidApplicationId($page['application_id']) && $this->isValidPageType($page['page_type'])) {
                    $validPages[] = $page;
                }
            }
        }

        return $validPages;
    }

    /**
     * Проверка валидности application_id
     *
     * @param string $applicationId
     * @return bool
     */
    private function isValidApplicationId($applicationId)
    {
        // Список допустимых значений для application_id
        $validApplicationIds = ['shop', 'site'];

        return in_array($applicationId, $validApplicationIds);
    }

    /**
     * Проверка валидности page_type
     *
     * @param string $pageType
     * @return bool
     */
    private function isValidPageType($pageType)
    {
        // Список допустимых значений для page_type
        $validPageTypes = ['infopage', 'category'];

        return in_array($pageType, $validPageTypes);
    }
}