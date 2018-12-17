INSERT INTO `s_articles_bundles`
(id, articleID, name, show_name, active, description, rab_type, taxID, ordernumber, max_quantity_enable, display_global, display_delivery, max_quantity, valid_from, valid_to, datum, sells, bundle_type, bundle_position)
VALUES
    (10000, 2, 'Bundle is limited to variant', 0, 1, '', 'pro', NULL, 'bundle_is_limited_to_variant', 0, 0, 1, 0, NULL, NULL, '2017-01-13 16:41:00', 0, 1, 0),
    (10001, 2, 'Bundle has in stock', 0, 1, '', 'pro', NULL, 'bundle_has_instock', 1, 0, 1, 0, NULL, NULL, '2017-01-16 08:23:49', 0, 1, 0),
    (10002, 218, 'Bundle for SW10216.1', 0, 1, '', 'pro', NULL, 'bundle_for_sw102216_1', 0, 0, 1, 0, NULL, NULL, '2017-01-16 11:27:51', 0, 1, 0),
    (10003, 218, 'Bundle for SW10216.2', 0, 1, '', 'pro', NULL, 'bundle_for_sw10216_2', 0, 0, 1, 0, NULL, NULL, '2017-01-16 11:29:12', 0, 1, 0),
    (10004, 237, 'Bundle without limited variants', 0, 1, '', 'pro', NULL, 'bundle_without_limited_variant', 0, 0, 1, 0, NULL, NULL, '2017-01-23 09:24:25', 0, 1, 0),
    (10005, 237, 'Bundle with percentage discount', 0, 1, '', 'pro', NULL, 'bundle_with_percentage_discount', 0, 0, 1, 0, NULL, NULL, '2017-01-23 09:24:25', 0, 1, 0),
    (10006, 237, 'Bundle with absolute discount', 0, 1, '', 'abs', NULL, 'Bundle_with_absolute_discount', 0, 0, 1, 0, NULL, NULL, '2017-01-23 09:24:25', 0, 1, 0);

INSERT INTO `s_articles_bundles_articles`
(id, bundle_id, article_detail_id, quantity, configurable, bundle_group_id, position)
VALUES
    (700, 10000, 827, 1, 0, NULL, 1),
    (701, 10001, 827, 1, 0, NULL, 1),
    (702, 10002, 50, 1, 0, NULL, 1),
    (703, 10003, 50, 1, 0, NULL, 1),
    (704, 10004, 792, 1, 0, NULL, 1),
    (705, 10005, 792, 1, 0, NULL, 1),
    (706, 10006, 792, 1, 0, NULL, 1);

INSERT INTO `s_articles_bundles_prices`
(id, bundle_id, customer_group_id, price)
VALUES
    (500, 10000, '1', 1),
    (501, 10000, '2', 1),
    (502, 10001, '1', 1),
    (503, 10001, '2', 1),
    (504, 10002, '1', 1),
    (505, 10002, '2', 1),
    (506, 10003, '1', 1),
    (507, 10003, '2', 1),
    (508, 10004, '1', 1),
    (509, 10005, '1', 10),
    (510, 10006, '1', 10);

INSERT INTO `s_articles_bundles_stint`
(id, bundle_id, article_detail_id)
VALUES
    (600, 10000, 123),
    (601, 10002, 769),
    (602, 10003, 770);

INSERT INTO s_articles_bundles_customergroups
(id, bundle_id, customer_group_id)
VALUES
    (800, 10000, 1),
    (801, 10000, 2),
    (802, 10001, 1),
    (803, 10001, 2),
    (804, 10002, 1),
    (805, 10002, 2),
    (806, 10003, 1),
    (807, 10003, 2),
    (808, 10004, 1),
    (809, 10005, 1),
    (810, 10006, 1);
