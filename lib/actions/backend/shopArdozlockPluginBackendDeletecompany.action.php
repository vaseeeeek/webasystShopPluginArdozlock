<?php
class shopArdozlockPluginBackendDeletecompanyAction extends waViewAction
{
    public function execute()
    {
        // Получение ID компании (ссылки) из POST запроса
        $linkId = waRequest::post('id');

        if (!$linkId) {
            echo json_encode(['success' => false, 'message' => 'No link ID provided.']);
            return;
        }

        $transactionStarted = false;
        $model = new waModel();
        
        try {
            // Начинаем транзакцию, если поддерживается базой данных
            if ($model->query("START TRANSACTION")) {
                $transactionStarted = true;
            }

            // Удаление записей из таблицы категорий
            $sql = "DELETE FROM ardozlock_link_categories WHERE link_id = i:link_id";
            $model->exec($sql, ['link_id' => $linkId]);

            // Удаление записей из таблицы инфостраниц
            $sql = "DELETE FROM ardozlock_link_shoppage WHERE link_id = i:link_id";
            $model->exec($sql, ['link_id' => $linkId]);

            // Удаление самой ссылки
            $sql = "DELETE FROM ardozlock_links WHERE id = i:id";
            $model->exec($sql, ['id' => $linkId]);

            // Если транзакция начата, подтверждаем изменения
            if ($transactionStarted) {
                $model->query("COMMIT");
            }

            // Возвращаем успешный JSON ответ
            echo json_encode(['success' => true, 'message' => 'All data for link ID ' . $linkId . ' has been successfully deleted.']);
        } catch (Exception $e) {
            // Если ошибка, откатываем транзакцию
            if ($transactionStarted) {
                $model->query("ROLLBACK");
            }
            
            // Возвращаем JSON ответ с сообщением об ошибке
            echo json_encode(['success' => false, 'message' => 'Error deleting data: ' . $e->getMessage()]);
        }
    }
}
