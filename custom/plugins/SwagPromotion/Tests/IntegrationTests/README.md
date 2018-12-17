# IntegrationTests
The tests in here should tests various promotion scenarios using the DummyRepository. The general pattern in here 
should be:

```
// get the (dummy) promotin repository
Shopware()->Container()->get(
    'promotion.repository'
// add some promotions to it. Varions default promotions values are defined in the PromotionFactory
)->set(
    [
        PromotionFactory::create(
            [
                'number' => 'ex1',
                'exclusive' => 1,
                'priority' => 1
            ]
        ),
        PromotionFactory::create(
            [
                'number' => 'ex2',
                'exclusive' => 0,
                'priority' => 2
            ]
        ),
        PromotionFactory::create(
            [
                'number' => 'ex3',
                'exclusive' => 0,
                'priority' => 1
            ]
        ),
        PromotionFactory::create(
            [
                'number' => 'ex4',
                'exclusive' => 0,
                'priority' => -1
            ]
        ),
    ]
);

// add some items
Shopware()->Modules()->Basket()->sDeleteBasket();
Shopware()->Modules()->Basket()->sAddArticle('SW10009', 1);
Shopware()->Modules()->Basket()->sAddArticle('SW10010', 1);
$basket = Shopware()->Modules()->Basket()->sGetBasket();

// Check, if the promotions worked out as expected
$this->assert…

```

If you experience any problems with promotion repository state, you can clean up the promotion repo before / after your
tests:

```
public static function tearDownAfterClass()
{
    Shopware()->Container()->get('swag_promotion.repository')->set([]);
    parent::tearDownAfterClass();
}
```

# Test coverage data
In order to get realistic test coverage data, using the `@coversNothing` annotation in the class doc block is recommended
for these kind of test. 

```

/**
 *
 *
 * 
 * @coversNothing
 */
classs SomeTest {
}
```
