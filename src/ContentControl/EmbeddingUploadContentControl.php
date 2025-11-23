<?php declare(strict_types=1);

namespace ManageAi\ContentControl;

use Base3Manager\ContentControl\AbstractContentControl;

class EmbeddingUploadContentControl extends AbstractContentControl {

        // Implementation of IBase

        public static function getName(): string {
                return "embeddinguploadcontentcontrol";
        }

	// Implementation of AbstractContentControl

        protected function getPath(): string {
                return DIR_PLUGIN . 'ManageAi';
        }

        protected function getTemplate(): string {
                return 'ContentControl/EmbeddingUploadContentControl.php';
        }
}
