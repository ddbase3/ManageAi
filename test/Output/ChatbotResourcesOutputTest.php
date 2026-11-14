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


namespace Base3\ManageAi\Test\Output;

use Base3\CoreFoundation\Api\IRequest;
use Base3\CoreFoundation\Authorization\Api\IAuthorizationService;
use Base3\CoreFoundation\Authorization\Dto\AuthorizationDecision;
use Base3\CoreFoundation\Usermanager\Api\IUsermanager;
use Base3\CoreFoundation\Usermanager\User;
use Base3\ClientStack\FileManager\FileManagerHttpService;
use Base3\ClientStack\FileManager\FileUploadService;
use Base3\ResourceFoundation\Api\IManagedFileStorageService;
use Base3\CoreFoundation\Translation\Api\ITranslation;
use Base3\ManageAi\Output\ChatbotResourcesOutput;
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
