jQuery(document).ready(function($){
	// make sure acf is loaded, it should be, but just in case
	if (typeof acf == 'undefined') { return; }

	var acf_field_taxonomy_dropdown = acf.getField('field_602ea483a67f3');

	acf_field_taxonomy_dropdown.on("change", function(e){
		load_taxonomy_terms();
	});

	// load on page load 
	load_taxonomy_terms();

});

function load_taxonomy_terms()
{

	// remove all previous repeaters..

	jQuery('div[data-key="field_602ea4a3a67f4"] tr a[data-event="remove-row"]').not('div[data-key="field_602ea4a3a67f4"] tr.acf-clone a[data-event="remove-row"]').trigger('click');
	
	jQuery(".acf-tooltip").each(function(){
		jQuery(this).find('a').eq(0).click();
	});


	var taxonomy_name = acf.getField('field_602ea483a67f3').val();

	if( !taxonomy_name){ return; }


	var data = {'action':'load_taxonomy_terms','taxonomy_name':taxonomy_name};

	jQuery.ajax({
		url:		acf.get('ajaxurl'),
		data:		data,
		type:		'post',
		dataType:	'json',
		async: true,
		success: function(response){

			if( response.status == true )
			{
				var taxonomy_terms = response.data.taxonomy_terms; 
				// perform repeater code by jquery
				for(var i=0;i<taxonomy_terms.length;i++)
				{
					jQuery('[data-key="field_602ea4a3a67f4"] .acf-input .acf-actions [data-event="add-row"]').trigger('click');
				}

				var feature_list = jQuery('div[data-key="field_602ea4a3a67f4"] tr.acf-row').not('div[data-key="field_602ea4a3a67f4"] tr.acf-clone');

				for(var i=0;i<taxonomy_terms.length;i++)
				{
					var id = feature_list[i].getAttribute('data-id');
					jQuery('div[data-key="field_602ea4a3a67f4"] tr[data-id="'+id+'"] td[data-key="field_602ea4b1a67f5"] input').val(taxonomy_terms[i].term_name);
					jQuery('div[data-key="field_602ea4a3a67f4"] tr[data-id="'+id+'"] td[data-key="field_602f91cc36144"] input').val(taxonomy_terms[i].term_id);
				}
				

			}
		}
	});
}