<?php
/**
 * Animated text list template
 */

$item_text = $this->__loop_item( array( 'item_text' ) );
$classes[] = 'jet-animated-text__animated-text-item';
$settings = $this->get_settings_for_display();

if ( 0 == $this->__processed_index ) {
	$classes[] = 'active';
	$classes[] = 'visible';
}

$direction = $this->__loop_item( array( 'item_text_direction' ) );

$split_type = ( 'fx12' === $settings['animation_effect'] ) ? 'symbol' : $settings['split_type'];

?>
<div class="<?php echo implode( ' ', $classes ); ?>" dir="<?php echo esc_attr( $direction ); ?>">
	<?php
		echo $this->str_to_spanned_html( $item_text, $split_type );
		$classes = array();
	?>
</div>
