{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid_message_grid_empty"}
    {include file="frontend/_includes/messages.tpl" type="info" content="{s name="NoResults"}There are no results yet.{/s}"}
{/block}

{block name="b2b_grid_form"}
    <div class="panel has--border is--rounded b2b--chart-filter">
        <div class="panel--title is--underline">
            <div class="block-group b2b--block-panel">
                <div class="block block--title">
                    <h3>{s name="Filter"}Filter{/s}</h3>
                </div>
            </div>
        </div>
        <div class="panel--body is--wide">

            <div class="block-group">
                <div class="block">
                    <h5 class="margin-top-none">{s name="Date"}Date{/s}</h5>

                    <div class="block-group group--date-from">
                        <div class="block">
                            {s name="From"}From{/s}:
                        </div>
                        <div class="block">
                            <input class="datepicker" data-datepicker="true" name="from" type="text" data-defaultDate="{if $from}{$from->format($dateFormat)}{else}{"- 10 days"|date_format:'Y-m-d'}{/if}"/>
                        </div>
                    </div>
                    <div class="block-group group--date-to margin-top-x-small">
                        <div class="block">
                            {s name="To"}To{/s}:
                        </div>
                        <div class="block">
                            <input class="datepicker" data-datepicker="true" name="to" type="text" data-defaultDate="{if $to}{$to->format($dateFormat)}{else}{$smarty.now|date_format:'Y-m-d'}{/if}"/>
                        </div>
                    </div>

                </div>
                <div class="block">
                    <h5 class="margin-top-none">{s name="StatisticTypes"}Statistic Types{/s}</h5>

                    <label>
                        <input type="checkbox" {if in_array('orders', $selects) || !$selects}checked="checked"{/if} name="selects[]" value="orders" />
                        {s name="OrdersCount"}Orders{/s} <br>
                    </label>
                    <label>
                        <input type="checkbox" {if in_array('orderAmount', $selects) || !$selects}checked="checked"{/if} name="selects[]" value="orderAmount" />
                        {s name="OrderAmountTotal"}Amounts{/s} <br>
                    </label>
                    <label>
                        <input type="checkbox" {if in_array('orderAmountNet', $selects) || !$selects}checked="checked"{/if} name="selects[]" value="orderAmountNet" />
                        {s name="OrderAmountWithoutTaxTotal"}Amounts net{/s} <br>
                    </label>
                    <label>
                        <input type="checkbox" {if in_array('itemCount', $selects) || !$selects}checked="checked"{/if} name="selects[]" value="itemCount" />
                        {s name="ItemsCount"}Ordered positions{/s} <br>
                    </label>
                    <label>
                        <input type="checkbox" {if in_array('itemQuantityCount', $selects) || !$selects}checked="checked"{/if} name="selects[]" value="itemQuantityCount" />
                        {s name="OrderItemQuantityCount"}Ordered positions quantity{/s} <br>
                    </label>
                </div>
                <div class="block">
                    <h5 class="margin-top-none">{s name="GroupBy"}Group by{/s}</h5>

                    <label>
                        <input type="radio" name="groupBy" value="week" {if $groupBy == 'week'}checked{/if} />
                        {s name="YEARWEEK"}Week{/s}
                    </label>
                    <br>
                    <label>
                        <input type="radio" name="groupBy" value="month" {if $groupBy == 'month'}checked{/if} />
                        {s name="MONTH"}Month{/s}
                    </label>
                    <br>
                    <label>
                        <input type="radio" name="groupBy" value="year" {if $groupBy == 'year'}checked{/if} />
                        {s name="YEAR"}Year{/s}
                    </label>
                </div>
                <div class="block">
                    {if $contacts|count > 1}
                        <h5 class="margin-top-none">{s name="ContactFilter"}Contact filter{/s}</h5>

                        <div class="select-field">
                            <select name="authId">
                                <option value="">{s name="AllContacts"}All contacts{/s}</option>
                                {foreach $contacts as $contact}
                                    <option {if $authId == $contact->authId}selected{/if} value="{$contact->authId}">{$contact->lastName}, {$contact->firstName} ({$contact->email}) </option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}

                    <h5 class="margin-top-none">{s name="RoleFilter"}Role filter{/s}</h5>

                    <div class="select-field">
                        <select name="roleId">
                            <option value="">{s name="DefaultFilterValue"}All{/s}</option>
                            {foreach $roles as $role}
                                <option {if $roleId == $role->id}selected{/if} value="{$role->id}">{$role->name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <h5 class="margin-top-none">{s name="StateFilter"}State filter{/s}</h5>

                    <div class="select-field">
                        <select name="stateId">
                            <option {if $stateId == 'all'}selected{/if} value='all'>{s name="DefaultFilterValue"}All{/s}</option>
                            {foreach $states as $state}
                                <option {if $stateId == $state.id}selected{/if} value={$state.id}>{$state.name|snippet:{$state.name}:"backend/static/order_status"}</option>
                            {/foreach}
                        </select>
                    </div>

                </div>
            </div>
            <div class="block-group group--actions text-right">
                <button type="submit" class="btn is--primary">{s name="ApplyFilters"}Apply filters{/s}</button>

                <a target="_blank"
                   href="{url action=exportCsv}{if $postParams}?{$postParams}{/if}"
                   class="btn action--export ignore--b2b-ajax-panel"
                   title="{s name="ExportOrders"}Export orders{/s} (CSV)"
                >
                    {s name="ExportOrders"}Export orders{/s} (CSV)
                </a>

                <a target="_blank"
                   href="{url action=exportXls}{if $postParams}?{$postParams}{/if}"
                   class="btn action--export ignore--b2b-ajax-panel"
                   title="{s name="ExportOrders"}Export orders{/s} (XLS)"
                >
                    {s name="ExportOrders"}Export orders{/s} (XLS)
                </a>

                <a target="_self"
                   href="{url action=index}"
                   class="btn right ignore--b2b-ajax-panel"
                   title="{s name="ResetFilter"}Reset filter{/s}"
                >
                    {s name="ResetFilter"}Reset filter{/s}
                </a>
            </div>
        </div>
    </div>
{/block}

{block name="b2b_grid_table_actions"}{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th>{s name="Date"}Date{/s}</th>
        <th>{s name="Ordernumber"}Ordernumber{/s}</th>
        <th>{s name="Customer"}Customer{/s}</th>
        <th class="is--align-right">{s name="OrderAmount"}Order Amount{/s}</th>
        <th class="is--align-center">{s name="Items"}Items{/s}</th>
        <th>{s name="Status"}Status{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    <tr class="ajax-panel-link {b2b_acl controller=b2border action=detail}" data-row-id="{$row->id}" data-target="order-detail" data-href="{url controller=b2border action=detail listId=$row->listId orderContextId=$row->orderContextId}">
        <td data-label="{s name="Date"}Date{/s}" class="is--align-left">
            {if $row->clearedAt}
                {$row->clearedAt|date:'DATE_LONG'}
            {else}
                {s name="OrderNotCleared"}Order not cleared{/s}
            {/if}
            <br>
            <small>{s name="OrderCreatedDate"}Order created{/s}: {$row->createdAt|date:'DATE_LONG'}</small>
        </td>
        <td data-label="{s name="Ordernumber"}Ordernumber{/s}">{$row->orderNumber}</td>
        <td data-label="{s name="Customer"}Customer{/s}" class="is--align-left">
            {if $row->contact}
                {$row->contact->firstName} {$row->contact->lastName}
            {else}
                {s name="ContactNotFound"}No contact found{/s}
            {/if}
        </td>
        <td data-label="{s name="OrderAmount"}Order Amount{/s}" class="is--align-right">
            {$row->amountNet|currency}
            <br>
            <small>{s name="withTax"}inkl. MwSt{/s}: {$row->amount|currency}</small>
        </td>
        <td data-label="{s name="Items"}Items{/s}" class="is--align-center">{$row->itemCount}</td>
        <td data-label="{s name="Status"}Status{/s}" class="is--align-left">{$row->status|snippet:{$row->status}:"backend/static/order_status"}</td>
    </tr>
{/block}