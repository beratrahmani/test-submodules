INSERT INTO `s_digital_publishing_content_banner` (`id`, `name`, `bgType`, `bgOrientation`, `bgMode`, `bgColor`, `mediaId`) VALUES
  (1999852, 'Unbenannt', 'color', 'center center', 'cover', '', NULL);

INSERT INTO `s_digital_publishing_layers` (`id`, `label`, `position`, `orientation`, `width`, `height`, `marginTop`, `marginRight`, `marginBottom`, `marginLeft`, `borderRadius`, `bgColor`, `link`, `contentBannerID`) VALUES
  (894563254, 'Neue Ebene', 0, 'center center', '20', '600', 20, 21, 21, 21, 8, '#2BFFE8', 'SW10178', 1999852);

INSERT INTO `s_digital_publishing_elements` (`id`, `name`, `label`, `position`, `payload`, `layerID`) VALUES
  (145687421, 'image', 'Bild', 0, '{"mediaId":759,"alt":"","maxWidth":100,"maxHeight":100,"orientation":"left","paddingTop":32,"paddingLeft":32,"paddingChain":true,"paddingRight":32,"paddingBottom":32,"class":""}', 894563254);