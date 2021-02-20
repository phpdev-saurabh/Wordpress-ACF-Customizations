<?php

// Incude this file in function.php 

new all_taxonomy_terms_extension();
	
class all_taxonomy_terms_extension {
	public function __construct() {
		
		if ( ! session_id() ) {
				session_start();
			}
		
		
		// enqueue js extension for acf
		// do this when ACF in enqueuing scripts
		add_action('acf/input/admin_enqueue_scripts', array($this, 'enqueue_script'));
		// ajax action for loading values
		add_action('wp_ajax_load_taxonomy_terms', array($this, 'load_taxonomy_terms'));	
		
		
	} // end public function __construct
	public function load_taxonomy_terms() {
		// this is the ajax function that gets the feature values from the selected term
			
		$taxonomy_name = $_POST['taxonomy_name'] ? sanitize_text_field($_POST['taxonomy_name']) : '';

		$taxonomy = get_taxonomy($taxonomy_name);

		if( !$taxonomy )
		{
			wp_send_json([ 'status' => false , 'msg' => 'Taxonomy not found ==> '.$taxonomy_name ]);
		}

		$taxonomy_name = $taxonomy->name; 

		$terms = get_terms($taxonomy_name, array(
		    'hide_empty' => false,
		));

		$output['taxonomy_name'] = $taxonomy_name;

		$term_names = [];

		if( count($terms) )
		{
			foreach($terms as $key=>$term)
			{
				$term_names[] = [
					'term_name' => $term->name,
					'term_id' => $term->term_id
				];
			}
		}
		
		$output['taxonomy_terms'] = $term_names;

		wp_send_json([ 'status' => true , 'data' => $output ]);


	}// end public function load_taxonomy_terms
	
	public function enqueue_script() {
		// enqueue acf extenstion
		
		// only enqueue the script on the post page where it needs to run
		/* *** THIS IS IMPORTANT
		       ACF uses the same scripts as well as the same field identification
		       markup (the data-key attribute) if the ACF field group editor
		       because of this, if you load and run your custom javascript on
		       the field group editor page it can have unintended side effects
		       on this page. It is important to alway make sure you're only
		       loading scripts where you need them.
		*/


		// the handle should be changed to your own unique handle
		$handle = 'all_taxonomy_terms_extension';
		
		// I'm using this method to set the src because
		// I don't know where this file will be located
		// you should alter this to use the correct fundtions
		// to set the src value to point to the javascript file


						/**********  Replace with your js file path **********/

		$src = get_bloginfo('template_directory').'/acf-custom/taxonomy_terms_repeater.js';



		// make this script dependent on acf-input
		$depends = array('acf-input');
		
		wp_register_script($handle, $src, $depends,rand(1,1000000));
		
		// localize the script with the current post id
		// we will need the current post ID to get existing
		// values from the post
		
		// you should change this object name to something unique
		// will will also need to change this object name in the JS file
		$object = 'all_taxonomy_terms_extension_object';
		
		$data = [];
		wp_localize_script($handle, $object, $data);
		
		wp_enqueue_script($handle);
	} // end public function enqueue_script// end public function enqueue_script
	
}


add_action('acf/save_post', 'save_selected_tax_terms', 20);

function save_selected_tax_terms($post_id)
{	
	$acf_form = $GLOBALS['acf_form'];
	
	if( $acf_form['id'] != "select-tax-edit-term" )
	{
		return;
	}
	$current_post = get_post($post_id);
	
	$acf_posted_taxonomy = get_field('taxonomy',$post_id);
	$acf_posted_terms = get_field('terms',$post_id);


	$terms = get_terms( $acf_posted_taxonomy,['hide_empty' => false]);
	
	$term_ids = $posted_term_ids = $diff_arr =  [];
	
	if( count($terms) )
	{
		foreach($terms as $k=>$term)
		{
			$term_ids[] = $term->term_id;
		}
	}

	if( count($acf_posted_terms) )
	{
		foreach( $acf_posted_terms as $key=>$value )
		{
			$post_term_id = $value['term_id'] ?? '';
			$post_term_name = $value['terms'] ?? '';
		
			if( $post_term_id == 0  )
			{
				// new term insertion.
				wp_insert_term($post_term_name,$acf_posted_taxonomy);
			}else{
				// update the term.
				wp_update_term($post_term_id,$acf_posted_taxonomy,[ 'name' => $post_term_name ]);
				
				$posted_term_ids[] = $post_term_id;
			}

		}
	}

	// get array diff and delete those terms..
	
	if( count($term_ids)  > count($posted_term_ids) )
	{
		$diff_arr = array_diff($term_ids,$posted_term_ids);
	}else if( count($posted_term_ids)  > count($term_ids) ){
		$diff_arr = array_diff($posted_term_ids,$term_ids);
	}else{
		$diff_arr = array_diff($term_ids,$posted_term_ids);
	}

	
	if( count($diff_arr) )
	{
		foreach( $diff_arr as $k=>$diff_term_id)
		{
			wp_delete_term($diff_term_id,$acf_posted_taxonomy);
		}
	}
	
	// remove form post..
	wp_delete_post($post_id);



}
