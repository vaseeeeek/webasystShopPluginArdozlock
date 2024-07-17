<?php
class shopArdozlockPluginBackendCreateAction extends waViewAction
{
    public function execute()
    {
        $post = waRequest::post();
        waLog::dump($post);
        $category_ids = waRequest::post('category_id');
        $expires_at = waRequest::post('expires_at');
        $email = waRequest::post('email');
        $company_name = waRequest::post('company_name', null, waRequest::TYPE_STRING_TRIM);
        $shop_pages_ids = waRequest::post('infopage_id');
        if (!(is_array($category_ids) && !empty($category_ids)) && !(is_array($shop_pages_ids) && !empty($shop_pages_ids))) {
            echo json_encode(['success' => false, 'error' => 'No categories or shop page selected']);
            return;
        }
        $linksModel = new shopArdozlockPluginLinksModel();

        if (is_array($category_ids) && !empty($category_ids)) {
            $linkCategoriesModel = new shopArdozlockPluginLinkCategoriesModel();

            $unique_hash = $this->generateUniqueHash(implode(',', $category_ids), $expires_at);

            $linkData = [
                'unique_hash' => $unique_hash,
                'expires_at' => $expires_at,
                'email' => $email,
                'company_name' => $company_name ? $company_name : '',
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'cat'
            ];

            $linkId = $linksModel->insert($linkData);

            foreach ($category_ids as $category_id) {
                $linkCategoryData = [
                    'link_id' => $linkId,
                    'category_id' => $category_id,
                ];
                $linkCategoriesModel->insert($linkCategoryData);
            }
        } else {
            // echo json_encode(['success' => false, 'error' => 'No categories selected']);
            // return;
        }

        if (is_array($shop_pages_ids) && !empty($shop_pages_ids)) {
            $unique_hash = $this->generateUniqueHash(implode(',', $shop_pages_ids), $expires_at);
            $linkShoppageModel = new shopArdozlockPluginLinkShoppageModel();

            $linkData = [
                'unique_hash' => $unique_hash,
                'expires_at' => $expires_at,
                'email' => $email,
                'company_name' => $company_name ? $company_name : '',
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'shop-page'
            ];

            $linkId = $linksModel->insert($linkData);

            foreach ($shop_pages_ids as $shop_pages_id) {
                $linkShoppageData = [
                    'link_id' => $linkId,
                    'page_id' => $shop_pages_id,
                ];
                $linkShoppageModel->insert($linkShoppageData);
            }

        } else {
            // echo json_encode(['success' => false, 'error' => 'No page selected']);
            // return;
        }

        $links = $linksModel->getLinks();

        echo json_encode(['success' => true, 'newCompany' => ['company_name' => $company_name, 'categories' => $links]]);
    }

    protected function generateUniqueHash($idsPageAsString, $expires_at)
    {
        return md5($idsPageAsString . $expires_at . uniqid(rand(), true));
    }
}

