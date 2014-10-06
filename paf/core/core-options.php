<?php

/**
 * @package plugin-admin-framework
 */

function paf_print_option( $option_id ) {
	
	global $paf_page_options;

	$option = $paf_page_options[ $option_id ];
	$option_type = K::get_var( 'type', $option, 'text' );

	// Determine the option callback function
	$callback = 'paf_print_option_type_' . $option_type;
	if( ! function_exists( $callback ) ) {
		$callback = 'paf_print_option_type_not_implemented';
	}

	call_user_func( $callback, array( $option_id => $option ) );
}

function paf_option_return_title( $option_def ) {

	$option_id = key( $option_def );
	$option = $option_def[ $option_id ];

	$title = K::get_var( 'title', $option, $option_id );
	$subtitle = K::get_var( 'subtitle', $option );

	$return = 
		$title 
		. ( $subtitle
			? '<br /><span style="font-weight: normal; font-style: italic; font-size: .9em;">' . $subtitle . '</span>'
			: '' 
		)
	;

	return $return;
}

function paf_print_option_type_text( $option_def ) {

	$option_id = key( $option_def );
	$option = $option_def[ $option_id ];

	K::input( 'paf_' . $option_id
		, array(
			'value' => K::get_var( 'value', $option, '' ),
		)
		, array(
			'colorpicker' => K::get_var( 'colorpicker', $option, FALSE ),
			'format' => sprintf( 
				'<table class="form-table"><tbody><tr><th scope="row">%s</th><td>:input<br />%s</td></tr></tbody></table>'
				, paf_option_return_title( $option_def )
				, ''//@d( $option )
			)
		)
	);
}

function paf_print_option_type_textarea( $option_def ) {

	$option_id = key( $option_def );
	$option = $option_def[ $option_id ];

	K::textarea( 'paf_' . $option_id
		, array(
			'class' => K::get_var( 'class', $option, 'large-text' ),
		)
		, array(
			'value' => K::get_var( 'value', $option, '' ),
			'editor' => K::get_var( 'editor', $option, FALSE ),
			'editor_height' => K::get_var( 'editor_height', $option ),
			'format' => sprintf( 
				'<table class="form-table"><tbody><tr><th scope="row">%s</th><td>:textarea<br />%s</td></tr></tbody></table>'
				, paf_option_return_title( $option_def )
				, ''//@d( $option )
			),
			'media_buttons' => K::get_var( 'media_buttons', $option, TRUE ),
			'teeny' => K::get_var( 'teeny', $option ),
			'textarea_rows' => K::get_var( 'textarea_rows', $option ),
		)
	);
}

function paf_print_option_type_select( $option_def ) {

	$option_id = key( $option_def );
	$option = $option_def[ $option_id ];

	$is_radio = 'radio' === $option[ 'type'];
	$is_checkbox = 'checkbox' === $option[ 'type'];
	$is_multiple = $is_checkbox || K::get_var( 'multiple', $option );

	$options = array();
	switch ( K::get_var( 'options', $option, array() ) ) {
		case 'posts':
			$posts = query_posts( K::get_var( 'args', $option, '' ) );
			foreach ( $posts as $post ) {
				$options[ $post->ID ] = $post->post_title;
			}
			break;
		case 'terms':
			$terms = get_terms(
				K::get_var( 'taxonomies', $option, '' )
				, K::get_var( 'args', $option )
			);
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = $term->name;
			}
			break;
		default:
			$options = K::get_var( 'options', $option, array() );
			break;
	}

	// Add an empty option to prevent auto-selecting the first radio
	if( $is_radio ) {
		$options = array_merge( 
			array( '__none__' => '' )
			, $options
		);
	}

	K::select( 'paf_' . $option_id
		, array(
			'class' => 'paf-option-type-' . $option[ 'type' ],
			'data-paf-separator' => K::get_var( 'separator', $option, '<br />' ),
			'multiple' => $is_multiple,
		)
		, array(
			'options' => $options,
			'selected' => K::get_var( 'selected', $option ),
			'format' => sprintf( 
				'<table class="form-table"><tbody><tr><th scope="row">%s</th><td>:select<br />%s</td></tr></tbody></table>'
				, paf_option_return_title( $option_def )
				, ''//@d( $option )
			)
		)
	);
}

function paf_print_option_type_radio( $option_def ) {

	return paf_print_option_type_select( $option_def );
}

function paf_print_option_type_checkbox( $option_def ) {	

	return paf_print_option_type_select( $option_def );
}

function paf_print_option_type_upload( $option_def ) {

	$option_id = key( $option_def );
	$option = $option_def[ $option_id ];

	$option_html_name = 'paf_' . $option_id;

	// Output
	K::input( 'paf_' . $option_id
		, array(
			'class' => 'paf-option-type-upload',
			'value' => K::get_var( 'value', $option, '' ),
		)
		, array(
			'format' => sprintf( 
				'<table class="form-table"><tbody><tr><th scope="row">%s</th><td>:input%s<br />%s</td></tr></tbody></table>'
				, paf_option_return_title( $option_def )
				, '<a class="button">' . __( 'Select Media') . '</a>'
				, ''//@d( $option )
			)
		)
	);
}

function paf_print_option_type_not_implemented( $option_def ) {

	$option_id = key( $option_def );
	$option = $option_def[ $option_id ];

	K::input( 'paf_' . $option_id
		, array(
			'value' => K::get_var( 'value', $option, '' ),
		)
		, array(
			'format' => sprintf( 
				'<table class="form-table"><tbody><tr><th scope="row">%s</th><td>:input<br />%s<br />%s</td></tr></tbody></table>'
				, paf_option_return_title( $option_def )
				, sprintf(
					'<p class="description"><span class="dashicons dashicons-no"></span> ' . __( 'The option type <code>%s</code> is not yet implemented' ) . '</p>'
					, $option[ 'type' ]
				)
				, ''//@d( $option )
			)
		)
	);
}