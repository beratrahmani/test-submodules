# CHANGELOG for B2B-Suite

This changelog references changes done in B2B-Suite versions.
[View all changes from B2B-Suite online.](https://docs.enterprise.shopware.com/b2b-suite/changelog/)


## 2.0.2

### Improvements

* Auditlog divided ENT-1618
* Restyling orderlists component ENT-1622
* Use contact id instead of email in frontend controllers
* Scrollable ajax product search results ENT-1568
* Scrollable B2B-navigation in the tablet view ENT-1671

### Fixes

* Correction of the english snippets ENT-1625
* Fix add product to order list ENT-1621
* Fix incorrect sorting by date ENT-1569
* Fix a fatal error when adding an invalid productnumber ENT-1640
* Corrected snippets and fixed an incorrect forwarding when adding products to the cart ENT-1636
* Customer group related prices ENT-1552
* Show error message if a sales representative client has no password ENT-1675
* Fix infinite loading indicators ENT-1688
* Rights are no longer given unexpectedly ENT-1697
* Error due to preselection of insufficient budget fixed. ENT-1687
* Fix navigation for firefox ENT-1690

### Additions

* Warning when overwriting productnumbers by file upload ENT-1620
* Added "add item" event to fast-order
* JavaScript Events for the fastorder module ENT-1652
* JavaScript Events for the custom product numbers module ENT-1653

## 2.0.1

### Fixes

* Fix product quantity update in the order clearance module
* Compatibility with ES
* Custom ordernumber product name searchable
* Fix offer request submission from cart

## 2.0.0

### Additions

* Hierarchies
* Request for quotation
* ProductNameAwareInterface for easy translation of the product name
* Debtor as contact person for Budgets ENT-1335
* Changed visibility of the wish list button in the product detail view
* Changed FrontendAccountFirewall $routes property from private to protected
* Performance improvements for the AuthenticationService
* Usage of ContextServiceInterface instead of ContextService

### Fixes

* Display variants in product search ENT-1427
* View all order lists ENT-1527
* Move AddressRepository to Bridge Namespace ENT-1555
* Support of required fields in billing/shipping addresses ENT-1313
* BudgetNotify Cron ENT-1591
* Fixed budget checkout handling
* Fixed "add another contact"
* Several fixes: ENT-1438, ENT-1549
* Fixed password reset function

### Removals

* Shopware\B2B\AclRoute\Frontend\AssignmentController::gridAction()
* Shopware\B2B\Address\Frontend\ContactAddressController::billingAction()
* Shopware\B2B\Address\Frontend\ContactAddressController::shippingAction()
* Shopware\B2B\Budget\Framework\BudgetRepository::fetchBudgetContactList()
* Shopware\B2B\Budget\Frontend\BudgetController::editAction()
* Shopware\B2B\Budget\Frontend\ContactBudgetController::gridAction()
* Shopware\B2B\Contact\Frontend\ContactContactVisibilityController::gridAction()
* Shopware\B2B\Contact\Frontend\ContactController::indexAction()->contactGrid view Variable
* Shopware\B2B\ContingentGroupContact\Frontend\ContactContingentController::gridAction()
* Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeFactory::getAllTypeNames()
* Shopware\B2B\FastOrder\Frontend\FastOrderController::processProductsAction()
* Shopware\B2B\FastOrder\Frontend\FastOrderController::processItemsFromListingAction()
* Shopware\B2B\Order\Bridge\OrderRepository::setRequestedDeliveryDateByOrderContextId()
* Shopware\B2B\Order\Bridge\OrderRepository::setOrderReferenceNumber()
* Shopware\B2B\Order\Bridge\OrderRepository::updateRequestedDeliveryDate()
* Shopware\B2B\Order\Bridge\OrderRepository::setRequestedDeliveryDate()
* Shopware\B2B\Order\Bridge\ShopOrderRepository::setRequestedDeliveryDate()
* Shopware\B2B\Order\Bridge\ShopOrderRepository::updateOrderReferenceNumber()
* Shopware\B2B\Order\Framework\OrderContextRepository::setOrderNumber()
* Shopware\B2B\Order\Framework\OrderRepositoryInterface::setOrderCommentByOrderContextId()
* Shopware\B2B\Order\Framework\OrderRepositoryInterface::setOrderCommentByOrderContextId()
* Shopware\B2B\Order\Framework\ShopOrderRepositoryInterface::setRequestedDeliveryDate()
* Shopware\B2B\Order\Framework\ShopOrderRepositoryInterface::setOrderCommentByOrderContextId()
* Shopware\B2B\Order\Framework\ShopOrderRepositoryInterface::updateOrderReferenceNumber()
* Shopware\B2B\OrderClearance\Framework\OrderClearanceRepositoryInterface::acceptOrder()
* Shopware\B2B\OrderList\Framework\OrderListService::createLineItemListFromProductsRequest()
* Shopware\B2B\OrderList\Framework\ContactOrderListController::gridAction()
* Shopware\B2B\OrderList\Framework\RoleOrderListController::gridAction()
* Shopware\B2B\RoleContact\Frontend\RoleContactVisibilityController::gridAction()
* Shopware\B2B\StoreFrontAuthentication\Bridge\CredentialsBuilder
* Shopware\B2B\StoreFrontAuthentication\Framework\CredentialsBuilderInterface
* Shopware\B2B\StoreFrontAuthentication\Bridge\UserRepository::syncContact()

## 1.5.1 

### Additions

* Performance improvements for the AuthenticationService

### Fixes

* Changed visibility of the wish list button in the product detail view
* Fixed budget checkout handling
* Fixed "add another contact"
* Changed FrontendAccountFirewall $routes property from private to protected
* Usage of ContextServiceInterface instead of ContextService
* Fixed password reset function

## 1.5.0 

### Additions

* Added a profile page for contacts, debtors and sales representatives
* Added a cronjob and cli command for order sync implemented
* Added html min values for number inputs
* Added default billing and shipping address
* Added sorting for order list items

### Fixes

* Improved API contact creation: get context owner id from debtor email
* Improved fast order upload
* Improved default styling
* Added missing tooltips to anchor tags and icons
* Fixed budget selection in checkout
* Fixed pagination in customer overview
* Optimized snippets
* Fixed order reference number and requested deliver date handling
* Fixed price handling for show net prices in frontend
* Fixed exception if order has no shipping method

### Removals

* Removed order attribute b2b_requested_delivery_date
* Removed order attribute b2b_order_reference
* Removed order attribute b2b_clearance_comment
* Removed user attribute b2b_sales_representative_media_id

## 1.4.2

### Fixes

* Fixed backend customer listing
    * pagination
    * listing
    * sorting
* Fixed customer creation by API for debtors

## 1.4.1

### Additions

* Added improvements for the docker environment

### Fixes

* Fixed cart history calculation
* Fixed datepicker handling
* Added contingent group grid reload after detail save
* Fixed role visibility after creation by contact
* Fixed contact visibility after creation by contact

### Removals

* Didn't clear the basket in produceCart method

## 1.4.0

### Additions

* Added PHP 7.1 support
* Added ACL classes to modal save buttons
* Added contact visibility permission to contact after contact creation
* Added contact visibility permission to contact after role creation
* Added mapped route assignment for allow process at permission management
   * `b2b_acl_route.route_mapping` - mapping DI variable

### Fixes

* Fixed wrong date declaration for `components/Statistic/Framework/Statistic` entity
* Fixed clearance decline error
* Fixed role grid reload after change in role detail modal
* Fixed `swDatePicker` duplication at statistics

### Removals

* Removed composer dependency `roave/security-advisories`

## 1.3.1

### Fixes

* Fixed migration for debtors without sales representatives
