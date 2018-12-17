# SwagPromotion
Powerful discount rules for shopware

# Run tests
From the promotion directory run 

```
phpunit
```

# Generate CodeCoverage
```
phpunit --coverage-html ./CodeCoverage --exclude-group=integration
```
Please be aware, that currently many files are covered by integration tests, so basically whenever a tests involves
e.g. `Shopware()->Basket()->sGetBasket()`, I set an explicit @covers information, to let the test only cover a certain
component, not the whole stack. This will basically lower the test coverage - but a