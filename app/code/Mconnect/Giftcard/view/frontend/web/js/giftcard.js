require([
		'jquery'
	],
	function($){
		$('#product_gc_amount').change(function(){
			var amount = $(this).val();
			if(amount == "custom"){
				$('#product_gc_custom_amount').show();
			}else {
				$('#product_gc_custom_amount').hide();
			}
		});
});