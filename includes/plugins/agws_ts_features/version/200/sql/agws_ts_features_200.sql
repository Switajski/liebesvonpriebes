ALTER TABLE `xplugin_agws_ts_features_config`
	ADD UNIQUE INDEX `UNIQUE_KEY_TSID` (`cTS_ID`),
	ADD UNIQUE INDEX `UNIQUE_KEY_SPRACHE` (`iTS_Sprache`);