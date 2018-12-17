INSERT INTO `s_digital_publishing_content_banner` (`id`, `name`, `bgType`, `bgOrientation`, `bgMode`, `bgColor`, `mediaId`) VALUES
  (1, 'NB1', 'image', 'center center', 'cover', '', 553),
  (2, 'NB2', 'image', 'top center', 'cover', '', 561),
  (3, 'nb3', 'color', 'center center', 'cover', '#00EF42', NULL);

INSERT INTO `s_digital_publishing_layers` (`id`, `label`, `position`, `orientation`, `width`, `height`, `marginTop`, `marginRight`, `marginBottom`, `marginLeft`, `borderRadius`, `bgColor`, `link`, `contentBannerID`) VALUES
  (1, 'Neue Ebene', 0, 'center center', 'auto', 'auto', 0, 0, 0, 0, 0, '', '', 1),
  (2, 'Neue Ebene', 0, 'center center', 'auto', 'auto', 0, 0, 0, 0, 0, '', '', 2),
  (3, 'Neue Ebene', 0, 'center center', 'auto', 'auto', 0, 0, 0, 0, 0, '', '', 3);

INSERT INTO `s_digital_publishing_elements` (`id`, `name`, `label`, `position`, `payload`, `layerID`) VALUES
  (1, 'text', 'Text', 0, '{\"text\":\"NEUER TEXT\",\"type\":\"h1\",\"font\":\"Tahoma\",\"adjust\":true,\"adjustDyn\":true,\"fontsize_xs\":20,\"fontsize_s\":24,\"fontsize_m\":30,\"fontsize_l\":38,\"fontsize_xl\":42,\"lineHeight\":1,\"fontcolor\":\"#FFEDAE\",\"textfield-2947-inputEl\":\"\",\"orientation\":\"left\",\"fontweight\":false,\"fontstyle\":true,\"underline\":false,\"uppercase\":false,\"shadowColor\":\"#F3FFB1\",\"textfield-2962-inputEl\":\"\",\"shadowOffsetX\":2,\"shadowOffsetY\":2,\"shadowBlur\":4,\"paddingTop\":0,\"paddingLeft\":0,\"paddingChain\":false,\"paddingRight\":114,\"paddingBottom\":51,\"class\":\"\"}', 1),
  (2, 'button', 'Button', 1, '{\"text\":\"LINK\",\"type\":\"is--secondary\",\"target\":\"_self\",\"link-search\":\"\",\"link\":\"\",\"orientation\":\"center\",\"paddingTop\":5,\"paddingLeft\":5,\"paddingChain\":true,\"paddingRight\":5,\"paddingBottom\":5,\"autoSize\":true,\"width\":200,\"height\":38,\"fontsize\":38,\"class\":\"\"}', 1),
  (3, 'image', 'Bild', 2, '{\"mediaId\":698,\"alt\":\"STOP IT\",\"maxWidth\":303,\"maxHeight\":100,\"orientation\":\"center\",\"paddingTop\":71,\"paddingLeft\":0,\"paddingChain\":false,\"paddingRight\":0,\"paddingBottom\":0,\"class\":\"\"}', 1),
  (4, 'text', 'Text', 0, '{\"text\":\"text\",\"type\":\"h1\",\"font\":\"Arial\",\"adjust\":true,\"adjustDyn\":false,\"fontsize_xs\":56,\"fontsize_s\":64,\"fontsize_m\":72,\"fontsize_l\":80,\"fontsize_xl\":88,\"lineHeight\":1,\"fontcolor\":\"#FFFFFF\",\"textfield-3479-inputEl\":\"\",\"orientation\":\"left\",\"fontweight\":false,\"fontstyle\":false,\"underline\":false,\"uppercase\":false,\"shadowColor\":\"#FFFFFF\",\"textfield-3494-inputEl\":\"\",\"shadowOffsetX\":0,\"shadowOffsetY\":0,\"shadowBlur\":0,\"paddingTop\":0,\"paddingLeft\":0,\"paddingChain\":false,\"paddingRight\":0,\"paddingBottom\":0,\"class\":\"\"}', 2),
  (5, 'image', 'Bild', 0, '{\"mediaId\":670,\"alt\":\"\",\"maxWidth\":500,\"maxHeight\":500,\"orientation\":\"left\",\"paddingTop\":0,\"paddingLeft\":0,\"paddingChain\":false,\"paddingRight\":0,\"paddingBottom\":0,\"class\":\"\"}', 3);
