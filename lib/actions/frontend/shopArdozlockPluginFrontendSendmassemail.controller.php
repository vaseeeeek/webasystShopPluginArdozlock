<?php

class shopArdozlockPluginFrontendSendmassemailController extends waJsonController
{
    public function execute()
    {
        $buyer_model = new shopArdozlockBuyersModel();
        $buyers = $buyer_model->getAll();

        $pages_model = new shopArdozlockUnlockedbuyerpagesModel();
        foreach ($buyers as $buyer) {
            $pages = $pages_model->getUnlockedPagesByBuyer($buyer['id']);
            $page_links = [];
            $buyer_hash = $buyer['hash'];
            foreach ($pages as $page) {
                $url = $this->generatePageUrl($page,$buyer_hash);
                if ($url) {
                    $page_links[] = $url;
                }
            }
            $this->sendEmail($buyer['id'], $page_links);
        }

        $this->response = ['status' => 'ok'];
    }

    protected function generatePageUrl($page, $buyer_hash)
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
                
                $url = $domain . "/category/" . $params['category_url'] . "?hash=" . $buyer_hash;
                return $url;
            }
        }


        if ($page['page_type'] === 'infopage' && $page['application_id'] === 'shop') {
            $shopPage = $shopHelper->page($page['page_id']);
            $url = $domain . "/" .  $shopPage['full_url'] . "?hash=" . $buyer_hash;
            return $url;
        }


        if ($page['page_type'] === 'infopage' && $page['application_id'] === 'site') {
            $sitePageModel = new waModel();
            
            // SQL-запрос для получения данных страницы
            $sql = "SELECT `full_url` FROM `site_page` WHERE `id` = :page_id";
            $sitePageData = $sitePageModel->query($sql, ['page_id' => $page['page_id']])->fetch();
            
            if ($sitePageData && !empty($sitePageData['full_url'])) {
                $url = $domain . "/" . $sitePageData['full_url'] . "?hash=" . $buyer_hash;
                return $url;
            }
        }

        return null;
    }

    protected function sendEmail($buyer_id, $page_links)
    {
        $buyer_model = new shopArdozlockBuyersModel();
        $buyer = $buyer_model->getById($buyer_id);

        if (!$buyer || empty($page_links)) {
            waLog::log("Ошибка: данные для покупателя ID $buyer_id отсутствуют.", 'ardozlock_sendmassemail.log');
            return;
        }

        // Загружаем путь к шаблону
        $pluginPath = wa()->getAppPath('plugins/ardozlock/templates/email_template.html', 'shop');

        // Инициализируем Smarty
        $smarty = new waSmarty3View(wa());
        $smarty->assign('buyer', $buyer);
        $smarty->assign('page_links', $page_links);

        // Генерируем тело письма из шаблона
        $body = $smarty->fetch($pluginPath);

        // Отправляем письмо
        $subject = 'Доступные для вас страницы';
        $mail_message = new waMailMessage($subject, $body, 'text/html');
        $mail_message->setTo($buyer['email'], $buyer['name']);
        $mail_message->send();
    }
}
