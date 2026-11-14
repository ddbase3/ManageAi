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


namespace Base3\ManageAi\ContentControl;

use Base3\Base3Manager\ContentControl\AbstractContentControl;

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
