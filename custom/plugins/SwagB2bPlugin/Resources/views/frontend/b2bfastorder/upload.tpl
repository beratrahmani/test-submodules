{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="panel has--border is--rounded">
    <div class="panel--title is--underline">

        <div class="block-group b2b--block-panel">
            <div class="block block--title">
                <h3>{s name="FastOrderFileUpload"}Fast order file upload{/s}</h3>
            </div>
            <div class="block block--actions">
                CSV, XLS, XLSX
            </div>
        </div>
    </div>
    <div class="panel--body is--wide">
        <form class="form--upload b2b--upload-form" method="post" action="{url action=processUpload}" enctype="multipart/form-data" data-url="{url action=processUpload}" data-target-panel-id="fast-order-grid">
            <div class="upload--input">

                {* Drag 'n Drop Upload *}
                <label for="file">
                    <h3 class="box--dragndrop">{s name="ChooseFile"}Choose a file{/s}</h3>
                    <span class="box--dragndrop"> {s name="DragFileHere"}or drag the file from your desktop here{/s}</span>
                </label>

                {* Fallback File Picker *}
                <input class="input--file" type="file" name="file" id="file" />
            </div>
        </form>
    </div>
    <div class="panel--body is--wide csv--configuration">
        <div class="configuration--header">
            <span>{s name="ExtendedConfiguration"}Extended configuration{/s}</span>
        </div>
        <div class="form--additional-inputs block-group configuration--content">
            <div class="block block-order-number-column">
                <h3><label for="orderNumberColumn">{s name="fileNumberColumn"}Number column{/s}:</label></h3>
                <input type="number" name="orderNumberColumn" id="orderNumberColumn" value=0>
            </div>
            <div class="block block-quantity-column">
                <h3><label for="quantityColumn">{s name="fileQuantityColumn"}Quantity column{/s}:</label></h3>
                <input type="number" min="1" name="quantityColumn" id="quantityColumn" value=1>
            </div>
            <div class="block block-csv-delimiter">
                <h3><label for="csvDelimiter">{s name="fileDelimiter"}Delimiter{/s}:</label></h3>
                <input type="text" id="csvDelimiter" name="csvDelimiter" value="," placeholder="{s name="fileDelimiter"}Delimiter{/s}">
            </div>
            <div class="block block-csv-enclosure">
                <h3><label for="csvEnclosure">{s name="fileEnclosure"}Enclosure{/s}:</label></h3>
                <input type="text" name="csvEnclosure" id="csvEnclosure" placeholder="{s name="fileEnclosure"}Enclosure{/s}">
            </div>
            <div class="additional-inputs--headline">
                <input type="checkbox" id="headline" name="headline">
                <h3><label for="headline">{s name="fileHeadline"}The file includes a headline{/s}</label></h3>
            </div>
        </div>
    </div>
</div>