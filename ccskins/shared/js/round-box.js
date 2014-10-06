
rbox_counter = 1;

function cc_round_box( e ) {
    if( window.disable_round_box )
        return;
    e = $(e);
    if( !e )
        return;
    var id_o = 'rboxo_' + (rbox_counter++);
    var id = 'rbox_' + (rbox_counter++);
    var h2_id = 'h2_rbox_' + rbox_counter;
    var wid = Element.getWidth(e) - 24;

    var h2 = e.getElementsByTagName('H2');
    var caption = '';
    if( h2.length > 0 ) {
        h2 = h2[0];
        caption = h2.innerHTML;
        e.removeChild(h2);
    }

    var html = '<div id="' + id_o + '" style="display:none;width:'+wid+'px;" class="cssbox"><div class="cssbox_head"><h2 id="' + 
                h2_id + '" >' + caption + '</h2></div><div id="' + id + '" class="cssbox_body">  </div></div>';
    new Insertion.Before(e,html);
    var e = Element.remove(e);
    e.style.width = '';
    $(id).appendChild(e);
    $(id_o).style.display = 'block';
    if( !caption.length )
        Element.addClassName(h2_id,'no_box_header');
    Element.removeClassName(e,'box'); // this allows multiple calls to cc_round_box as elements arrive
}

function cc_round_boxes( className ) {
    if( window.disable_row_box )
        return;
    $$('.box').each( cc_round_box );
}
