<?php

/**
 * Class ext_update
 *
 * Performs update tasks for extension Fluidcontent
 */
class ext_update {

	/**
	 * @return boolean
	 */
	public function access() {
		return TRUE;
	}

	/**
	 * @return string
	 */
	public function main() {
		$clause = "tx_fed_page_controller_action LIKE 'fluidpages->%' OR tx_fed_page_controller_action_sub LIKE 'fluidpages->%'";
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,tx_fed_page_controller_action,tx_fed_page_controller_action_sub', 'pages', $clause);
		$count = array();
		// first pass: adjust page template selections to match new extension_key->actionName syntax
		foreach($rows as $row) {
			$modified = FALSE;
			if (FALSE !== strpos($row['tx_fed_page_controller_action'], 'fluidpages->')) {
				$row['tx_fed_page_controller_action'] = str_replace('fluidpages->', 'fluidpages_bootstrap->', $row['tx_fed_page_controller_action']);
				$modified = TRUE;
			}
			if (FALSE !== strpos($row['tx_fed_page_controller_action_sub'], 'fluidpages->')) {
				$row['tx_fed_page_controller_action_sub'] = str_replace('fluidpages->', 'fluidpages_bootstrap->', $row['tx_fed_page_controller_action_sub']);
				$modified = TRUE;
			}
			if (TRUE === $modified) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('pages', "uid='" . intval($row['uid']) . "'", $row);
				if (FALSE === in_array($row['uid'], $count)) {
					array_push($count, $row['uid']);
				}
			}
		}
		// second pass: analyse the page root line starting from pid zero, finding all pages which either have selected
		// or inherited a fluidpages_bootstrap template - adjust FlexForm XML, adding prefix "settings." to all field names
		// which don't already have this prefix.
		$count = $this->adjustFlexFormSourceForAssociatedRecordsInPidRecursive(0, $count);
		$message = count($count) . ' row(s) updated. <br /><br />';
		if (0 < count($count)) {
			$message .= '<ul>';
			foreach ($count as $uid) {
				$pageTitle = array_pop(array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('title', 'pages', "uid = '" . $uid . "'")));
				if (TRUE === empty($pageTitle)) {
					$pageTitle = '[no title]';
				}
				$message .= '<li>Page "' . $pageTitle . '" [' . $uid . ']</li>';
			}
			$message .= '</ul><br /><br />';
			$message .= 'YOUR PAGES HAVE BEEN ADJUSTED to use 1) the proper extension key "fluidpages_bootstrap" in all selections which';
			$message .= ' before now contained "fluidpages" as selected provider and 2) the prefix "settings." for field names stored in';
			$message .= ' the page configuration in order to match the new naming scheme in the Flux form definitions. <br /><br />';
			$message .= '<strong>PLEASE NOTE:</strong> IF YOU HAVE an inheritance structures in your root line which resemble this chain:';
			$message .= ' [TOP page] -> fluidpages_bootstrap -> ... -> OTHERPROVIDER -> ... -> fluidpages_bootstrap (in other words: if inheritance';
			$message .= ' is interrupted somewhere in the root line) then any pages beneath "OTHERPROVIDER" will not be detected and must be manually';
			$message .= ' adjusted (see EXT:fluidpages_bootstrap/class.ext_update.php for detailed information about which actions to take in this case).';
		}
		return $message;
	}

	/**
	 * @param integer $pid
	 * @param array $count Maintained record of encountered DB rows
	 * @return array
	 */
	protected function adjustFlexFormSourceForAssociatedRecordsInPidRecursive($pid, $count) {
		$clause = "pid = '" . strval(intval($pid)) . "'";
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,tx_fed_page_controller_action,tx_fed_page_controller_action_sub,tx_fed_page_flexform', 'pages', $clause);
		foreach ($rows as $row) {
			$fieldModified = FALSE;
			// check: skip records which don't have an empty value (inherited) or use a different template provider. This also
			// prevents recursive processing, which means that if you break inheritance and restart using fluidpages_bootstrap
			// further down that root line, those pages will NOT be processed correctly!
			if (FALSE === strpos($row['tx_fed_page_controller_action'], 'fluidpages->') && FALSE === empty($row['tx_fed_page_controller_action'])) {
				continue;
			}
			if (FALSE === strpos($row['tx_fed_page_controller_action_sub'], 'fluidpages->') && FALSE === empty($row['tx_fed_page_controller_action_sub'])) {
				continue;
			}
			if (FALSE === empty($row['tx_fed_page_flexform'])) {
				$dom = new DOMDocument();
				$dom->loadXML($row['tx_fed_page_flexform']);
				foreach ($dom->getElementsByTagName('field') as $fieldNode) {
					/** @var $fieldNode DOMNode */
					$name = $fieldNode->attributes->getNamedItem('index')->nodeValue;
					if (0 !== strpos($name, 'settings')) {
						$fieldNode->attributes->getNamedItem('index')->nodeValue = 'settings.' . $name;
						$fieldModified = TRUE;
					}
				}
				if (TRUE === $fieldModified) {
					$row['tx_fed_page_flexform'] = $dom->saveXML();
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('pages', "uid='" . intval($row['uid']) . "'", $row);
					if (FALSE === in_array($row['uid'], $count)) {
						array_push($count, $row['uid']);
					}
				}
			}
			$count = $this->adjustFlexFormSourceForAssociatedRecordsInPidRecursive($row['uid'], $count);
		}
		return $count;
	}

}
