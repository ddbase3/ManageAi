<?php declare(strict_types=1);

namespace ManageAi\Test\Output;

use Base3\Api\IRequest;
use Base3\Authorization\Api\IAuthorizationService;
use Base3\Authorization\Dto\AuthorizationDecision;
use Base3\Usermanager\Api\IUsermanager;
use Base3\Usermanager\User;
use ClientStack\FileManager\FileManagerHttpService;
use ClientStack\FileManager\FileUploadService;
use ResourceFoundation\Api\IManagedFileStorageService;
use Base3\Translation\Api\ITranslation;
use ManageAi\Output\ChatbotResourcesOutput;
use PHPUnit\Framework\TestCase;

final class ChatbotResourcesOutputTest extends TestCase {

	public function testAnonymousFileRequestIsRejectedBeforeStorageAccess(): void {
		$users = $this->createStub(IUsermanager::class);
		$users->method('getUser')->willReturn(null);
		$storage = $this->createMock(IManagedFileStorageService::class);
		$storage->expects(self::never())->method('openOrCreate');
		$files = new FileManagerHttpService(
			$storage, new FileUploadService($storage, sys_get_temp_dir() . '/base3-no-upload-expected'),
			$this->createStub(IRequest::class), $this->createStub(ITranslation::class)
		);
		$endpoint = new ChatbotResourcesOutput(
			$this->createStub(IAuthorizationService::class), $users,
			$this->createStub(IRequest::class), $files
		);
		self::assertSame('', $endpoint->getOutput('json'));
		self::assertSame(403, http_response_code());
	}

	public function testDeniedAdminPermissionCannotWriteResources(): void {
		$users = $this->createStub(IUsermanager::class);
		$users->method('getUser')->willReturn(new User());
		$auth = $this->createStub(IAuthorizationService::class);
		$auth->method('getDefaultAuthority')->willReturn('xrm');
		$auth->method('decide')->willReturn(AuthorizationDecision::deny());
		$storage = $this->createMock(IManagedFileStorageService::class);
		$storage->expects(self::never())->method('openOrCreate');
		$files = new FileManagerHttpService(
			$storage, new FileUploadService($storage, sys_get_temp_dir() . '/base3-no-upload-expected'),
			$this->createStub(IRequest::class), $this->createStub(ITranslation::class)
		);
		$endpoint = new ChatbotResourcesOutput($auth, $users, $this->createStub(IRequest::class), $files);
		self::assertSame('', $endpoint->getOutput('json'));
		self::assertSame(403, http_response_code());
	}
}
