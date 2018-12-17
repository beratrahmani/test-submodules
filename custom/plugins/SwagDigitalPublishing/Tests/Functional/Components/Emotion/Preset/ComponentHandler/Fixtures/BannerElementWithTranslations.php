<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

return '
{
  "showListing": false,
  "templateId": 1,
  "active": false,
  "name": "my shopping world",
  "position": 1,
  "device": "0,1,2,3,4",
  "fullscreen": 0,
  "isLandingPage": 0,
  "seoTitle": "",
  "seoKeywords": "",
  "seoDescription": "",
  "rows": 20,
  "cols": 4,
  "cellSpacing": 10,
  "cellHeight": 185,
  "articleHeight": 2,
  "mode": "fluid",
  "customerStreamIds": null,
  "replacement": null,
  "elements": [
    {
      "componentId": "emotion-digital-publishing",
      "startRow": 1,
      "startCol": 1,
      "endRow": 1,
      "endCol": 1,
      "cssClass": "",
      "viewports": [
        {
          "alias": "xs",
          "startRow": 1,
          "startCol": 1,
          "endRow": 1,
          "endCol": 1,
          "visible": true
        },
        {
          "alias": "s",
          "startRow": 1,
          "startCol": 1,
          "endRow": 1,
          "endCol": 1,
          "visible": true
        },
        {
          "alias": "m",
          "startRow": 1,
          "startCol": 1,
          "endRow": 1,
          "endCol": 1,
          "visible": true
        },
        {
          "alias": "l",
          "startRow": 1,
          "startCol": 1,
          "endRow": 1,
          "endCol": 1,
          "visible": true
        },
        {
          "alias": "xl",
          "startRow": 1,
          "startCol": 1,
          "endRow": 1,
          "endCol": 1,
          "visible": true
        }
      ],
      "data": [
        {
          "componentId": "emotion-digital-publishing",
          "fieldId": "digital_publishing_banner_id",
          "key": "digital_publishing_banner_id",
          "valueType": ""
        },
        {
          "componentId": "emotion-digital-publishing",
          "fieldId": "digital_publishing_banner_data",
          "value": "a87ff679a2f3e71d9181a67b7542122c",
          "key": "digital_publishing_banner_data",
          "valueType": "json"
        }
      ],
      "syncKey": "preset-element-598c6dfe51cc96.17246053"
    }
  ],
  "syncData": {
    "assets": {},
    "banners": {
      "a87ff679a2f3e71d9181a67b7542122c": {
        "name": "Unbenannt",
        "bgType": "color",
        "bgOrientation": "center center",
        "bgMode": "cover",
        "bgColor": "",
        "mediaId": null,
        "layers": [
          {
            "label": "layer title",
            "position": 0,
            "orientation": "center center",
            "width": "auto",
            "height": "auto",
            "marginTop": 0,
            "marginRight": 0,
            "marginBottom": 0,
            "marginLeft": 0,
            "borderRadius": 0,
            "bgColor": "",
            "link": "http://example.de",
            "elements": [
              {
                "name": "text",
                "label": "Text",
                "position": 0,
                "payload": "{\"text\":\"eb1 text1 de\",\"type\":\"h1\",\"font\":\"Open Sans\",\"fontsize\":16,\"lineHeight\":1,\"fontcolor\":\"#FFFFFF\",\"textfield-1439-inputEl\":\"\",\"orientation\":\"left\",\"fontweight\":false,\"fontstyle\":false,\"underline\":false,\"uppercase\":false,\"shadowColor\":\"\",\"textfield-1443-inputEl\":\"\",\"shadowOffsetX\":0,\"shadowOffsetY\":0,\"shadowBlur\":0,\"paddingTop\":0,\"paddingLeft\":0,\"paddingChain\":false,\"paddingRight\":0,\"paddingBottom\":0,\"class\":\"\"}"
              },
              {
                "name": "button",
                "label": "Button",
                "position": 1,
                "payload": "{\"text\":\"eb1 but1 de\",\"type\":\"standard\",\"target\":\"_self\",\"link-search\":\"\",\"link\":\"SW10003\",\"orientation\":\"left\",\"paddingTop\":0,\"paddingLeft\":0,\"paddingChain\":false,\"paddingRight\":0,\"paddingBottom\":0,\"autoSize\":true,\"width\":200,\"height\":38,\"fontsize\":14,\"class\":\"\"}"
              },
              {
                "name": "image",
                "label": "Bild",
                "position": 2,
                "payload": "{\"mediaId\": null,\"alt\":\"alt text de\",\"maxWidth\":100,\"maxHeight\":100,\"orientation\":\"left\",\"paddingTop\":0,\"paddingLeft\":0,\"paddingChain\":false,\"paddingRight\":0,\"paddingBottom\":0,\"class\":\"\"}"
              }
            ]
          }
        ]
      }
    },
    "bannerTranslations": {
      "a87ff679a2f3e71d9181a67b7542122c": {
        "0_0": [
          {
            "objecttype": "contentBannerElement",
            "objectdata": "a:1:{s:4:\"text\";s:12:\"eb1 text1 en\";}",
            "locale": "en_GB",
            "shop": "English"
          },
          {
            "objecttype": "contentBannerElement",
            "objectdata": "a:1:{s:4:\"text\";s:12:\"eb1 text1 fr\";}",
            "locale": "fr_FR",
            "shop": "french"
          },
          {
            "objecttype": "contentBannerElement",
            "objectdata": "a:1:{s:4:\"text\";s:13:\"eb1 text1 fr2\";}",
            "locale": "fr_FR",
            "shop": "french2"
          }
        ],
        "0_1": [
          {
            "objecttype": "contentBannerElement",
            "objectdata": "a:2:{s:4:\"text\";s:11:\"eb1 but1 de\";s:4:\"link\";s:14:\"http://test.en\";}",
            "locale": "en_GB",
            "shop": "English"
          },
          {
            "objecttype": "contentBannerElement",
            "objectdata": "a:2:{s:4:\"text\";s:11:\"eb1 but1 de\";s:4:\"link\";s:12:\"kljfdlkksjgf\";}",
            "locale": "fr_FR",
            "shop": "french"
          },
          {
            "objecttype": "contentBannerElement",
            "objectdata": "a:2:{s:4:\"text\";s:11:\"eb1 but1 de\";s:4:\"link\";s:7:\"SW10004\";}",
            "locale": "fr_FR",
            "shop": "french2"
          }
        ],
        "0_2": [
          {
            "objecttype": "contentBannerElement",
            "objectdata": "a:1:{s:3:\"alt\";s:6:\"alt en\";}",
            "locale": "en_GB",
            "shop": "English"
          },
          {
            "objecttype": "contentBannerElement",
            "objectdata": "a:1:{s:3:\"alt\";s:6:\"alt fr\";}",
            "locale": "fr_FR",
            "shop": "french"
          },
          {
            "objecttype": "contentBannerElement",
            "objectdata": "a:1:{s:3:\"alt\";s:7:\"alt fr2\";}",
            "locale": "fr_FR",
            "shop": "french2"
          }
        ],
        "0": [
          {
            "objecttype": "digipubLink",
            "objectdata": "a:1:{s:4:\"link\";s:16:\"http://ebene.com\";}",
            "locale": "en_GB",
            "shop": "English"
          },
          {
            "objecttype": "digipubLink",
            "objectdata": "a:1:{s:4:\"link\";s:15:\"http://ebene.fr\";}",
            "locale": "fr_FR",
            "shop": "french"
          },
          {
            "objecttype": "digipubLink",
            "objectdata": "a:1:{s:4:\"link\";s:16:\"http://ebene.fr2\";}",
            "locale": "fr_FR",
            "shop": "french2"
          }
        ]
      }
    }
  }
}';
