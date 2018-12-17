{include file="string:{config name=emailheaderplain}"}

Ihr Budget hat {$budget.notifyAuthorPercentage}% erreicht.

{$budgetStatus.usedBudget|currency} von {$budgetStatus.availableBudget|currency} sind bereits verbraucht worden.

{include file="string:{config name=emailfooterplain}"}