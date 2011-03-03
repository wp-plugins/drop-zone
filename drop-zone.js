var addEvent = (function(){
	return function (el, type, fn) {
		if (el && el.nodeName || el === window) {
            el.addEventListener(type, fn, false);
            } else if (el && el.length) {
            for (var i = 0; i < el.length; i++) {
                addEvent(el[i], type, fn);
            }
        }
    };
})();

DropZone = {
    activeChanges:[],   
    droppable:function(elements) {
	jQuery(elements).each(function(){
		jQuery(this).removeAttr('data-new');
        });    
        addEvent(elements, 'dragover', function (e) {
            if (e.preventDefault) e.preventDefault(); // allows us to drop
            jQuery(this).addClass('over');
            return false;
        });
        addEvent(elements, 'dragstart', function (e) {
        	jQuery('#dragged').attr('id', '');
        	jQuery(this).attr('id', 'dragged');
        });
        addEvent(elements, 'dragleave', function () {
        	jQuery(this).removeClass('over');
        });
        addEvent(elements, 'drop', function (e) {
            if (e.stopPropagation) e.stopPropagation();
            jQuery('#dragged').attr('id', '');
            DropZone.setArticleFromUrl(e.dataTransfer.getData('Text'), this);
            jQuery('#dragged').attr('id', '');
            return false;
        });
    },
    trash:function(elements) {
        addEvent(elements, 'dragover', function (e) {
            if (e.preventDefault) e.preventDefault(); // allows us to drop
            return false;
        });
        addEvent(elements, 'drop', function (e) {
            if (e.preventDefault) e.preventDefault(); // stops redirecting
            var dragged = document.getElementById('dragged');
            if (dragged !== null) {
            	jQuery('#dragged').attr('id', '');
                if (dragged.getAttribute('data-removable') == 'true') {
                    DropZone.removeElement(dragged);
                }
            }
        });
    },
    removeElement:function(element) {
    	position = element.getAttribute('data-position');
    	url = element.getAttribute('data-url');
    	jQuery.post(DropZoneFront.ajaxurl, { action : 'drop-zone-remove', nonce : DropZoneFront.nonce, position: position, url: url }, function(data) { 
    		jQuery('[data-position=' + position + ']').html('').addClass('droppable');
    	});    	
    },
    getDataBlockFromElement : function (element) {
        var block = {};
        block.index = element.getAttribute('data-addable') == 'true' ? -1 : element.getAttribute('data-index');
        block.position = element.getAttribute('data-position');
        return block;
    },
    fetchElement:function(infoBlock, mainElement) {    	
        jQuery.get(DropZoneFront.ajaxurl, { action : 'drop-zone-submit', nonce : DropZoneFront.nonce, infoBlock: infoBlock }, function(data) {
            var targets = jQuery('[data-position=' + infoBlock.position + '][data-index="' + infoBlock.index + '"]');
            data=jQuery(data).attr('data-new','true');
            if (mainElement.getAttribute('data-position') == 'fake') {
                var el = jQuery('div[data-position=fake][data-addable=true]');
                el.before(data);
            } else {
            	jQuery(mainElement).before(data.addClass(infoBlock.first_last));
                if (!(mainElement.getAttribute('data-addable') == 'true') ||
                        (mainElement.getAttribute('data-removable') == 'true' && mainElement.getAttribute('data-index') == -1)) {
                	jQuery(targets).remove();
                } else if (mainElement.getAttribute('data-removable')) {
                	jQuery(mainElement).remove();
                }
            }
            var elements = document.querySelectorAll('[data-position=' + infoBlock.position + '][data-index="' + infoBlock.index + '"][data-droppable=true][data-new=true]');           
            DropZone.droppable(elements);
        });
    },
    setArticleFromUrl:function(url, element) {
        var block = DropZone.getDataBlockFromElement(element);
        block.url = url;
        if (!(element.getAttribute('data-addable') == 'true')) {
            DropZone.activeChanges[block.position + '-' + block.index] = block;
        }
        DropZone.fetchElement(block, element);
    }
};
addEvent(document, 'DOMContentLoaded', function() {
    DropZone.droppable(document.querySelectorAll("[data-droppable=true]"));
    DropZone.trash(document.querySelector('body'));
    document.defaultAction = false;
});