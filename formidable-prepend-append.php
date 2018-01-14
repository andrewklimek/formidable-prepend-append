<?php
/*
Plugin Name: Formidable Prepend / Append
Plugin URI:  https://github.com/andrewklimek/formidable-prepend-append
Description: the Prepend / Append functionality of the Bootstrap Formidable Forms add-on, without the Bootstrap bloat
Version:     1.0
Author:      Andrew J Klimek
Author URI:  https://github.com/andrewklimek/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Formidable Prepend / Append is free software: you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by the Free 
Software Foundation, either version 2 of the License, or any later version.

Formidable Prepend / Append is distributed in the hope that it will be useful, but without 
any warranty; without even the implied warranty of merchantability or fitness for a 
particular purpose. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
Formidable Prepend / Append. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
	
	
add_action('frm_field_options_form', 'mnml_frm_pend_field_options', 10, 3);
add_filter('frm_default_field_opts', 'mnml_frm_pend_default_field_opts', 10, 3);
// add_filter('frm_checkbox_class', 'mnml_frm_pend_inline_class', 10, 2);
// add_filter('frm_radio_class', 'mnml_frm_pend_inline_class', 10, 2);
add_filter('frm_before_replace_shortcodes', 'mnml_frm_pend_field_html', 30, 2);
// add_action('wp_enqueue_scripts', 'mnml_frm_pend_register_style');
add_action('frm_enqueue_form_scripts', 'mnml_frm_pend_enqueue_style');


function mnml_frm_pend_field_options($field, $display, $values){
	$default = array('prepend' => '', 'append' => '');
	if(empty($field['btsp']) || !is_array($field['btsp'])){
		$field['btsp'] = $default;
	}else{
		foreach($default as $k => $v){
			if(!isset($field['btsp'][$k]))
				$field['btsp'][$k] = $v;
			unset($k);
			unset($v);
		}
	}

	if ( in_array( $display['type'], array( 'phone', 'number', 'text', 'email', 'url', 'date', 'image', 'tag', 'password' ) ) || ( isset( $field['data_type'] ) && $field['data_type'] === 'text' ) )
	{
		?>
		<tr><td><label>Prepend and Append</label></td>
			<td>Include before input:
				<input type="text" name="field_options[btsp_<?php echo $field['id'] ?>][prepend]" value="<?php echo esc_attr($field['btsp']['prepend']) ?>" size="3">
				Include after input:
				<input type="text" name="field_options[btsp_<?php echo $field['id'] ?>][append]" value="<?php echo esc_attr($field['btsp']['append']) ?>" size="3">
			</td>
		</tr>
		<?php
	}
}

// Doesn't save field settings wihtout this, not sure why though
function mnml_frm_pend_default_field_opts($opts, $values, $field){
	$opts['btsp'] = '';
	return $opts;
}


function mnml_frm_pend_register_style() {
		
	wp_register_style('bootstrap', 'some.css', array(), null);
		
}

function mnml_frm_pend_enqueue_style() {
	// wp_enqueue_style('bootstrap');
	$custom_css = "
	.input-group {
        position: relative;
        display: table;
        border-collapse: separate;
    }
	.input-group-addon {
		line-height: 1;
	    color: #555;
	    text-align: center;
	    background: #eee;
	    width: 1%;
	    white-space: nowrap;
	    vertical-align: middle;
	    display: table-cell;
	}
	.frm_forms .frm_form_field .input-group > :not(:first-child) {
	    border-top-left-radius: 0 !important;
	    border-bottom-left-radius: 0 !important;
	}
	.frm_forms .frm_form_field .input-group > :not(:last-child) {
	    border-right: 0 !important;
		border-top-right-radius: 0 !important;
		border-bottom-right-radius: 0 !important;
	}
	";
	echo "<style>{$custom_css}</style>";
// 	wp_add_inline_style( 'formidable-css', $custom_css );
}
	
function mnml_frm_pend_field_html($html, $field){
    
	if ( ! empty($field['btsp']) && is_array($field['btsp']) ) {

		if ( ! empty($field['btsp']['prepend']) || ! empty($field['btsp']['append']) ) {
		    
		    $found = 0;
		    $html = str_replace( '[input]', '<div class="input-group">[input]</div>', $html, $found );
		    
		    if ( $found )// we can do this without regex most of the time
		    {
		        // handle append
		        if ( ! empty($field['btsp']['append']) )
    			{
		            $html = str_replace( '[input]', '[input] <span class="input-group-addon frm_form_fields_style">' . $field['btsp']['append'] .'</span>', $html );
    			}
		    }
		    else// didn’t find '[input]' (with no params), so need regex
		    {
		        error_log("Didn’t find '[input]' in:\n $html");
    			preg_match_all( "/\[(input)\b(.*?)(?:(\/))?\]/s", $html, $matches, PREG_PATTERN_ORDER);
    			foreach ( $matches[0] as $match_key => $val )
    			{
    				$html = str_replace($val, '<div class="input-group">'. $val .'</div>', $html);
    			}
                // handle append
    			if ( ! empty($field['btsp']['append']) )
    			{
    				preg_match_all( '/\[input\b(.*?)(?:(\/))?\]/s', $html, $matches, PREG_PATTERN_ORDER );
    				$input = '[input]';
    				if ( isset( $matches[0] ) && isset( $matches[0][0] ) )
    				{
    					$input = $matches[0][0];
    				}
    				$html = str_replace( $input, $input . ' <span class="input-group-addon frm_form_fields_style">' . $field['btsp']['append'] .'</span>', $html );
    			}
		    }
		    
		    // prepend doesnt need regex either way
		    if ( ! empty($field['btsp']['prepend']) )
		    {
				$html = str_replace('[input', '<span class="input-group-addon frm_form_fields_style">'. $field['btsp']['prepend'] .'</span> [input', $html);
			}
		}
	}

	return $html;
}
	
// prob dont need
function mnml_frm_pend_inline_class($class, $field){
	$type = $field['type'];

	if ( $field['type'] == 'data' ) {
		$type = $field['data_type'];
	} else if ( $field['type'] == 'lookup' ) {
		$type = $field['data_type'];
	}

	if(isset($field['align']) and $field['align'] == 'inline')
		$class .= ' '. $type .'-inline';

	$class .= ' '. $type;

	return $class;
}
