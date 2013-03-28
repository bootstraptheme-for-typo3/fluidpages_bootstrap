<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Fluid Pages Bootstrap: Page Templates');
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Settings', 'Fluid Pages Bootstrap: TS based settings');

Tx_Fluidpages_Core::registerProviderExtensionKey('fluidpages_bootstrap');