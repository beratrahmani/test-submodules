{block name="backend/base/header/css"}
	{$smarty.block.parent}
	<style type="text/css">
		.sprite-bundle {
			background: url({link file="backend/_resources/images/bundle.png"}) no-repeat 0 0 !important;
		}

		.x-article-detail-window .bundle-description .x-translation-globe {
			display: block !important;
			margin: 7px 5px 0 0;
		}
	</style>
{/block}