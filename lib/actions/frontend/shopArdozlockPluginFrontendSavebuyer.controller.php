<?php

class shopArdozlockPluginFrontendSavebuyerController extends waJsonController
{
    /**
     * Выполнение экшена сохранения нового покупателя.
     */
    public function execute()
    {
        try {
            // Получаем данные из тела запроса
            $data = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new waException('Invalid JSON data received.');
            }

            // Проверка наличия необходимых полей
            if (!isset($data['name']) || !isset($data['email'])) {
                throw new waException('Name and Email are required.');
            }

            // Валидация email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new waException('Invalid email format.');
            }

            // Генерация уникального хеша для покупателя
            $hash = $this->generateUniqueHash($data['email']);

            // Добавляем хеш в данные для сохранения
            $data['hash'] = $hash;

            // Сохранение покупателя через модель
            $buyersModel = new shopArdozlockBuyersModel();
            $buyerId = $buyersModel->saveBuyer($data);

            // Возвращаем успешный ответ с ID нового покупателя
            $this->response = ['buyer_id' => $buyerId, 'hash' => $hash];
        } catch (Exception $e) {
            // Обработка ошибок
            $this->setError($e->getMessage());
        }
    }

    /**
     * Генерация уникального хеша для покупателя на основе email.
     *
     * @param string $email Email покупателя
     * @return string Сгенерированный уникальный хеш
     */
    protected function generateUniqueHash($email)
    {
        return md5(uniqid($email, true)); // Генерация хеша на основе email и случайного уникального ID
    }
}
