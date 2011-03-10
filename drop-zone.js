DropZone = {
    droppable:function(elements){

    	jQuery(elements).bind('dragover',function(e){
    		 if (e.preventDefault) e.preventDefault();
             jQuery(this).addClass('over');
        });
    	jQuery(elements).bind('dragstart',function(e){
        	jQuery('#dragged').attr('id', '');
        	jQuery(this).attr('id', 'dragged');
        });
    	jQuery(elements).bind('dragleave',function(e){
        	jQuery(this).removeClass('over');
        });
    	jQuery(elements).bind('drop',function(e){
            if (e.stopPropagation) e.stopPropagation();
            jQuery('#dragged').attr('id', '');
            DropZone.setArticleFromUrl(e.originalEvent.dataTransfer.getData('Text'), this);
            jQuery('#dragged').attr('id', '');
        });
    },
    trash:function(elements) {
    	jQuery(elements).bind('dragover',function(e){
            if (e.preventDefault) e.preventDefault(); // allows us to drop
            return false;
        });
    	jQuery(elements).bind('drop',function(e){
            if (e.preventDefault) e.preventDefault(); // stops redirecting
            var dragged = document.getElementById('dragged');
            if (dragged !== null) {
            	jQuery('#dragged').attr('id', '');
                if (dragged.getAttribute('data-removable') == 'true'){
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
        	var html=jQuery(data);
        	DropZone.droppable(html);
        	jQuery(mainElement).before(html).remove();
        });
    },
    setArticleFromUrl:function(url, element) {
        var block = DropZone.getDataBlockFromElement(element);
        block.url = url;
        DropZone.fetchElement(block, element);
    }
};
jQuery(document).ready(function(){
    DropZone.droppable(document.querySelectorAll("[data-droppable=true]"));
    DropZone.trash(document.querySelector('body'));
});