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
			waLog::log("Начало проверки доступа для страницы: application_id={$application_id}, page_type={$page_type}, page_id={$page_id}", 'ardozlock.log');

			// Валидация входных данных
			if (!$application_id || !$page_type || !$page_id) {
				throw new waException('Не указаны все обязательные параметры.');
			}

			// Инициализация модели глобально заблокированных страниц
			$globalBlockedPagesModel = new shopArdozlockGlobalblockedpagesModel();

			// Проверяем, заблокирована ли страница глобально
			$globalBlockedPage = $globalBlockedPagesModel->getByField([
				'page_id' => $page_id,
				'page_type' => $page_type,
				'application_id' => $application_id,
			]);

			// Если страница не заблокирована глобально, доступ разрешен
			if (!$globalBlockedPage) {
				waLog::log("Страница не заблокирована глобально, доступ разрешен.", 'ardozlock.log');
				return true; // Страница доступна
			}

			// Если страница заблокирована, проверяем наличие хеша в URL или куках
			$hash = waRequest::get('hash', null, 'string');

			// Если хеша в URL нет, ищем его в куках
			if (!$hash) {
				$hash = waRequest::cookie('buyer_hash', null, 'string');
			}

			// Если найден хеш, ищем покупателя по хешу
			if ($hash) {
				waLog::log("Найден хеш: {$hash}", 'ardozlock.log');

				$buyersModel = new shopArdozlockBuyersModel();
				$buyer = $buyersModel->getByField('hash', $hash);

				// Если покупатель найден, проверяем его доступ к странице
				if ($buyer) {
					waLog::log("Найден покупатель с ID: {$buyer['id']}, устанавливаем куку.", 'ardozlock.log');

					// Устанавливаем куку с хешем для покупателя (срок действия - 1 дней)
					wa()->getResponse()->setCookie('buyer_hash', $hash, time() + 1 * 86400); // Кука на 30 дней

					$unlockedPagesModel = new shopArdozlockUnlockedbuyerpagesModel();
					$unlockedPage = $unlockedPagesModel->getByField([
						'buyer_id' => $buyer['id'],
						'page_id' => $page_id,
						'page_type' => $page_type,
						'application_id' => $application_id,
					]);

					// Если страница разблокирована для покупателя, доступ разрешен
					if ($unlockedPage) {
						waLog::log("Страница разблокирована для покупателя, доступ разрешен.", 'ardozlock.log');
						return true;
					} else {
						waLog::log("Страница не разблокирована для покупателя, доступ запрещен.", 'ardozlock.log');
					}
				} else {
					waLog::log("Покупатель с указанным хешем не найден.", 'ardozlock.log');
				}
			} else {
				waLog::log("Хеш не найден в URL или куках.", 'ardozlock.log');
			}

			// Если хеш не найден или доступ не разрешен, доступ запрещен
			waLog::log("Доступ к странице запрещен.", 'ardozlock.log');
			return false;
		} catch (waException $e) {
			// Логируем ошибку
			waLog::log("Ошибка при проверке доступа к странице: " . $e->getMessage(), 'ardozlock.log');
			return false; // В случае ошибки доступ запрещаем
		}
	}
}
