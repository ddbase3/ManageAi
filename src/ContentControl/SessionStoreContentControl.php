<?php declare(strict_types=1);

namespace ManageAi\ContentControl;

use Base3Manager\ContentControl\AbstractContentControl;

class SessionStoreContentControl extends AbstractContentControl {

        // Implementation of IBase

        public static function getName(): string {
                return "sessionstorecontentcontrol";
        }

	// Implementation of AbstractContentControl

        protected function getPath(): string {
                return DIR_PLUGIN . 'ManageAi';
        }

        protected function getTemplate(): string {
                return 'ContentControl/SessionStoreContentControl.php';
        }
}
