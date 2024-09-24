<?php

class shopArdozlockPluginFrontendUpdatebuyeraccessController extends waJsonController
{
    public function execute()
    {
        try {
            $inputData = json_decode(file_get_contents('php://input'), true);
            
            $buyer_id = waRequest::param('buyer_id', null, 'int');
            $access_duration_days = isset($inputData['access_duration_days']) ? (int) $inputData['access_duration_days'] : null;
            
            if (!$buyer_id || !$access_duration_days || $access_duration_days <= 0) {
                waLog::log("Неверные параметры: buyer_id={$buyer_id}, access_duration_days={$access_duration_days}", 'ardozlock.log');
                throw new waException('Invalid buyer_id or access_duration_days.');
            }
            
            $buyerService = new shopArdozlockBuyerService();

            $buyerService->setBuyerAccessDuration($buyer_id, $access_duration_days);

            $this->response = ['status' => 'ok', 'message' => 'Access duration updated successfully.'];
        } catch (waException $e) {
            $this->setError($e->getMessage());
        } catch (Exception $e) {
            $this->setError('Unexpected error occurred.');
        }
        

    }
}
