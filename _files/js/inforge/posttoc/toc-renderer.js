function getTocEntries(post)
{
	var entries = [];
	$(post).find('.posttoc-index').each(function() {
		var entry = new Object();
		entry.txt = $(this).text();
		entry.depth = parseInt($(this).prop('tagName').substring(1)) - 2;
		entry.anchor = '#' + $(this).prop('id');
		entries.push(entry);
	});
	return entries;
}

function getNameFromDepth(depth)
{
	switch (depth) {
	case 0:
		return 'chapter';
	case 1:
		return 'section';
	case 2:
		return 'subsection';
	case 3:
		return 'subsubsection';
	case 4:
		return 'paragraph';
	default:
		return '';
	}
}

function fillTableOfContents(entries, idx, depth, toc)
{
	if (idx >= entries.length)
		return;
	for (var i = idx; i < entries.length; i++) {
		var entry = entries[i];
		if (entry.depth < depth)
			return i;
		var item = $('<li>');
		if (entry.depth > depth) {
			var newtoc = $('<ul>');
			$(item).append(newtoc);
			i = fillTableOfContents(entries, i, depth + 1, newtoc) - 1;
		} else {
			var link = $('<a>', {
				text: entry.txt,
				title: 'Go to ' + getNameFromDepth(depth),
				href: entry.anchor,
			});
			$(link).addClass('posttoc-' + getNameFromDepth(depth))
			$(item).append(link);
		}
		$(toc).append(item);
	}
	return entries.length;
}

function createTocForPost(post)
{
	$(post).off('DOMSubtreeModified', subtreeModifiedHandler)
	$(post).find('div.bbTocBlock-toc').each(function() {
		$(this).empty();
		var toc = $('<ul>');
		$(this).append(toc);
		fillTableOfContents(getTocEntries(post), 0, 0, toc);
	});
	$(post).on('DOMSubtreeModified', subtreeModifiedHandler)
}

function subtreeModifiedHandler()
{
	createTocForPost(this);
}

function initTocForEachPost()
{
	$('article.message, article.resourceBody-main, blockquote.message-body, div.bbCodeDemoBlock').each(function() {
		createTocForPost(this);
	});
}

$(document).ready(initTocForEachPost);
