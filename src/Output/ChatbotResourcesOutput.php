<?php declare(strict_types=1);

namespace ManageAi\Output;

use Base3\Api\IOutput;
use Base3\Api\IRequest;
use Base3\Authorization\Api\IAuthorizationService;
use Base3\Usermanager\Api\IUsermanager;
use Chatbot\SettingsTab\ChatbotconfigSettingsTab;
use ClientStack\FileManager\FileManagerHttpService;
use ResourceFoundation\Api\IManagedFileStorageService;
use UiFoundation\Settings\SettingsAdministrationAccess;

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
