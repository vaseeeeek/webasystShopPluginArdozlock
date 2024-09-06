<?php
class shopArdozlockBlockedPagesModel extends waModel
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
}