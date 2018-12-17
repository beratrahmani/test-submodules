INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
(674660, 'sessionId', 0, 'Meine neue Promotion', 0, 'promo_gratisartikel', 1, 1, -19.95, -16.764705882353, 19, '0000-00-00 00:00:00', 4, 0, '', '', '', '', 1);

INSERT INTO `s_order_basket_attributes` (`id`, `basketID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `swag_promotion_id`, `swag_is_free_good_by_promotion_id`) VALUES
(3665823, 674660, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL);
