<?php declare(strict_types=1);

namespace ManageAi;

use Base3\Api\IContainer;
use Base3\Configuration\Api\IConfiguration;
use Base3\Database\Api\IDatabase;
use Base3\Database\Mysql\MysqlDatabase;
use Base3\Settings\Api\ISettingsStore;
use Base3\Settings\Database\DatabaseSettingsStore;
use Base3\Api\ILinkTargetService;
use Base3\Event\Api\IEventManager;
use Base3\Api\IRequest;
use Base3\Translation\Api\ITranslation;
use ClientStack\FileManager\FileManagerHttpService;
use ClientStack\FileManager\FileUploadService;
use FileBridge\Local\LocalManagedFileStorageService;
use ResourceFoundation\Api\IManagedFileStorageService;
use ManageAi\Output\ChatbotResourcesOutput;
use Base3Manager\Plugin\AbstractPlugin;

class ManageAiPlugin extends AbstractPlugin {

	// Implementation of IPlugin

	public function init() {
		$this->container
			->set(self::getName(), $this, IContainer::SHARED)

                        ->set(IDatabase::class, fn($c) => new MysqlDatabase($c->get(IConfiguration::class)), IContainer::SHARED)
                        ->set('database', IDatabase::class, IContainer::ALIAS)

			->set(ISettingsStore::class, fn($c) => new DatabaseSettingsStore($c->get(IDatabase::class)), IContainer::SHARED)

			->set(IManagedFileStorageService::class, fn($c) => new LocalManagedFileStorageService(
				$c->get(IConfiguration::class),
				$c->get(IEventManager::class),
				$this->getPrivateFileManagerRoot($c->get(IConfiguration::class))
			), IContainer::SHARED | IContainer::NOOVERWRITE)
			->set(FileUploadService::class, fn($c) => new FileUploadService(
				$c->get(IManagedFileStorageService::class),
				$this->getPrivateFileManagerRoot($c->get(IConfiguration::class)) . '/uploads'
			), IContainer::SHARED)
			->set(FileManagerHttpService::class, fn($c) => new FileManagerHttpService(
				$c->get(IManagedFileStorageService::class),
				$c->get(FileUploadService::class),
				$c->get(IRequest::class),
				$c->get(ITranslation::class)
			), IContainer::SHARED)
			->set('chatbotConfigDisplayData', fn($c) => [
				'group' => 'chatbot',
				'name' => 'default',
				'resources' => [
					'enabled' => true,
					'endpoints' => FileManagerHttpService::buildEndpoints(
						$c->get(ILinkTargetService::class)->getLink([
							'name' => ChatbotResourcesOutput::getName(), 'out' => 'json'
						])
					)
				]
			], IContainer::SHARED);
	}

	/** The existing FileBridge backend must use an operator-configured private directory. */
	private function getPrivateFileManagerRoot(IConfiguration $configuration): string {
		$directory = rtrim(trim($configuration->getString('directories', 'data')), '/');
		if ($directory === '' || !str_starts_with($directory, DIRECTORY_SEPARATOR)) {
			throw new \RuntimeException('Configure an absolute private [directories] data path for Manager uploads.');
		}
		return $directory . '/filemanager';
	}

	// Implementation of ICheck

	public function checkDependencies(): array {
		return array(
			"Check" => "Ok"
		);
	}
}
