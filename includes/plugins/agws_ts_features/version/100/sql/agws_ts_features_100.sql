CREATE TABLE `xplugin_agws_ts_features_config` (  
 `cTS_ID` varchar( 50  )  NOT  NULL  COMMENT  'eindeutige TS-ID',
 `iTS_Sprache` int( 2  )  NOT  NULL DEFAULT  '0'  COMMENT  'kSprache aus tsprache',
 `cTS_BadgeCode` varchar( 2000  )  NULL COMMENT  'js-code für TS-Badge',
 `bTS_RatingWidgetShow` tinyint( 1  )  NOT  NULL DEFAULT  '0' COMMENT  'Anzeigeoption TS-Rating Widget (0=nein, 1=ja)',
 `iTS_RatingWidgetPosition` int( 1  )  NOT  NULL DEFAULT  '0' COMMENT  'Anzeigeposition TS-RatingWidget (1=Sidebarbox links, 2=Sidebarbox rechts, 3=Footer)',
 `bTS_ReviewStickerShow` tinyint( 1  )  NOT  NULL DEFAULT  '0' COMMENT  'Anzeigeoption TS-Review Sticker (0=nein, 1=ja)',
 `iTS_ReviewStickerPosition` int( 1  )  NOT  NULL DEFAULT  '0' COMMENT  'Anzeigeposition TS-Review Sticker (1=Sidebarbox links, 2=Sidebarbox rechts, 3=Footer)',
 `bTS_RichSnippetsCategory` tinyint( 1  )  NOT  NULL DEFAULT  '0' COMMENT  'Anzeigeoption RichSnippets Kategorieseite (0=nein, 1=ja)',
 `bTS_RichSnippetsProduct` tinyint( 1  )  NOT  NULL DEFAULT  '0' COMMENT  'Anzeigeoption RichSnippets Artikelseite (0=nein, 1=ja)',
 `bTS_RichSnippetsMain` tinyint( 1  )  NOT  NULL DEFAULT  '0' COMMENT  'Anzeigeoption RichSnippets Startseite (0=nein, 1=ja)',
 `cTS_ReviewStickerCode` varchar( 2000  )  DEFAULT NULL  COMMENT  'js-code für Review Sticker (opt.)',
 PRIMARY  KEY (  `cTS_ID`  )  
) ENGINE  =  MyISAM  DEFAULT CHARSET  = latin1 COMMENT  =  'TS-Features';