<?php

class shopArdozlockPluginFrontendUpdatebuyerprofileController extends waJsonController
{
    public function execute()
    {
        try {
            // Получаем ID покупателя из параметра маршрута
            $buyerId = waRequest::param('buyer_id', null, 'int');

            // Получение данных из запроса
            $data = json_decode(file_get_contents('php://input'), true);
            $name = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');

            // Проверка валидности данных
            if (!$buyerId || !$name || !$email) {
                throw new waException('Необходимые данные не указаны.');
            }

            // Инициализация модели
            $buyersModel = new shopArdozlockBuyersModel();

            // Проверка уникальности email
            $existingBuyer = $buyersModel->getByField('email', $email);
            if ($existingBuyer && $existingBuyer['id'] != $buyerId) {
                throw new waException('Этот email уже используется.');
            }

            // Обновление данных
            $buyersModel->updateName($buyerId, $name);
            $buyersModel->updateEmail($buyerId, $email);

            // Ответ успешного обновления
            $this->response = [
                'status' => 'ok',
                'message' => 'Профиль успешно обновлен',
                'data' => ['name' => $name, 'email' => $email]
            ];
        } catch (waException $e) {
            $this->setError($e->getMessage());
        }
    }
}
