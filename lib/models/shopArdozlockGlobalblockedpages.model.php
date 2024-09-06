<?php

class shopArdozlockGlobalblockedpagesModel extends waModel
{
    protected $table = 'shop_ardozlock_global_blocked_pages';

    /**
     * Сохранение блокированных страниц
     *
     * @param array $blockedPages
     * @return void
     */
    public function saveBlockedPages(array $blockedPages)
    {
        foreach ($blockedPages as $page) {
            $this->insert($page, 2); // Используем insert с флагом 2 для обновления существующих записей
        }
    }

    /**
     * Удаление всех блокировок перед обновлением.
     */
    public function clearAllBlockedPages()
    {
        $this->deleteByField([]);
    }

    /**
     * Получить все заблокированные страницы
     *
     * @return array
     */
    public function getAllBlockedPages()
    {
        return $this->select('*')->fetchAll();
    }

    /**
     * Получить заблокированные страницы для конкретного приложения и типа страницы
     *
     * @param string $applicationId
     * @param string $pageType
     * @return array
     */
    public function getBlockedPagesByAppAndType($applicationId, $pageType)
    {
        return $this->select('*')
            ->where('application_id = ? AND page_type = ?', $applicationId, $pageType)
            ->fetchAll();
    }
}
