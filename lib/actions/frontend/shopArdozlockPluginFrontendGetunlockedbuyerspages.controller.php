<?php

class shopArdozlockPluginFrontendGetunlockedbuyerspagesController extends waJsonController
{
    public function execute()
    {
        $buyer_id = waRequest::param('buyer_id');

        // Логика для получения разблокированных страниц покупателя
        $unlockedPagesModel = new shopArdozlockUnlockedbuyerpagesModel();
        $unlockedPages = $unlockedPagesModel->getUnlockedPagesByBuyer($buyer_id);

        $this->response = [
            'status' => 'ok',
            'unlockedPages' => $unlockedPages
        ];
    }
}
