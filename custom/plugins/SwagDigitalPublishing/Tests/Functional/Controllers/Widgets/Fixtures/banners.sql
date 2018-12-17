INSERT INTO `s_digital_publishing_content_banner` (`id`, `name`, `bgType`, `bgOrientation`, `bgMode`, `bgColor`, `mediaId`) VALUES
  (3500993, 'Test Banner 3', 'image', 'top center', 'cover', '', 440);

INSERT INTO `s_digital_publishing_layers` (`id`, `label`, `position`, `orientation`, `width`, `height`, `marginTop`, `marginRight`, `marginBottom`, `marginLeft`, `borderRadius`, `bgColor`, `link`, `contentBannerID`) VALUES
  (376803, 'Test Ebene 1', 0, 'center center', 'auto', 'auto', 0, 0, 53, 377, 30, '#FA1240', '', 3500993),
  (476804, 'Test Ebene 2', 1, 'center center', 'auto', 'auto', 0, 0, 0, 0, 0, '', 'https://www.google.de/', 3500993);

INSERT INTO `s_digital_publishing_elements` (`id`, `name`, `label`, `position`, `payload`, `layerID`) VALUES
  (4996384, 'text', 'Text', 0, '{"text":"Lorem ipsum dolor sit amet","type":"h1","font":"Open Sans","fontsize":16,"lineHeight":1,"fontcolor":"#FFFFFF","textfield-2854-inputEl":"","orientation":"left","fontweight":false,"fontstyle":false,"underline":false,"uppercase":false,"shadowColor":"","textfield-2858-inputEl":"","shadowOffsetX":0,"shadowOffsetY":0,"shadowBlur":0,"paddingTop":10,"paddingLeft":10,"paddingChain":true,"paddingRight":10,"paddingBottom":10,"class":""}', 376803),
  (5996385, 'button', 'Button', 1, '{"text":"Zum Kleid","type":"standard","target":"_self","link-search":"","link":"https://www.google.de/","orientation":"center","paddingTop":5,"paddingLeft":0,"paddingChain":false,"paddingRight":0,"paddingBottom":15,"autoSize":true,"width":200,"height":38,"fontsize":14,"class":""}', 376803),
  (6996386, 'image', 'Bild', 0, '{"mediaId":439,"alt":"","maxWidth":100,"maxHeight":100,"orientation":"left","paddingTop":200,"paddingLeft":400,"paddingChain":false,"paddingRight":0,"paddingBottom":0,"class":""}', 476804);