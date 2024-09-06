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

            // Сохранение покупателя через модель
            $buyersModel = new shopArdozlockBuyersModel();
            $buyerId = $buyersModel->saveBuyer($data);

            // Возвращаем успешный ответ с ID нового покупателя
            $this->response = ['buyer_id' => $buyerId];
        } catch (Exception $e) {
            // Обработка ошибок
            $this->setError($e->getMessage());
        }
    }
}
