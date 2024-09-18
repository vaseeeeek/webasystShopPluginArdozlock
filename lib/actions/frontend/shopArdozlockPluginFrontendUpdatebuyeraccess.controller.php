<?php

class shopArdozlockPluginFrontendUpdatebuyeraccessController extends waJsonController
{
    public function execute()
    {
        try {
            // Получаем сырые данные из входного потока
            $inputData = json_decode(file_get_contents('php://input'), true);
        
            // Логируем полученные данные для отладки
            waLog::log("Полученные данные: " . json_encode($inputData), 'ardozlock.log');
        
            // Получаем buyer_id и срок доступа из входных данных
            $buyer_id = waRequest::param('buyer_id', null, 'int');
            $access_duration_days = isset($inputData['access_duration_days']) ? (int) $inputData['access_duration_days'] : null;
        
            // Проверяем валидность входных данных
            if (!$buyer_id || !$access_duration_days || $access_duration_days <= 0) {
                waLog::log("Неверные параметры: buyer_id={$buyer_id}, access_duration_days={$access_duration_days}", 'ardozlock.log');
                throw new waException('Invalid buyer_id or access_duration_days.');
            }
        
            waLog::log("Параметры корректны, начинаем обновление срока доступа.", 'ardozlock.log');
        
            // Инициализация сервиса покупателей
            $buyerService = new shopArdozlockBuyerService();
        
            // Логируем перед обновлением
            waLog::log("Обновляем срок доступа для покупателя с ID {$buyer_id} на {$access_duration_days} дней.", 'ardozlock.log');
        
            // Обновляем срок доступа покупателя
            $buyerService->setBuyerAccessDuration($buyer_id, $access_duration_days);
        
            waLog::log("Срок доступа для покупателя с ID {$buyer_id} успешно обновлен.", 'ardozlock.log');
        
            // Ответ в случае успешного обновления
            $this->response = ['status' => 'ok', 'message' => 'Access duration updated successfully.'];
        } catch (waException $e) {
            // Логируем ошибку
            waLog::log("Ошибка при обновлении срока доступа для покупателя с ID {$buyer_id}: " . $e->getMessage(), 'ardozlock.log');
            
            // Отправляем ошибку в ответе
            $this->setError($e->getMessage());
        } catch (Exception $e) {
            // Логирование любой другой ошибки
            waLog::log("Неизвестная ошибка при обновлении срока доступа: " . $e->getMessage(), 'ardozlock.log');
            $this->setError('Unexpected error occurred.');
        }
        

    }
}
