/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Used to generate page tree
 */
define(['jquery', 'd3'], function ($, d3) {
	'use strict';

	// Set the dimensions and margins of the tree
	var margin = {top: 20, right: 20, bottom: 30, left: 5},
		width = 245 - margin.right - margin.left,

		barHeight = 20,
		barWidth = width + 12,

		nodeStep = 12,

		dyText = 4,
		dxText = 14,

		xRect = 7,

		polygonShape = '-5, -3 5, -3 0 5',

		iconHeight = 16,
		iconWidth = 16;

	var i = 0,
		linksCount = 0,
		duration = 400,
		root,
		rootFiltered,
		svg,
		tree,
		treeInitialized = false,
		clickedRectOnce = false,
		activeNodeClass = 'active',
		nonActiveNodeClass = 'no-active',
		hasChildrenClass = 'has-children',
		hasChildrenExpandedClass = 'has-children-exp',
		noChildrenClass = 'no-children';

	/**
	 * Local storage
	 */
	var storage,
		storageData;

	/**
	 * Filter by search word
	 */
	var lastSearchWord = '',
		searchRunning = false,
		isFilteringActive = false;

	/**
	 * Editing
	 *
	 */
	var editingOriginalNode;

	/**
	 * Dom jquery selectors
	 * @type {{}}
	 */
	var domSelectors = {
		pageTree: '#page-tree-wrapper',
		searchInput: 'input.search-page-tree',
		editInput: '#edit-page-tree-node'
	};

	/**
	 * Main method
	 *
	 * @param treeData
	 */
	function init(treeData) {
		storage = F.getStorage();
		storageData = storage.getAllData();

		_initArrayContains();
		_initSvg();

		root = d3.hierarchy(treeData); // Constructs a root node from the specified hierarchical data.
		tree = d3.tree().nodeSize([0, nodeStep]); //Invokes tree

		root.children.forEach(_collapse);

		// Initialize function,
		// Do it once after left bar open animation is complete
		F.on(F.LEFT_PANEL_TOGGLE, function (isOpen) {
			if (treeInitialized === false && isOpen) {
				_update(root);

				// Reset search input
				$(_getDomSelector('searchInput')).val('');

				treeInitialized = true;
			}
		});

		// Editing initialize
		$(_getDomSelector('editInput'))
			.on('keyup', function (e) {
				var code = (e.keyCode ? e.keyCode : e.which);

				if (code === 13) {
					_finishEditing($(this).val().trim());
				} else if (code === 27) {
					_resetEditing();
				}
			})
			.on('focusout', function () {
				var $this = $(this);
				if ($this.is(':visible')) {
					_finishEditing($this.val().trim());
				}
			});
	}

	/**
	 * Filter function
	 *
	 * @param searchWord
	 */
	function treeFilter(searchWord) {
		setTimeout(function () {
			if ($(_getDomSelector('searchInput')).val().trim() === searchWord && searchWord !== lastSearchWord) {
				lastSearchWord = searchWord;
				if (searchWord === '') {
					_resetFilter();
				} else if (searchWord.length > 2) {
					_loadFilterTree(searchWord);
				}
			}
		}, 1000);
	}

	/**
	 * Is true when search ajax in process
	 *
	 * @return {boolean}
	 */
	function isSearchRunning() {
		return searchRunning;
	}

	/**
	 * Create SVG
	 *
	 * @private
	 */
	function _initSvg() {
		svg = d3.select(_getDomSelector('pageTree')).append('svg')
			.attr('width', width + margin.right + margin.left)
			.append('g')
			.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');
	}

	/**
	 * Update tree
	 *
	 * @param source
	 * @private
	 */
	function _update(source) {
		var nodes = tree(isFilteringActive ? rootFiltered : root), //returns a single node with the properties of d3.tree()
			nodesSort = [];

		var links = nodes.descendants().slice(1),
			icons = nodes.descendants();

		d3.select('svg')
			.transition()
			.duration(duration); //transition to make svg looks smoother

		// returns all nodes and each descendant in pre-order traversal (sort)
		nodes.eachBefore(function (n) {
			nodesSort.push(n);
		});

		// Compute the "layout".
		nodesSort.forEach(function (n, i) {
			n.x = i * barHeight;
		});

		// Update the nodes…
		var node = svg.selectAll('g.node')
			.data(nodesSort, function (d) {
				return d.id || (d.id = ++i);
			}); //assigning id for each node

		var nodeEnter = node.enter().append('g')
			.attr('class', 'node')
			.attr('transform', 'translate(' + source.y + ',' + source.x + ')')
			.style('opacity', 1e-6)
			.call(function () {
				if (linksCount < links.length) {
					linksCount = links.length;
					d3.select('svg').attr('height', (links.length + 2) * barHeight);
				}
			});

		// Add triangle-polygon for the nodes
		nodeEnter.append('polygon')
			.attr('r', 1e-6)
			.attr('class', function (d) {
				return _determinateNodeClassDependingOnChildren(d);
			})
			.on('click', _clickPolygon);

		nodeEnter.append('rect')
			.attr('y', -barHeight / 2)
			.attr('x', xRect)
			.attr('height', barHeight)
			.attr('width', _barWidth)
			.attr('class', function (d) {
				return d.data.isActive ? activeNodeClass : nonActiveNodeClass;
			})
			.on('click', _clickRect);

		nodeEnter.append('text')
			.attr('dy', dyText)
			.attr('dx', dxText + iconWidth)
			.text(function (d) {
				return d.data.name;
			});

		// UPDATE
		var nodeUpdate = nodeEnter.merge(node);

		// Transition nodes to their new position.
		nodeUpdate.transition()
			.duration(duration)
			.attr('transform', function (d) {
				return 'translate(' + d.y + ',' + d.x + ')';
			})
			.style('opacity', 1);

		// Update the node attributes and style
		nodeUpdate.select('polygon')
			.attr('points', polygonShape)
			.attr('class', function (d) {
				return _determinateNodeClassDependingOnChildren(d);
			})
			.attr('cursor', 'pointer');

		// Transition exiting nodes to the parent's new position.
		var nodeExit = node.exit().transition()
			.duration(duration)
			.attr('transform', 'translate(' + source.y + ',' + source.x + ')')
			.style('opacity', 1e-6)
			.remove();

		nodeExit.on('end', function () {
			var height = d3.select('svg').attr('height');
			linksCount = links.length;
			d3.select('svg').attr('height', height - barHeight);
		});

		nodes.eachBefore(function (d) {
			d.x0 = d.x;
			d.y0 = d.y
		});

		var link = svg.selectAll('path.link')
			.data(links, function (d) {
				return d.id;
			});

		// Enter any new links at the parent's previous position.
		var linkEnter = link.enter().insert('path', 'g')
			.attr('class', 'link')
			.attr('d', function (d) {
				var o = {x: source.x0, y: source.y0};
				return _diagonal(o, o)
			});

		// UPDATE
		var linkUpdate = linkEnter.merge(link);

		// Transition back to the parent element position
		linkUpdate.transition()
			.duration(duration)
			.attr('d', function (d) {
				return _diagonal(d, d.parent)
			});

		// Remove any exiting links
		link.exit().transition()
			.duration(duration)
			.attr('d', function (d) {
				var o = {x: source.x, y: source.y};
				return _diagonal(o, o)
			})
			.remove();

		var icon = svg.selectAll('image.tree-icon')
			.data(icons, function (d) {
				return d.id;
			});

		// Enter any new icon
		var iconEnter = icon.enter().insert('image', 'svg')
			.style('opacity', 1e-6)
			.attr('class', 'tree-icon')
			.attr('height', iconHeight)
			.attr('width', iconWidth)
			.attr('transform', 'translate(' + source.y + ',' + source.x + ')')
			.attr('xlink:href', function (d) {
				return d.data.icon;
			});

		// UPDATE
		var iconUpdate = iconEnter.merge(icon);

		// Transition back to the parent element position
		iconUpdate.transition()
			.duration(duration)
			.attr('transform', function (d) {
				return 'translate(' + (d.y + iconWidth / 2) + ',' + (d.x - iconHeight / 2) + ')';
			})
			.style('opacity', 1);

		// Remove any exiting links
		icon.exit().transition()
			.duration(duration)
			.attr('transform', 'translate(' + source.y + ',' + source.x + ')')
			.style('opacity', 1e-6)
			.remove();
	}

	/**
	 * Collapse children
	 *
	 * @param d
	 * @private
	 */
	function _collapse(d) {
		if ((d.children && typeof d.data.uid === 'undefined')
			|| (d.children && !_isExpanded(d.data.uid))) {
			d._children = d.children;
			d._children.forEach(_collapse);
			d.children = null;
		} else if (d.children) {
			d.children.forEach(_collapse);
		}
	}

	/**
	 * Collapse children for filtered tree by expanded field
	 *
	 * @param d
	 * @private
	 */
	function _collapseFiltered(d) {
		if ((d.children && typeof d.data.uid === 'undefined')
			|| (d.children && !d.data.expanded)) {
			d._children = d.children;
			d._children.forEach(_collapseFiltered);
			d.children = null;
		} else if (d.children) {
			d.children.forEach(_collapseFiltered);
		}
	}

	/**
	 * Check if value is in list
	 *
	 * @param uid
	 * @return {boolean}
	 */
	function _isExpanded(uid) {
		return (typeof storageData.pageTreeState !== 'undefined' && storageData.pageTreeState.contains(uid));
	}

	/**
	 * Toggle children on click.
	 *
	 * @param d
	 * @private
	 */
	function _clickPolygon(d) {
		if (d.children) {
			d._children = d.children;
			d.children = null;
			if (d.data.uid && !isFilteringActive) {
				_removeFromActiveStateList(d.data.uid);
			}
		} else {
			d.children = d._children;
			d._children = null;
			if (d.data.uid && !isFilteringActive) {
				_addToActiveStateList(d.data.uid);
			}
		}
		_update(d);
	}

	/**
	 * Save expanded page in page tree
	 *
	 * @param uid
	 * @private
	 */
	function _addToActiveStateList(uid) {
		var pageTreeState = _getCurrentPageTreeStateStorageData();

		if (!pageTreeState.contains(uid)) {
			pageTreeState.push(uid);
		}

		storage.addItem('pageTreeState', pageTreeState);
	}

	/**
	 * Remove expanded page
	 *
	 * @param uid
	 * @private
	 */
	function _removeFromActiveStateList(uid) {
		var pageTreeState = _getCurrentPageTreeStateStorageData(),
			index = pageTreeState.indexOf(uid);

		if (index > -1) {
			pageTreeState.splice(index, uid);
		}

		storage.addItem('pageTreeState', pageTreeState);
	}

	/**
	 * Current state of page tree
	 * @return {*}
	 * @private
	 */
	function _getCurrentPageTreeStateStorageData() {
		var currentStorageData = storage.getAllData();

		if (typeof currentStorageData.pageTreeState === 'undefined') {
			currentStorageData.pageTreeState = [];
		}

		return currentStorageData.pageTreeState;
	}

	/**
	 * Init contains function
	 *
	 * @private
	 */
	function _initArrayContains() {
		if (!Array.prototype.contains) {
			Array.prototype.contains = function (v) {
				for (var i = 0; i < this.length; i++) {
					if (this[i] === v) return true;
				}
				return false;
			};
		}
	}

	/**
	 * Clicked on rect, check if was double click
	 *
	 * @param d
	 * @private
	 */
	function _clickRect(d) {
		if (clickedRectOnce) {
			_clickRectDouble(d, this);
		} else {
			setTimeout(function () {
				if (clickedRectOnce) {
					_clickRectOnce(d);
				}
			}, 350);
			clickedRectOnce = true;
		}
	}

	/**
	 * If clicked once go to link
	 *
	 * @param d
	 * @private
	 */
	function _clickRectOnce(d) {
		if (d.data.link) {
			var linkUrl = d.data.link;
			F.navigate(linkUrl);
			F.showLoadingScreen();
		}
		clickedRectOnce = false;
	}

	/**
	 * If double click start editing
	 *
	 * @private
	 */
	function _clickRectDouble(d, currentNode) {
		var box = currentNode.getBBox();

		clickedRectOnce = false;

		// Save current active node
		editingOriginalNode = {
			d: d,
			currentNode: currentNode
		};

		$(_getDomSelector('editInput'))
			.val(d.data.name)
			.css('width', box.width + 2)
			.css('left', d.y + xRect + nodeStep)
			.css('top', d.x + nodeStep)
			.show()
			.focus();
	}

	/**
	 * Call ajax to save new page name
	 *
	 * @param newValue
	 * @private
	 */
	function _finishEditing(newValue) {
		if (editingOriginalNode.d.data.name !== newValue) {
			// Set new value for tree node
			/**
			 * @TODO is there a better way to get text element ?
			 */
			d3.select(editingOriginalNode.currentNode.nextElementSibling).text(newValue);
			// Set new name in node
			editingOriginalNode.d.data.name = newValue;

			// Save the new title
			var saveItemData = {
				'action': 'update',
				'table': 'pages',
				'uid': editingOriginalNode.d.data.uid,
				'field': 'title',
				'content': newValue
			};

			F.trigger(F.REQUEST_START);

			$.ajax({
				url: F.getEndpointUrl(),
				method: 'POST',
				data: saveItemData
			}).done(function (data) {
				F.trigger(
					F.UPDATE_PAGES_COMPLETE,
					{
						message: data.message
					}
				);
			}).fail(function (jqXHR) {
				F.trigger(
					F.REQUEST_ERROR,
					{
						message: jqXHR.responseText
					}
				);
			}).always(function () {
				F.trigger(F.REQUEST_COMPLETE);
			});
		}

		_resetEditing();
	}

	/**
	 * Cancel editing of page
	 *
	 * @private
	 */
	function _resetEditing() {
		// reset editing input
		$(_getDomSelector('editInput')).hide();
		editingOriginalNode = null;
	}

	/**
	 * Generate path
	 *
	 * @param s
	 * @param d
	 * @return {string}
	 * @private
	 */
	function _diagonal(s, d) {
		return 'M ' + d.y + ' ' + d.x + ' V ' + s.x + ' H ' + s.y;

		// Curved line
		/*return `M ${s.y} ${s.x}
		 C ${(s.y + d.y) / 2} ${s.x},
		 ${(s.y + d.y) / 2} ${d.x},
		 ${d.y} ${d.x}`;*/

	}

	/**
	 * Count bar width (rect)
	 * @param d
	 * @return {number}
	 * @private
	 */
	function _barWidth(d) {
		return barWidth - d.depth * nodeStep;
	}

	/**
	 * Get selector
	 *
	 * @param key
	 * @return {*|string}
	 * @private
	 */
	function _getDomSelector(key) {
		return domSelectors[key] || '';
	}

	/**
	 * Redraw svg
	 *
	 * @private
	 */
	function _prepareRedraw() {
		// Redraw tree with filtered results
		svg.selectAll('*').remove();
		// Reset links count
		linksCount = 0;
	}

	/**
	 * Remove filtering state
	 *
	 * @private
	 */
	function _resetFilter() {
		_prepareRedraw();
		isFilteringActive = false;
		_update(root);
	}

	/**
	 * Load filtered results with search word
	 * @param searchWord
	 * @private
	 */
	function _loadFilterTree(searchWord) {
		searchRunning = true;

		var dataSend = {
			'searchWord': searchWord
		};

		$.ajax({
			url: F.getFilteringUrl(),
			method: 'POST',
			data: dataSend
		}).done(function (data) {
			if (data.success) {
				isFilteringActive = true;
				_prepareRedraw();

				rootFiltered = d3.hierarchy(data.treeData);
				rootFiltered.children.forEach(_collapseFiltered);

				_update(rootFiltered);
			} else {
				_resetFilter();
			}
		}).fail(function (jqXHR) {
			F.trigger(
				F.REQUEST_ERROR,
				{
					message: jqXHR.responseText
				}
			);
		}).always(function () {
			searchRunning = false;
		});
	}

	/**
	 * Check if node is expanded and has children
	 * @param node
	 * @private
	 */
	function _determinateNodeClassDependingOnChildren(node) {
		if (!node._children && !node.children) {
			return noChildrenClass;
		} else if (node._children && !node.children) {
			return hasChildrenClass;
		} else if (!node._children && node.children) {
			return hasChildrenExpandedClass;
		}
	}

	/**
	 * Return public methods
	 */
	return {
		init: init,
		treeFilter: treeFilter,
		isSearchRunning: isSearchRunning
	}
});
