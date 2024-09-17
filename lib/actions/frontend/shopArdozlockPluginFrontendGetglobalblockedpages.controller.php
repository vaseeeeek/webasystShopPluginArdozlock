<?php

class shopArdozlockPluginFrontendGetglobalblockedpagesController extends waJsonController
{
    /**
     * Получение глобально заблокированных страниц.
     */
    public function execute()
    {
        try {
            // Создаем объект модели для работы с таблицей глобальных заблокированных страниц
            $blockedPagesModel = new shopArdozlockGlobalblockedpagesModel();
            
            // Получаем список всех заблокированных страниц
            $blockedPages = $blockedPagesModel->getAllBlockedPages();

            // Формируем ответ с данными
            $this->response = [
                'blockedPages' => $blockedPages
            ];
        } catch (Exception $e) {
            // В случае ошибки возвращаем сообщение с ошибкой
            $this->setError($e->getMessage());
        }
    }
}
