INSERT INTO `s_digital_publishing_content_banner` (`id`, `name`, `bgType`, `bgOrientation`, `bgMode`, `bgColor`, `mediaId`) VALUES
(1111, 'Hintergrund', 'image', 'center center', 'cover', '#001DF7', 781);

INSERT INTO `s_digital_publishing_layers` (`id`, `label`, `position`, `orientation`, `width`, `height`, `marginTop`, `marginRight`, `marginBottom`, `marginLeft`, `borderRadius`, `bgColor`, `link`, `contentBannerID`) VALUES
(11111, 'Ebene1', 0, 'center left', 'auto', 'auto', 0, 0, 0, 0, 0, '', '', 1111),
(11119, 'Ebene2', 1, 'center center', 'auto', 'auto', 0, 0, 0, 0, 0, '', '', 1111);

INSERT INTO `s_digital_publishing_elements` (`id`, `name`, `label`, `position`, `payload`, `layerID`) VALUES
(111113, 'image', 'Bild', 1, '{"mediaId":561,"alt":"","maxWidth":100,"maxHeight":100,"orientation":"left","paddingTop":0,"paddingLeft":0,"paddingChain":false,"paddingRight":0,"paddingBottom":0,"class":""}', 11111),
(111114, 'image', 'Bild', 0, '{"mediaId":553,"alt":"","maxWidth":100,"maxHeight":100,"orientation":"left","paddingTop":0,"paddingLeft":0,"paddingChain":false,"paddingRight":0,"paddingBottom":0,"class":""}', 11119);