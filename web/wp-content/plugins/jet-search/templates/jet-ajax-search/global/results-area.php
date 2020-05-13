<?php
/**
 * Result Area template
 */
?>

<div class="jet-ajax-search__results-area">
	<div class="jet-ajax-search__results-holder">
		<div class="jet-ajax-search__results-header">
			<?php $this->__glob_inc_if( 'results-count', array( 'show_results_counter' ) ); ?>
			<div class="jet-ajax-search__navigation-holder"></div>
		</div>
		<div class="jet-ajax-search__results-list">
			<div class="jet-ajax-search__results-list-inner"></div>
		</div>
		<div class="jet-ajax-search__results-footer">
			<?php $this->__html( 'full_results_btn_text', '<button class="jet-ajax-search__full-results">%s</button>' ); ?>
			<div class="jet-ajax-search__navigation-holder"></div>
		</div>
	</div>
	<div class="jet-ajax-search__message"></div>
	<?php include $this->__get_global_template( 'spinner' ); ?>
</div>
