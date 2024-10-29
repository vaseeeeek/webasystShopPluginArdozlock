<?php

class shopArdozlockPluginFrontendSendemailController extends waJsonController
{
    public function execute()
    {
        $buyer_id = waRequest::param('buyer_id', null, 'int');
        if (!$buyer_id) {

            $this->setError('Не указан ID покупателя');
            return;
        }

        $pages_model = new shopArdozlockUnlockedbuyerpagesModel();
        $pages = $pages_model->getUnlockedPagesByBuyer($buyer_id);

        $page_links = [];

        foreach ($pages as $page) {
            $url = $this->generatePageUrl($page);

            if ($url) {
                $page_links[] = $url;
            }
        }
        waLog::dump($page_links);
        $this->sendEmail($buyer_id, $page_links);
    }

    /**
     * Метод для генерации URL страницы на основе данных о странице
     */
    protected function generatePageUrl($page)
    {
        $routing = wa()->getRouting();
        $domain = wa()->getConfig()->getDomain();
        $route = $routing->getRoute();
        $shopHelper = new shopViewHelper(wa('shop'));
        
        if ($page['page_type'] === 'category' && $page['application_id'] === 'shop') {
            $category = $shopHelper->category($page['page_id']);
            
            if ($category) {
                $params = [
                    'category_url' => $category[$route['url_type'] == 1 ? 'url' : 'full_url']
                ];
                
                $url = $domain . "/category/" . $params['category_url'];
                return $url;
            }
        }


        if ($page['page_type'] === 'infopage' && $page['application_id'] === 'shop') {
            $shopPage = $shopHelper->page($page['page_id']);
            $url = $domain . "/" .  $shopPage['full_url'];
            return $url;
        }


        if ($page['page_type'] === 'infopage' && $page['application_id'] === 'site') {
            $sitePageModel = new waModel();
            
            // SQL-запрос для получения данных страницы
            $sql = "SELECT `full_url` FROM `site_page` WHERE `id` = :page_id";
            $sitePageData = $sitePageModel->query($sql, ['page_id' => $page['page_id']])->fetch();
            
            if ($sitePageData && !empty($sitePageData['full_url'])) {
                $url = $domain . "/" . $sitePageData['full_url'];
                return $url;
            }
        }

        return null;
    }


    /**
     * Метод для отправки письма покупателю
     */
    protected function sendEmail($buyer_id, $page_links)
    {
        $buyer_model = new shopArdozlockBuyersModel();
        $buyer = $buyer_model->getById($buyer_id);
        
        if (!$buyer || empty($page_links)) {
            $this->setError('Невозможно отправить письмо: данные покупателя или страницы отсутствуют.');
            return;
        }
        
        $subject = 'Доступные для вас страницы';
        
        // Формируем HTML-тело письма
        $body = '<html><body>';
        $body .= '<p>Уважаемый ' . htmlspecialchars($buyer['name']) . ',</p>';
        $body .= '<p>Вот список доступных страниц для вас:</p><ul>';
        
        foreach ($page_links as $link) {
            $body .= '<li><a href="' . htmlspecialchars($link) . '">' . htmlspecialchars($link) . '</a></li>';
        }
        
        $body .= '</ul>';
        $body .= '<p>С уважением,<br>Ваш магазин</p>';
        $body .= '</body></html>';
        
        // Создаем и отправляем сообщение
        $mail_message = new waMailMessage($subject, $body, 'text/html');
        $mail_message->setTo($buyer['email'], $buyer['name']);
        if ($mail_message->send()) {
            // waLog::log("Письмо успешно отправлено на email: {$buyer['email']}", 'ardozlock_sendemail.log');
        } else {
            waLog::log("Ошибка при отправке письма для покупателя ID: $buyer_id", 'ardozlock_sendemail.log');
        }
        
    }
}
