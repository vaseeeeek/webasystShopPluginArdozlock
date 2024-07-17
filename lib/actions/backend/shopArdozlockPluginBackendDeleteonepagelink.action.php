<?php
class shopArdozlockPluginBackendDeleteonepageAction extends waViewAction
{
    public function execute()
    {
        // Получение ID ссылки и ID инфостраницы из POST запроса
        $linkId = waRequest::post('link_id');
        $pageId = waRequest::post('page_id');

        if (!$linkId || !$pageId) {
            echo json_encode(['success' => false, 'message' => 'No link ID or page ID provided.']);
            return;
        }

        $transactionStarted = false;
        $model = new waModel();
        
        try {
            // Начинаем транзакцию, если поддерживается базой данных
            if ($model->query("START TRANSACTION")) {
                $transactionStarted = true;
            }

            // Удаление записи из таблицы инфостраниц
            $sql = "DELETE FROM ardozlock_link_shoppage WHERE link_id = i:link_id AND page_id = i:page_id";
            $model->exec($sql, ['link_id' => $linkId, 'page_id' => $pageId]);

            // Проверка, если больше нет записей в ardozlock_link_shoppage для данного link_id
            $sql = "SELECT COUNT(*) FROM ardozlock_link_shoppage WHERE link_id = i:link_id";
            $count = $model->query($sql, ['link_id' => $linkId])->fetchField();
            if ($count == 0) {
                // Удаляем ссылку, если больше нет связанных инфостраниц
                $sql = "DELETE FROM ardozlock_links WHERE id = i:id";
                $model->exec($sql, ['id' => $linkId]);
            }

            // Если транзакция начата, подтверждаем изменения
            if ($transactionStarted) {
                $model->query("COMMIT");
            }

            // Возвращаем успешный JSON ответ
            echo json_encode(['success' => true, 'message' => 'The link for page ID ' . $pageId . ' has been successfully deleted.']);
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
