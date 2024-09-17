<?php
class shopArdozlockUnlockedbuyerpagesModel extends waModel
{
    protected $table = 'shop_ardozlock_unlocked_pages'; // Изменено на таблицу для разблокированных страниц

    /**
     * Получение всех разблокированных страниц для конкретного покупателя.
     *
     * @param int $buyer_id ID покупателя
     * @return array Список разблокированных страниц
     */
    public function getUnlockedPagesByBuyer($buyer_id)
    {
        return $this->getByField('buyer_id', $buyer_id, true);
    }

    /**
     * Удаление всех разблокированных страниц для конкретного покупателя.
     *
     * @param int $buyer_id ID покупателя
     */
    public function deleteUnlockedPagesByBuyer($buyer_id)
    {
        $this->deleteByField('buyer_id', $buyer_id);
    }

    /**
     * Добавление новых разблокированных страниц для конкретного покупателя.
     *
     * @param int $buyer_id ID покупателя
     * @param array $pages Список страниц для разблокировки
     */
    public function addUnlockedPagesForBuyer($buyer_id, $pages)
    {
        foreach ($pages as $page) {
            $this->insert([
                'buyer_id' => $buyer_id,
                'page_id' => $page['page_id'],
                'page_type' => $page['page_type'],
                'application_id' => $page['application_id'],
            ]);
        }
    }
}
