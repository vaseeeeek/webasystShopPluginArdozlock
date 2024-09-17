<?php

class shopArdozlockPluginFrontendSaveBlockedPagesController extends waJsonController
{
    public function execute()
    {
        try {
            // Получаем данные из тела запроса
            $data = json_decode(file_get_contents('php://input'), true);
            $buyer_id = isset($data['buyer_id']) ? (int)$data['buyer_id'] : 0;
            $blockedPages = isset($data['blockedPages']) ? $data['blockedPages'] : [];

            if (!$buyer_id || empty($blockedPages)) {
                throw new waException('Invalid data');
            }

            $blockedPagesModel = new shopArdozlockUnlockedbuyerpagesModel();

            // Удаляем старые записи
            $blockedPagesModel->getUnlockedPagesByBuyer($buyer_id);

            // Добавляем новые записи
            $blockedPagesModel->addBlockedPagesForBuyer($buyer_id, $blockedPages);

            // Возвращаем успешный ответ
            $this->response = ['status' => 'ok'];
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }
}
