<?php

class shopArdozlockPluginFrontendResetallactivationdatesController extends waJsonController
{
    public function execute()
    {
        try {
            $buyersModel = new shopArdozlockBuyersModel();
            $buyers = $buyersModel->getAllBuyers();
            foreach ($buyers as $buyer) {
                $buyersModel->setAccessStartDate($buyer['id'], null); // Сброс даты активации
            }
            
            $this->response = ['status' => 'ok', 'message' => 'Даты активации сброшены для всех пользователей.'];
        } catch (waException $e) {
            $this->setError($e->getMessage());
        }
    }
}
