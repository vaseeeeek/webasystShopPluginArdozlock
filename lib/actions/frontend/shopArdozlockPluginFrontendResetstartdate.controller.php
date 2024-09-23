<?php
class shopArdozlockPluginFrontendResetstartdateController extends waJsonController
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

            // Инициализация модели покупателей
            $buyersModel = new shopArdozlockBuyersModel();

            // Сбрасываем дату начала доступа
            $result = $buyersModel->setAccessStartDate($buyer_id, null); // Устанавливаем дату в null

            // Проверка успешности операции сброса даты
            if ($result) {
                $this->response = ['status' => 'ok', 'message' => 'Дата начала доступа успешно сброшена.'];
            } else {
                throw new waException('Ошибка при сбросе даты начала доступа.');
            }
        } catch (waException $e) {
            // Логируем ошибку
            waLog::log("Ошибка при сбросе даты начала доступа для покупателя с ID {$buyer_id}: " . $e->getMessage(), 'ardozlock.log');

            // Отправляем ошибку в ответе
            $this->setError($e->getMessage());
        }
    }
}
