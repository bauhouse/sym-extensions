UIControl.deploy("input[type=submit]", function(button) {
	var lang = Symphony.Language;

	DOM.Event.addListener(button, "click", function(event) {
		var withSelected = DOM.select("select[name=with-selected]");
		try {  if (withSelected[0].options[withSelected[0].selectedIndex].value == 'delete') withSelected = 'delete' } catch (e) { withSelected = ''; }
		var	temp = DOM.select("tbody input").map(function(input, position) {
			if (input.checked) {
				if (input.name.match(/^delete\[[\w_]+\]$/) || withSelected == 'delete') return input;
			}
			return false;
		});
		var inputs = [];
		for (var i = 0; i < temp.length; i++) {
			if (typeof(temp[i]) == 'object') inputs.push(temp[i]);
		}

		if (inputs.length > 0 && !confirm(lang.CONFIRM_MANY.replace("{$action}", 'delete').replace("{$count}", inputs.length))) event.preventDefault();
	});
});

// Override default Selectable, so it works like radio instead of checkbox
function Selectable(elements, callback, targets) {
	this.callback = callback;
	this.targets  = targets || /^(?:h4|td)$/i;
	this.select   = this.select.bind(this);
	this.items    = [];

	elements.forEach(function(element) {
		DOM.Event.addListener(element, "click", this.select);
	}, this);
}
Selectable.prototype.select = function(event) {
	var element = event.currentTarget,
		 movable = Orderable.CURRENT_ITEM,
		 shifted = movable && movable.element === element && movable.movement,
		 allowed = this.targets.test(event.target.nodeName);

	if (!allowed || shifted) return;

	if (DOM.hasClass("single", element) && !DOM.hasClass("selected", element)) {
		for (var i = 0; i < this.items.length; i++) {
			if (DOM.hasClass("single", this.items[i])) DOM.toggleClass("selected", this.items[i]);
		}
		this.items = [];
	}

	DOM.toggleClass("selected", element);

	return (this.items.indexOf(element) != -1)
		? this.callback(element, this.items.remove(element) & 0)
		: this.callback(element, this.items.push(element));
};
