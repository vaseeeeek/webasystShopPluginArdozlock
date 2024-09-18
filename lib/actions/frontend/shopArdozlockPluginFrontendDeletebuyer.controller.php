<?php

class shopArdozlockPluginFrontendDeletebuyerController extends waJsonController
{
    public function execute()
    {
        try {
            // Получаем buyer_id из параметров маршрута
            $buyer_id = waRequest::param('buyer_id', null, 'int');

            // Проверяем валидность входных данных
            if (!$buyer_id) {
                throw new waException('Invalid buyer_id.');
            }

            // Инициализируем модель покупателей
            $buyersModel = new shopArdozlockBuyersModel();

            // Удаляем покупателя
            $result = $buyersModel->deleteById($buyer_id);

            // Проверка успешности операции удаления
            if ($result) {
                $this->response = ['status' => 'ok', 'message' => 'Покупатель успешно удален.'];
            } else {
                throw new waException('Ошибка при удалении покупателя.');
            }
        } catch (waException $e) {
            // Логируем ошибку
            waLog::log("Ошибка при удалении покупателя с ID {$buyer_id}: " . $e->getMessage(), 'ardozlock.log');

            // Отправляем ошибку в ответе
            $this->setError($e->getMessage());
        }
    }
}
