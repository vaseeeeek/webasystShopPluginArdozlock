<?php

class shopArdozlockPluginFrontendGetbuyersController extends waJsonController
{
    public function execute()
    {
        try {
            $buyersModel = new shopArdozlockBuyersModel();
            $buyers = $buyersModel->getAllBuyers();
            $this->response = ['buyers' => $buyers];
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }
}
