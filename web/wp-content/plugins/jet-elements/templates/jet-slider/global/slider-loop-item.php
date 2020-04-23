<?php
/**
 * Slide item template
 */
$settings      = $this->get_settings_for_display();
$item_settings = $this->__processed_item;

$container_width = $settings['slider_container_width']['size'];
$container_width = ( '%' === $settings['slider_container_width']['unit'] ) ? $container_width . $settings['slider_container_width']['unit'] : $container_width;

$content_type = ! empty( $item_settings['item_content_type'] ) ? $item_settings['item_content_type'] : 'default';

$show_transition = ! empty( $settings['content_show_transition'] ) ? $settings['content_show_transition'] : 'up';

$target_primary   = $this->__loop_item( array( 'item_button_primary_target' ), ' target="%s"' );
$rel_primary      = $this->__loop_item( array( 'item_button_primary_rel' ), ' rel="%s"' );
$target_secondary = $this->__loop_item( array( 'item_button_secondary_target' ), ' target="%s"' );
$rel_secondary    = $this->__loop_item( array( 'item_button_secondary_rel' ), ' rel="%s"' );
$title_tag        = $this->__loop_item( array( 'item_title_html_tag' ), '%s' );
$subtitle_tag     = $this->__loop_item( array( 'item_subtitle_html_tag' ), '%s' );
$title_tag        = ! empty( $title_tag ) ? $title_tag : 'h5';
$subtitle_tag     = ! empty( $subtitle_tag ) ? $subtitle_tag : 'h5';
$title_format     = '<' . $title_tag . ' class="jet-slider__title">%s</' . $title_tag . '>';
$subtitle_format  = '<' . $subtitle_tag . ' class="jet-slider__subtitle">%s</' . $subtitle_tag . '>';

?><div class="jet-slider__item sp-slide"><?php
		echo $this->__loop_item_image_tag();

		if ( filter_var( $settings['thumbnails'], FILTER_VALIDATE_BOOLEAN ) ) {
			echo $this->__loop_item_image_thumb();
		}
	?><div class="jet-slider__content sp-layer" data-position="centerCenter" data-width="100%" data-height="100%" data-horizontal="0%" data-show-transition="<?php echo esc_attr( $show_transition ); ?>" data-show-duration="400" data-show-delay="400">
		<div class="jet-slider__content-item">
			<div class="jet-slider__content-inner"><?php
				switch ( $content_type ) {
					case 'default':

						echo $this->__render_icon( 'item_icon', '<div class="jet-slider__icon"><div class="jet-slider-icon-inner">%s</div></div>', '', false );
						echo $this->__loop_item( array( 'item_title' ), $title_format );
						echo $this->__loop_item( array( 'item_subtitle' ), $subtitle_format );
						echo $this->__loop_item( array( 'item_desc' ), '<div class="jet-slider__desc">%s</div>' );?>

						<div class="jet-slider__button-wrapper"><?php
							echo $this->__loop_button_item( array( 'item_button_primary_url', 'item_button_primary_text' ), '<a class="elementor-button elementor-size-md jet-slider__button jet-slider__button--primary" href="%1$s"' . $target_primary . $rel_primary . '>%2$s</a>' );
							echo $this->__loop_button_item( array( 'item_button_secondary_url', 'item_button_secondary_text' ), '<a class="elementor-button elementor-size-md jet-slider__button jet-slider__button--secondary" href="%1$s"' . $target_secondary . $rel_secondary . '>%2$s</a>' ); ?>
						</div><?php

						break;

					case 'template':
						echo $this->__loop_item_template_content();
						break;
				}
			?></div>
		</div>
	</div>
</div>

