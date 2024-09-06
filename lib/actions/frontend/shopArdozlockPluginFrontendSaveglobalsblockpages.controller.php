<?php

class shopArdozlockPluginFrontendSaveglobalsblockpagesController extends waJsonController
{
    private $globalBlockPageService;

    public function __construct()
    {
        $this->globalBlockPageService = new shopArdozlockGlobalblockpageservice();
    }

    public function execute()
    {
        // Получаем данные запроса
        $data = $this->getRequestData();

        if ($data === null) {
            $this->response = ['success' => false, 'error' => 'Invalid JSON data received.'];
            return;
        }

        // Обрабатываем данные через сервис
        $result = $this->globalBlockPageService->processBlockedPages($data);

        // Возвращаем результат
        $this->response = $result;
    }

    /**
     * Получение данных из запроса
     *
     * @return array|null
     */
    private function getRequestData()
    {
        if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
            return json_decode(file_get_contents('php://input'), true)['blockedPages'];
        }

        return waRequest::post('blockedPages', [], waRequest::TYPE_ARRAY);
    }
}
