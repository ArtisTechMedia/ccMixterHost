/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: cc-topics.js 8960 2008-02-11 22:15:54Z fourstones $
*
*/


function cc_update_hot_topics()
{
    var box = $('cc_hot_topic_picker');
    if( !box )
        return;
    if( !box.observing )
    {   
        box.observing = true;
        Event.observe(box, 'change', function (e)
            {
                cc_update_hot_topics();
            }
        );

    }
    var url = query_url + 't=reviews_recent&datasource=topics&f=html&sinced=' + box.options[ box.selectedIndex ].value;
    var myAjax = new Ajax.Updater( 
                    'cc_hot_topics_div',
                     url, 
                    { method: 'get' });    

}

cc_update_hot_topics();

$$('.xlat_link').each( function(element) {
      element.href = 'javascript:// xlat link ' + element.id;


      Event.observe(element,'click', function (event)
            {
                var id = Event.element(event).id;
                var topic_id = id.match(/[0-9]+$/);
                var xlat_id = id.match(/_([0-9]+)_/)[1];
                var url = home_url + 'topics/gettext/' + xlat_id;
                var ajax = new Ajax.Updater(
                                'topic_text_' + topic_id,
                                url,
                                { method: 'get' });
            });
});
