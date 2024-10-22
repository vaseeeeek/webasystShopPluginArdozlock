<?php

class shopArdozlockBuyersModel extends waModel
{
    protected $table = 'shop_ardozlock_buyers';


    /**
     * Обновление имени покупателя
     */
    public function updateName($buyerId, $name)
    {
        $this->updateById($buyerId, ['name' => $name]);
    }


    /**
     * Обновление email покупателя
     */
    public function updateEmail($buyerId, $email)
    {
        if (!$this->isEmailUnique($email, $buyerId)) {
            throw new waException('Этот email уже используется.');
        }
        $this->updateById($buyerId, ['email' => $email]);
    }

    /**
     * Проверка уникальности email
     */
    private function isEmailUnique($email, $buyerId)
    {
        $existing = $this->getByField('email', $email);
        return empty($existing) || $existing['id'] == $buyerId;
    }

    /**
     * Получить дату окончания доступа.
     *
     * @param int $buyer_id ID покупателя
     * @return string|null Дата окончания доступа или null, если срок не установлен
     */
    public function getAccessEndDate($buyer_id)
    {
        $buyer = $this->getById($buyer_id);

        if ($buyer && $buyer['access_start_date'] && $buyer['access_duration_days']) {
            $end_date = date('Y-m-d', strtotime("{$buyer['access_start_date']} + {$buyer['access_duration_days']} days"));
            return $end_date;
        }

        return null;
    }

    /**
     * Устанавливаем срок доступа для покупателя.
     *
     * @param int $buyer_id ID покупателя
     * @param int $access_duration_days Срок доступа в днях
     * @return bool Успешность операции
     */
    public function setAccessDuration($buyer_id, $access_duration_days)
    {
        return $this->updateById($buyer_id, [
            'access_duration_days' => $access_duration_days,
        ]);
    }

    /**
     * Устанавливаем дату начала доступа.
     *
     * @param int $buyer_id ID покупателя
     * @param string $start_date Дата начала (в формате YYYY-MM-DD)
     * @return bool Успешность операции
     */
    public function setAccessStartDate($buyer_id, $start_date = null)
    {
        return $this->updateById($buyer_id, [
            'access_start_date' => $start_date,
        ]);
    }


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
