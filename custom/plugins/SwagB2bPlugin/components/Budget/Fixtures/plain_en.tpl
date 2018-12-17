{include file="string:{config name=emailheaderplain}"}

The Budget reached {$budget.notifyAuthorPercentage}%.

Already {$budgetStatus.usedBudget|currency} from {$budgetStatus.availableBudget|currency} currently used.

{include file="string:{config name=emailfooterplain}"}