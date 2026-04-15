<?php
/**
 * Main class for the Talkright MediaWiki extension
 * @author Marc Noirot - marc dot noirot at gmail
 * @author P.Levque - User:Phillev
 * @author James Montalvo - User:Jamesmontalvo3
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserIdentity;

class TalkRight {

	/** @var bool Guard against re-entrancy when resolving rights */
	private static $inUserGetRights = false;

	/**
	 * Grant "edit" on talk pages when the user may use the "talk" right.
	 * Replaces legacy hacks on User::$mRights / ParserBeforeStrip, which break on MediaWiki 1.36+
	 * (Parser::getUser removed; User::$mRights is not a mutable list in 1.43).
	 *
	 * @param UserIdentity $user
	 * @param string[] &$rights
	 */
	public static function onUserGetRights( UserIdentity $user, array &$rights ) {
		if ( self::$inUserGetRights ) {
			return;
		}

		$title = \RequestContext::getMain()->getTitle();
		if ( !$title || !$title->isTalkPage() ) {
			return;
		}

		$hasTalk = in_array( 'talk', $rights, true );
		if ( !$hasTalk ) {
			self::$inUserGetRights = true;
			try {
				$hasTalk = MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
					$user,
					'talk'
				);
			} finally {
				self::$inUserGetRights = false;
			}
		}

		if ( $hasTalk && !in_array( 'edit', $rights, true ) ) {
			$rights[] = 'edit';
		}
	}

	/**
	 * @deprecated Kept for backwards compatibility if something still references the old hook name.
	 */
	public static function alternateEdit( $editPage ) {
		return true;
	}

	/**
	 * @deprecated Kept for backwards compatibility if something still references the old hook name.
	 */
	public static function giveEditRightsWhenViewingTalkPages( &$parser, &$test1, &$test2 ) {
		return true;
	}
}
