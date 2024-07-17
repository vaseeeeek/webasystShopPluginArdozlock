<?php
class shopArdozlockPluginBackendClearallAction extends waViewAction
{
    public function execute()
    {
        $model = new waModel();

        try {
            // Очистка таблицы ссылок
            $sql = "TRUNCATE TABLE ardozlock_links";
            $model->exec($sql);

            // Очистка таблицы категорий ссылок
            $sql = "TRUNCATE TABLE ardozlock_link_categories";
            $model->exec($sql);

            // Отправка JSON-ответа о успешной операции
            echo json_encode(['success' => true, 'message' => 'All data has been successfully cleared.']);
        } catch (Exception $e) {
            // В случае ошибки, отправка JSON-ответа с информацией об ошибке
            echo json_encode(['success' => false, 'message' => 'Error clearing data: ' . $e->getMessage()]);
        }
    }
}

