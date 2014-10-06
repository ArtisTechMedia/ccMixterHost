<?
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
* $Id: upload_page_wide.php 12607 2009-05-13 04:10:13Z fourstones $
*
*/

/*
[meta]
    type     = page
    desc     = _('Single upload page (wide)')
    dataview = upload_page
[/meta]
*/
?>

<?
    if( empty($A['records']) )
        return 'ok';

    /*
     * sorry but by the time IE8 rolled around
     * any hope of doing the DIV version was
     * completely killed.
     */
    $T->Call('upload_page_tbl_layout');
?>
