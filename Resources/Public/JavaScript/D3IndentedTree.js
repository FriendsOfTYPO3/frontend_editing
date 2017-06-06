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
	var margin = {top: 20, right: 20, bottom: 30, left: 10},
		width = 280 - margin.right - margin.left,

		barHeight = 20,
		barWidth = width * .9,

		nodeStep = 10,

		dyText = 2.5,
		dxText = 12,

		xRect = 7,

		circleRadius = 5;

	var i = 0,
		linksCount = 0,
		duration = 400,
		root,
		svg,
		tree;

	/**
	 * Local storage
	 */
	var storage,
		storageData;

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

		// Initialize function
		_update(root);
	}

	/**
	 * Create SVG
	 *
	 * @private
	 */
	function _initSvg() {
		svg = d3.select('#page-tree-wrapper').insert('svg')
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
		var nodes = tree(root), //returns a single node with the properties of d3.tree()
			nodesSort = [];

		var links = nodes.descendants().slice(1);

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
			.attr('transform', function (d) {
				return 'translate(' + source.y + ',' + source.x + ')';
			})
			.style('opacity', 1e-6)
			.call(function () {
				if (linksCount < links.length) {
					linksCount = links.length;
					d3.select('svg').attr('height', (links.length + 2) * barHeight);
				}
			});

		// Add Circle for the nodes
		nodeEnter.append('circle')
			.attr('r', 1e-6)
			.attr('class', function (d) {
				return d._children ? 'has-children' : 'no-children';
			})
			.on('click', _clickCircle);

		nodeEnter.append('rect')
			.attr('y', -barHeight / 2)
			.attr('x', xRect)
			.attr('height', barHeight)
			.attr('width', _barWidth)
			.attr('class', function (d) {
				return d.data.isActive ? 'active' : 'no-active';
			})
			.on('click', _clickRect);

		nodeEnter.append('text')
			.attr('dy', dyText)
			.attr('dx', dxText)
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
		nodeUpdate.select('circle')
			.attr('r', circleRadius)
			.attr('class', function (d) {
				return d._children ? 'has-children' : 'no-children';
			})
			.attr('cursor', 'pointer');

		// Transition exiting nodes to the parent's new position.
		var nodeExit = node.exit().transition()
			.duration(duration)
			.attr('transform', function (d) {
				return 'translate(' + source.y + ',' + source.x + ')';
			})
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
				console.log(source);
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
	function _clickCircle(d) {
		if (d.children) {
			d._children = d.children;
			d.children = null;
			if (d.data.uid) {
				_removeFromActiveStateList(d.data.uid);
			}
		} else {
			d.children = d._children;
			d._children = null;
			if (d.data.uid) {
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
	 * Clicked on rect
	 *
	 * @param d
	 * @private
	 */
	function _clickRect(d) {
		if (d.data.link) {
			var linkUrl = d.data.link;
			F.navigate(linkUrl);
			F.showLoadingScreen();
		}
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
	 * Return public methods
	 */
	return {
		init: init
	}
});
