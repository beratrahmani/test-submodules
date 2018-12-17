<?php declare (strict_types = 1); return array (
  'acl' => 
  array (
    'B2bAcl' => 
    array (
      'error' => 'free',
      'silentError' => 'free',
    ),
  ),
  'dashboard' => 
  array (
    'B2bDashboard' => 
    array (
      'index' => 'free',
    ),
  ),
  'address' => 
  array (
    'B2bAddress' => 
    array (
      'remove' => 'delete',
      'billingGrid' => 'list',
      'shippingGrid' => 'list',
      'new' => 'create',
      'create' => 'create',
      'detail' => 'detail',
      'update' => 'update',
      'billing' => 'list',
      'shipping' => 'list',
      'index' => 'list',
    ),
    'B2bContactAddress' => 
    array (
      'grid' => 'list',
      'assign' => 'assign',
    ),
    'B2bContactAddressDefault' => 
    array (
      'grid' => 'list',
      'default' => 'assign',
    ),
    'B2bRoleAddress' => 
    array (
      'grid' => 'detail',
      'assign' => 'assign',
      'billing' => 'detail',
      'shipping' => 'detail',
    ),
  ),
  'contact' => 
  array (
    'B2bContact' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'detail' => 'detail',
      'update' => 'update',
      'remove' => 'delete',
      'new' => 'create',
      'create' => 'create',
      'edit' => 'detail',
    ),
    'B2bContactContactVisibility' => 
    array (
      'index' => 'detail',
      'grid' => 'assign',
      'assign' => 'assign',
    ),
    'B2bRoleContactVisibility' => 
    array (
      'index' => 'detail',
      'grid' => 'assign',
      'assign' => 'assign',
    ),
  ),
  'role' => 
  array (
    'B2bRole' => 
    array (
      'index' => 'list',
      'children' => 'list',
      'move' => 'update',
      'subtree' => 'list',
      'remove' => 'delete',
      'new' => 'create',
      'create' => 'create',
      'detail' => 'detail',
      'edit' => 'detail',
      'update' => 'update',
      'visibleRoot' => 'list',
    ),
    'B2bRoleRoleVisibility' => 
    array (
      'index' => 'detail',
      'grid' => 'assign',
      'assign' => 'assign',
      'tree' => 'detail',
    ),
    'B2bContactRole' => 
    array (
      'index' => 'detail',
      'grid' => 'detail',
      'assign' => 'assign',
      'tree' => 'detail',
    ),
    'B2bContactRoleVisibility' => 
    array (
      'index' => 'detail',
      'grid' => 'detail',
      'assign' => 'assign',
      'tree' => 'detail',
    ),
  ),
  'route' => 
  array (
    'B2bRoleRoute' => 
    array (
      'index' => 'detail',
      'grid' => 'detail',
      'assign' => 'assign',
      'allowAll' => 'assign',
      'denyAll' => 'assign',
      'assignComponent' => 'assign',
    ),
    'B2bContactRoute' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'assign' => 'assign',
      'allowAll' => 'assign',
      'denyAll' => 'assign',
      'assignComponent' => 'assign',
    ),
  ),
  'contingent' => 
  array (
    'B2bContingentGroup' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'create' => 'create',
      'update' => 'update',
      'remove' => 'delete',
      'new' => 'create',
      'detail' => 'detail',
      'edit' => 'detail',
    ),
    'B2bContactContingent' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'assign' => 'assign',
    ),
    'B2bRoleContingentGroup' => 
    array (
      'index' => 'detail',
      'grid' => 'detail',
      'assign' => 'assign',
    ),
  ),
  'contingentrule' => 
  array (
    'B2bContingentRule' => 
    array (
      'grid' => 'list',
      'detail' => 'detail',
      'update' => 'update',
      'remove' => 'delete',
      'new' => 'create',
      'create' => 'create',
      'tr' => 'detail',
    ),
    'B2bContingentRestriction' => 
    array (
      'grid' => 'list',
      'detail' => 'detail',
      'update' => 'update',
      'remove' => 'delete',
      'new' => 'create',
      'create' => 'create',
    ),
    'B2bContingentRuleOrderItemQuantity' => 
    array (
      'new' => 'detail',
      'edit' => 'detail',
    ),
    'B2bContingentRuleOrderAmount' => 
    array (
      'new' => 'detail',
      'edit' => 'detail',
    ),
    'B2bContingentRuleOrderQuantity' => 
    array (
      'new' => 'detail',
      'edit' => 'detail',
    ),
    'B2bContingentRuleCategory' => 
    array (
      'new' => 'detail',
      'edit' => 'detail',
    ),
    'B2bContingentRuleProductPrice' => 
    array (
      'new' => 'detail',
      'edit' => 'detail',
    ),
    'B2bContingentRuleProductOrderNumber' => 
    array (
      'new' => 'detail',
      'edit' => 'detail',
    ),
  ),
  'order' => 
  array (
    'B2bOrder' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'detail' => 'detail',
      'overview' => 'detail',
      'log' => 'detail',
    ),
    'B2bOrderClearance' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'accept' => 'create',
      'decline' => 'update',
      'remove' => 'delete',
      'acceptOrder' => 'create',
      'declineOrder' => 'update',
      'detail' => 'detail',
      'stopAcceptance' => 'create',
    ),
    'B2bOrderLineItemReference' => 
    array (
      'masterData' => 'detail',
      'list' => 'detail',
      'saveComment' => 'update',
      'updateMasterData' => 'update',
      'updateOrderReference' => 'update',
      'updateLineItem' => 'update',
      'removeLineItem' => 'update',
      'new' => 'update',
      'create' => 'update',
    ),
    'B2bStatistic' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'chartData' => 'list',
      'chart' => 'list',
      'exportCsv' => 'list',
      'exportXls' => 'list',
    ),
  ),
  'orderlist' => 
  array (
    'B2bOrderList' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'produceCart' => 'list',
      'new' => 'create',
      'create' => 'create',
      'createAjax' => 'create',
      'duplicate' => 'create',
      'detail' => 'detail',
      'edit' => 'detail',
      'update' => 'update',
      'remove' => 'delete',
    ),
    'B2bOrderOrderList' => 
    array (
      'createNewOrderList' => 'create',
    ),
    'B2bOrderListRemote' => 
    array (
      'remoteList' => 'update',
      'remoteListCart' => 'update',
      'addListThroughCart' => 'update',
      'processAddProductsToOrderList' => 'update',
    ),
    'B2bOrderListLineItemReference' => 
    array (
      'index' => 'detail',
      'new' => 'update',
      'create' => 'update',
      'update' => 'update',
      'remove' => 'delete',
      'sort' => 'update',
    ),
    'B2bContactOrderList' => 
    array (
      'index' => 'detail',
      'grid' => 'detail',
      'assign' => 'assign',
    ),
    'B2bRoleOrderList' => 
    array (
      'index' => 'detail',
      'grid' => 'detail',
      'assign' => 'assign',
    ),
  ),
  'fastorder' => 
  array (
    'B2bFastOrder' => 
    array (
      'index' => 'create',
      'upload' => 'create',
      'defaultList' => 'create',
      'getProductName' => 'create',
      'processUpload' => 'create',
      'processProducts' => 'create',
      'processItemsFromListing' => 'create',
    ),
    'B2bFastOrderRemote' => 
    array (
      'remoteListFastOrder' => 'create',
      'processProducts' => 'create',
      'addProductsToOrderList' => 'create',
      'addProductsToCart' => 'create',
    ),
  ),
  'budget' => 
  array (
    'B2bBudget' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'detail' => 'detail',
      'edit' => 'detail',
      'new' => 'create',
      'update' => 'update',
      'remove' => 'delete',
      'create' => 'create',
    ),
    'B2bRoleBudget' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'assign' => 'assign',
    ),
    'B2bContactBudget' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'assign' => 'assign',
    ),
  ),
  'salesRepresentative' => 
  array (
    'B2bSalesRepresentative' => 
    array (
      'index' => 'free',
      'grid' => 'free',
      'clientLogin' => 'free',
      'salesRepresentativeLogin' => 'free',
    ),
  ),
  'B2bontactPasswordActivation' => 
  array (
    'B2bContactPasswordActivation' => 
    array (
    ),
  ),
  'productSearch' => 
  array (
    'B2bProductSearch' => 
    array (
      'searchProduct' => 'free',
    ),
  ),
  'confirmModal' => 
  array (
    'B2bConfirm' => 
    array (
      'remove' => 'free',
      'error' => 'free',
      'override' => 'free',
    ),
  ),
  'offer' => 
  array (
    'B2bOfferThroughCheckout' => 
    array (
      'index' => 'create',
      'masterData' => 'create',
      'grid' => 'create',
      'getData' => 'detail',
      'sendOffer' => 'update',
      'data' => 'detail',
      'update' => 'update',
      'new' => 'update',
      'create' => 'update',
      'updateDiscount' => 'update',
      'remove' => 'delete',
      'backToCheckout' => 'delete',
    ),
    'B2bOfferLineItemReference' => 
    array (
      'index' => 'detail',
      'new' => 'update',
      'create' => 'update',
      'update' => 'update',
      'remove' => 'update',
      'grid' => 'detail',
      'updateDiscount' => 'update',
    ),
    'B2bOffer' => 
    array (
      'index' => 'list',
      'detail' => 'detail',
      'edit' => 'detail',
      'lineitemlist' => 'detail',
      'sendOffer' => 'update',
      'grid' => 'list',
      'accept' => 'update',
      'declineOffer' => 'update',
      'remove' => 'delete',
      'stopOffer' => 'update',
    ),
    'B2bCreateOfferThroughCart' => 
    array (
      'createOffer' => 'create',
    ),
    'B2bOfferLog' => 
    array (
      'log' => 'list',
      'comment' => 'list',
      'newComment' => 'list',
      'commentList' => 'list',
    ),
  ),
  'account' => 
  array (
    'B2bAccount' => 
    array (
      'index' => 'free',
      'savePassword' => 'free',
      'processUpload' => 'free',
    ),
  ),
  'ordernumber' => 
  array (
    'B2bOrderNumber' => 
    array (
      'index' => 'list',
      'grid' => 'list',
      'create' => 'create',
      'update' => 'update',
      'remove' => 'delete',
      'getProductName' => 'list',
      'exportCsv' => 'list',
      'exportXls' => 'list',
      'processUpload' => 'update',
      'upload' => 'update',
    ),
  ),
  'company' => 
  array (
    'B2bCompany' => 
    array (
      'index' => 'list',
      'defaultTab' => 'list',
    ),
  ),
  'default' => 
  array (
    'B2bEmpty' => 
    array (
      'index' => 'free',
    ),
  ),
);