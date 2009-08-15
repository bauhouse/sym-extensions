/*----------------------------------------------------------------------------*/
	
	function FlexDateField(field, formats) {
		var self = this;
		
		self.field = field;
		self.format = null;
		self.formats = formats;
		self.formatsList = null;
		self.options = [];
		self.optionsList = null;
		
		self.chooseFormat = function() {
			var name = $(this).text();
			
			$.each(self.formats, function(index, current) {
				if (current.name == name) {
					self.format = current;
					return false;
				}
			});
			
			if (self.format == null) return false;
			
			$.each(self.format.options, function(index, values) {
				var item = self.optionsList.append('<li></li>').find('li:last');
				
				$.each(values, function(name, value) {
					item.append('<span>' + name + '</span>');
				});
				
				self.options[index] = null;
			});
			
			self.optionsList.find('span').click(self.chooseOption);
			self.formatsList.hide();
			self.optionsList.show();
		};
		
		self.chooseOption = function() {
			var options = self.format.options[$(this).parent().prevAll().length];
			var index = $(this).parent().prevAll().length;
			var name = $(this).text();
			var complete = true;
			var content = self.format.format;
			
			$(this).addClass('active');
			
			self.options[index] = options[name];
			
			$.each(self.options, function(index, value) {
				if (value == null) {
					complete = false;
					return false;
					
				} else {
					content = content.replace('{' + (index + 1) + '}', value);
				}
			});
			
			if (complete) {
				self.field.find('input').val(content);
				
				self.formatsList.show();
				self.optionsList.hide().empty();
			}
		};
		
		self.field.append('<ul class="formats"></ul>');
		self.formatsList = self.field.find('.formats');
		self.field.append('<ul class="options"></ul>');
		self.optionsList = self.field.find('.options');
		
		// Populate formats list:
		$.each(self.formats, function(index, format) {
			self.formatsList.append('<li><span>' + format.name + '</span></li>');
		});
		
		self.formatsList.find('span').click(self.chooseFormat);
	}
	
/*----------------------------------------------------------------------------*/
	
	$(document).ready(function() {
		var formats = [
			{
				name:		'add',
				format:		'+{1} {2}',
				options:	[
					{
						'one':		'1',
						'two':		'2',
						'three':	'3',
						'four':		'4',
						'five':		'5',
						'six':		'6',
						'seven':	'7',
						'eight':	'8',
						'nine':		'9',
						'ten':		'10'
					},
					{
						'seconds':	'seconds',
						'minutes':	'minutes',
						'hours':	'hours',
						'days':		'days',
						'weeks':	'weeks',
						'months':	'months',
						'years':	'years'
					}
				]
			},
			{
				name:		'take',
				format:		'-{1} {2}',
				options:	[
					{
						'one':		'1',
						'two':		'2',
						'three':	'3',
						'four':		'4',
						'five':		'5',
						'six':		'6',
						'seven':	'7',
						'eight':	'8',
						'nine':		'9',
						'ten':		'10'
					},
					{
						'seconds':	'seconds',
						'minutes':	'minutes',
						'hours':	'hours',
						'days':		'days',
						'weeks':	'weeks',
						'months':	'months',
						'years':	'years'
					}
				]
			},
			{
				name:		'skip to',
				format:		'{1} {2}',
				options:	[
					{
						'this':			'this',
						'next':			'next',
						'last':			'last'
					},
					{
						'monday':		'monday',
						'tuesday':		'tuesday',
						'wednesday':	'wednesday',
						'thursday':		'thursday',
						'friday':		'friday',
						'saturday':		'saturday',
						'sunday':		'sunday',
						'week':			'week',
						'month':		'month',
						'year':			'year'
					}
				]
			}
		];
		
		$('.field-flexdate').each(function() {
			new FlexDateField($(this), formats);
		});
	});
	
/*----------------------------------------------------------------------------*/