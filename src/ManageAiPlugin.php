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


namespace Base3\ManageAi;

use Base3\CoreFoundation\Api\IContainer;
use Base3\CoreFoundation\Configuration\Api\IConfiguration;
use Base3\CoreFoundation\Database\Api\IDatabase;
use Base3\Core\Database\Mysql\MysqlDatabase;
use Base3\CoreFoundation\Settings\Api\ISettingsStore;
use Base3\Core\Settings\Database\DatabaseSettingsStore;
use Base3\CoreFoundation\LinkTarget\Api\ILinkTargetService;
use Base3\CoreFoundation\Event\Api\IEventManager;
use Base3\CoreFoundation\Api\IRequest;
use Base3\CoreFoundation\Translation\Api\ITranslation;
use Base3\ClientStack\FileManager\FileManagerHttpService;
use Base3\ClientStack\FileManager\FileUploadService;
use Base3\FileBridge\Local\LocalManagedFileStorageService;
use Base3\ResourceFoundation\Api\IManagedFileStorageService;
use Base3\ManageAi\Output\ChatbotResourcesOutput;
use Base3\Base3Manager\Plugin\AbstractPlugin;

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
