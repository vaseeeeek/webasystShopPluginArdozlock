<?php

class shopArdozlockPluginFrontendSaveemailtemplateController extends waJsonController
{
    public function execute()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $content = $data['content'];
        if (empty($content)) {
            $this->setError('Содержимое письма не может быть пустым.');
            return;
        }

        $pluginPath = wa()->getAppPath('plugins/ardozlock/templates/email_template.html', 'shop');

        try {
            file_put_contents($pluginPath, $content);
            $this->response = 'Содержимое успешно сохранено!';
        } catch (Exception $e) {
            $this->setError('Ошибка при сохранении файла: ' . $e->getMessage());
        }
    }
}
