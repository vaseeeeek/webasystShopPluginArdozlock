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
        
        if ($page['page_type'] === 'category' && $page['application_id'] === 'shop') {=
            $category_model = new shopCategoryModel();
            $category = $category_model->getById($page['page_id']);

            if ($category) {
                $params = [
                    'category_url' => $category[$route['url_type'] == 1 ? 'url' : 'full_url']
                ];
                $url = $routing->getRouteUrl('shop/frontend/category', $params, true, $domain);

                return $url;
            }
        }


        if ($page['page_type'] === 'infopage' && $page['application_id'] === 'shop') {
            $shopPage = wa()->shop->page();
            $params = [
                'page_id' => $page['page_id']
            ];
            
            $url = $routing->getRouteUrl('shop/frontend/page', $params, true, $domain);
            
            return $url;
        }


        if ($page['page_type'] === 'infopage' && $page['application_id'] === 'site') {
            $params = [
                'page_id' => $page['page_id']
            ];

            $url = $routing->getRouteUrl('site/frontend/page', $params, true, $domain);
            
            return $url;
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

        $subject = 'Доступные страницы для вас';
        $body = 'Уважаемый ' . $buyer['name'] . ",\n\nВот список доступных страниц для вас:\n";
        foreach ($page_links as $link) {
            $body .= $link . "\n";
        }

        $mail_message = new waMailMessage($subject, $body);
        $mail_message->setTo($buyer['email'], $buyer['name']);
    }
}
