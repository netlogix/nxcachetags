<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * There's a checkbox when editing page properties, right besides
 * the default "cache/nocache" checkbox.
 * When it's ticked, this means the current FrontendUsers uid should
 * be part of the pages cache identifier, which makes this particular
 * page cached on a per user basis. This even kicks in if no user is
 * logged in, which results in having a dedicated "no logged in user"
 * cache entry.
 */
class UserToHashBaseService implements SingletonInterface
{

    /**
     * Add the FrontendUser::$uid to the cache identifier.
     *
     * @param array $params
     * @param TypoScriptFrontendController $typoScriptFrontendController
     */
    public function createHashBase(
        array &$params,
        TypoScriptFrontendController $typoScriptFrontendController
    ) {

        if (!$typoScriptFrontendController->page['nxcachetags_cacheperuser']) {
            return;
        }

        $params['hashParameters']['Nxcachetags\\UserToHashBaseService'] = @intval($typoScriptFrontendController->fe_user->user['uid']);

    }

}
