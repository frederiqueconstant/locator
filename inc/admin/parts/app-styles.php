<?php if ( ! defined( 'WPINC' ) ) {
	die;
} ?>
<style type="text/css">
	#app .flex {
		display: flex;
		align-items: center;
	}

	#app .flex.inline {
		display: inline-flex;
	}

	#app .flex.wrap {
		flex-wrap: wrap;
	}

	#app .flex.column {
		flex-direction: column;
		align-items: stretch;
	}

	#app .flex.gap {
		gap: 20px;
	}

	#app .flex-1 {
		flex: 1;
	}

	#app table {
		border-collapse: separate;
		border-spacing: 0;
	}

	#app thead {
		position: sticky;
		top: var(--wp-admin--admin-bar--height, 100px);
		background-color: #fff;
	}

	#app thead th {
		text-align: center;
	}

	#app td {}

	#app th,
	#app td {
		vertical-align: middle;
		padding: 3px 9px;
		border: .5px solid #000;
	}

	#app input[type="text"]:focus {
		width: fit-content;
	}

	#adminmenuwrap,
	#adminmenuback {
		display: none;
	}

	#wpcontent {
		margin-left: 0;
	}

	#app input.always-show[type=checkbox]:disabled {
		opacity: 1;
		background: #fff;
		border-color: #8c8f94;
		box-shadow: inset 0 1px 2px rgb(0 0 0 / 10%);
		color: #50575e;
	}

	#app input.always-show[type=checkbox]:disabled:before {
		opacity: 1;
	}

	.selected-location {
		background-color: lightskyblue !important;
	}


	#app .app_popup {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(255, 255, 255, .75);
		z-index: 999999;
		padding: 2em;
		overflow: auto;
	}

	#app .app_popup .map {
		width: 100%;
		height: 80vh;
	}

	#app .app_popup.close-button {
		background-color: red;
		width: 45px;
		height: 45px;
		border-radius: 50%;
	}

	.selected-location,
	.sticky-left {
		position: sticky;
		left: 0px;
		background-color: #f0f0f1;
	}

	.setting-key.name {
		position: sticky;
		left: 60px;
		background-color: #f0f0f1;
	}

	.setting-key.name {
		background-color: #ffffbb;
	}

	.setting-key .flex {
		justify-content: space-between;
	}
</style>