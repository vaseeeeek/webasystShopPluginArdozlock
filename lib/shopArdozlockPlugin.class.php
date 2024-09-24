<?php

class shopArdozlockPlugin extends shopPlugin
{
	const RIGHT_BACKEND = 'ardozlock.backend';

	public function backendMenu()
	{
		if (wa()->getUser()->getRights('shop', self::RIGHT_BACKEND) == 0) {
			return array();
		}

		return array(
			'core_li' => '<li class="no-tab"><a href="?plugin=ardozlock&action=closedcatlist">' . _wp('Закрытые категории') . '</a></li>',
		);
	}

	public function rightsConfigHandler(waRightConfig $config)
	{
		$config->addItem('ardozlock_header', 'Закрытые категории', 'header', array('cssclass' => 'c-access-subcontrol-header', 'tag' => 'div'));
		$config->addItem(self::RIGHT_BACKEND, 'Доступ к списку', 'checkbox', array('cssclass' => 'c-access-subcontrol-item'));
	}
	
    /**
     * Проверяет доступность страницы для текущего пользователя и устанавливает куку с хешем.
     *
     * @param string $application_id Идентификатор приложения (например, 'shop', 'site')
     * @param string $page_type Тип страницы (например, 'infopage', 'category')
     * @param int $page_id Идентификатор страницы
     * @return bool Возвращает true, если страница доступна, и false, если она заблокирована
     */
    public static function checkPageStatus($application_id, $page_type, $page_id)
    {
        try {
            if (!$application_id || !$page_type || !$page_id) {
                throw new waException('Не указаны все обязательные параметры.');
            }

            $globalBlockedPagesModel = new shopArdozlockGlobalblockedpagesModel();
            
            $globalBlockedPage = $globalBlockedPagesModel->getByField([
                'page_id' => $page_id,
                'page_type' => $page_type,
                'application_id' => $application_id,
            ]);
            
            if (!$globalBlockedPage) {
                return true;
            }
            
            $hash = waRequest::get('hash', null, 'string');
            
            if (!$hash) {
                $hash = waRequest::cookie('buyer_hash', null, 'string');
            }
            
            if ($hash) {
                $buyersModel = new shopArdozlockBuyersModel();
                $buyer = $buyersModel->getByField('hash', $hash);
                
                if ($buyer) {
                    wa()->getResponse()->setCookie('buyer_hash', $hash, time() + 1 * 86400);
                    
                    $buyerService = new shopArdozlockBuyerService();
                    $buyerService->setAccessStartDateIfNotSet($buyer['id']);
                    
                    if (!$buyerService->isAccessAllowed($buyer['id'])) {
                        return false;
                    }
                    
                    $unlockedPagesModel = new shopArdozlockUnlockedbuyerpagesModel();
                    $unlockedPage = $unlockedPagesModel->getByField([
                        'buyer_id' => $buyer['id'],
                        'page_id' => $page_id,
                        'page_type' => $page_type,
                        'application_id' => $application_id,
                    ]);
                    
                    if ($unlockedPage) {
                        return true;
                    }
                }
            }
            return false;
        } catch (waException $e) {
            // Логируем ошибку
            waLog::log("Ошибка при проверке доступа к странице: " . $e->getMessage(), 'ardozlock.log');
            return false; // В случае ошибки доступ запрещаем
        }
    }
}
