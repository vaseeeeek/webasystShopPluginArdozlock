<?php

class shopArdozlockBuyersModel extends waModel
{
    protected $table = 'shop_ardozlock_buyers';

    /**
     * Сохранение нового покупателя.
     *
     * @param array $buyerData Данные покупателя
     * @return int Идентификатор нового покупателя
     * @throws waException В случае ошибки валидации или сохранения
     */
    public function saveBuyer(array $buyerData)
    {
        // Проверка на наличие обязательных полей
        if (empty($buyerData['name']) || empty($buyerData['email'])) {
            throw new waException('Name and Email are required.');
        }

        // Проверка на уникальность email
        $existingBuyer = $this->getByField('email', $buyerData['email']);
        if ($existingBuyer) {
            throw new waException('A buyer with this email already exists.');
        }

        // Сохранение покупателя и возврат его ID
        return $this->insert($buyerData);
    }
    
    /**
     * Получение всех покупателей.
     *
     * @return array Список всех покупателей
     */
    public function getAllBuyers()
    {
        return $this->getAll();
    }
    
    public function getBuyerByHash($hash)
    {
        return $this->getByField('hash', $hash);
    }
}
