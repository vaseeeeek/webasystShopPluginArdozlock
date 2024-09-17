<?php

class shopArdozlockPluginFrontendSaveunlockedbuyerspagesController extends waJsonController
{
    public function execute()
    {
        try {
            // Получаем данные из тела запроса
            $data = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new waException('Invalid JSON data received.');
            }

            // Проверяем, что передан buyer_id и список разблокированных страниц
            if (!isset($data['buyer_id']) || !isset($data['unlockedPages'])) {
                throw new waException('buyer_id and unlockedPages are required.');
            }

            $buyer_id = (int) $data['buyer_id'];
            $unlockedPages = $data['unlockedPages'];

            // Валидация списка разблокированных страниц
            if (!is_array($unlockedPages)) {
                throw new waException('unlockedPages should be an array.');
            }

            // Модель для работы с таблицей разблокированных страниц
            $unlockedPagesModel = new shopArdozlockUnlockedbuyerpagesModel();

            // Удаляем старые записи для этого покупателя
            $unlockedPagesModel->deleteByField('buyer_id', $buyer_id);

            // Сохраняем новые разблокированные страницы для покупателя
            foreach ($unlockedPages as $page) {
                $unlockedPagesModel->insert([
                    'buyer_id' => $buyer_id,
                    'page_id' => $page['page_id'],
                    'page_type' => $page['page_type'],
                    'application_id' => $page['application_id'],
                ]);
            }

            $this->response = ['status' => 'ok'];
        } catch (Exception $e) {
            // Обработка ошибок
            $this->setError($e->getMessage());
        }
    }
}
