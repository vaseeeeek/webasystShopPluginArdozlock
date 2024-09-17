<?php
class shopArdozlockBlockedbuyerpagesModel extends waModel
{
    protected $table = 'shop_ardozlock_blocked_pages';

    /**
     * Получение всех заблокированных страниц для конкретного покупателя.
     *
     * @param int $buyer_id ID покупателя
     * @return array Список заблокированных страниц
     */
    public function getBlockedPagesByBuyer($buyer_id)
    {
        return $this->getByField('buyer_id', $buyer_id, true);
    }

    /**
     * Удаление всех заблокированных страниц для конкретного покупателя.
     *
     * @param int $buyer_id ID покупателя
     */
    public function deleteBlockedPagesByBuyer($buyer_id)
    {
        $this->deleteByField('buyer_id', $buyer_id);
    }

    /**
     * Добавление новых заблокированных страниц для конкретного покупателя.
     *
     * @param int $buyer_id ID покупателя
     * @param array $pages Список страниц для блокировки
     */
    public function addBlockedPagesForBuyer($buyer_id, $pages)
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
