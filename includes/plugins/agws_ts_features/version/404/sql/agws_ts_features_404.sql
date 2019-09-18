ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `cTS_ID` `ts_id` VARCHAR(50);
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `iTS_Sprache` `ts_sprache` int(2) DEFAULT '0';
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `cTS_BadgeCode` `ts_BadgeCode` VARCHAR(2000);
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `bTS_RatingWidgetShow` `ts_RatingWidgetAktiv` TINYINT(1) DEFAULT '0';
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `iTS_RatingWidgetPosition` `ts_RatingWidgetPosition` INT(1) DEFAULT '0';
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `bTS_ReviewStickerShow` `ts_ReviewStickerAktiv` TINYINT(1) DEFAULT '0';
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `iTS_ReviewStickerPosition` `ts_ReviewStickerPosition` INT(1) DEFAULT '1';
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `cTS_ReviewStickerCode` `ts_ReviewStickerCode` VARCHAR(2000) AFTER `ts_ReviewStickerPosition`;
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `bTS_RichSnippetsCategory` `ts_RichSnippetsKategorieseite` TINYINT(1) DEFAULT '0';
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `bTS_RichSnippetsProduct` `ts_RichSnippetsArtikelseite` TINYINT(1) DEFAULT '0';
ALTER TABLE `xplugin_agws_ts_features_config` CHANGE COLUMN `bTS_RichSnippetsMain` `ts_RichSnippetsStartseite` TINYINT(1) DEFAULT '0';

ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_modus` CHAR(1) NULL DEFAULT 'S' AFTER `ts_sprache`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_BadgeVariante` CHAR(10) NULL DEFAULT 'reviews' AFTER `ts_modus`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_BadgeYOffset` TINYINT(4) NULL DEFAULT '0' AFTER `ts_BadgeVariante`;



ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungAktiv` TINYINT(1) NULL DEFAULT '0' AFTER `ts_BadgeCode`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungRegisterAktiv` TINYINT(1) NULL DEFAULT '0' AFTER `ts_ProduktBewertungAktiv`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungRegisterNameTab` VARCHAR(50) NULL DEFAULT 'Trusted Shops Bewertungen' AFTER `ts_ProduktBewertungRegisterAktiv`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungRegisterRahmenfarbe` VARCHAR(10) NULL DEFAULT '#c0c0c0' AFTER `ts_ProduktBewertungRegisterNameTab`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungRegisterSternfarbe` VARCHAR(10) NULL DEFAULT '#ffdc0f' AFTER `ts_ProduktBewertungRegisterRahmenfarbe`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungRegisterSterngroesse` TINYINT(10) NULL DEFAULT '14' AFTER `ts_ProduktBewertungRegisterSternfarbe`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungRegisterLeer` TINYINT(1) NULL DEFAULT '0' AFTER `ts_ProduktBewertungRegisterSterngroesse`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungRegisterCode` VARCHAR(2000) AFTER `ts_ProduktBewertungRegisterLeer`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungRegisterSelector` VARCHAR(100) NULL DEFAULT '#article-tabs' AFTER `ts_ProduktBewertungRegisterCode`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungRegisterMethode` VARCHAR(25) NULL DEFAULT 'append' AFTER `ts_ProduktBewertungRegisterSelector`;

ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungSterneAktiv` VARCHAR(10) NULL DEFAULT '0' AFTER `ts_ProduktBewertungRegisterMethode`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungSterneSternfarbe` VARCHAR(10) NULL DEFAULT '#ffdc0f' AFTER `ts_ProduktBewertungSterneAktiv`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungSterneSterngroesse` TINYINT(10) NULL DEFAULT '15' AFTER `ts_ProduktBewertungSterneSternfarbe`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungSterneSchriftgroesse` TINYINT(10) NULL DEFAULT '12' AFTER `ts_ProduktBewertungSterneSterngroesse`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungSterneLeer` TINYINT(1) NULL DEFAULT '0' AFTER `ts_ProduktBewertungSterneSchriftgroesse`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungSterneCode` VARCHAR(2000) AFTER `ts_ProduktBewertungSterneLeer`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungSterneSelector` VARCHAR(100) NULL DEFAULT '#product-offer .info-essential' AFTER `ts_ProduktBewertungSterneCode`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ProduktBewertungSterneMethode` VARCHAR(25) NULL DEFAULT 'before' AFTER `ts_ProduktBewertungSterneSelector`;

ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ReviewStickerModus` VARCHAR(25) NULL DEFAULT 'testimonials' AFTER `ts_ReviewStickerAktiv`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ReviewStickerSchriftart` VARCHAR(25) NULL DEFAULT 'Arial' AFTER `ts_ReviewStickerPosition`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ReviewStickerBewertungsanzahl` VARCHAR(25) NULL DEFAULT '5' AFTER `ts_ReviewStickerSchriftart`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ReviewStickerMindestnote` VARCHAR(25) NULL DEFAULT '3.0' AFTER `ts_ReviewStickerBewertungsanzahl`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_ReviewStickerHintergrundfarbe` VARCHAR(10) NULL DEFAULT '#ffdc0f' AFTER `ts_ReviewStickerMindestnote`;

ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_RichSnippetsAktiv` VARCHAR(10) NULL DEFAULT '0' AFTER `ts_ReviewStickerCode`;
ALTER TABLE `xplugin_agws_ts_features_config` ADD COLUMN `ts_RichSnippetsCode` VARCHAR(2000) AFTER `ts_RichSnippetsStartseite`;

