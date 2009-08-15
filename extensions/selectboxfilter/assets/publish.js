$(document).ready(function() {
	$('label > select[multiple = multiple]:only-child').each(function() {
		var self = this;
		
		// Initialize:
		self.select = $(self);
		self.label = self.select.parent('label');
		
		self.label
			.wrap('<div class="selectboxfilter"></div>')
			.after('<input />');
			
		self.parent = self.label.parent('div');
		self.input = self.parent.find('input');
		
		// Filtering:
		self.values = self.select.find('option');
		self.input.keyup(function() {
			var search = $(this).val().toLowerCase();
			
			if (search.length) {
				self.values.show().each(function() {
					var value = $(this).text().toLowerCase();
					
					if (value.indexOf(search) == -1) {
						$(this).hide();
					}
				});
				
			} else {
				self.values.show();
			}
		});
	});
});