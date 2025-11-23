<?php declare(strict_types=1);

namespace ManageAi\ContentControl;

use Base3Manager\ContentControl\AbstractContentControl;

class VectorStoreContentControl extends AbstractContentControl {

        // Implementation of IBase

        public static function getName(): string {
                return "vectorstorecontentcontrol";
        }

	// Implementation of AbstractContentControl

        protected function getPath(): string {
                return DIR_PLUGIN . 'ManageAi';
        }

        protected function getTemplate(): string {
                return 'ContentControl/VectorStoreContentControl.php';
        }
}
