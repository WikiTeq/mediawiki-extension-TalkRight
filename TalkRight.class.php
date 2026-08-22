<?php
/**
 * Main class for the Talkright MediaWiki extension
 * @author Marc Noirot - marc dot noirot at gmail
 * @author P.Levêque - User:Phillev
 * @author James Montalvo - User:Jamesmontalvo3
 */

class TalkRight {

	/**
	 * Bypass the missing-"edit"-right error when EDITING talk pages if the user has
	 * the 'talk' right.
	 *
	 * TitleQuickPermissions receives the actual Title being checked, so the bypass is
	 * correctly scoped to that page: it works for web, API and CLI requests alike, and
	 * cannot leak 'edit' to other titles (PermissionManager caches rights per user, so
	 * UserGetRights-based grants would apply sitewide once added).
	 *
	 * Returning false with an empty $errors skips only PermissionManager's built-in
	 * required-right check; page protections, cascading restrictions, user blocks and
	 * all other checks still run in their own stages.
	 *
	 * @param MediaWiki\Title\Title $title title being accessed
	 * @param MediaWiki\User\User $user user performing the action
	 * @param string $action action being performed
	 * @param array &$errors errors accumulated so far
	 * @param bool $doExpensiveQueries whether expensive DB queries may be run
	 * @param bool $short whether to stop at the first error
	 * @return bool false to override the quick permission check, true otherwise
	 */
	public static function onTitleQuickPermissions( $title, $user, $action, &$errors,
		$doExpensiveQueries, $short
	) {
		if ( $action !== 'edit' || !$title->isTalkPage() ) {
			return true;
		}
		if ( in_array( 'edit', (array)$errors, true ) ) {
			return true;
		}
		if ( !$user->isAllowed( 'talk' ) ) {
			return true;
		}
		// The only quick-permission failure for this title/action would be the missing
		// 'edit' right; aborting the hook chain with no errors lets the edit proceed
		// while leaving every other check stage untouched.
		return false;
	}

	/**
	 * Former AlternateEdit handler. No-op since the 1.43 port: granting rights here
	 * mutated global state without affecting PermissionManager checks.
	 *
	 * @deprecated since 2.1.0
	 * @param object $editPage the EditPage object
	 * @return bool true to resume normal operation
	 */
	public static function alternateEdit( $editPage ) {
		return true;
	}

	/**
	 * Former ParserBeforeStrip handler. The hook was removed from MediaWiki core
	 * (deprecated 1.36, removed 1.38), so this never fires on supported branches.
	 *
	 * @deprecated since 2.1.0
	 * @param object $parser parser object
	 * @param string &$text text being parsed
	 * @param string &$strip_state strip state
	 * @return bool true to continue parsing
	 */
	public static function giveEditRightsWhenViewingTalkPages( $parser, &$text, &$strip_state ) {
		return true;
	}
}
