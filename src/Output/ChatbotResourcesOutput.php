<?php declare(strict_types=1);

/***********************************************************************
 * This file is part of ManageAi for BASE3 Framework.
 *
 * ManageAi configures AI services and their resources.
 * It supplies manager controls and service connectivity tests.
 * File manager integration uses existing BASE3 services.
 *
 * Developed by Daniel Dahme
 * Licensed under GPL-3.0
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * https://base3.de
 ***********************************************************************/


namespace Base3\ManageAi\Output;

use Base3\CoreFoundation\Api\IOutput;
use Base3\CoreFoundation\Api\IRequest;
use Base3\CoreFoundation\Authorization\Api\IAuthorizationService;
use Base3\CoreFoundation\Usermanager\Api\IUsermanager;
use Base3\Chatbot\SettingsTab\ChatbotconfigSettingsTab;
use Base3\ClientStack\FileManager\FileManagerHttpService;
use Base3\ResourceFoundation\Api\IManagedFileStorageService;
use Base3\UiFoundation\Settings\SettingsAdministrationAccess;

/** Authorizes each FileManager operation for the canonical Manager chatbot owner. */
final class ChatbotResourcesOutput implements IOutput {

	public function __construct(
		private readonly IAuthorizationService $authorization,
		private readonly IUsermanager $users,
		private readonly IRequest $request,
		private readonly FileManagerHttpService $files
	) {}

	public static function getName(): string {
		return 'manageaichatbotfiles';
	}

	public function getOutput(string $out = 'html', bool $final = false): string {
		$user = $this->users->getUser();
		if ($user === null || !SettingsAdministrationAccess::isAllowed(
			$this->authorization, ChatbotconfigSettingsTab::getName()
		)) {
			http_response_code(403);
			return '';
		}
		if ($out !== 'json') {
			http_response_code(404);
			return '';
		}
		$action = $this->request->get('fm_action', '');
		if (!is_string($action) || $action === '') {
			http_response_code(400);
			return '';
		}
		if ($action !== 'download' && $this->request->server('HTTP_X_REQUESTED_WITH', '') !== 'XMLHttpRequest') {
			http_response_code(403);
			return '';
		}
		return $this->files->handle(
			$action,
			'chatbot',
			'default',
			IManagedFileStorageService::MODE_COLLECTION,
			(string)$user->id
		);
	}
}
