/**
 * jtl_debug js
 *
 * @package     jtl_debug
 * @version     100
 * @createdAt   18.11.14
 * @author      Felix Moche <felix.moche@jtl-software.com>
 */
$(function () {
	if (typeof jtl_debug === 'undefined') {
		return;
	}
	var debugOpen = false,
		jtlSetDebugWindowVisible,
		jtlToggleDebugWindowVisible;

	/**
	 * @param visible
	 */
	jtlSetDebugWindowVisible = function (visible) {
		debugOpen = visible;
		if (debugOpen) {
			$('body').addClass('jtl-debug-open');
			$('#jtl-debug-content').find('#jtl-debug-searchbox').focus();
		} else {
			$('body').removeClass('jtl-debug-open');
			$('#jtl-debug-searchbox').blur();
		}
	};

	/**
	 *
	 */
	jtlToggleDebugWindowVisible = function () {
		jtlSetDebugWindowVisible(!debugOpen);
	};

	/**
	 * @param element
	 * @param event
	 */
	jtl_debug.selectPath = function (element, event) {
		var doc = document,
			range,
			selection;
		if (doc.body.createTextRange) {
			range = document.body.createTextRange();
			range.moveToElementText(element);
			range.select();
		} else if (window.getSelection) {
			selection = window.getSelection();
			range = document.createRange();
			range.selectNodeContents(element);
			selection.removeAllRanges();
			selection.addRange(range);
		}
		if (event) {
			event.cancelBubble = true;
		}
	};

    jtl_debug.copy2clipboard = function (element, event)  {
        if (document.queryCommandSupported("copy")) {
            var temp = $("<input>");
            var currentColor = $(element).css("color");
            $("body").append(temp);
            temp.val($(element).text()).select();
            document.execCommand("copy");
            $(element).css("color","#777");
            setTimeout(function(){$(element).css("color", currentColor)}, 500);
            temp.remove();
            if (event) {
                event.cancelBubble = true;
            }
        } else {
            jtl_debug.selectPath(this, event);
        }
    }

	$(function () {
		$('#jtl-debug-content-toggle').click(function () {
			jtlToggleDebugWindowVisible();
		});

		//select the path
		$('.jtl-debug-heading').click(function () {
			$(this).find('.jtl-debug-path').selectText();
		});

		$('#jtl-debug-show').click(function (evt) {
			evt.preventDefault();
			jtlSetDebugWindowVisible(true);
			$('#jtl-debug-searchbox').focus().select();
			return false;
		});

		$('#jtl-debug-hide').click(function (evt) {
			evt.preventDefault();
			jtlSetDebugWindowVisible(false);
            return false;
		});

		//listen to key press to open debug window
		$(document).bind('keydown', function (evt) {
			//CTRL + ENTER => show
			if (evt.ctrlKey && (evt.which === 13)) {
				evt.preventDefault();
				evt.stopPropagation();
				evt.handled = true;
				jtlSetDebugWindowVisible(true);
				$('#jtl-debug-searchbox').focus().select();
			}
			//ESC => close
			if (evt.which === 27) {
				evt.preventDefault();
				jtlSetDebugWindowVisible(false);
			}
		});

		var secIdx = 0,     //a continous index to identify sections
			sections = {},  //a collection of all sections (key: sectionIndex, value: section)
			refIdx = 0,     //a continous index to identify object references to a data node
			refMap = {};    //a map storing a referece to every single data node

		/**
		 * make the debug objects usable
		 * @param node
		 * @returns {*}
		 */
		var transformDebugOutput = function (node) {
			//add a reference id to every node
			var nodeIdx = refIdx++;
			node.idx = nodeIdx;
			refMap[nodeIdx] = node;
			//initialize the filter array
			node.filter = [];
			if (typeof node.children === 'object') {
				//set expanded state
				node.expanded = node.type === 'section';
				//transform any child nodes
				for (var key in node.children) {
					transformDebugOutput(node.children[key]);
				}
			}
			return node;
		};

		/**
		 * sanitize a value with html content, so the output does not render as HTML in the browser but as the actual code
		 * @param value
		 * @returns {*}
		 */
		var sanitize = function (value) {
			if (typeof value === 'string') {
				return $('<div/>').text(value).html();
			}
			return value;
		};

		/**
		 * count the number of children of a node
		 * @param node
		 * @returns {number}
		 */
		var countChildren = function (node) {
			if (typeof node.children !== 'object') {
				return 0;
			}
			var count = 0,
				childIdx;
			for (childIdx in node.children) {
				count++;
			}
			return count;
		};

		/**
		 * create a html node out of a data node
		 * @param node
		 * @param secIdx
		 * @returns {string}
		 */
		var createNode = function (node, secIdx) {
			var jNode = '',
				childName;
			//do not render a not if it is filtered out
			if (node.filter.indexOf('found-nothing') !== -1 && node.filter.indexOf('found-parent') === -1) {
				return '';
			}
			if (node.type === 'section') {
				//render section
				jNode =
					'<div class="jtl-debug-section section-idx-' + secIdx + '">' +
					'<div class="jtl-debug-section-heading" onclick="jtl_debug.toggleExpanded(' + node.idx + ',' + secIdx + ');">' +
					node.name +
					(countChildren(node) > 0 ?
						'<span class="' + (node.expanded ? 'toggle minus' : 'toggle') + '">' + (node.expanded ? '&#45;' : '&#43;') + '</span>' :
							''
					) +
					'</div>' +
					'<div class="jtl-debug-section-content">';
				for (childName in node.children) {
					if (node.expanded === true) {
						jNode += createNode(node.children[childName], secIdx);
					}
				}
				jNode += '</div></div>';
			} else if (node.type === 'object' || node.type === 'array' || node.type === 'assoc_array') {
				//render objects or arrays
				jNode =
					'<div class="jtl-debug-details expandable' + (node.expanded ? ' click-parent' : '') + '">' +
					'<span class="jtl-debug-heading jtl-debug-attribute expandable" onclick="jtl_debug.toggleExpanded(' + node.idx + ',' + secIdx + ');">' +
					'<span class="key' + (node.filter.indexOf('found-key') !== -1 ? ' jtl-found-element' : '') + '">' + node.key + '</span>' +
					' : ' +
					'<span class="type">' + node.type + (node.type === 'array' || node.type === 'assoc_array' ? ' (' + node.length + ')' : '') + '</span>' +
                    '<span class="jtl-debug-path' + (node.filter.indexOf('found-path') !== -1 ? ' jtl-found-element' : '') + '" title="copy to clipboard" onclick="jtl_debug.copy2clipboard(this, event);">' + node.path + '</span>' +
					(node.filter.indexOf('found-parent') !== -1 ? '<span class="jtl-show-more-button" title="alle anzeigen" data-nodeIdx=' + node.idx + ' data-secidx=' + secIdx + '>...</span>' : '') +
					'</span>' +
					'<div class="jtl-debug-wrapper click-parent">';
				for (childName in node.children) {
					if (node.expanded === true) {
						jNode += createNode(node.children[childName], secIdx);
					}
				}
				jNode += '</div></div>';
			} else {
				//render literals
				jNode =
					'<div class="jtl-deubg-details">' +
					'<span class="jtl-debug-heading jtl-debug-attribute">' +
					'<span class="key' + (node.filter.indexOf('found-key') !== -1 ? ' jtl-found-element' : '') + '">' + node.key + '</span>' +
					' : ' +
					'<span class="value ' + node.type + (node.filter.indexOf('found-value') !== -1 ? ' jtl-found-element' : '') + '">' + ((node.type === 'string' && node.value.length > 500) ? ((sanitize(node.value)).substr(0, 500) + '[...]') : sanitize(node.value)) + '</span>' +
					'<span class="jtl-debug-path' + (node.filter.indexOf('found-path') !== -1 ? ' jtl-found-element' : '') + '" title="copy to clipboard" onclick="jtl_debug.copy2clipboard(this);">' + node.path + '</span>' +
					'</span>' +
					'</div>';
			}
			return jNode;
		};

		/**
		 * toggle the expanded state of a data node
		 * @param nodeIdx
		 * @param secIdx
		 */
		jtl_debug.toggleExpanded = function (nodeIdx, secIdx) {
			var node = refMap[nodeIdx],
				childName,
				child,
				foundNothingIdx;
			if (typeof node === 'object') {
				node.expanded = !node.expanded;
				if (typeof node.children === 'object' && node.filter.indexOf('found-parent') === -1) {
					for (childName in node.children) {
						child = node.children[childName];
						foundNothingIdx = child.filter.indexOf('found-nothing');
						if (foundNothingIdx !== -1) {
							child.filter.splice(foundNothingIdx, 1);
						}
					}
				}
				renderSection(sections[secIdx]);
			}
		};

		/**
		 * collapse all data nodes
		 * @param node
		 */
		var collapseAll = function (node) {
			var key;
			if (typeof node.expanded !== 'undefined') {
				node.expanded = false;
			}
			if (typeof node.children === 'object') {
				for (key in node.children) {
					collapseAll(node.children[key]);
				}
			}
		};

		/**
		 * reset the filter on all data nodes
		 * @param node
		 */
		var resetFilter = function (node) {
			var key;
			node.filter = [];
			if (typeof node.expanded !== 'undefined') {
				node.expanded = node.type === 'section';
			}
			if (typeof node.children === 'object') {
				for (key in node.children) {
					resetFilter(node.children[key]);
				}
			}
		};

		/**
		 * make all child nodes visible
		 * @param nodeIdx
		 * @param secIdx
		 */
		var showAllChildren = function (nodeIdx, secIdx) {
			var node = refMap[nodeIdx],
				foundParentIdx = node.filter.indexOf('found-parent'),
				child,
				foundNothingIdx;
			if (foundParentIdx !== -1) {
				node.filter.splice(foundParentIdx, 1);
			}
			foundNothingIdx = node.filter.indexOf('found-nothing');
			if (foundNothingIdx !== -1) {
				node.filter.splice(foundNothingIdx, 1);
			}
			if (typeof node.children === 'object') {
				for (var childName in node.children) {
					child = node.children[childName];
					foundNothingIdx = child.filter.indexOf('found-nothing');
					if (foundNothingIdx !== -1) {
						child.filter.splice(foundNothingIdx, 1);
					}
				}
				renderSection(sections[secIdx]);
			}
		};

		/**
		 * adds filters to a node
		 * @param term
		 * @param node
		 * @returns {Array}
		 */
		var filterNode = function (term, node) {
			var filter = [],
				key = ('' + node.key + '').toLowerCase(),
				value = ('' + node.value + '').toLowerCase(),
				path = (typeof node.path !== 'undefined' && node.path !== null) ? node.path.toLowerCase() : 'ERROR';
			term = term.toLowerCase();

			if (term.indexOf('"') === 0 && term.lastIndexOf('"') === (term.length - 1)) {
				//exact search
				term = term.substr(1, term.length - 2);
				if (key === term) {
					filter.push('found-key');
				}
				if (value === term) {
					filter.push('found-value');
				}
			} else if (term.indexOf('$') === 0 || term.indexOf('>') === 0) {
				//search in path
				term = term.substr(1);
				if (path.indexOf(term) !== -1) {
					filter.push('found-path');
				}
			} else if (term.indexOf('=') === 0) {
				//search in value
				if (term.indexOf('"') === 1 && term.lastIndexOf('"') === term.length - 1) {
					//exact search in value
					term = term.substr(2, term.length - 3);
					if (value === term) {
						filter.push('found-value');
					}
				} else {
					term = term.substr(1);
					if (value.indexOf(term) !== -1) {
						filter.push('found-value');
					}
				}
			} else {
				//search in key and value
				if (key.indexOf(term) !== -1) {
					filter.push('found-key');
				}
				if (value.indexOf(term) !== -1) {
					filter.push('found-value');
				}
			}
			return filter;
		};

		/**
		 * filter nodes recursively with a search term
		 * @param term
		 * @param node
		 * @param prevNodes
		 */
		var filter = function (term, node, prevNodes) {
			var found,
				i,
				myNodes,
				childName;
			node.filter = [];
			if (typeof node.expanded !== 'undefined') {
				node.expanded = false;
			}
			if (typeof prevNodes === 'undefined') {
				prevNodes = [];
			}

			//search the node
			node.filter = filterNode(term, node);
			found = node.filter.length > 0;

			//if we've found something we have to update the list of parent nodes to let them know they are in a branch that has a hit
			if (found === true) {
				window.jtl_debug.results++;
				for (i = 0; i < prevNodes.length; i++) {
					if (prevNodes[i].filter.indexOf('found-parent') === -1) {
						prevNodes[i].filter.push('found-parent');
					}
					if (typeof prevNodes[i].expanded !== 'undefined') {
						prevNodes[i].expanded = true;
					}
				}
			} else {
				node.filter.push('found-nothing');
			}

			if (typeof node.children === 'object') {
				//add this nodes to the previous nodes collection to pass them to the children nodes
				myNodes = [];
				for (i = 0; i < prevNodes.length; i++) {
					myNodes.push(prevNodes[i]);
				}
				myNodes.push(node);
				for (childName in node.children) {
					filter(term, node.children[childName], myNodes);
				}
			}
		};

		/**
		 * prepare sections stored in jtl_debug_vars for later use
		 */
		var prepareSections = function () {
			// a request to a store url like this: "?jtl-debug=1&jtl-get-session=1" will get the debug output as JSON
			var data = {},
				sectionName,
				idx,
				content,
				url,
				tmp = window.location.href.split('/');
			url = (tmp[tmp.length-1].indexOf('.php') !== -1) ?
				window.location.href.replace(tmp[tmp.length-1], '') :
				window.location.href;
			data[window.jtl_debug.enableSmartyDebugParam] = 1;
			data[window.jtl_debug.getDebugSessionParam] = 1;
			data.isAjax = true;
			$.ajax({
				url: url,
				data: data,
				dataType: 'json',
				type: 'GET',
				success: function(res) {
					window.jtl_debug.jtl_debug_vars = res;
					for (sectionName in jtl_debug.jtl_debug_vars) {
						idx = secIdx++;
						content = transformDebugOutput(jtl_debug.jtl_debug_vars[sectionName]);
						sections[idx] = {
							idx: idx,
							content: content,
							name: sectionName
						}
					}
					$('#jtl-debug-info-area').html('');
					renderSections();
				},
				error: function(err) {
					console.error('Error: ', err);
					$('#jtl-debug-info-area').html('Error fetching debug objects: ' + JSON.stringify(err));
				}
			});

		};

		/**
		 * render a whole section, add it to the dom
		 * @param section
		 */
		var renderSection = function (section) {
			var jTree = createNode(section.content, section.idx),
				jNode;
			section = $('#jtl-debug-content > .section-idx-' + section.idx);
			if (section.length === 0) {
				$('#jtl-debug-content').append(jTree);
			} else {
				section.empty().append(jTree);
			}
			$('.jtl-show-more-button').click(function (evt) {
				evt.stopPropagation();
				jNode = $(this);
				showAllChildren(jNode.attr('data-nodeIdx'), jNode.attr('data-secidx'));
			});
		};

		/**
		 * render all sections
		 */
		var renderSections = function () {
			var idx;
			for (idx in sections) {
				renderSection(sections[idx]);
			}
		};

		/**
		 * execute the filter when a search term is applied
		 */
		var registerSearchHandler = function () {
			$('#jtl-debug-searchbox').bind('keydown', function (e) {
				var term,
					idx;
				if (e.which === 13 && !e.ctrlKey) {
					term = $('#jtl-debug-searchbox').val();
					if (term.trim().length === 0) {
						//search term is empty, reset all filters
						for (idx in sections) {
							resetFilter(sections[idx].content);
						}
						$('#jtl-debug-search-results').text('');
					} else {
						window.jtl_debug.results = 0;
						for (idx in sections) {
							filter(term, sections[idx].content, []);
						}
						$('#jtl-debug-search-results').text(window.jtl_debug.results + ' ' + jtl_debug.jtl_lang_var_search_results);
					}
					renderSections();
				}
			});
		};
		prepareSections();
		registerSearchHandler();
	});
});
